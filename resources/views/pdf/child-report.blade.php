<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapport de suivi - {{ $child->name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            padding: 40px;
        }
        
        .report {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #1a0a2e 0%, #2d1b4e 50%, #667eea 100%);
            padding: 30px 40px;
            text-align: center;
            position: relative;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .logo-icon {
            font-size: 50px;
        }
        
        .app-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.5px;
        }
        
        .app-subtitle {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            margin-top: 5px;
        }
        
        .report-title {
            font-size: 22px;
            font-weight: 600;
            color: white;
            margin-top: 10px;
        }
        
        .child-info {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            padding: 8px 24px;
            border-radius: 50px;
            margin-top: 15px;
            font-size: 14px;
            color: white;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            background: #e5e7eb;
            margin: 30px;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .stat-item {
            background: white;
            padding: 25px 15px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Content */
        .content {
            padding: 0 30px 30px 30px;
        }
        
        /* Info Cards */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        
        .info-card-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .info-card-label {
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-card-value {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 5px;
        }
        
        /* Section Titles */
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 25px 0 15px 0;
            padding-left: 12px;
            border-left: 4px solid #667eea;
        }
        
        /* Graph Container */
        .graph-container {
            background: #f8fafc;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 25px;
        }
        
        .graph-bars {
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
        
        .bar-wrapper {
            flex: 1;
            text-align: center;
        }
        
        .bar {
            background: linear-gradient(180deg, #667eea, #764ba2);
            height: calc(var(--value) * 8px);
            min-height: 5px;
            max-height: 150px;
            border-radius: 8px 8px 4px 4px;
            margin-bottom: 10px;
            transition: height 0.5s;
        }
        
        .bar-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }
        
        .bar-value {
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
            margin-top: 6px;
        }
        
        /* Mood Distribution */
        .mood-distribution {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin: 20px 0;
        }
        
        .mood-item {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
        }
        
        .mood-emoji {
            font-size: 36px;
            margin-bottom: 8px;
        }
        
        .mood-count {
            font-size: 24px;
            font-weight: 700;
            margin: 8px 0;
        }
        
        .mood-label {
            font-size: 12px;
            color: #64748b;
        }
        
        .mood-happy .mood-count { color: #10b981; }
        .mood-neutral .mood-count { color: #f59e0b; }
        .mood-sad .mood-count { color: #ef4444; }
        
        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .data-table th {
            background: #f1f5f9;
            padding: 12px 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }
        
        .data-table td {
            padding: 10px 12px;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        
        /* Routine Items */
        .routine-items {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .routine-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 15px;
            background: #f8fafc;
            border-radius: 10px;
        }
        
        .routine-day {
            font-weight: 600;
            color: #667eea;
            min-width: 100px;
            font-size: 12px;
        }
        
        .routine-time {
            color: #64748b;
            font-size: 12px;
            min-width: 60px;
        }
        
        .routine-activity {
            flex: 1;
            font-size: 12px;
        }
        
        /* Recommendations */
        .recommendation-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 15px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 8px;
        }
        
        .rec-check {
            width: 20px;
            height: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }
        
        .rec-check.completed {
            background: #10b981;
            color: white;
        }
        
        .rec-check.pending {
            background: white;
            border: 2px solid #cbd5e1;
        }
        
        .rec-content {
            flex: 1;
        }
        
        .rec-title {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .rec-category {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
        }
        
        /* Action Plan */
        .action-plan {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 20px;
            border-radius: 16px;
            color: white;
        }
        
        .risk-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin: 10px 0;
        }
        
        .risk-low { background: #10b981; }
        .risk-medium { background: #f59e0b; }
        .risk-high { background: #ef4444; }
        
        .activities {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        
        .activity-block h4 {
            font-size: 12px;
            margin-bottom: 8px;
            opacity: 0.9;
        }
        
        .activity-block ul {
            list-style: none;
            padding-left: 0;
        }
        
        .activity-block li {
            padding: 4px 0 4px 18px;
            position: relative;
            font-size: 11px;
        }
        
        .activity-block li:before {
            content: "▹";
            position: absolute;
            left: 0;
            color: #fbbf24;
        }
        
        /* Footer */
        .footer {
            background: #0f172a;
            padding: 25px 30px;
            text-align: center;
            margin-top: 30px;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 11px;
        }
        
        .copyright {
            font-size: 10px;
            color: #64748b;
        }
        
        .generated-date {
            font-size: 9px;
            color: #475569;
            margin-top: 10px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .stat-item, .info-card, .graph-container {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="report">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo-icon">🌟</div>
                <div>
                    <div class="app-title">KidCare Insight</div>
                    <div class="app-subtitle">Suivi pédagogique & psychologique</div>
                </div>
            </div>
            <div class="report-title">Rapport de suivi personnalisé</div>
            <div class="child-info">{{ $child->name }} • {{ $child->age }} ans</div>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">{{ $stats['total_logs'] }}</div>
                <div class="stat-label">Journaux d'observation</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $stats['avg_focus'] }}/5</div>
                <div class="stat-label">Concentration moyenne</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $stats['positive_mood'] }}</div>
                <div class="stat-label">Jours de bonne humeur</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $stats['completion_rate'] }}%</div>
                <div class="stat-label">Recommandations</div>
            </div>
        </div>
        
        <div class="content">
            <!-- Informations -->
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-icon">👨‍👩‍👧</div>
                    <div class="info-card-label">Parent</div>
                    <div class="info-card-value">{{ $parent->name ?? 'Non renseigné' }}</div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">📚</div>
                    <div class="info-card-label">Enseignant</div>
                    <div class="info-card-value">{{ $teacher->name ?? 'Non assigné' }}</div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">👩‍⚕️</div>
                    <div class="info-card-label">Psychologue</div>
                    <div class="info-card-value">{{ $psychologist->name ?? 'Non assigné' }}</div>
                </div>
            </div>
            
            <!-- Graphique évolution -->
            @if($last7Days->count() > 0 && $last7Days->sum('focus') > 0)
            <div class="graph-container">
                <div style="font-weight: 600; margin-bottom: 10px;">📈 Évolution de la concentration (7 derniers jours)</div>
                <div class="graph-bars">
                    @foreach($last7Days as $day)
                    <div class="bar-wrapper">
                        <div class="bar" style="--value: {{ $day['focus'] }}"></div>
                        <div class="bar-label">{{ $day['date'] }}</div>
                        <div class="bar-value">{{ number_format($day['focus'], 1) }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- Distribution humeurs -->
            @if($stats['total_logs'] > 0)
            <div class="mood-distribution">
                <div class="mood-item mood-happy">
                    <div class="mood-emoji">😊</div>
                    <div class="mood-count">{{ $stats['positive_mood'] }}</div>
                    <div class="mood-label">Heureux</div>
                </div>
                <div class="mood-item mood-neutral">
                    <div class="mood-emoji">😐</div>
                    <div class="mood-count">{{ $stats['neutral_mood'] }}</div>
                    <div class="mood-label">Neutre</div>
                </div>
                <div class="mood-item mood-sad">
                    <div class="mood-emoji">😔</div>
                    <div class="mood-count">{{ $stats['sad_mood'] }}</div>
                    <div class="mood-label">Triste</div>
                </div>
            </div>
            @endif
            
            <!-- Dernières observations -->
            @if($logs->count() > 0)
            <div class="section-title">📝 Dernières observations</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Humeur</th>
                        <th>Concentration</th>
                        <th>Sommeil</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs->take(7) as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge badge-{{ $log->mood === 'happy' ? 'success' : ($log->mood === 'neutral' ? 'warning' : 'danger') }}">
                                {{ $log->mood === 'happy' ? '😊 Heureux' : ($log->mood === 'neutral' ? '😐 Neutre' : '😔 Triste') }}
                            </span>
                        </td>
                        <td>{{ $log->focus_level }}/5</td>
                        <td>{{ $log->sleep_hours }}h</td>
                        <td>{{ Str::limit($log->note ?? '-', 40) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
            
            <!-- Routine -->
            @if($routines->count() > 0)
            <div class="section-title">⏰ Routine quotidienne</div>
            <div class="routine-items">
                @foreach($routines->take(8) as $routine)
                <div class="routine-row">
                    <div class="routine-day">{{ ucfirst($routine->day_of_week) }}</div>
                    <div class="routine-time">{{ \Carbon\Carbon::parse($routine->time)->format('H:i') }}</div>
                    <div class="routine-activity">{{ $routine->activity }}</div>
                </div>
                @endforeach
            </div>
            @endif
            
            <!-- Recommandations -->
            @if($recommendations->count() > 0)
            <div class="section-title">💡 Recommandations</div>
            @foreach($recommendations->take(5) as $recommendation)
            <div class="recommendation-row">
                <div class="rec-check {{ $recommendation->is_completed ? 'completed' : 'pending' }}">
                    @if($recommendation->is_completed) ✓ @endif
                </div>
                <div class="rec-content">
                    <div class="rec-title">{{ $recommendation->title }}</div>
                    <div class="rec-category">{{ $recommendation->category }}</div>
                </div>
            </div>
            @endforeach
            @endif
            
            <!-- Plan d'action -->
            @if($actionPlan)
            <div class="section-title">🎯 Plan d'action</div>
            <div class="action-plan">
                <div>
                    <strong>{{ \Carbon\Carbon::parse($actionPlan->generated_date)->format('d/m/Y') }}</strong>
                    <span class="risk-badge risk-{{ $actionPlan->risk_level }}">{{ ucfirst($actionPlan->risk_level) }}</span>
                </div>
                <div class="activities">
                    <div class="activity-block">
                        <h4>🌅 Matin</h4>
                        <ul>
                            @foreach(array_slice(json_decode($actionPlan->morning_activities), 0, 3) as $activity)
                            <li>{{ $activity }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="activity-block">
                        <h4>🌙 Soir</h4>
                        <ul>
                            @foreach(array_slice(json_decode($actionPlan->evening_activities), 0, 3) as $activity)
                            <li>{{ $activity }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-links">
                <a href="#">🏠 KidCare Insight</a>
                <a href="#">📧 Contact</a>
                <a href="#">🔒 Confidentialité</a>
            </div>
            <div class="copyright">
                © {{ date('Y') }} KidCare Insight - Tous droits réservés
            </div>
            <div class="generated-date">
                Rapport généré le {{ $generated_date }}
            </div>
        </div>
    </div>
</body>
</html>