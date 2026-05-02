@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('topbar-actions')
    @if(auth()->user()->isParent())
        <a href="{{ route('children.create') }}" class="btn btn-primary btn-sm">
            ➕ Add Child
        </a>
    @endif
    @if(auth()->user()->isTeacher())
        <button onclick="document.getElementById('quickLogModal').classList.add('open')" class="btn btn-primary btn-sm">
            📝 Quick Log
        </button>
    @endif
@endsection

@section('content')

{{-- ══════════ PARENT DASHBOARD ══════════ --}}
@if(auth()->user()->isParent())

    {{-- Stats Row --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#EDE9FF;">👶</div>
            <div>
                <div class="stat-value" style="color:#6C63FF;">{{ $children->count() }}</div>
                <div class="stat-label">My Children</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#D1FAE5;">📊</div>
            <div>
                <div class="stat-value" style="color:#10B981;">{{ $recentLogs->count() }}</div>
                <div class="stat-label">Recent Logs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7;">💬</div>
            <div>
                <div class="stat-value" style="color:#F59E0B;">{{ $unreadMessages }}</div>
                <div class="stat-label">Unread Messages</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEE2E2;">🎯</div>
            <div>
                <div class="stat-value" style="color:#EF4444;">
                    {{ $children->sum(fn($c) => $c->recommendations->where('is_completed', false)->count()) }}
                </div>
                <div class="stat-label">Pending Recommendations</div>
            </div>
        </div>
    </div>

    {{-- Children Cards --}}
    <div class="flex-between mb-6">
        <h2 class="card-title" style="margin:0">My Children</h2>
        <a href="{{ route('children.create') }}" class="btn btn-secondary btn-sm">+ Add Child</a>
    </div>

    @if($children->isEmpty())
        <div class="card" style="text-align:center; padding:60px;">
            <div style="font-size:60px; margin-bottom:16px;">👶</div>
            <h3 style="margin-bottom:8px;">No children added yet</h3>
            <p class="text-muted" style="margin-bottom:24px;">Add your first child to start tracking their development.</p>
            <a href="{{ route('children.create') }}" class="btn btn-primary">➕ Add First Child</a>
        </div>
    @else
        <div class="child-cards mb-6">
            @foreach($children as $child)
                @php
                    $latestLog = $child->behaviors->first();
                    $riskLevel = 'low';
                    if ($child->behaviors->count() > 0) {
                        $avgFocus = $child->behaviors->avg('focus_level');
                        $avgSleep = $child->behaviors->avg('sleep_hours');
                        $score = 0;
                        if ($avgFocus < 2.5) $score += 2; elseif ($avgFocus < 3.5) $score += 1;
                        if ($avgSleep < 6) $score += 2; elseif ($avgSleep < 7) $score += 1;
                        $riskLevel = $score >= 4 ? 'high' : ($score >= 2 ? 'medium' : 'low');
                    }
                @endphp
                <div class="child-card">
                    <div class="child-avatar">{{ substr($child->name, 0, 1) }}</div>
                    <div class="flex-between" style="margin-bottom:6px;">
                        <div class="child-name">{{ $child->name }}</div>
                        <span class="risk-badge risk-{{ $riskLevel }}">
                            {{ $riskLevel === 'high' ? '🔴' : ($riskLevel === 'medium' ? '🟡' : '🟢') }}
                            {{ ucfirst($riskLevel) }}
                        </span>
                    </div>
                    <div class="child-age">{{ $child->age }} years old</div>

                    @if($latestLog)
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px;">
                            <div style="background:#F9FAFB; border-radius:10px; padding:10px; text-align:center;">
                                <div style="font-size:11px; color:#6B7280; font-weight:700;">FOCUS</div>
                                <div style="font-size:20px; font-weight:800; color:#6C63FF;">{{ $latestLog->focus_level }}/5</div>
                            </div>
                            <div style="background:#F9FAFB; border-radius:10px; padding:10px; text-align:center;">
                                <div style="font-size:11px; color:#6B7280; font-weight:700;">SLEEP</div>
                                <div style="font-size:20px; font-weight:800; color:#43D9AD;">{{ $latestLog->sleep_hours }}h</div>
                            </div>
                        </div>
                    @else
                        <div style="background:#F9FAFB; border-radius:10px; padding:12px; text-align:center; margin-bottom:16px; color:#9CA3AF; font-size:13px;">
                            No logs yet
                        </div>
                    @endif

                    <div style="display:flex; gap:8px;">
                        <a href="{{ route('children.show', $child) }}" class="btn btn-primary btn-sm" style="flex:1; justify-content:center;">View</a>
                        <a href="{{ route('insights.show', $child) }}" class="btn btn-secondary btn-sm" style="flex:1; justify-content:center;">📊 Insights</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Recent Activity --}}
    @if($recentLogs->isNotEmpty())
    <div class="card">
        <div class="card-title">📋 Recent Activity</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Child</th>
                        <th>Date</th>
                        <th>Focus</th>
                        <th>Mood</th>
                        <th>Sleep</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogs as $log)
                    <tr>
                        <td class="fw-bold">{{ $log->child->name }}</td>
                        <td class="text-muted">{{ $log->log_date->format('M d, Y') }}</td>
                        <td>
                            <div class="stars">
                                @for($i=1; $i<=5; $i++)
                                    <span class="star {{ $i <= $log->focus_level ? 'star-filled' : 'star-empty' }}">★</span>
                                @endfor
                            </div>
                        </td>
                        <td><span class="mood-{{ $log->mood }}"> {{ ucfirst($log->mood) }}</span></td>
                        <td>{{ $log->sleep_hours }}h</td>
                        <td class="text-muted">{{ Str::limit($log->note, 40) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

{{-- ══════════ TEACHER DASHBOARD ══════════ --}}
@elseif(auth()->user()->isTeacher())

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#EDE9FF;">👥</div>
            <div>
                <div class="stat-value" style="color:#6C63FF;">{{ $children->count() }}</div>
                <div class="stat-label">Total Children</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEE2E2;">⚠️</div>
            <div>
                <div class="stat-value" style="color:#EF4444;">{{ $highRiskChildren->count() }}</div>
                <div class="stat-label">Need Attention</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#D1FAE5;">✅</div>
            <div>
                <div class="stat-value" style="color:#10B981;">{{ $todayLogs->count() }}</div>
                <div class="stat-label">Logged Today</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7;">💬</div>
            <div>
                <div class="stat-value" style="color:#F59E0B;">{{ $unreadMessages }}</div>
                <div class="stat-label">Unread Messages</div>
            </div>
        </div>
    </div>

    @if($highRiskChildren->isNotEmpty())
    <div class="card mb-6" style="border-left:4px solid #EF4444;">
        <div class="card-title">⚠️ Children Needing Attention</div>
        <div class="child-cards">
            @foreach($highRiskChildren as $child)
            <div class="child-card" style="border-color:#FEE2E2;">
                <div class="flex-between mb-6">
                    <div>
                        <div class="child-name">{{ $child->name }}</div>
                        <div class="child-age">{{ $child->age }} years old · Parent: {{ $child->parent->name ?? 'N/A' }}</div>
                    </div>
                    <span class="risk-badge risk-high">🔴 High Risk</span>
                </div>
                <div style="display:flex; gap:8px;">
                    <a href="{{ route('children.show', $child) }}" class="btn btn-danger btn-sm" style="flex:1; justify-content:center;">View Profile</a>
                    <a href="{{ route('insights.show', $child) }}" class="btn btn-secondary btn-sm" style="flex:1; justify-content:center;">📊 Insights</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card">
        <div class="flex-between mb-6">
            <div class="card-title" style="margin:0">📋 All Children</div>
            <button onclick="document.getElementById('quickLogModal').classList.add('open')" class="btn btn-primary btn-sm">
                📝 Quick Log
            </button>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Parent</th>
                        <th>Last Log</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($children as $child)
                    @php
                        $lastLog = $child->behaviors->first();
                        $riskLevel = 'low';
                        if ($lastLog) {
                            $avg = $child->behaviors->avg('focus_level');
                            $riskLevel = $avg < 2.5 ? 'high' : ($avg < 3.5 ? 'medium' : 'low');
                        }
                    @endphp
                    <tr>
                        <td class="fw-bold">{{ $child->name }}</td>
                        <td>{{ $child->age }} yrs</td>
                        <td class="text-muted">{{ $child->parent->name ?? '—' }}</td>
                        <td class="text-muted">{{ $lastLog ? $lastLog->log_date->format('M d') : 'Never' }}</td>
                        <td><span class="risk-badge risk-{{ $riskLevel }}">{{ ucfirst($riskLevel) }}</span></td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                <a href="{{ route('children.show', $child) }}" class="btn btn-secondary btn-sm">View</a>
                                <a href="{{ route('insights.show', $child) }}" class="btn btn-secondary btn-sm">📊</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

{{-- ══════════ PSYCHOLOGIST DASHBOARD ══════════ --}}
@elseif(auth()->user()->isPsychologist())

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#EDE9FF;">👥</div>
            <div>
                <div class="stat-value" style="color:#6C63FF;">{{ $children->count() }}</div>
                <div class="stat-label">Assigned Children</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEE2E2;">🔴</div>
            <div>
                <div class="stat-value" style="color:#EF4444;">{{ $highRiskChildren->count() }}</div>
                <div class="stat-label">High Risk Cases</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7;">📋</div>
            <div>
                <div class="stat-value" style="color:#F59E0B;">{{ $pendingRecs->count() }}</div>
                <div class="stat-label">Pending Recommendations</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#D1FAE5;">💬</div>
            <div>
                <div class="stat-value" style="color:#10B981;">{{ $unreadMessages }}</div>
                <div class="stat-label">New Messages</div>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div>
            <div class="card mb-6">
                <div class="card-title">👥 Assigned Children</div>
                @foreach($children as $child)
                @php
                    $recentLogs = $child->behaviors->take(7);
                    $riskLevel = 'low';
                    if ($recentLogs->isNotEmpty()) {
                        $avg = $recentLogs->avg('focus_level');
                        $riskLevel = $avg < 2.5 ? 'high' : ($avg < 3.5 ? 'medium' : 'low');
                    }
                @endphp
                <div style="display:flex; align-items:center; gap:14px; padding:14px 0; border-bottom:1px solid #F3F4F6;">
                    <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#6C63FF,#FF6584);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:18px;flex-shrink:0;">
                        {{ substr($child->name, 0, 1) }}
                    </div>
                    <div style="flex:1;">
                        <div class="fw-bold">{{ $child->name }}</div>
                        <div class="text-muted">{{ $child->age }} yrs · {{ $child->parent->name ?? 'No parent' }}</div>
                    </div>
                    <span class="risk-badge risk-{{ $riskLevel }}">{{ ucfirst($riskLevel) }}</span>
                    <a href="{{ route('children.show', $child) }}" class="btn btn-secondary btn-sm">View</a>
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-title">📋 Pending Recommendations</div>
                @forelse($pendingRecs as $rec)
                <div style="padding:14px 0; border-bottom:1px solid #F3F4F6;">
                    <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:6px;">
                        <div class="fw-bold" style="font-size:14px;">{{ $rec->title }}</div>
                        <span style="font-size:11px; background:#EDE9FF; color:#6C63FF; padding:3px 10px; border-radius:20px; font-weight:700; white-space:nowrap; margin-left:8px;">
                            {{ ucfirst($rec->category) }}
                        </span>
                    </div>
                    <div class="text-muted">For: {{ $rec->child->name }}</div>
                    <form action="{{ route('recommendations.toggle', $rec) }}" method="POST" style="margin-top:8px;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm">✓ Mark Complete</button>
                    </form>
                </div>
                @empty
                    <div style="text-align:center; padding:30px; color:#9CA3AF;">
                        <div style="font-size:40px; margin-bottom:8px;">✅</div>
                        <div>All recommendations completed!</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

@endif

{{-- ══════════ QUICK LOG MODAL (Teacher) ══════════ --}}
@if(auth()->user()->isTeacher())
<div class="modal-overlay" id="quickLogModal">
    <div class="modal">
        <div class="flex-between mb-6">
            <div class="modal-title">📝 Quick Behavior Log</div>
            <button onclick="document.getElementById('quickLogModal').classList.remove('open')"
                style="background:none;border:none;font-size:22px;cursor:pointer;color:#6B7280;">✕</button>
        </div>

        <form action="{{ route('behaviors.quick-log') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Select Child</label>
                <select name="child_id" class="form-control" required>
                    <option value="">— Choose a child —</option>
                    @foreach($children as $child)
                        <option value="{{ $child->id }}">{{ $child->name }} ({{ $child->age }} yrs)</option>
                    @endforeach
                </select>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Focus Level (1-5)</label>
                    <input type="number" name="focus_level" class="form-control" min="1" max="5" value="3" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Sleep Hours</label>
                    <input type="number" name="sleep_hours" class="form-control" min="0" max="24" step="0.5" value="8" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Social Interaction (1-5)</label>
                    <input type="number" name="social_interaction" class="form-control" min="1" max="5" value="3" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mood</label>
                    <select name="mood" class="form-control" required>
                        <option value="happy">😊 Happy</option>
                        <option value="neutral" selected>😐 Neutral</option>
                        <option value="sad">😢 Sad</option>
                        <option value="anxious">😰 Anxious</option>
                        <option value="angry">😠 Angry</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Note (optional)</label>
                <textarea name="note" class="form-control" rows="3" placeholder="Any observations..."></textarea>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">Save Log</button>
                <button type="button" onclick="document.getElementById('quickLogModal').classList.remove('open')"
                    class="btn btn-secondary" style="flex:1; justify-content:center;">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
