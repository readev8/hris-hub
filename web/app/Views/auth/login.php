<!--
============================================================================
Sign In — HRIS-Hub
============================================================================
Description: Halaman login standalone untuk autentikasi user.
Standalone: yes (bukan extends template/index)
-->
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
    <link rel="stylesheet" href="<?= base_url('public/assets/css/page/auth/login.css?v=' . config('App')->assetVersion) ?>">
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
