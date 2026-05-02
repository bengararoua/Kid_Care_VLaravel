<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\BehaviorLog;
use App\Models\Recommendation;
use App\Models\Routine;
use App\Models\ActionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function exportChildReport($childId)
    {
        try {
            $child = Child::with(['parent', 'psychologist', 'teacher'])->findOrFail($childId);
            
            // Vérifier les permissions
            $user = Auth::user();
            if ($user->role !== 'psychologist' && $user->id !== $child->parent_id && $user->id !== $child->teacher_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            // Récupérer les données des 30 derniers jours
            $logs = BehaviorLog::where('child_id', $childId)
                ->with('user')
                ->where('log_date', '>=', Carbon::now()->subDays(30))
                ->orderBy('log_date', 'desc')
                ->get();
            
            $recommendations = Recommendation::where('child_id', $childId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $routines = Routine::where('child_id', $childId)
                ->orderBy('day_of_week', 'asc')
                ->orderBy('time', 'asc')
                ->get();
            
            $actionPlan = ActionPlan::where('child_id', $childId)->latest()->first();
            
            // Préparer les données pour les graphiques
            $last7Days = collect();
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dayLogs = $logs->filter(function($log) use ($date) {
                    return Carbon::parse($log->log_date)->format('Y-m-d') === $date->format('Y-m-d');
                });
                
                $last7Days->push([
                    'date' => $date->format('d/m'),
                    'focus' => $dayLogs->avg('focus_level') ?? 0,
                    'sleep' => $dayLogs->avg('sleep_hours') ?? 0,
                    'social' => $dayLogs->avg('social_interaction') ?? 0,
                ]);
            }
            
            // Statistiques globales
            $stats = [
                'avg_focus' => round($logs->avg('focus_level') ?? 0, 1),
                'avg_sleep' => round($logs->avg('sleep_hours') ?? 0, 1),
                'avg_social' => round($logs->avg('social_interaction') ?? 0, 1),
                'total_logs' => $logs->count(),
                'positive_mood' => $logs->where('mood', 'happy')->count(),
                'neutral_mood' => $logs->where('mood', 'neutral')->count(),
                'sad_mood' => $logs->where('mood', 'sad')->count(),
                'completed_recommendations' => $recommendations->where('is_completed', true)->count(),
                'total_recommendations' => $recommendations->count(),
                'completion_rate' => $recommendations->count() > 0 ? round(($recommendations->where('is_completed', true)->count() / $recommendations->count()) * 100, 1) : 0,
            ];
            
            // Distribution des humeurs
            $moodDistribution = [
                'labels' => ['😊 Heureux', '😐 Neutre', '😔 Triste'],
                'data' => [$stats['positive_mood'], $stats['neutral_mood'], $stats['sad_mood']],
                'colors' => ['#10b981', '#f59e0b', '#ef4444']
            ];
            
            $data = [
                'child' => $child,
                'logs' => $logs,
                'recommendations' => $recommendations,
                'routines' => $routines,
                'actionPlan' => $actionPlan,
                'stats' => $stats,
                'last7Days' => $last7Days,
                'moodDistribution' => $moodDistribution,
                'generated_date' => Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm'),
                'psychologist' => $child->psychologist,
                'teacher' => $child->teacher,
                'parent' => $child->parent,
                'user_role' => $user->role
            ];
            
            $pdf = Pdf::loadView('pdf.child-report', $data);
            $pdf->setPaper('A4', 'portrait');
            
            return $pdf->download("rapport_{$child->name}_{$child->id}.pdf");
            
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}