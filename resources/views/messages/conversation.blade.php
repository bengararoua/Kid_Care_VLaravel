@extends('layouts.app')
@section('title', 'Chat — ' . $user->name)
@section('page-title', '💬 ' . $user->name)
@section('content')
<div style="display:grid; grid-template-columns:300px 1fr; gap:20px; height:calc(100vh - 180px);">

    {{-- Contacts --}}
    <div class="card" style="overflow-y:auto; padding:0;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); font-weight:700;">Contacts</div>
        @foreach($contacts as $contact)
        <a href="{{ route('messages.conversation', $contact) }}"
            style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid #F3F4F6;text-decoration:none;color:var(--text);{{ $contact->id == $user->id ? 'background:#F0EEFF;' : '' }}"
            onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='{{ $contact->id == $user->id ? '#F0EEFF' : '' }}'">
            <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#6C63FF,#43D9AD);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;flex-shrink:0;">
                {{ substr($contact->name,0,1) }}
            </div>
            <div>
                <div style="font-weight:700;font-size:14px;">{{ $contact->name }}</div>
                <div style="font-size:12px;color:#9CA3AF;">{{ ucfirst($contact->role) }}</div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Conversation --}}
    <div class="card" style="display:flex;flex-direction:column;padding:0;overflow:hidden;">
        {{-- Header --}}
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#6C63FF,#43D9AD);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:18px;">
                {{ substr($user->name,0,1) }}
            </div>
            <div>
                <div style="font-weight:800;font-size:16px;">{{ $user->name }}</div>
                <div style="font-size:12px;color:#9CA3AF;">{{ ucfirst($user->role) }}</div>
            </div>
        </div>

        {{-- Messages --}}
        <div id="msgList" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:12px;">
            @forelse($messages as $msg)
            @php $isMe = $msg->sender_id == auth()->id(); @endphp
            <div style="display:flex;justify-content:{{ $isMe ? 'flex-end' : 'flex-start' }};">
                <div style="max-width:70%;background:{{ $isMe ? '#6C63FF' : '#F3F4F6' }};color:{{ $isMe ? 'white' : 'var(--text)' }};padding:12px 16px;border-radius:{{ $isMe ? '18px 18px 4px 18px' : '18px 18px 18px 4px' }};font-size:14px;line-height:1.5;">
                    {{ $msg->content }}
                    <div style="font-size:10px;opacity:.6;margin-top:4px;text-align:right;">
                        {{ $msg->created_at->format('h:i A') }}
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:#9CA3AF;margin:auto;">
                <div style="font-size:40px;margin-bottom:8px;">👋</div>
                <div>Start the conversation!</div>
            </div>
            @endforelse
        </div>

        {{-- Input --}}
        <div style="padding:16px 20px;border-top:1px solid var(--border);">
            <form action="{{ route('messages.send', $user) }}" method="POST" style="display:flex;gap:12px;">
                @csrf
                <input type="text" name="content" class="form-control" placeholder="Type a message..." required autocomplete="off" style="flex:1;">
                <button type="submit" class="btn btn-primary">Send →</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Scroll to bottom of messages
    const list = document.getElementById('msgList');
    if(list) list.scrollTop = list.scrollHeight;
</script>
@endsection
