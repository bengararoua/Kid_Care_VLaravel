@extends('layouts.app')
@section('title', 'Profile Settings')
@section('page-title', '⚙️ Profile Settings')
@section('content')
<div style="max-width:600px;margin:0 auto;">
    <div class="card mb-6">
        <div class="card-title">Personal Information</div>
        @if(session('status') === 'profile-updated')
            <div class="alert alert-success">✅ Profile updated successfully!</div>
        @endif
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf @method('PATCH')
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="{{ ucfirst(auth()->user()->role) }}" disabled style="background:#F9FAFB;color:#9CA3AF;">
            </div>
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Change Password</div>
        <form action="{{ route('password.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">🔒 Update Password</button>
        </form>
    </div>
</div>
@endsection
