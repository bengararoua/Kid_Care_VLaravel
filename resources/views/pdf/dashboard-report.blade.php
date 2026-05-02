<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; padding: 20px; border-bottom: 3px solid #667eea; margin-bottom: 30px; }
        .header h1 { color: #1a0a2e; margin: 0; }
        .stats-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat-box { background: #667eea; color: white; padding: 15px; border-radius: 8px; text-align: center; }
        .stat-box .number { font-size: 28px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #667eea; color: white; }
        .footer { text-align: center; padding: 20px; font-size: 12px; border-top: 1px solid #ddd; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>👶 KidCare Insight</h1>
        <p>Rapport Dashboard - {{ $generated_at }}</p>
        <p>Utilisateur : {{ $user->name }} ({{ ucfirst($user->role) }})</p>
    </div>

    <div class="stats-summary">
        <div class="stat-box"><div class="number">{{ $total_children }}</div>Enfants suivis</div>
        <div class="stat-box"><div class="number">{{ $total_logs }}</div>Total Logs</div>
        <div class="stat-box"><div class="number">{{ $children->sum(fn($c) => round($c->behaviors->avg('focus_level') ?? 0, 1)) }}</div>Focus Moyen</div>
        <div class="stat-box"><div class="number">{{ $children->sum(fn($c) => round($c->behaviors->avg('sleep_hours') ?? 0, 1)) }}</div>Sommeil Moyen</div>
    </div>

    <h3>📋 Détail par enfant</h3>
    <table>
        <thead><tr><th>Nom</th><th>Âge</th><th>Nb Logs</th><th>Focus</th><th>Sommeil</th></tr></thead>
        <tbody>
            @foreach($children as $child)
            <tr>
                <td>{{ $child->name }}</td>
                <td>{{ $child->age }} ans</td>
                <td>{{ $child->behaviors->count() }}</td>
                <td>{{ round($child->behaviors->avg('focus_level') ?? 0, 1) }}/5</td>
                <td>{{ round($child->behaviors->avg('sleep_hours') ?? 0, 1) }}h</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} KidCare Insight. Tous droits réservés.</p>
    </div>
</body>
</html>