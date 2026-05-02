@extends('layouts.app')
@section('title', 'Messages')
@section('page-title', '💬 Messages')
@section('content')
<div style="display:grid; grid-template-columns:300px 1fr; gap:20px; height:calc(100vh - 180px);">

    {{-- Contacts sidebar --}}
    <div class="card" style="overflow-y:auto; padding:0;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); font-weight:700;">Contacts</div>
        @foreach($contacts as $contact)
        <a href="{{ route('messages.conversation', $contact) }}"
            style="display:flex; align-items:center; gap:12px; padding:14px 20px; border-bottom:1px solid #F3F4F6; text-decoration:none; color:var(--text); transition:background .15s;"
            onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
            <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#6C63FF,#43D9AD);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;flex-shrink:0;">
                {{ substr($contact->name,0,1) }}
            </div>
            <div>
                <div style="font-weight:700; font-size:14px;">{{ $contact->name }}</div>
                <div style="font-size:12px; color:#9CA3AF;">{{ ucfirst($contact->role) }}</div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Empty state --}}
    <div class="card" style="display:flex; align-items:center; justify-content:center; flex-direction:column;">
        <div style="font-size:60px; margin-bottom:16px;">💬</div>
        <h3 style="margin-bottom:8px;">Select a contact to start messaging</h3>
        <p class="text-muted">Choose someone from the left to begin a conversation.</p>
    </div>
</div>
@endsection
