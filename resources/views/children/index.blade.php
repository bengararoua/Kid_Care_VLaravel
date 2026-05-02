@extends('layouts.app')
@section('title', 'Children')
@section('page-title', '👶 Children')
@section('topbar-actions')
    @if(auth()->user()->isParent())
        <a href="{{ route('children.create') }}" class="btn btn-primary btn-sm">➕ Add Child</a>
    @endif
@endsection
@section('content')
<div class="child-cards">
    @forelse($children as $child)
    @php
        $lastLog = $child->behaviors->first();
        $riskLevel = 'low';
        if ($lastLog) {
            $avg = $child->behaviors->avg('focus_level');
            $riskLevel = $avg < 2.5 ? 'high' : ($avg < 3.5 ? 'medium' : 'low');
        }
    @endphp
    <div class="child-card">
        <div class="child-avatar">{{ substr($child->name,0,1) }}</div>
        <div class="flex-between" style="margin-bottom:6px;">
            <div class="child-name">{{ $child->name }}</div>
            <span class="risk-badge risk-{{ $riskLevel }}">{{ ucfirst($riskLevel) }}</span>
        </div>
        <div class="child-age">{{ $child->age }} years old</div>
        @if($child->parent ?? false)
            <div class="text-muted" style="margin-bottom:12px;">👨‍👩‍👧 {{ $child->parent->name }}</div>
        @endif
        <div style="display:flex; gap:8px; margin-top:12px;">
            <a href="{{ route('children.show',$child) }}" class="btn btn-primary btn-sm" style="flex:1;justify-content:center;">View</a>
            <a href="{{ route('insights.show',$child) }}" class="btn btn-secondary btn-sm" style="flex:1;justify-content:center;">📊</a>
        </div>
    </div>
    @empty
    <div class="card" style="text-align:center;padding:60px;grid-column:1/-1;">
        <div style="font-size:60px;margin-bottom:16px;">👶</div>
        <h3 style="margin-bottom:8px;">No children found</h3>
        @if(auth()->user()->isParent())
            <a href="{{ route('children.create') }}" class="btn btn-primary" style="margin-top:16px;">➕ Add First Child</a>
        @endif
    </div>
    @endforelse
</div>
@endsection
