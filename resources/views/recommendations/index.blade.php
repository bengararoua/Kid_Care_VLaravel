@extends('layouts.app')
@section('title', 'Recommendations — ' . $child->name)
@section('page-title', '📋 Recommendations — ' . $child->name)
@section('topbar-actions')
    <a href="{{ route('children.show', $child) }}" class="btn btn-secondary btn-sm">← Back</a>
@endsection
@section('content')

@if(auth()->user()->isPsychologist())
<div class="card mb-6">
    <div class="card-title">➕ Add Recommendation</div>
    <form action="{{ route('recommendations.store', $child) }}" method="POST">
        @csrf
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Morning Mindfulness" required>
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" class="form-control" required>
                    <option value="focus">🎯 Focus</option>
                    <option value="social">🤝 Social</option>
                    <option value="relaxation">🧘 Relaxation</option>
                    <option value="routine">⏰ Routine</option>
                    <option value="sleep">😴 Sleep</option>
                    <option value="nutrition">🥗 Nutrition</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" required placeholder="Detailed recommendation..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">💾 Add Recommendation</button>
    </form>
</div>
@endif

<div class="card">
    <div class="card-title">All Recommendations</div>
    @php $categoryIcons = ['focus'=>'🎯','social'=>'🤝','relaxation'=>'🧘','routine'=>'⏰','sleep'=>'😴','nutrition'=>'🥗']; @endphp
    @forelse($recommendations as $rec)
    <div style="display:flex;align-items:start;gap:14px;padding:18px 0;border-bottom:1px solid #F3F4F6;">
        <div style="font-size:28px;">{{ $categoryIcons[$rec->category] ?? '📌' }}</div>
        <div style="flex:1;">
            <div style="font-weight:700;margin-bottom:4px;{{ $rec->is_completed ? 'text-decoration:line-through;color:#9CA3AF;' : '' }}">
                {{ $rec->title }}
            </div>
            <div class="text-muted" style="margin-bottom:8px;">{{ $rec->description }}</div>
            <span style="background:#EDE9FF;color:#6C63FF;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                {{ ucfirst($rec->category) }}
            </span>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            @if($rec->is_completed)
                <span class="risk-badge risk-low">✓ Completed</span>
            @else
                <span class="risk-badge risk-medium">Pending</span>
            @endif
            <form action="{{ route('recommendations.toggle',$rec) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-success btn-sm">{{ $rec->is_completed ? '↩ Undo' : '✓ Done' }}</button>
            </form>
            @if(auth()->user()->isPsychologist())
            <form action="{{ route('recommendations.destroy',$rec) }}" method="POST" onsubmit="return confirm('Delete this?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:40px;color:#9CA3AF;">
        <div style="font-size:48px;margin-bottom:12px;">📋</div>
        <div>No recommendations yet.</div>
    </div>
    @endforelse
</div>
@endsection
