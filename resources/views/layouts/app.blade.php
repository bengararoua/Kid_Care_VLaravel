<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KidCare Insight') — Supporting Children's Development</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --primary: #6C63FF;
            --primary-dark: #5A52D5;
            --secondary: #FF6584;
            --success: #43D9AD;
            --warning: #FFB547;
            --danger: #FF5A5F;
            --bg: #F0F2FF;
            --card: #FFFFFF;
            --text: #1A1D2E;
            --text-muted: #6B7280;
            --border: #E5E7F0;
            --sidebar-w: 260px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(160deg, #1A1D2E 0%, #2D2F55 100%);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            display: flex;
            flex-direction: column;
            padding: 0;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,.15);
        }

        .sidebar-logo {
            padding: 28px 24px 24px;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .sidebar-logo .brand {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 22px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-logo .brand span { color: var(--primary); }

        .sidebar-logo .tagline {
            font-size: 11px;
            color: rgba(255,255,255,.4);
            margin-top: 4px;
            letter-spacing: .5px;
        }

        .sidebar-user {
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .user-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .avatar-parent { background: rgba(108,99,255,.25); }
        .avatar-teacher { background: rgba(67,217,173,.25); }
        .avatar-psychologist { background: rgba(255,101,132,.25); }

        .user-info .name { font-size: 13px; font-weight: 700; color: white; }
        .user-info .role-badge {
            display: inline-block;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 20px;
            margin-top: 3px;
            font-weight: 600;
            letter-spacing: .3px;
        }
        .badge-parent { background: rgba(108,99,255,.3); color: #9D97FF; }
        .badge-teacher { background: rgba(67,217,173,.3); color: #43D9AD; }
        .badge-psychologist { background: rgba(255,101,132,.3); color: #FF9BAD; }

        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }

        .nav-section-title {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,.3);
            text-transform: uppercase;
            padding: 8px 12px 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: rgba(255,255,255,.6);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
            transition: all .2s;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(108,99,255,.25);
            color: white;
        }

        .nav-link.active { color: #A5A1FF; }

        .nav-link .icon { font-size: 18px; width: 22px; text-align: center; }

        .nav-link .badge-count {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 700;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,.07);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 11px 14px;
            background: rgba(255,90,95,.15);
            border: none;
            border-radius: 10px;
            color: #FF8A8E;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }
        .logout-btn:hover { background: rgba(255,90,95,.3); }

        /* ── MAIN CONTENT ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }

        .page-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--text);
        }

        .topbar-actions { display: flex; gap: 12px; align-items: center; }

        .content { padding: 32px; flex: 1; }

        /* ── CARDS ── */
        .card {
            background: var(--card);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1px solid var(--border);
        }

        .card-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text);
        }

        /* ── STAT CARDS ── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 28px; }

        .stat-card {
            background: var(--card);
            border-radius: 16px;
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1px solid var(--border);
        }

        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .stat-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
        }

        .stat-label { font-size: 12px; color: var(--text-muted); font-weight: 600; margin-top: 4px; }

        /* ── RISK BADGE ── */
        .risk-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }
        .risk-low { background: #D1FAE5; color: #065F46; }
        .risk-medium { background: #FEF3C7; color: #92400E; }
        .risk-high { background: #FEE2E2; color: #991B1B; }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(108,99,255,.35); }
        .btn-secondary { background: var(--bg); color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { background: #E5E7F0; }
        .btn-danger { background: #FEE2E2; color: var(--danger); }
        .btn-danger:hover { background: var(--danger); color: white; }
        .btn-success { background: #D1FAE5; color: #065F46; }
        .btn-sm { padding: 7px 14px; font-size: 13px; }

        /* ── FORMS ── */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 6px; color: var(--text); }
        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Nunito', sans-serif;
            color: var(--text);
            background: white;
            transition: border-color .2s;
        }
        .form-control:focus { outline: none; border-color: var(--primary); }
        .form-error { color: var(--danger); font-size: 12px; margin-top: 5px; }

        /* ── TABLE ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--text-muted); padding: 10px 14px; border-bottom: 2px solid var(--border); }
        td { padding: 13px 14px; border-bottom: 1px solid var(--border); font-size: 14px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #F8F9FF; }

        /* ── ALERTS ── */
        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
        .alert-success { background: #D1FAE5; color: #065F46; border-left: 4px solid #10B981; }
        .alert-danger { background: #FEE2E2; color: #991B1B; border-left: 4px solid var(--danger); }

        /* ── CHILD CARD ── */
        .child-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }

        .child-card {
            background: var(--card);
            border-radius: 16px;
            padding: 22px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            transition: all .2s;
        }
        .child-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }

        .child-avatar {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 14px;
        }

        .child-name { font-size: 18px; font-weight: 800; }
        .child-age { font-size: 13px; color: var(--text-muted); margin-bottom: 12px; }

        /* ── MOOD EMOJI ── */
        .mood-happy::before { content: '😊'; }
        .mood-neutral::before { content: '😐'; }
        .mood-sad::before { content: '😢'; }
        .mood-anxious::before { content: '😰'; }
        .mood-angry::before { content: '😠'; }

        /* ── STAR RATING ── */
        .stars { display: flex; gap: 3px; }
        .star { font-size: 14px; }
        .star-filled { color: #FFB547; }
        .star-empty { color: #E5E7F0; }

        /* ── MODAL ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: white;
            border-radius: 20px;
            padding: 32px;
            width: 90%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp .25s ease;
        }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-title { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 20px; font-weight: 800; margin-bottom: 24px; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
            .content { padding: 20px; }
        }

        /* ── UTILS ── */
        .flex { display: flex; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; }
        .gap-3 { gap: 12px; }
        .mb-6 { margin-bottom: 24px; }
        .mt-4 { margin-top: 16px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .text-muted { color: var(--text-muted); font-size: 13px; }
        .fw-bold { font-weight: 700; }
    </style>

    @stack('styles')
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="brand">🧠 Kid<span>Care</span></div>
            <div class="tagline">Children's Behavioral Insight</div>
        </div>

        <div class="sidebar-user">
            @php $role = auth()->user()->role; @endphp
            <div class="user-avatar avatar-{{ $role }}">
                {{ $role === 'parent' ? '👨‍👩‍👧' : ($role === 'teacher' ? '📚' : '🧑‍⚕️') }}
            </div>
            <div class="user-info">
                <div class="name">{{ auth()->user()->name }}</div>
                <span class="role-badge badge-{{ $role }}">{{ ucfirst($role) }}</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Main</div>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="icon">🏠</span> Dashboard
            </a>

            @if(auth()->user()->isParent())
                <a href="{{ route('children.index') }}" class="nav-link {{ request()->routeIs('children.*') ? 'active' : '' }}">
                    <span class="icon">👶</span> My Children
                </a>
            @endif

            @if(auth()->user()->isTeacher())
                <a href="{{ route('children.index') }}" class="nav-link {{ request()->routeIs('children.*') ? 'active' : '' }}">
                    <span class="icon">👥</span> All Children
                </a>
            @endif

            @if(auth()->user()->isPsychologist())
                <a href="{{ route('children.index') }}" class="nav-link {{ request()->routeIs('children.*') ? 'active' : '' }}">
                    <span class="icon">👥</span> Assigned Children
                </a>
            @endif

            <div class="nav-section-title" style="margin-top:12px">Communication</div>

            <a href="{{ route('messages.index') }}" class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}">
                <span class="icon">💬</span> Messages
                @if(isset($unreadMessages) && $unreadMessages > 0)
                    <span class="badge-count">{{ $unreadMessages }}</span>
                @endif
            </a>

            <div class="nav-section-title" style="margin-top:12px">Account</div>

            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <span class="icon">⚙️</span> Profile Settings
            </a>
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <span>🚪</span> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <div class="topbar">
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            <div class="topbar-actions">
                @yield('topbar-actions')
            </div>
        </div>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">⚠️ {{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
