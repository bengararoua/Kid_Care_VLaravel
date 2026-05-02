<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        // Récupérer tous les contacts avec qui l'utilisateur a échangé des messages
        $contacts = collect();
        
        // Messages envoyés
        $sentContacts = Message::where('sender_id', $userId)
            ->with('receiver')
            ->get()
            ->pluck('receiver')
            ->unique('id');
        
        // Messages reçus
        $receivedContacts = Message::where('receiver_id', $userId)
            ->with('sender')
            ->get()
            ->pluck('sender')
            ->unique('id');
        
        $contacts = $sentContacts->merge($receivedContacts)->unique('id')->values();
        
        // Ajouter le nombre de messages non lus par contact
        foreach ($contacts as $contact) {
            $contact->unread_count = Message::where('sender_id', $contact->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();
        }
        
        $unreadCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'contacts' => $contacts,
            'unreadCount' => $unreadCount
        ]);
    }
    
    public function getConversation($userId)
    {
        $authId = Auth::id();
        
        $messages = Message::where(function($query) use ($authId, $userId) {
            $query->where('sender_id', $authId)
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($authId, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();
        
        // Marquer les messages comme lus
        Message::where('sender_id', $userId)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json(['messages' => $messages]);
    }
    
    public function send(Request $request, $userId)
    {
        $request->validate([
            'content' => 'required|string|max:5000'
        ]);
        
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $userId,
            'content' => $request->content,
            'is_read' => false,
            'is_appointment' => $request->is_appointment ?? false,
            'appointment_id' => $request->appointment_id ?? null
        ]);
        
        $message->load('sender', 'receiver');
        
        broadcast(new \App\Events\MessageSent($message))->toOthers();
        
        return response()->json($message, 201);
    }
    
    public function getUnreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();
        
        return response()->json(['count' => $count]);
    }
    
    public function markAsRead($messageId)
    {
        $message = Message::where('id', $messageId)
            ->where('receiver_id', Auth::id())
            ->first();
        
        if ($message) {
            $message->is_read = true;
            $message->save();
        }
        
        return response()->json(['success' => true]);
    }
}