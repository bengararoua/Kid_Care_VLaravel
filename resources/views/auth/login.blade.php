<x-guest-layout>
    <!-- Tabs -->
    <div class="auth-tabs">
        <a href="{{ route('login') }}" class="auth-tab active">🔐 Sign In</a>
        <a href="{{ route('register') }}" class="auth-tab">✨ Create Account</a>
    </div>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <div class="input-wrap">
                <span class="input-icon">📧</span>
                <input type="email" name="email" class="form-input"
                    value="{{ old('email') }}"
                    placeholder="your@email.com"
                    required autofocus autocomplete="username">
            </div>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-wrap">
                <span class="input-icon">🔒</span>
                <input type="password" name="password" id="pwd" class="form-input"
                    placeholder="••••••••"
                    required autocomplete="current-password">
                <button type="button" class="password-toggle" onclick="togglePwd()">👁️</button>
            </div>
        </div>

        <!-- Forgot -->
        @if (Route::has('password.request'))
        <div class="forgot-link">
            <a href="{{ route('password.request') }}">Forgot Password?</a>
        </div>
        @endif

        <button type="submit" class="btn-submit">Sign In →</button>
    </form>

    <div class="divider">🎯 TRY DEMO ACCOUNTS</div>

    <div class="demo-accounts">
        <button class="demo-btn" onclick="fillDemo('parent@example.com')">
            <span class="demo-icon">👨‍👩‍👧</span>
            <div class="demo-info">
                <div>Parent Account</div>
                <div class="demo-role">parent@example.com</div>
            </div>
        </button>
        <button class="demo-btn" onclick="fillDemo('teacher@example.com')">
            <span class="demo-icon">📚</span>
            <div class="demo-info">
                <div>Teacher Account</div>
                <div class="demo-role">teacher@example.com</div>
            </div>
        </button>
        <button class="demo-btn" onclick="fillDemo('psychologist@example.com')">
            <span class="demo-icon">🧑‍⚕️</span>
            <div class="demo-info">
                <div>Psychologist Account</div>
                <div class="demo-role">psychologist@example.com</div>
            </div>
        </button>
    </div>

    <script>
        function fillDemo(email) {
            document.querySelector('input[name="email"]').value = email;
            document.querySelector('input[name="password"]').value = 'password';
        }
        function togglePwd() {
            const p = document.getElementById('pwd');
            p.type = p.type === 'password' ? 'text' : 'password';
        }
    </script>
</x-guest-layout>
