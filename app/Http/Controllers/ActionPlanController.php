<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\ActionPlan;
use App\Models\BehaviorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionPlanController extends Controller
{
    // Générer un plan d'action basé sur les logs récents
    public function generate($childId)
    {
        try {
            $child = Child::findOrFail($childId);
            
            // Vérifier les permissions
            $user = Auth::user();
            if ($user->isParent() && $child->parent_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            if ($user->isTeacher() && $child->teacher_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            if ($user->isPsychologist() && $child->psychologist_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            // Récupérer les 7 derniers logs
            $recentLogs = BehaviorLog::where('child_id', $childId)
                ->orderBy('log_date', 'desc')
                ->take(7)
                ->get();
            
            if ($recentLogs->isEmpty()) {
                // Retourner un plan par défaut si pas assez de données
                return response()->json([
                    'risk_level' => 'low',
                    'generated_date' => now()->toDateString(),
                    'morning_activities' => ['📝 Pas assez de données pour générer un plan personnalisé'],
                    'afternoon_activities' => ['📝 Continuez à enregistrer des logs quotidiens'],
                    'evening_activities' => ['📝 7 jours de logs sont nécessaires pour un plan complet'],
                    'communication_tips' => ['💬 Enregistrez plus de données pour des conseils personnalisés'],
                    'games_activities' => ['🎮 Revenez après quelques jours de suivi']
                ]);
            }
            
            // Calculer les moyennes
            $avgFocus = $recentLogs->avg('focus_level');
            $avgSleep = $recentLogs->avg('sleep_hours');
            $avgSocial = $recentLogs->avg('social_interaction');
            
            // Déterminer le niveau de risque
            $riskLevel = $this->calculateRiskLevel($avgFocus, $avgSleep, $avgSocial);
            
            // Générer le plan d'action
            $actionPlan = $this->createActionPlan($child, $riskLevel, $recentLogs);
            
            return response()->json($actionPlan);
            
        } catch (\Exception $e) {
            \Log::error('Error generating action plan: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // Récupérer le dernier plan d'action
    public function getLatest($childId)
    {
        try {
            $plan = ActionPlan::where('child_id', $childId)
                ->orderBy('generated_date', 'desc')
                ->first();
            
            if (!$plan) {
                return response()->json(['message' => 'Aucun plan trouvé'], 404);
            }
            
            return response()->json($plan);
            
        } catch (\Exception $e) {
            \Log::error('Error getting latest plan: ' . $e->getMessage());
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
    
    private function createActionPlan($child, $riskLevel, $logs)
    {
        // Plans selon le niveau de risque
        $plans = [
            'low' => [
                'morning' => [
                    '🌅 Réveil en douceur avec 5 minutes de câlins',
                    '🥣 Petit-déjeuner équilibré ensemble',
                    '📋 Faire une liste des tâches de la journée avec l\'enfant'
                ],
                'afternoon' => [
                    '🎨 Activité créative (dessin, peinture, pâte à modeler)',
                    '🏃‍♂️ 30 minutes de jeu en extérieur',
                    '📚 Lecture d\'une histoire ensemble'
                ],
                'evening' => [
                    '🍽️ Dîner en famille sans écrans',
                    '🛁 Rituel du bain relaxant',
                    '📖 Histoire du soir et câlins'
                ],
                'communication' => [
                    '🗣️ Poser des questions ouvertes sur sa journée',
                    '👂 Écouter activement sans jugement',
                    '🎉 Féliciter les efforts, pas seulement les résultats'
                ],
                'games' => [
                    '🧩 Jeux de société éducatifs',
                    '🎲 Memory ou puzzles',
                    '🎭 Jeux d\'imitation'
                ]
            ],
            'medium' => [
                'morning' => [
                    '🌅 Réveil progressif avec musique douce',
                    '🥣 Petit-déjeuner protéiné pour l\'énergie',
                    '📝 Établir un planning visuel de la journée'
                ],
                'afternoon' => [
                    '🧘 Exercices de respiration (5 minutes)',
                    '🎯 Jeux de concentration (Lego, puzzle)',
                    '🚶‍♂️ Promenade calme dans la nature'
                ],
                'evening' => [
                    '🍽️ Dîner calme sans stimulation',
                    '🛁 Bain aux huiles essentielles (lavande)',
                    '📖 Lecture calmante et discussion'
                ],
                'communication' => [
                    '💬 Utiliser des phrases courtes et claires',
                    '😊 Valider ses émotions ("Je comprends que tu sois...")',
                    '🎯 Proposer des choix limités (2-3 options)'
                ],
                'games' => [
                    '🎯 Jeux d\'équilibre',
                    '🧘 Yoga pour enfants',
                    '🎨 Coloriage mandalas'
                ]
            ],
            'high' => [
                'morning' => [
                    '🌅 Réveil anticipé pour éviter la pression',
                    '🥣 Petit-déjeuner riche en protéines',
                    '📋 Routine visuelle avec pictogrammes'
                ],
                'afternoon' => [
                    '🛑 Créer un coin calme avec coussins',
                    '🤗 Activité sensorielle (bouteille magique, sable)',
                    '👂 Écoute de musique relaxante'
                ],
                'evening' => [
                    '🌙 Réduire les écrans 2h avant le coucher',
                    '🛏️ Rituel de coucher structuré',
                    '📝 Journal de gratitude'
                ],
                'communication' => [
                    '🤝 Utiliser le toucher rassurant',
                    '📏 Parler avec un ton calme et bas',
                    '⏰ Donner des avertissements avant les transitions'
                ],
                'games' => [
                    '🎭 Jeux de rôle pour exprimer les émotions',
                    '🧸 Jeu avec pâte à modeler anti-stress',
                    '🃏 Cartes des émotions'
                ]
            ]
        ];
        
        $selectedPlan = $plans[$riskLevel];
        
        // Ajouter des conseils personnalisés basés sur les logs
        $customTips = [];
        if ($logs->avg('focus_level') < 2.5) {
            $customTips[] = '⚠️ Difficultés de concentration détectées → Privilégiez les activités courtes (15-20 min)';
        }
        if ($logs->avg('sleep_hours') < 7) {
            $customTips[] = '😴 Manque de sommeil → Essayez le coucher 30 minutes plus tôt';
        }
        if ($logs->avg('social_interaction') < 2.5) {
            $customTips[] = '👥 Interactions sociales faibles → Organisez une date de jeu avec 1 ami seulement';
        }
        
        // Sauvegarder ou mettre à jour le plan en base de données
        $actionPlan = ActionPlan::updateOrCreate(
            [
                'child_id' => $child->id,
                'generated_date' => now()->toDateString()
            ],
            [
                'risk_level' => $riskLevel,
                'morning_activities' => $selectedPlan['morning'],
                'afternoon_activities' => $selectedPlan['afternoon'],
                'evening_activities' => $selectedPlan['evening'],
                'communication_tips' => array_merge($selectedPlan['communication'], $customTips),
                'games_activities' => $selectedPlan['games']
            ]
        );
        
        return $actionPlan;
    }
}