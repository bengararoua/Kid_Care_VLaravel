<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KidCare Insight — @yield('title', 'Welcome')</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #F0F2FF;
        }

        /* LEFT PANEL */
        .left-panel {
            width: 45%;
            background: linear-gradient(160deg, #1A1D2E 0%, #2D2F55 60%, #3D3B7C 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(108,99,255,.3) 0%, transparent 70%);
            border-radius: 50%;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,101,132,.2) 0%, transparent 70%);
            border-radius: 50%;
        }

        .brand {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            position: relative; z-index: 1;
        }

        .brand span { color: #A5A1FF; }

        .tagline {
            font-size: 15px;
            color: rgba(255,255,255,.5);
            margin-bottom: 60px;
            position: relative; z-index: 1;
        }

        .features { position: relative; z-index: 1; }

        .feature {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 32px;
        }

        .feature-icon {
            width: 48px; height: 48px;
            background: rgba(108,99,255,.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .feature h3 { font-size: 16px; font-weight: 700; color: white; margin-bottom: 4px; }
        .feature p { font-size: 13px; color: rgba(255,255,255,.5); line-height: 1.5; }

        /* RIGHT PANEL */
        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .auth-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 20px 60px rgba(0,0,0,.1);
        }

        /* TABS */
        .auth-tabs {
            display: flex;
            background: #F0F2FF;
            border-radius: 14px;
            padding: 5px;
            margin-bottom: 32px;
        }

        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            color: #6B7280;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .auth-tab.active {
            background: white;
            color: #6C63FF;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }

        /* FORM ELEMENTS */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 7px;
            color: #374151;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
        }

        .form-input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Nunito', sans-serif;
            color: #1A1D2E;
            transition: border-color .2s;
        }

        .form-input:focus { outline: none; border-color: #6C63FF; }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .forgot-link {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 18px;
        }
        .forgot-link a { font-size: 13px; color: #6C63FF; text-decoration: none; font-weight: 600; }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #6C63FF, #43D9AD);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Nunito', sans-serif;
            transition: all .2s;
            margin-bottom: 24px;
        }

        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(108,99,255,.35); }

        /* ROLE SELECTOR */
        .role-label { font-size: 13px; font-weight: 700; margin-bottom: 10px; color: #374151; }

        .role-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px; }

        .role-option { display: none; }
        .role-card {
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 14px 8px;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
        }
        .role-option:checked + .role-card {
            border-color: #6C63FF;
            background: #F0EEFF;
        }
        .role-card .role-emoji { font-size: 24px; display: block; margin-bottom: 6px; }
        .role-card .role-name { font-size: 12px; font-weight: 700; color: #374151; }

        /* DEMO ACCOUNTS */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            color: #9CA3AF;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .5px;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #E5E7EB; }

        .demo-accounts { display: flex; flex-direction: column; gap: 8px; }

        .demo-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
        }
        .demo-btn:hover { background: #F0EEFF; border-color: #6C63FF; }
        .demo-btn .demo-icon { font-size: 22px; }
        .demo-btn .demo-info { text-align: left; }
        .demo-btn .demo-role { font-size: 11px; color: #6B7280; }

        .error-box {
            background: #FEE2E2;
            border: 1px solid #FECACA;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #991B1B;
        }

        @media (max-width: 768px) {
            .left-panel { display: none; }
        }
    </style>
</head>
<body>
    <div class="left-panel">
        <div class="brand">🧠 Kid<span>Care</span> Insight</div>
        <div class="tagline">Supporting children's behavioral development</div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">📊</div>
                <div>
                    <h3>Behavioral Tracking</h3>
                    <p>Monitor focus, mood, sleep, and social interaction with insightful charts.</p>
                </div>
            </div>
            <div class="feature">
                <div class="feature-icon">🤝</div>
                <div>
                    <h3>Team Collaboration</h3>
                    <p>Parents, teachers, and psychologists work together for each child.</p>
                </div>
            </div>
            <div class="feature">
                <div class="feature-icon">🧠</div>
                <div>
                    <h3>AI-Powered Insights</h3>
                    <p>Automatic risk assessment and personalized recommendations.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="auth-card">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
