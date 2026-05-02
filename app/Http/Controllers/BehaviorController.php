<?php

namespace App\Http\Controllers;

use App\Models\BehaviorLog;
use App\Models\Child;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BehaviorController extends Controller
{
    public function index($child_id)
    {
        try {
            $child = Child::find($child_id);
            if (!$child) {
                return response()->json(['error' => 'Child not found'], 404);
            }
            
            $logs = BehaviorLog::where('child_id', $child_id)
                ->orderBy('log_date', 'desc')
                ->get();
            
            return response()->json($logs);
            
        } catch (\Exception $e) {
            \Log::error('Error in BehaviorController@index: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'child_id' => 'required|exists:children,id',
                'focus_level' => 'required|integer|min:1|max:5',
                'mood' => 'required|string',
                'sleep_hours' => 'required|numeric|min:0|max:24',
                'social_interaction' => 'nullable|integer|min:1|max:5',
                'note' => 'nullable|string',
                'log_date' => 'required|date'
            ]);

            $log = BehaviorLog::create([
                'child_id' => $validated['child_id'],
                'user_id' => Auth::id(),
                'focus_level' => $validated['focus_level'],
                'mood' => $validated['mood'],
                'sleep_hours' => $validated['sleep_hours'],
                'social_interaction' => $validated['social_interaction'] ?? 3,
                'note' => $validated['note'] ?? null,
                'log_date' => $validated['log_date']
            ]);
            
            // Créer une notification pour le parent
            $child = Child::find($validated['child_id']);
            if ($child && $child->parent_id) {
                $title = 'New Behavior Log';
                $message = "A new behavior log has been added for {$child->name}";
                
                Notification::create([
                    'user_id' => $child->parent_id,
                    'type' => 'behavior_alert',
                    'title' => $title,
                    'message' => $message,
                    'data' => json_encode(['child_id' => $child->id, 'log_id' => $log->id]),
                    'is_read' => false
                ]);
            }
            
            // Donner des points si c'est un parent (GAMIFICATION)
            $user = Auth::user();
            if ($user->isParent()) {
                // Vérifier si déjà logué aujourd'hui
                if ($user->last_log_date != now()->toDateString()) {
                    $user->increment('points', 10);
                    $user->last_log_date = now()->toDateString();
                    $user->save();
                    
                    \Log::info('Points ajoutés pour user: ' . $user->id . ' - Nouveau total: ' . $user->points);
                }
            }
            
            return response()->json($log, 201);
            
        } catch (\Exception $e) {
            \Log::error('Error in BehaviorController@store: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $log = BehaviorLog::findOrFail($id);
            $user = Auth::user();
            
            if ($log->user_id !== $user->id && !$user->isParent()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $log->update($request->only(['focus_level', 'mood', 'sleep_hours', 'social_interaction', 'note']));
            return response()->json($log);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $log = BehaviorLog::findOrFail($id);
            $user = Auth::user();
            
            if ($log->user_id !== $user->id && !$user->isParent()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $log->delete();
            return response()->json(['message' => 'Log deleted']);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
