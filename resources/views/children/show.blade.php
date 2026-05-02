@extends('layouts.app')

@section('title', $child->name . ' — Profile')
@section('page-title', '👶 ' . $child->name . '\'s Profile')

@section('topbar-actions')
    <a href="{{ route('insights.show', $child) }}" class="btn btn-secondary btn-sm">📊 View Insights</a>
    @if(auth()->user()->isPsychologist())
        <a href="{{ route('recommendations.index', $child) }}" class="btn btn-primary btn-sm">📋 Recommendations</a>
    @endif
@endsection

@section('content')

{{-- Child Info Card --}}
<div class="grid-2 mb-6">
    <div class="card">
        <div style="display:flex; align-items:center; gap:20px; margin-bottom:20px;">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#6C63FF,#FF6584);display:flex;align-items:center;justify-content:center;font-size:32px;color:white;font-weight:800;flex-shrink:0;">
                {{ substr($child->name, 0, 1) }}
            </div>
            <div>
                <h2 style="font-size:22px; font-weight:800; margin-bottom:4px;">{{ $child->name }}</h2>
                <div class="text-muted">{{ $child->age }} years old</div>
                @if($child->psychologist)
                    <div style="font-size:13px; margin-top:4px;">🧑‍⚕️ <strong>{{ $child->psychologist->name }}</strong></div>
                @endif
            </div>
        </div>

        @if($child->notes)
            <div style="background:#F9FAFB; border-radius:10px; padding:14px; font-size:14px; color:#374151;">
                <strong>Notes:</strong> {{ $child->notes }}
            </div>
        @endif

        @if(auth()->user()->isParent())
            <div style="margin-top:16px; display:flex; gap:8px;">
                <a href="{{ route('children.edit', $child) }}" class="btn btn-secondary btn-sm">✏️ Edit</a>
            </div>
        @endif
    </div>

    {{-- Quick Stats --}}
    <div class="card">
        <div class="card-title">📊 Recent Stats (Last 7 Days)</div>
        @php
            $recentLogs = $behaviors->take(7);
            $avgFocus = $recentLogs->avg('focus_level') ?? 0;
            $avgSleep = $recentLogs->avg('sleep_hours') ?? 0;
            $avgSocial = $recentLogs->avg('social_interaction') ?? 0;
        @endphp
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div style="text-align:center; background:#F0EEFF; border-radius:12px; padding:16px;">
                <div style="font-size:26px; font-weight:800; color:#6C63FF;">{{ number_format($avgFocus, 1) }}</div>
                <div style="font-size:11px; font-weight:700; color:#6C63FF; text-transform:uppercase;">Focus</div>
            </div>
            <div style="text-align:center; background:#ECFDF5; border-radius:12px; padding:16px;">
                <div style="font-size:26px; font-weight:800; color:#10B981;">{{ number_format($avgSleep, 1) }}h</div>
                <div style="font-size:11px; font-weight:700; color:#10B981; text-transform:uppercase;">Sleep</div>
            </div>
            <div style="text-align:center; background:#FFF7ED; border-radius:12px; padding:16px;">
                <div style="font-size:26px; font-weight:800; color:#F59E0B;">{{ number_format($avgSocial, 1) }}</div>
                <div style="font-size:11px; font-weight:700; color:#F59E0B; text-transform:uppercase;">Social</div>
            </div>
        </div>
    </div>
</div>

{{-- Add Behavior Log (Teachers and Parents) --}}
@if(auth()->user()->isTeacher() || auth()->user()->isParent())
<div class="card mb-6">
    <div class="card-title">➕ Add Behavior Log</div>
    <form action="{{ route('behaviors.store', $child) }}" method="POST">
        @csrf
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:16px;">
            <div class="form-group">
                <label class="form-label">Date</label>
                <input type="date" name="log_date" class="form-control" value="{{ today()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Focus Level (1–5)</label>
                <input type="number" name="focus_level" class="form-control" min="1" max="5" value="3" required>
            </div>
            <div class="form-group">
                <label class="form-label">Sleep Hours</label>
                <input type="number" name="sleep_hours" class="form-control" min="0" max="24" step="0.5" value="8" required>
            </div>
            <div class="form-group">
                <label class="form-label">Social (1–5)</label>
                <input type="number" name="social_interaction" class="form-control" min="1" max="5" value="3" required>
            </div>
            <div class="form-group">
                <label class="form-label">Mood</label>
                <select name="mood" class="form-control">
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
            <textarea name="note" class="form-control" rows="2" placeholder="Observations..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">💾 Save Log</button>
    </form>
</div>
@endif

{{-- Behavior Logs Table --}}
<div class="card">
    <div class="flex-between mb-6">
        <div class="card-title" style="margin:0">📋 Behavior History</div>
        <span class="text-muted">{{ $behaviors->total() }} total logs</span>
    </div>

    @if($behaviors->isEmpty())
        <div style="text-align:center; padding:40px; color:#9CA3AF;">
            <div style="font-size:48px; margin-bottom:12px;">📭</div>
            <div>No behavior logs yet. Start adding logs above.</div>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Focus</th>
                        <th>Mood</th>
                        <th>Sleep</th>
                        <th>Social</th>
                        <th>Note</th>
                        <th>Logged By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($behaviors as $log)
                    <tr>
                        <td class="fw-bold">{{ $log->log_date->format('M d, Y') }}</td>
                        <td>
                            <div class="stars">
                                @for($i=1;$i<=5;$i++)
                                    <span class="star {{ $i<=$log->focus_level?'star-filled':'star-empty' }}">★</span>
                                @endfor
                            </div>
                        </td>
                        <td><span class="mood-{{ $log->mood }}"> {{ ucfirst($log->mood) }}</span></td>
                        <td>{{ $log->sleep_hours }}h</td>
                        <td>
                            <div class="stars">
                                @for($i=1;$i<=5;$i++)
                                    <span class="star {{ $i<=$log->social_interaction?'star-filled':'star-empty' }}">★</span>
                                @endfor
                            </div>
                        </td>
                        <td class="text-muted" style="max-width:200px;">{{ Str::limit($log->note, 50) }}</td>
                        <td class="text-muted">{{ $log->user->name ?? 'Unknown' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:16px;">{{ $behaviors->links() }}</div>
    @endif
</div>

@endsection
