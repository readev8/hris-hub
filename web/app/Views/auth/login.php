<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Project Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap-icons/1.11.3/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/global/style.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #0F4C81 0%, #1a6bb0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, sans-serif;
            padding: 20px;
            margin: 0;
        }
        .login-card {
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .login-icon {
            width: 56px; height: 56px;
            background: rgba(15,76,129,0.1);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: #0F4C81;
            font-size: 28px;
        }
        .login-card h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0F4C81;
            margin-bottom: 4px;
            text-align: center;
        }
        .login-card .subtitle {
            color: #64748B;
            text-align: center;
            margin-bottom: 32px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .input-with-icon {
            position: relative;
        }
        .input-with-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 18px;
        }
        .input-with-icon input {
            width: 100%;
            padding: 12px 14px 12px 44px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        .input-with-icon input:focus {
            outline: none;
            border-color: #0F4C81;
            box-shadow: 0 0 0 3px rgba(15,76,129,0.1);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #0F4C81;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover {
            background: #0a3a66;
        }
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: #FEF2F2;
            color: #E11D48;
            border: 1px solid #FECACA;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            display: inline-block;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-icon">
            <i class="bi bi-kanban"></i>
        </div>
        <h1>Project Management</h1>
        <p class="subtitle">Sign in to your account</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= esc($error) ?></div>
        <?php endif; ?>

        <?= form_open('/login') ?>
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-with-icon">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="you@company.com" required>
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-icon">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>
            </div>
            <button type="submit" class="btn-login" id="loginBtn">
                <i class="bi bi-box-arrow-in-right"></i>
                Sign In
            </button>
        <?= form_close() ?>
    </div>
    <script>
    document.querySelector('form') && document.querySelector('form').addEventListener('submit', function() {
        var btn = document.getElementById('loginBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Signing in...';
        }
    });
    </script>
</body>
</html>
