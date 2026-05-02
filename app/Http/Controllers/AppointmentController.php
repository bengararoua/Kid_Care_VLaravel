<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    public function index()
    {
        try {
            $appointments = Appointment::where('sender_id', Auth::id())
                ->orWhere('receiver_id', Auth::id())
                ->with(['sender', 'receiver'])
                ->orderBy('scheduled_at', 'desc')
                ->get();
            
            return response()->json($appointments);
        } catch (\Exception $e) {
            Log::error('Error fetching appointments: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch appointments'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'title' => 'nullable|string|max:255',
                'scheduled_at' => 'required|date|after:now',
                'duration' => 'required|integer|min:15|max:180',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'type' => 'required|in:video,phone,in_person'
            ]);

            $appointment = DB::transaction(function () use ($request) {
                $appointment = Appointment::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $request->receiver_id,
                    'title' => $request->title ?? 'Rendez-vous',
                    'scheduled_at' => $request->scheduled_at,
                    'duration' => $request->duration,
                    'location' => $request->location,
                    'notes' => $request->notes,
                    'type' => $request->type,
                    'status' => 'pending'
                ]);

                // UN SEUL message créé ici
                $messageContent = $this->formatAppointmentMessage($appointment);
                $appointmentLink = "/appointments/{$appointment->id}";
                $fullMessage = $messageContent . "\n\n🔗 " . $appointmentLink;
                
                Message::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $request->receiver_id,
                    'content' => $fullMessage,
                    'is_appointment' => true,
                    'appointment_id' => $appointment->id,
                    'is_read' => false
                ]);

                return $appointment;
            });

            $appointment->load(['sender', 'receiver']);
            
            try {
                broadcast(new \App\Events\AppointmentCreated($appointment))->toOthers();
            } catch (\Exception $e) {
                Log::warning('WebSocket broadcast failed: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Appointment created successfully',
                'appointment' => $appointment
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating appointment: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create appointment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $appointment = Appointment::where(function($query) use ($id) {
                $query->where('id', $id)
                      ->where(function($q) {
                          $q->where('sender_id', Auth::id())
                            ->orWhere('receiver_id', Auth::id());
                      });
            })->with(['sender', 'receiver'])->firstOrFail();
            
            return response()->json($appointment);
        } catch (\Exception $e) {
            Log::error('Error fetching appointment: ' . $e->getMessage());
            return response()->json(['error' => 'Appointment not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $appointment = Appointment::where(function($query) use ($id) {
                $query->where('id', $id)
                      ->where(function($q) {
                          $q->where('sender_id', Auth::id())
                            ->orWhere('receiver_id', Auth::id());
                      });
            })->firstOrFail();
            
            $request->validate([
                'scheduled_at' => 'sometimes|date|after:now',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'title' => 'nullable|string|max:255',
                'duration' => 'sometimes|integer|min:15|max:180',
                'type' => 'sometimes|in:video,phone,in_person',
                'status' => 'sometimes|in:pending,confirmed,cancelled,completed'
            ]);
            
            $appointment->update($request->only([
                'scheduled_at', 'location', 'notes', 'title', 'duration', 'type', 'status'
            ]));
            
            if ($request->has('status') && $request->status != $appointment->getOriginal('status')) {
                $otherPartyId = $appointment->sender_id === Auth::id() ? $appointment->receiver_id : $appointment->sender_id;
                $statusMessages = [
                    'confirmed' => "✅ Le rendez-vous a été confirmé",
                    'cancelled' => "❌ Le rendez-vous a été annulé",
                    'completed' => "✓ Le rendez-vous est terminé"
                ];
                
                if (isset($statusMessages[$request->status])) {
                    Message::create([
                        'sender_id' => Auth::id(),
                        'receiver_id' => $otherPartyId,
                        'content' => $statusMessages[$request->status],
                        'is_appointment' => true,
                        'appointment_id' => $appointment->id,
                        'is_read' => false
                    ]);
                }
            }
            
            try {
                broadcast(new \App\Events\AppointmentUpdated($appointment))->toOthers();
            } catch (\Exception $e) {
                Log::warning('WebSocket broadcast failed: ' . $e->getMessage());
            }
            
            return response()->json([
                'message' => 'Appointment updated successfully',
                'appointment' => $appointment
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating appointment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update appointment'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,confirmed,cancelled,completed'
            ]);
            
            $appointment = Appointment::where(function($query) use ($id) {
                $query->where('id', $id)
                      ->where(function($q) {
                          $q->where('sender_id', Auth::id())
                            ->orWhere('receiver_id', Auth::id());
                      });
            })->firstOrFail();
            
            $oldStatus = $appointment->status;
            $appointment->status = $request->status;
            $appointment->save();
            
            if ($oldStatus !== $request->status) {
                $otherPartyId = $appointment->sender_id === Auth::id() ? $appointment->receiver_id : $appointment->sender_id;
                $statusMessages = [
                    'confirmed' => "✅ Le rendez-vous a été confirmé",
                    'cancelled' => "❌ Le rendez-vous a été annulé",
                    'completed' => "✓ Le rendez-vous est terminé"
                ];
                
                if (isset($statusMessages[$request->status])) {
                    Message::create([
                        'sender_id' => Auth::id(),
                        'receiver_id' => $otherPartyId,
                        'content' => $statusMessages[$request->status],
                        'is_appointment' => true,
                        'appointment_id' => $appointment->id,
                        'is_read' => false
                    ]);
                }
            }
            
            try {
                broadcast(new \App\Events\AppointmentUpdated($appointment))->toOthers();
            } catch (\Exception $e) {
                Log::warning('WebSocket broadcast failed: ' . $e->getMessage());
            }
            
            return response()->json([
                'message' => 'Appointment status updated successfully',
                'appointment' => $appointment
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating appointment status: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update appointment status'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $appointment = Appointment::where('sender_id', Auth::id())
                ->findOrFail($id);
            
            $messageContent = "❌ Le rendez-vous du " . 
                Carbon::parse($appointment->scheduled_at)->format('d/m/Y à H:i') . 
                " a été annulé";
            
            Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $appointment->receiver_id,
                'content' => $messageContent,
                'is_appointment' => true,
                'appointment_id' => $appointment->id,
                'is_read' => false
            ]);
            
            $appointment->delete();
            
            return response()->json(['message' => 'Appointment deleted successfully']);
            
        } catch (\Exception $e) {
            Log::error('Error deleting appointment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete appointment'], 500);
        }
    }

    public function getUpcomingReminders()
    {
        try {
            $reminders = Appointment::where(function($query) {
                    $query->where('sender_id', Auth::id())
                          ->orWhere('receiver_id', Auth::id());
                })
                ->where('status', 'confirmed')
                ->where('scheduled_at', '>', now())
                ->where('scheduled_at', '<', now()->addDays(7))
                ->with(['sender', 'receiver'])
                ->orderBy('scheduled_at', 'asc')
                ->get();
            
            return response()->json($reminders);
            
        } catch (\Exception $e) {
            Log::error('Error fetching reminders: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch reminders'], 500);
        }
    }

    private function formatAppointmentMessage($appointment)
    {
        $date = Carbon::parse($appointment->scheduled_at);
        
        $typeMap = [
            'video' => '📹 Visioconférence',
            'phone' => '📞 Téléphone',
            'in_person' => '🏢 En personne'
        ];
        
        $message = "📅 **NOUVEAU RENDEZ-VOUS**\n\n";
        $message .= "**Titre:** {$appointment->title}\n";
        $message .= "**Type:** {$typeMap[$appointment->type]}\n";
        $message .= "**Date:** {$date->locale('fr')->isoFormat('dddd D MMMM YYYY')}\n";
        $message .= "**Heure:** {$date->format('H:i')}\n";
        $message .= "**Durée:** {$appointment->duration} minutes\n";
        
        if ($appointment->location) {
            $message .= "**Lieu:** {$appointment->location}\n";
        }
        
        if ($appointment->notes) {
            $message .= "\n**Notes:**\n{$appointment->notes}\n";
        }
        
        return $message;
    }
}