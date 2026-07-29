<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — HRIS-Hub</title>
    <link rel="stylesheet" href="<?= base_url('public/vendor/fonts/plus-jakarta-sans.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/font-awesome/6.6.0/css/all.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/animations.css') ?>">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--sap-bg, #f5f6f7);
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }

        /* Left Panel — Branding */
        .auth-split-left {
            flex: 1;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #0F172A 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }
        .auth-split-left::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(15,118,110,0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(20,184,166,0.05) 0%, transparent 50%);
            animation: authBgDrift 20s ease-in-out infinite alternate;
        }
        @keyframes authBgDrift {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(2%, 2%) rotate(3deg); }
        }
        .auth-brand {
            position: relative; z-index: 1;
            text-align: center; max-width: 420px;
        }
        .auth-brand .brand-icon {
            width: 72px; height: 72px;
            background: rgba(15,118,110,0.15);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            color: #fff; font-size: 36px;
            animation: authFloat 3s ease-in-out infinite;
        }
        @keyframes authFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .auth-brand h1 {
            font-size: 28px; font-weight: 800;
            color: #fff; margin: 0 0 8px;
            letter-spacing: -0.02em;
        }
        .auth-brand p {
            font-size: 14px;
            color: rgba(255,255,255,0.6);
            line-height: 1.6; margin: 0;
        }
        .auth-features {
            display: flex; flex-direction: column;
            gap: 12px; margin-top: 40px;
            text-align: left;
        }
        .auth-feature {
            display: flex; align-items: center; gap: 12px;
            color: rgba(255,255,255,0.7); font-size: 13px;
        }
        .auth-feature i {
            width: 32px; height: 32px;
            background: rgba(15,118,110,0.15);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #0F766E; font-size: 14px; flex-shrink: 0;
        }

        /* Right Panel — Form */
        .auth-split-right {
            width: 440px;
            display: flex; align-items: center; justify-content: center;
            padding: 40px;
            background: #fff;
            min-height: 100vh;
        }
        .auth-form-wrap {
            width: 100%; max-width: 360px;
            animation: authFadeInUp 0.4s ease-out;
        }
        @keyframes authFadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .auth-form-logo {
            width: 48px; height: 48px;
            background: #0F766E;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 22px;
            margin-bottom: 24px;
        }
        .auth-form-wrap h2 {
            font-size: 22px; font-weight: 700;
            color: var(--sap-text, #1a1a1a);
            margin: 0 0 4px;
        }
        .auth-form-wrap .subtitle {
            font-size: 13px;
            color: var(--sap-text-secondary, #6b7280);
            margin: 0 0 28px;
        }

        /* Form Fields */
        .field-group { margin-bottom: 18px; }
        .field-group label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: var(--sap-text, #1a1a1a);
            margin-bottom: 6px;
        }
        .field-input-wrap {
            position: relative;
        }
        .field-input-wrap i {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--sap-text-muted, #9ca3af);
            font-size: 16px; pointer-events: none;
        }
        .field-input-wrap .field-input {
            width: 100%;
            padding: 10px 12px 10px 40px;
            border: 1.5px solid var(--sap-border-input, #d1d5db);
            border-radius: var(--sap-radius, 8px);
            font-size: 14px;
            font-family: inherit;
            color: var(--sap-text, #1a1a1a);
            background: var(--sap-surface, #f9fafb);
            transition: border-color 200ms ease, box-shadow 200ms ease;
            height: 42px; box-sizing: border-box;
        }
        .field-input-wrap .field-input:focus {
            outline: none;
            border-color: #0F766E;
            box-shadow: 0 0 0 3px rgba(15,118,110,0.12);
        }
        .field-input-wrap .field-input::placeholder {
            color: var(--sap-text-placeholder, #9ca3af);
        }
        .field-error {
            font-size: 12px; color: var(--sap-error, #dc2626);
            margin-top: 4px; display: none;
        }
        .field-error.visible { display: block; }

        /* Submit Button */
        .btn-auth-submit {
            width: 100%;
            padding: 11px;
            background: #0F766E;
            color: #fff;
            border: none;
            border-radius: var(--sap-radius, 8px);
            font-size: 14px; font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 200ms ease, transform 150ms ease;
            display: flex; align-items: center; justify-content: center;
            gap: 8px; height: 42px;
        }
        .btn-auth-submit:hover { background: #14B8A6; }
        .btn-auth-submit:active { transform: scale(0.98); }
        .btn-auth-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-auth-submit .spinner {
            display: inline-block;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: authSpin 0.6s linear infinite;
        }
        @keyframes authSpin { to { transform: rotate(360deg); } }

        /* Alert */
        .auth-alert {
            padding: 10px 14px;
            border-radius: var(--sap-radius, 8px);
            font-size: 13px;
            margin-bottom: 18px;
            background: var(--sap-error-bg, #fef2f2);
            color: var(--sap-error, #dc2626);
            border: 1px solid var(--sap-error-border, #fecaca);
            animation: authShake 0.4s ease-out;
        }
        .auth-alert i { margin-right: 6px; }
        @keyframes authShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .auth-footer {
            margin-top: 28px;
            text-align: center;
            font-size: 11px;
            color: var(--sap-text-muted, #9ca3af);
        }

        .auth-public-links {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .auth-public-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: all 150ms;
        }
        .auth-public-link--primary {
            background: #0F766E;
            color: #fff;
        }
        .auth-public-link--primary:hover {
            background: #14B8A6;
            color: #fff;
        }
        .auth-public-link:not(.auth-public-link--primary) {
            background: transparent;
            color: var(--sap-text-secondary, #6a6d70);
            border: 1px solid var(--sap-border, #d9d9d9);
        }
        .auth-public-link:not(.auth-public-link--primary):hover {
            background: var(--sap-surface, #f8f9fa);
            color: #0F766E;
        }

        @media (max-width: 900px) {
            .auth-split-left { display: none; }
            .auth-split-right { width: 100%; padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="auth-split-left">
        <div class="auth-brand">
            <div class="brand-icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <h1>HRIS-Hub</h1>
            <p>Human Resource Information System — Kelola SDM dengan mudah dan efisien.</p>
            <div class="auth-features">
                <div class="auth-feature">
                    <i class="fas fa-users"></i>
                    <span>Manajemen data karyawan</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-check-double"></i>
                    <span>Approval workflow otomatis</span>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard &amp; analytics real-time</span>
                </div>
            </div>
        </div>
    </div>
    <div class="auth-split-right">
        <div class="auth-form-wrap">
            <div class="auth-form-logo">
                <i class="fas fa-users-cog"></i>
            </div>
            <h2>Welcome back</h2>
            <p class="subtitle">Sign in with your HRIS account to continue</p>

            <?php if (!empty($error)): ?>
                <div class="auth-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <?= form_open('/login') ?>
                <div class="field-group">
                    <label for="username">Username</label>
                    <div class="field-input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" id="username" class="field-input" placeholder="Enter your username" required autocomplete="username" autofocus>
                    </div>
                </div>
                <div class="field-group">
                    <label for="password">Password</label>
                    <div class="field-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="field-input" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="btn-auth-submit" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            <?= form_close() ?>

            <div class="auth-public-links">
                <a href="<?= site_url('public/tickets/create') ?>" class="auth-public-link auth-public-link--primary">
                    <i class="fas fa-paper-plane"></i> Submit a Ticket
                </a>
                <a href="<?= site_url('public/tickets') ?>" class="auth-public-link">
                    <i class="fas fa-ticket-alt"></i> Track My Tickets
                </a>
            </div>

            <p class="auth-footer">HRIS-Hub &mdash; Human Resource Information System</p>
        </div>
    </div>
    <script>
    document.querySelector('form') && document.querySelector('form').addEventListener('submit', function() {
        var btn = document.getElementById('loginBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Signing in\u2026';
        }
    });
    </script>
</body>
</html>
