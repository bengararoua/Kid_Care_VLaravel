<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        try {
            $notifications = Notification::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
            
            $unreadCount = Notification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->count();
            
            return response()->json([
                'notifications' => $notifications,
                'unreadCount' => $unreadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notifications' => [],
                'unreadCount' => 0
            ]);
        }
    }

    public function markAsRead($id)
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
            
            if ($notification) {
                $notification->update(['is_read' => true]);
                return response()->json(['success' => true]);
            }
            
            return response()->json(['error' => 'Notification not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            Notification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
            
            if ($notification) {
                $notification->delete();
                return response()->json(['success' => true]);
            }
            
            return response()->json(['error' => 'Notification not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroyAll()
    {
        try {
            Notification::where('user_id', Auth::id())->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}