<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\BehaviorLog;
use App\Models\Message;
use App\Models\Recommendation;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = [];

        if ($user->isParent()) {
            $children = Child::where('parent_id', $user->id)
                ->with(['behaviors' => function($q) {
                    $q->orderBy('log_date', 'desc')->limit(5);
                }, 'recommendations'])
                ->get();

            $recentLogs = BehaviorLog::whereIn('child_id', $children->pluck('id'))
                ->with('child')
                ->orderBy('log_date', 'desc')
                ->limit(10)
                ->get();

            $unreadMessages = Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            // Vérifier les alertes pour chaque enfant
            foreach ($children as $child) {
                $recentBehaviors = $child->behaviors()->orderBy('log_date', 'desc')->take(7)->get();
                if ($recentBehaviors->count() >= 3) {
                    $avgFocus = $recentBehaviors->avg('focus_level');
                    
                    if ($avgFocus < 2.5) {
                        // Vérifier si une notification existe déjà
                        $existingNotif = Notification::where('user_id', $user->id)
                            ->where('type', 'behavior_alert')
                            ->where('data', 'like', '%' . $child->id . '%')
                            ->where('created_at', '>', now()->subDay())
                            ->first();
                        
                        if (!$existingNotif) {
                            Notification::create([
                                'user_id' => $user->id,
                                'type' => 'behavior_alert',
                                'title' => '⚠️ Attention Needed',
                                'message' => "{$child->name}'s focus level has dropped significantly. Please review the recent logs.",
                                'data' => json_encode(['child_id' => $child->id]),
                                'is_read' => false
                            ]);
                        }
                    }
                }
            }

            $data = compact('children', 'recentLogs', 'unreadMessages');

        } 
        // Teacher : voit seulement les enfants qui lui sont assignés
        elseif ($user->isTeacher()) {
            // Ne voir que les enfants assignés à ce teacher
            $children = Child::where('teacher_id', $user->id)
                ->with(['parent', 'behaviors' => function($q) {
                    $q->orderBy('log_date', 'desc')->limit(1);
                }])
                ->get();

            $todayLogs = BehaviorLog::where('user_id', $user->id)
                ->whereDate('log_date', today())
                ->with('child')
                ->get();

            $unreadMessages = Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            $highRiskChildren = $children->filter(function($child) {
                $recentLogs = $child->behaviors->take(7);
                if ($recentLogs->isEmpty()) return false;
                $avgFocus = $recentLogs->avg('focus_level');
                return $avgFocus < 2.5;
            });

            $data = compact('children', 'todayLogs', 'unreadMessages', 'highRiskChildren');

        } 
        // Psychologist : voit seulement les enfants qui lui sont assignés
        elseif ($user->isPsychologist()) {
            // Ne voir que les enfants assignés à ce psychologue
            $children = Child::where('psychologist_id', $user->id)
                ->with(['parent', 'behaviors' => function($q) {
                    $q->orderBy('log_date', 'desc')->limit(7);
                }, 'recommendations'])
                ->get();

            $unreadMessages = Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            $highRiskChildren = $children->filter(function($child) {
                $recentLogs = $child->behaviors->take(7);
                if ($recentLogs->isEmpty()) return false;
                $avgFocus = $recentLogs->avg('focus_level');
                $avgSleep = $recentLogs->avg('sleep_hours');
                return $avgFocus < 2.5 || $avgSleep < 6;
            });

            $pendingRecs = Recommendation::whereIn('child_id', $children->pluck('id'))
                ->where('is_completed', false)
                ->with('child')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $data = compact('children', 'unreadMessages', 'highRiskChildren', 'pendingRecs');
        }

        return view('dashboard', array_merge(['user' => $user], $data));
    }
}