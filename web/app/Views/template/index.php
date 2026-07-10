<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Project Management' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/5.3.3/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap-icons/1.11.3/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/datatables/1.13.6/css/jquery.dataTables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/datatables/1.13.6/css/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/toastr/2.1.4/toastr.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/sweetalert2/11.14.5/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/select2/4.1.0-rc.0/select2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap-multiselect/0.9.15/bootstrap-multiselect.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/global/style.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <?= $this->include('template/partial/navbar') ?>
    <?= $this->include('template/partial/sidebar') ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <input type="hidden" id="i" value="<?= csrf_token() ?>">

    <script src="<?= base_url('assets/vendor/jquery/3.7.1/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/5.3.3/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/1.13.6/js/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/1.13.6/js/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/chartjs/4.4.0/chart.umd.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/toastr/2.1.4/toastr.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/sweetalert2/11.14.5/sweetalert2.all.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/select2/4.1.0-rc.0/select2.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap-multiselect/0.9.15/bootstrap-multiselect.min.js') ?>"></script>

    <script>
        var base_url = '<?= base_url() ?>';
        var site_url = '<?= site_url() ?>';
    </script>

    <script src="<?= base_url('assets/js/global/gc.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/d.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/custom.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/populate.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/sanitize.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/secure-ajax.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/select2.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/toastr.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/sweetalert.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/c.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/e.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/f.js') ?>"></script>
    <script src="<?= base_url('assets/js/global/h.js') ?>"></script>

    <script>
    $(function() {
        // Sidebar toggle
        $('[data-toggle="sidebar"]').on('click', function() {
            $('body').toggleClass('sidebar-open');
        });
        $('#sidebarOverlay').on('click', function() {
            $('body').removeClass('sidebar-open');
        });

        // Bootstrap dropdowns
        $('.dropdown-toggle').dropdown();

        // Activate sidebar item based on current URL
        var path = window.location.pathname;
        $('.sidebar-menu li a').each(function() {
            var href = $(this).attr('href');
            if (href && path.indexOf(href) === 0) {
                $(this).addClass('active');
            }
        });
    });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
