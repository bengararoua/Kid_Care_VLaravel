<x-guest-layout>
    <!-- Tabs -->
    <div class="auth-tabs">
        <a href="{{ route('login') }}" class="auth-tab">🔐 Sign In</a>
        <a href="{{ route('register') }}" class="auth-tab active">✨ Create Account</a>
    </div>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <div class="input-wrap">
                <span class="input-icon">👤</span>
                <input type="text" name="name" class="form-input"
                    value="{{ old('name') }}"
                    placeholder="Your full name"
                    required autofocus>
            </div>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <div class="input-wrap">
                <span class="input-icon">📧</span>
                <input type="email" name="email" class="form-input"
                    value="{{ old('email') }}"
                    placeholder="your@email.com"
                    required>
            </div>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-wrap">
                <span class="input-icon">🔒</span>
                <input type="password" name="password" id="pwd" class="form-input"
                    placeholder="Min. 8 characters"
                    required>
                <button type="button" class="password-toggle" onclick="togglePwd('pwd')">👁️</button>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <div class="input-wrap">
                <span class="input-icon">✓</span>
                <input type="password" name="password_confirmation" id="pwd2" class="form-input"
                    placeholder="Repeat password"
                    required>
                <button type="button" class="password-toggle" onclick="togglePwd('pwd2')">👁️</button>
            </div>
        </div>

        <!-- Role -->
        <div class="form-group">
            <div class="role-label">I am a...</div>
            <div class="role-grid">
                <label>
                    <input type="radio" name="role" value="parent" class="role-option"
                        {{ old('role', 'parent') === 'parent' ? 'checked' : '' }}>
                    <div class="role-card">
                        <span class="role-emoji">👨‍👩‍👧</span>
                        <span class="role-name">Parent</span>
                    </div>
                </label>
                <label>
                    <input type="radio" name="role" value="teacher" class="role-option"
                        {{ old('role') === 'teacher' ? 'checked' : '' }}>
                    <div class="role-card">
                        <span class="role-emoji">📚</span>
                        <span class="role-name">Teacher</span>
                    </div>
                </label>
                <label>
                    <input type="radio" name="role" value="psychologist" class="role-option"
                        {{ old('role') === 'psychologist' ? 'checked' : '' }}>
                    <div class="role-card">
                        <span class="role-emoji">🧑‍⚕️</span>
                        <span class="role-name">Psychologist</span>
                    </div>
                </label>
            </div>
        </div>

        <button type="submit" class="btn-submit">Create Account →</button>
    </form>

    <script>
        function togglePwd(id) {
            const p = document.getElementById(id);
            p.type = p.type === 'password' ? 'text' : 'password';
        }
    </script>
</x-guest-layout>
