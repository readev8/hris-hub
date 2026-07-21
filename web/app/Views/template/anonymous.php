<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Ticket Portal') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('public/vendor/bootstrap/5.3.3/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/font-awesome/6.6.0/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/toastr/2.1.4/css/toastr.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/style.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body class="anon-body">
    <header class="anon-header">
        <div class="anon-header-inner">
            <a href="<?= site_url('login') ?>" class="anon-logo">
                <i class="fas fa-life-ring"></i>
                <span>Ticket Portal</span>
            </a>
            <a href="<?= site_url('login') ?>" class="anon-back">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </header>

    <main class="anon-main">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="anon-footer">
        <p>Project Management &mdash; Anonymous Ticket Portal</p>
    </footer>

    <script src="<?= base_url('public/vendor/jquery/3.7.1/jquery.min.js') ?>"></script>
    <script src="<?= base_url('public/vendor/toastr/2.1.4/js/toastr.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script>
        var site_url = '<?= site_url() ?>';
        toastr.options = { positionClass: 'toast-top-right', timeOut: 3000, progressBar: true };
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
