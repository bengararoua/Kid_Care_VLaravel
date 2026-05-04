<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>KidCare Insight – Rapport de suivi</title>
    <style>
        /* ===== RESET & BASE ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
            line-height: 1.5;
        }

        /* ===== HEADER ===== */
        .header {
            background-color: #667eea;
            padding: 28px 35px 24px 35px;
            color: #fff;
        }

        .header-top {
            width: 100%;
            margin-bottom: 18px;
        }

        .header-top td { vertical-align: middle; }

        .logo-circle {
            width: 46px;
            height: 46px;
            background: #fff;
            border-radius: 50%;
            text-align: center;
            line-height: 46px;
            font-size: 22px;
            display: inline-block;
        }

        .app-name {
            font-size: 19px;
            font-weight: 700;
            color: #fff;
            margin-left: 10px;
            vertical-align: middle;
        }

        .app-tagline {
            font-size: 10px;
            color: rgba(255,255,255,0.8);
            margin-left: 10px;
        }

        .report-badge {
            background: rgba(255,255,255,0.25);
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 11px;
            color: #fff;
            font-weight: 600;
            float: right;
        }

        .header-center {
            text-align: center;
            padding-top: 6px;
        }

        .main-title {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .sub-title {
            font-size: 12px;
            color: rgba(255,255,255,0.85);
        }

        .child-badge {
            display: inline-block;
            background: rgba(255,255,255,0.22);
            padding: 5px 18px;
            border-radius: 20px;
            margin-top: 12px;
            font-size: 12px;
            color: #fff;
            font-weight: 600;
        }

        /* ===== STATS ===== */
        .stats-section {
            padding: 20px 30px 0 30px;
        }

        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3px;
        }

        .stats-table td {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            padding: 16px 10px;
            width: 25%;
        }

        .stat-number {
            font-size: 26px;
            font-weight: 800;
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 9px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        /* ===== CONTENT ===== */
        .content {
            padding: 20px 30px 15px 30px;
        }

        /* Info boxes */
        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 20px;
        }

        .info-table td {
            background: #f8fafc;
            border-left: 4px solid #667eea;
            border-radius: 10px;
            padding: 10px 14px;
            width: 33%;
            vertical-align: top;
        }

        .info-label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
        }

        .info-value {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 4px;
        }

        /* Section titles */
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            border-left: 4px solid #667eea;
            padding-left: 10px;
            margin: 18px 0 10px 0;
        }

        /* ===== CHART (barres SVG-like via table) ===== */
        .chart-box {
            background: #f8fafc;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 18px;
        }

        .chart-title {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 10px;
        }

        .chart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .chart-table td {
            text-align: center;
            vertical-align: bottom;
            padding: 0 4px;
        }

        .bar-wrap {
            height: 80px;
            vertical-align: bottom;
            text-align: center;
        }

        .bar-inner {
            background-color: #667eea;
            border-radius: 4px 4px 2px 2px;
            display: inline-block;
            width: 22px;
        }

        .bar-day  { font-size: 9px;  color: #64748b; font-weight: 500; padding-top: 4px; }
        .bar-score{ font-size: 10px; color: #667eea; font-weight: 700; }

        /* ===== MOODS ===== */
        .mood-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 18px;
        }

        .mood-table td {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px 10px;
            text-align: center;
            width: 33%;
        }

        .mood-emoji { font-size: 24px; }
        .mood-count { font-size: 20px; font-weight: 800; margin: 4px 0; }
        .mood-label { font-size: 10px; color: #64748b; }
        .mood-happy  .mood-count { color: #10b981; }
        .mood-neutral .mood-count { color: #f59e0b; }
        .mood-sad    .mood-count { color: #ef4444; }

        /* ===== TABLE OBSERVATIONS ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-bottom: 18px;
        }

        .data-table th {
            background: #f1f5f9;
            padding: 8px 10px;
            text-align: left;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        .data-table tr:nth-child(even) td { background: #f8fafc; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 700;
        }

        .badge-good    { background: #d1fae5; color: #065f46; }
        .badge-neutral { background: #fef3c7; color: #92400e; }
        .badge-bad     { background: #fee2e2; color: #991b1b; }

        /* ===== ROUTINE ===== */
        .routine-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 5px;
            margin-bottom: 18px;
        }

        .routine-table td {
            background: #f8fafc;
            padding: 8px 12px;
            vertical-align: middle;
        }

        .routine-table tr td:first-child {
            border-radius: 8px 0 0 8px;
            font-weight: 700;
            color: #667eea;
            font-size: 11px;
            width: 100px;
        }

        .routine-table tr td:last-child {
            border-radius: 0 8px 8px 0;
            font-size: 11px;
        }

        .routine-time { color: #64748b; font-size: 10px; width: 55px; }

        /* ===== RECOMMANDATIONS ===== */
        .rec-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 5px;
            margin-bottom: 18px;
        }

        .rec-table td {
            background: #f8fafc;
            padding: 8px 12px;
            vertical-align: middle;
        }

        .rec-table tr td:first-child {
            border-radius: 8px 0 0 8px;
            width: 28px;
            text-align: center;
        }

        .rec-table tr td:last-child { border-radius: 0 8px 8px 0; }

        .check-done {
            background: #10b981;
            color: #fff;
            border-radius: 5px;
            width: 18px;
            height: 18px;
            display: inline-block;
            text-align: center;
            line-height: 18px;
            font-size: 10px;
            font-weight: 700;
        }

        .check-pending {
            border: 2px solid #cbd5e1;
            border-radius: 5px;
            width: 18px;
            height: 18px;
            display: inline-block;
            background: #fff;
        }

        .rec-title { font-size: 11px; font-weight: 700; color: #1e293b; }
        .rec-cat   { font-size: 9px;  color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px; }

        /* ===== PLAN D'ACTION ===== */
        .plan-card {
            background-color: #667eea;
            padding: 16px;
            border-radius: 14px;
            margin-top: 8px;
        }

        .plan-header-table {
            width: 100%;
            margin-bottom: 12px;
        }

        .plan-date { color: #fff; font-size: 11px; font-weight: 500; }

        .risk-tag {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            float: right;
        }

        .risk-low    { background: #10b981; color: #fff; }
        .risk-medium { background: #f59e0b; color: #fff; }
        .risk-high   { background: #ef4444; color: #fff; }

        .plan-cols { width: 100%; border-collapse: separate; border-spacing: 12px; }

        .plan-cols td { vertical-align: top; width: 50%; }

        .plan-group-title {
            font-size: 11px;
            color: rgba(255,255,255,0.95);
            font-weight: 700;
            margin-bottom: 7px;
        }

        .plan-item {
            font-size: 10px;
            color: rgba(255,255,255,0.88);
            padding: 3px 0 3px 14px;
            position: relative;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: #0f172a;
            padding: 18px 30px 14px 30px;
            text-align: center;
            margin-top: 18px;
        }

        .footer-links { margin-bottom: 10px; }
        .footer-link  { color: #94a3b8; font-size: 10px; margin: 0 10px; }
        .copyright    { font-size: 9px;  color: #64748b; margin-bottom: 4px; }
        .generated    { font-size: 8px;  color: #475569; }

        /* ===== PAGE BREAK ===== */
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

{{-- ==================== HEADER ==================== --}}
<div class="header">
    <table class="header-top" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <span style="font-size:24px;">&#127775;</span>
                <span class="app-name">KidCare Insight</span><br>
                <span class="app-tagline" style="margin-left:10px;">Suivi pédagogique &amp; psychologique</span>
            </td>
            <td style="text-align:right;">
                <span class="report-badge">RAPPORT OFFICIEL</span>
            </td>
        </tr>
    </table>

    <div class="header-center">
        <div class="main-title">Rapport de suivi personnalisé</div>
        <div class="sub-title">Évaluation complète du bien-être et du développement</div>
        <div class="child-badge">{{ $child->name }} &bull; {{ $child->age }} ans</div>
    </div>
</div>

{{-- ==================== STATISTIQUES ==================== --}}
<div class="stats-section">
    <table class="stats-table" cellpadding="0" cellspacing="3">
        <tr>
            <td>
                <span class="stat-number">{{ $stats['total_logs'] }}</span>
                <span class="stat-label">Journaux d'observation</span>
            </td>
            <td>
                <span class="stat-number">{{ $stats['avg_focus'] }}/5</span>
                <span class="stat-label">Concentration moyenne</span>
            </td>
            <td>
                <span class="stat-number">{{ $stats['positive_mood'] }}</span>
                <span class="stat-label">Jours de bonne humeur</span>
            </td>
            <td>
                <span class="stat-number">{{ $stats['completion_rate'] }}%</span>
                <span class="stat-label">Objectifs atteints</span>
            </td>
        </tr>
    </table>
</div>

{{-- ==================== CONTENU ==================== --}}
<div class="content">

    {{-- Informations générales --}}
    <table class="info-table" cellpadding="0" cellspacing="8">
        <tr>
            <td>
                <div class="info-label">Parent / Tuteur</div>
                <div class="info-value">{{ $parent->name ?? 'Non renseigné' }}</div>
            </td>
            <td>
                <div class="info-label">Enseignant(e)</div>
                <div class="info-value">{{ $teacher->name ?? 'Non assigné' }}</div>
            </td>
            <td>
                <div class="info-label">Psychologue</div>
                <div class="info-value">{{ $psychologist->name ?? 'Non assigné' }}</div>
            </td>
        </tr>
    </table>

    {{-- Graphique concentration 7 jours --}}
    @if($last7Days->count() > 0 && $last7Days->sum('focus') > 0)
    <div class="chart-box">
        <div class="chart-title">Evolution de la concentration (7 derniers jours)</div>
        <table class="chart-table" cellpadding="0" cellspacing="0">
            <tr>
                @foreach($last7Days as $day)
                @php $barH = max(4, (int)($day['focus'] * 16)); @endphp
                <td>
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="bar-wrap" style="height:80px; vertical-align:bottom; text-align:center;">
                                <div class="bar-inner" style="height:{{ $barH }}px; width:22px; background:#667eea; border-radius:4px 4px 2px 2px; display:inline-block;"></div>
                            </td>
                        </tr>
                        <tr><td class="bar-day" style="text-align:center;">{{ $day['date'] }}</td></tr>
                        <tr><td class="bar-score" style="text-align:center;">{{ $day['focus'] }}</td></tr>
                    </table>
                </td>
                @endforeach
            </tr>
        </table>
    </div>
    @endif

    {{-- Distribution des humeurs --}}
    @if($stats['total_logs'] > 0)
    <table class="mood-table" cellpadding="0" cellspacing="8">
        <tr>
            <td class="mood-happy">
                <div class="mood-emoji">&#128522;</div>
                <div class="mood-count">{{ $stats['positive_mood'] }}</div>
                <div class="mood-label">Heureux / Joyeux</div>
            </td>
            <td class="mood-neutral">
                <div class="mood-emoji">&#128528;</div>
                <div class="mood-count">{{ $stats['neutral_mood'] }}</div>
                <div class="mood-label">Neutre / Calme</div>
            </td>
            <td class="mood-sad">
                <div class="mood-emoji">&#128532;</div>
                <div class="mood-count">{{ $stats['sad_mood'] }}</div>
                <div class="mood-label">Triste / Fatigué</div>
            </td>
        </tr>
    </table>
    @endif

    {{-- Dernières observations --}}
    @if($logs->count() > 0)
    <div class="section-title">Dernières observations</div>
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Humeur</th>
                <th>Concentration</th>
                <th>Sommeil</th>
                <th>Interaction</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs->take(6) as $log)
            <tr>
                <td>{{ \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') }}</td>
                <td>
                    @if($log->mood === 'happy')
                        <span class="badge badge-good">Heureux</span>
                    @elseif($log->mood === 'neutral')
                        <span class="badge badge-neutral">Neutre</span>
                    @else
                        <span class="badge badge-bad">Triste</span>
                    @endif
                </td>
                <td><strong>{{ $log->focus_level }}</strong>/5</td>
                <td>{{ $log->sleep_hours }}h</td>
                <td>{{ $log->social_interaction }}/5</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Routine quotidienne --}}
    @if($routines->count() > 0)
    <div class="section-title">Routine quotidienne</div>
    <table class="routine-table" cellpadding="0" cellspacing="0">
        @foreach($routines->take(6) as $routine)
        <tr>
            <td>{{ ucfirst($routine->day_of_week) }}</td>
            <td class="routine-time">{{ \Carbon\Carbon::parse($routine->time)->format('H:i') }}</td>
            <td>{{ $routine->activity }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    {{-- Recommandations --}}
    @if($recommendations->count() > 0)
    <div class="section-title">Recommandations</div>
    <table class="rec-table" cellpadding="0" cellspacing="0">
        @foreach($recommendations->take(4) as $rec)
        <tr>
            <td>
                @if($rec->is_completed)
                    <span class="check-done">&#10003;</span>
                @else
                    <span class="check-pending"></span>
                @endif
            </td>
            <td>
                <div class="rec-title">{{ $rec->title }}</div>
                <div class="rec-cat">{{ $rec->category }}</div>
            </td>
        </tr>
        @endforeach
    </table>
    @endif

    {{-- Plan d'action --}}
    @if($actionPlan)
    <div class="section-title">Plan d'action personnalisé</div>
    <div class="plan-card">
        <table class="plan-header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="plan-date">
                    &#128197; {{ \Carbon\Carbon::parse($actionPlan->generated_date)->format('d/m/Y') }}
                </td>
                <td style="text-align:right;">
                    <span class="risk-tag risk-{{ $actionPlan->risk_level }}">
                        Niveau {{ ucfirst($actionPlan->risk_level) }}
                    </span>
                </td>
            </tr>
        </table>

        <table class="plan-cols" cellpadding="0" cellspacing="12">
            <tr>
                <td>
                    <div class="plan-group-title">MATIN</div>
                    @foreach(array_slice(json_decode($actionPlan->morning_activities, true) ?? [], 0, 3) as $activity)
                    <div class="plan-item">&#9733; {{ $activity }}</div>
                    @endforeach
                </td>
                <td>
                    <div class="plan-group-title">SOIR</div>
                    @foreach(array_slice(json_decode($actionPlan->evening_activities, true) ?? [], 0, 3) as $activity)
                    <div class="plan-item">&#9733; {{ $activity }}</div>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>
    @endif

</div>{{-- end .content --}}

{{-- ==================== FOOTER ==================== --}}
<div class="footer">
    <div class="footer-links">
        <span class="footer-link">KidCare Insight</span>
        <span class="footer-link">|</span>
        <span class="footer-link">Support</span>
        <span class="footer-link">|</span>
        <span class="footer-link">Confidentialité</span>
        <span class="footer-link">|</span>
        <span class="footer-link">Mentions légales</span>
    </div>
    <div class="copyright">
        &copy; {{ date('Y') }} KidCare Insight – Application de suivi pédagogique et psychologique
    </div>
    <div class="generated">
        Rapport généré le {{ $generated_date }}
    </div>
</div>

</body>
</html>