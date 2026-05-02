<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\BehaviorLog;
use Illuminate\Http\Request;

class InsightController extends Controller
{
    public function getInsights($child_id)
    {
        try {
            $child = Child::with('behaviors')->findOrFail($child_id);
            
            $logs = $child->behaviors()
                ->where('log_date', '>=', now()->subDays(30))
                ->orderBy('log_date')
                ->get();
            
            if ($logs->isEmpty()) {
                return response()->json([
                    'message' => 'Not enough data for insights',
                    'risk_level' => 'insufficient_data',
                    'avg_focus' => null,
                    'avg_sleep' => null,
                    'pattern' => [],
                    'weekly_comparison' => null,
                    'total_logs' => 0
                ]);
            }
            
            $avgFocus = $logs->avg('focus_level');
            $avgSleep = $logs->avg('sleep_hours');
            $avgSocial = $logs->avg('social_interaction');
            $riskLevel = $this->calculateRiskLevel($avgFocus, $avgSleep, $avgSocial);
            $pattern = $this->detectPatterns($logs);
            $weeklyComparison = $this->getWeeklyComparison($logs);
            
            return response()->json([
                'child_name' => $child->name,
                'risk_level' => $riskLevel,
                'avg_focus' => round($avgFocus, 1),
                'avg_sleep' => round($avgSleep, 1),
                'avg_social' => round($avgSocial, 1),
                'pattern' => $pattern,
                'weekly_comparison' => $weeklyComparison,
                'total_logs' => $logs->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in InsightController: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    private function calculateRiskLevel($focus, $sleep, $social)
    {
        $score = 0;
        if ($focus < 2.5) $score += 2;
        elseif ($focus < 3.5) $score += 1;
        if ($sleep < 6) $score += 2;
        elseif ($sleep < 7) $score += 1;
        if ($social < 2.5) $score += 2;
        elseif ($social < 3.5) $score += 1;
        
        if ($score >= 4) return 'high';
        if ($score >= 2) return 'medium';
        return 'low';
    }
    
    private function detectPatterns($logs)
    {
        $patterns = [];
        
        $lowSleepLogs = $logs->filter(fn($l) => $l->sleep_hours < 7);
        if ($lowSleepLogs->count() > 0) {
            $avgFocusLowSleep = $lowSleepLogs->avg('focus_level');
            $avgFocusNormalSleep = $logs->filter(fn($l) => $l->sleep_hours >= 7)->avg('focus_level');
            if ($avgFocusLowSleep && $avgFocusNormalSleep && $avgFocusLowSleep < $avgFocusNormalSleep) {
                $patterns[] = 'Low sleep correlates with lower focus levels';
            }
        }
        
        $recentLogs = $logs->take(7);
        $olderLogs = $logs->slice(7, 7);
        if ($recentLogs->count() >= 3 && $olderLogs->count() >= 3) {
            $recentAvg = $recentLogs->avg('focus_level');
            $olderAvg = $olderLogs->avg('focus_level');
            if ($recentAvg < $olderAvg - 0.5) {
                $patterns[] = 'Focus level has decreased compared to previous period';
            } elseif ($recentAvg > $olderAvg + 0.5) {
                $patterns[] = 'Focus level is improving!';
            }
        }
        
        return $patterns;
    }
    
    private function getWeeklyComparison($logs)
    {
        $currentWeek = $logs->filter(fn($l) => $l->log_date >= now()->subDays(7));
        $previousWeek = $logs->filter(fn($l) => $l->log_date >= now()->subDays(14) && $l->log_date < now()->subDays(7));
        
        return [
            'current_week_focus' => round($currentWeek->avg('focus_level') ?? 0, 1),
            'previous_week_focus' => round($previousWeek->avg('focus_level') ?? 0, 1),
            'change' => round(($currentWeek->avg('focus_level') ?? 0) - ($previousWeek->avg('focus_level') ?? 0), 1)
        ];
    }
}