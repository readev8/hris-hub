<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Project Management' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.14.5/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.min.css">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/animations.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <?= $this->include('template/partial/navbar') ?>
    <?= $this->include('template/partial/sidebar') ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content sap-page-enter">
        <?= $this->renderSection('content') ?>
    </main>

    <input type="hidden" id="i" value="<?= csrf_token() ?>">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.14.5/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.min.js"></script>

    <script>
        var base_url = '<?= base_url() ?>';
        var site_url = '<?= site_url() ?>';
    </script>

    <script src="<?= base_url('public/assets/js/global/gc.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/d.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/custom.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/populate.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/sanitize.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/secure-ajax.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/select2.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/toastr.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/sweetalert.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/c.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/e.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/f.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/global/h.js') ?>"></script>

    <script>
    $(function() {
        $('[data-toggle="sidebar"]').on('click', function() {
            $('body').toggleClass('sidebar-open');
        });
        $('#sidebarOverlay').on('click', function() {
            $('body').removeClass('sidebar-open');
        });
        $('.dropdown-toggle').dropdown();

        var path = window.location.pathname;
        $('.sidebar-nav li a').each(function() {
            var href = $(this).attr('href');
            if (href && path.indexOf(href) === 0 && href !== '/') {
                $(this).addClass('active');
            } else if (href === '/' || href === site_url + '/' || href === site_url + '/dashboard') {
                if (path === '/' || path === '/dashboard' || path === site_url + '/' || path === site_url + '/dashboard') {
                    $(this).addClass('active');
                }
            }
        });

        $('.sap-count-up').each(function() {
            var $el = $(this);
            var target = parseInt($el.text().replace(/\D/g, '')) || 0;
            if (target > 0) {
                $el.text('0');
                var duration = 600;
                var start = performance.now();
                function step(now) {
                    var progress = Math.min((now - start) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    $el.text(Math.floor(eased * target));
                    if (progress < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
            }
        });
    });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
