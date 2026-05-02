@extends('layouts.app')
@section('title', 'Add Child')
@section('page-title', '➕ Add Child')
@section('content')
<div style="max-width:600px; margin:0 auto;">
    <div class="card">
        <div class="card-title">Child Information</div>
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
        @endif
        <form action="{{ route('children.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Child's name">
            </div>
            <div class="form-group">
                <label class="form-label">Age</label>
                <input type="number" name="age" class="form-control" value="{{ old('age') }}" min="1" max="18" required>
            </div>
            <div class="form-group">
                <label class="form-label">Assign Psychologist (optional)</label>
                <select name="psychologist_id" class="form-control">
                    <option value="">— None —</option>
                    @foreach($psychologists as $p)
                        <option value="{{ $p->id }}" {{ old('psychologist_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Any important notes...">{{ old('notes') }}</textarea>
            </div>
            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary">💾 Add Child</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
