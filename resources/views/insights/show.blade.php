@extends('layouts.app')

@section('title', $child->name . ' — Insights')
@section('page-title', '📊 Insights — ' . $child->name)

@section('topbar-actions')
    <a href="{{ route('children.show', $child) }}" class="btn btn-secondary btn-sm">← Back to Profile</a>
@endsection

@push('styles')
<style>
    .insight-metric {
        background: white;
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        border: 1px solid var(--border);
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .insight-value { font-size: 42px; font-weight: 800; font-family: 'Plus Jakarta Sans', sans-serif; }
    .insight-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; margin-top: 4px; }
    .chart-card { background: white; border-radius: 16px; padding: 24px; border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    .pattern-item { display: flex; align-items: start; gap: 12px; padding: 14px; background: #FFFBEB; border-radius: 12px; margin-bottom: 10px; border-left: 4px solid #F59E0B; }
</style>
@endpush

@section('content')

@if(isset($insufficient) && $insufficient)
    <div class="card" style="text-align:center; padding:60px;">
        <div style="font-size:60px; margin-bottom:16px;">📊</div>
        <h3 style="margin-bottom:8px;">Not Enough Data</h3>
        <p class="text-muted" style="margin-bottom:24px;">At least a few behavior logs are needed to generate insights.</p>
        <a href="{{ route('children.show', $child) }}" class="btn btn-primary">➕ Add Behavior Logs</a>
    </div>
@else

{{-- Risk Level Banner --}}
@php
    $riskColors = ['low' => '#D1FAE5', 'medium' => '#FEF3C7', 'high' => '#FEE2E2'];
    $riskTextColors = ['low' => '#065F46', 'medium' => '#92400E', 'high' => '#991B1B'];
    $riskIcons = ['low' => '🟢', 'medium' => '🟡', 'high' => '🔴'];
@endphp
<div style="background:{{ $riskColors[$riskLevel] }}; border-radius:16px; padding:24px 28px; margin-bottom:28px; display:flex; align-items:center; gap:16px;">
    <div style="font-size:48px;">{{ $riskIcons[$riskLevel] }}</div>
    <div>
        <div style="font-size:13px; font-weight:700; color:{{ $riskTextColors[$riskLevel] }}; text-transform:uppercase; letter-spacing:.8px;">Overall Risk Level</div>
        <div style="font-size:32px; font-weight:800; color:{{ $riskTextColors[$riskLevel] }};">{{ ucfirst($riskLevel) }} Risk</div>
        <div style="font-size:13px; color:{{ $riskTextColors[$riskLevel] }}; opacity:.8;">Based on last 30 days of data ({{ $logs->count() }} logs)</div>
    </div>
</div>

{{-- Key Metrics --}}
<div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:20px; margin-bottom:28px;">
    <div class="insight-metric">
        <div class="insight-value" style="color:#6C63FF;">{{ $avgFocus }}</div>
        <div class="insight-label" style="color:#6C63FF;">Avg Focus</div>
        <div style="font-size:12px; color:#9CA3AF; margin-top:4px;">out of 5</div>
    </div>
    <div class="insight-metric">
        <div class="insight-value" style="color:#43D9AD;">{{ $avgSleep }}h</div>
        <div class="insight-label" style="color:#43D9AD;">Avg Sleep</div>
        <div style="font-size:12px; color:#9CA3AF; margin-top:4px;">per night</div>
    </div>
    <div class="insight-metric">
        <div class="insight-value" style="color:#FFB547;">{{ $avgSocial }}</div>
        <div class="insight-label" style="color:#FFB547;">Social Score</div>
        <div style="font-size:12px; color:#9CA3AF; margin-top:4px;">out of 5</div>
    </div>
    <div class="insight-metric">
        @php
            $change = $weeklyComp['change'];
            $changeColor = $change >= 0 ? '#10B981' : '#EF4444';
        @endphp
        <div class="insight-value" style="color:{{ $changeColor }};">{{ $change >= 0 ? '+' : '' }}{{ $change }}</div>
        <div class="insight-label" style="color:{{ $changeColor }};">Weekly Change</div>
        <div style="font-size:12px; color:#9CA3AF; margin-top:4px;">focus trend</div>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid-2 mb-6">
    {{-- Focus Chart --}}
    <div class="chart-card">
        <div class="card-title">📈 Focus Level Over Time</div>
        <canvas id="focusChart" height="200"></canvas>
    </div>

    {{-- Sleep Chart --}}
    <div class="chart-card">
        <div class="card-title">😴 Sleep Hours Over Time</div>
        <canvas id="sleepChart" height="200"></canvas>
    </div>
</div>

<div class="grid-2 mb-6">
    {{-- Mood Distribution --}}
    <div class="chart-card">
        <div class="card-title">😊 Mood Distribution</div>
        <canvas id="moodChart" height="200"></canvas>
    </div>

    {{-- Weekly Comparison --}}
    <div class="chart-card">
        <div class="card-title">📊 Weekly Comparison</div>
        <div style="display:flex; gap:20px; justify-content:center; align-items:center; height:200px;">
            <div style="text-align:center;">
                <div style="font-size:48px; font-weight:800; color:#9CA3AF;">{{ $weeklyComp['previous_week_focus'] }}</div>
                <div style="font-size:12px; font-weight:700; color:#9CA3AF; text-transform:uppercase;">Previous Week</div>
            </div>
            <div style="font-size:32px; color:#D1D5DB;">→</div>
            <div style="text-align:center;">
                <div style="font-size:48px; font-weight:800; color:#6C63FF;">{{ $weeklyComp['current_week_focus'] }}</div>
                <div style="font-size:12px; font-weight:700; color:#6C63FF; text-transform:uppercase;">Current Week</div>
            </div>
        </div>
    </div>
</div>

{{-- Patterns --}}
@if(!empty($patterns))
<div class="card mb-6">
    <div class="card-title">🔍 Detected Patterns</div>
    @foreach($patterns as $pattern)
    <div class="pattern-item">
        <span style="font-size:20px;">⚡</span>
        <div>{{ $pattern }}</div>
    </div>
    @endforeach
</div>
@endif

{{-- Recommendations for this child --}}
@php $recs = $child->recommendations; @endphp
@if($recs->isNotEmpty())
<div class="card">
    <div class="flex-between mb-6">
        <div class="card-title" style="margin:0">📋 Recommendations</div>
        @if(auth()->user()->isPsychologist())
            <a href="{{ route('recommendations.index', $child) }}" class="btn btn-primary btn-sm">Manage</a>
        @endif
    </div>
    <div style="display:grid; gap:12px;">
        @foreach($recs as $rec)
        <div style="display:flex; align-items:start; gap:14px; padding:16px; background:{{ $rec->is_completed ? '#F0FDF4' : '#FAFAFA' }}; border-radius:12px; border:1px solid {{ $rec->is_completed ? '#BBF7D0' : '#E5E7EB' }};">
            <div style="font-size:24px;">{{ ['focus'=>'🎯','social'=>'🤝','relaxation'=>'🧘','routine'=>'⏰','sleep'=>'😴','nutrition'=>'🥗'][$rec->category] ?? '📌' }}</div>
            <div style="flex:1;">
                <div style="font-weight:700; {{ $rec->is_completed ? 'text-decoration:line-through; color:#9CA3AF;' : '' }}">{{ $rec->title }}</div>
                <div class="text-muted" style="margin-top:4px;">{{ $rec->description }}</div>
            </div>
            @if($rec->is_completed)
                <span style="background:#D1FAE5; color:#065F46; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; white-space:nowrap;">✓ Done</span>
            @else
                @if(auth()->user()->isParent())
                <form action="{{ route('recommendations.toggle', $rec) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">✓ Complete</button>
                </form>
                @endif
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

@endif

@endsection

@push('scripts')
<script>
    const chartData = @json($chartData ?? []);
    const moodDist  = @json($moodDist ?? []);

    // Focus Chart
    new Chart(document.getElementById('focusChart'), {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [{
                label: 'Focus Level',
                data: chartData.map(d => d.focus),
                borderColor: '#6C63FF',
                backgroundColor: 'rgba(108,99,255,.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#6C63FF',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            scales: { y: { min: 0, max: 5, ticks: { stepSize: 1 } } },
            plugins: { legend: { display: false } }
        }
    });

    // Sleep Chart
    new Chart(document.getElementById('sleepChart'), {
        type: 'bar',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [{
                label: 'Sleep Hours',
                data: chartData.map(d => d.sleep),
                backgroundColor: 'rgba(67,217,173,.7)',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            scales: { y: { min: 0, max: 12 } },
            plugins: { legend: { display: false } }
        }
    });

    // Mood Pie Chart
    const moodLabels = Object.keys(moodDist);
    const moodColors = { happy:'#43D9AD', neutral:'#6C63FF', sad:'#FF6584', anxious:'#FFB547', angry:'#FF5A5F' };
    new Chart(document.getElementById('moodChart'), {
        type: 'doughnut',
        data: {
            labels: moodLabels.map(m => m.charAt(0).toUpperCase() + m.slice(1)),
            datasets: [{
                data: Object.values(moodDist),
                backgroundColor: moodLabels.map(m => moodColors[m] || '#9CA3AF'),
                borderWidth: 0,
            }]
        },
        options: { responsive: true, cutout: '65%' }
    });
</script>
@endpush
