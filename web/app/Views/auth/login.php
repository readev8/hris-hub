<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Project Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/animations.css') ?>">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
        }
        .sap-split-left {
            flex: 1;
            background: linear-gradient(135deg, #1D2D3E 0%, #2C4056 50%, #1D2D3E 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }
        .sap-split-left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(0,112,242,0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(0,176,255,0.05) 0%, transparent 50%);
            animation: sapBgDrift 20s ease-in-out infinite alternate;
        }
        @keyframes sapBgDrift {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(2%, 2%) rotate(3deg); }
        }
        .sap-split-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 480px;
        }
        .sap-split-left-content .hero-icon {
            width: 80px;
            height: 80px;
            background: rgba(0,112,242,0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: #fff;
            font-size: 40px;
            animation: sapFloat 3s ease-in-out infinite;
        }
        @keyframes sapFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .sap-split-left-content h1 {
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            margin: 0 0 8px;
            letter-spacing: -0.02em;
        }
        .sap-split-left-content p {
            font-size: 15px;
            color: rgba(255,255,255,0.6);
            line-height: 1.6;
            margin: 0;
        }
        .sap-split-left-content .feature-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 40px;
            text-align: left;
        }
        .sap-split-left-content .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            animation: sapSlideInRight 0.5s ease-out both;
        }
        .sap-split-left-content .feature-item:nth-child(1) { animation-delay: 0.2s; }
        .sap-split-left-content .feature-item:nth-child(2) { animation-delay: 0.35s; }
        .sap-split-left-content .feature-item:nth-child(3) { animation-delay: 0.5s; }
        .sap-split-left-content .feature-item i {
            width: 32px; height: 32px;
            background: rgba(0,112,242,0.15);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0070F2;
            font-size: 16px;
            flex-shrink: 0;
        }
        .sap-split-right {
            width: 440px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #fff;
            min-height: 100vh;
        }
        .sap-login-form {
            width: 100%;
            max-width: 360px;
            animation: sapFadeInUp 0.4s ease-out;
        }
        .sap-login-form .login-logo {
            width: 48px; height: 48px;
            background: var(--sap-brand);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            margin-bottom: 24px;
        }
        .sap-login-form h2 {
            font-size: 22px;
            font-weight: 700;
            color: var(--sap-text);
            margin: 0 0 4px;
        }
        .sap-login-form .subtitle {
            font-size: 14px;
            color: var(--sap-text-secondary);
            margin: 0 0 32px;
        }
        .sap-form-group {
            margin-bottom: 20px;
        }
        .sap-form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--sap-text);
            margin-bottom: 6px;
        }
        .sap-input-wrap {
            position: relative;
        }
        .sap-input-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--sap-text-muted);
            font-size: 18px;
            pointer-events: none;
        }
        .sap-input-wrap .sap-input {
            width: 100%;
            padding: 10px 12px 10px 42px;
            border: 1px solid var(--sap-border-input);
            border-radius: var(--sap-radius);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--sap-text);
            background: var(--sap-surface);
            transition: border-color var(--sap-transition), box-shadow var(--sap-transition);
            height: 44px;
            box-sizing: border-box;
        }
        .sap-input-wrap .sap-input:focus {
            outline: none;
            border-color: var(--sap-brand);
            box-shadow: 0 0 0 3px rgba(0,112,242,0.15);
        }
        .sap-input-wrap .sap-input::placeholder {
            color: var(--sap-text-placeholder);
        }
        .sap-btn-login {
            width: 100%;
            padding: 12px;
            background: var(--sap-brand);
            color: #fff;
            border: none;
            border-radius: var(--sap-radius);
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background var(--sap-transition), transform var(--sap-transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
        }
        .sap-btn-login:hover { background: var(--sap-brand-dark); }
        .sap-btn-login:active { transform: scale(0.98); }
        .sap-btn-login:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .sap-alert {
            padding: 12px 16px;
            border-radius: var(--sap-radius);
            font-size: 14px;
            margin-bottom: 20px;
            background: var(--sap-error-bg);
            color: var(--sap-error);
            border: 1px solid var(--sap-error-border);
            animation: sapShake 0.4s ease-out;
        }
        @keyframes sapShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }
        @keyframes sapFadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes sapSlideInRight {
            from { opacity: 0; transform: translateX(-12px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .sap-spinner {
            display: inline-block;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: sapSpin 0.6s linear infinite;
        }
        @keyframes sapSpin { to { transform: rotate(360deg); } }
        .sap-footer-text {
            margin-top: 32px;
            text-align: center;
            font-size: 12px;
            color: var(--sap-text-muted);
        }
        @media (max-width: 900px) {
            .sap-split-left { display: none; }
            .sap-split-right { width: 100%; padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="sap-split-left">
        <div class="sap-split-left-content">
            <div class="hero-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <h1>Project Management</h1>
            <p>Streamline your team's workflow with enterprise-grade ticket tracking, approvals, and project oversight.</p>
            <div class="feature-list">
                <div class="feature-item">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Centralized bug &amp; issue tracking</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Multi-level approval workflows</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Real-time dashboard &amp; analytics</span>
                </div>
            </div>
        </div>
    </div>
    <div class="sap-split-right">
        <div class="sap-login-form">
            <div class="login-logo">
                <i class="fas fa-tasks"></i>
            </div>
            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to your account to continue</p>

            <?php if (!empty($error)): ?>
                <div class="sap-alert">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <?= form_open('/login') ?>
                <div class="sap-form-group">
                    <label for="email">Email</label>
                    <div class="sap-input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" class="sap-input" placeholder="you@company.com" required autocomplete="email">
                    </div>
                </div>
                <div class="sap-form-group">
                    <label for="password">Password</label>
                    <div class="sap-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="sap-input" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="sap-btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            <?= form_close() ?>

            <p class="sap-footer-text">Project Management &mdash; Enterprise Edition</p>
        </div>
    </div>
    <script>
    document.querySelector('form') && document.querySelector('form').addEventListener('submit', function() {
        var btn = document.getElementById('loginBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="sap-spinner"></span> Signing in\u2026';
        }
    });
    </script>
</body>
</html>
