<?php
/**
 * ============================================================================
 * TEMPLATE SHELL
 * ============================================================================
 *
 * Layout global shell HTML. Render sections: styles, content, modals, scripts.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'HRIS-Hub' ?></title>
    <link rel="stylesheet" href="<?= base_url('public/vendor/fonts/plus-jakarta-sans.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/bootstrap/5.3.3/css/bootstrap.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/font-awesome/6.6.0/css/all.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/datatables/1.13.6/css/jquery.dataTables.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/datatables/1.13.6/css/dataTables.bootstrap5.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/datatables-responsive/2.5.0/css/responsive.bootstrap5.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/datatables-buttons/2.4.2/css/buttons.bootstrap5.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/toastr/2.1.4/css/toastr.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/sweetalert2/11.14.5/css/sweetalert2.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/select2/4.1.0-rc.0/css/select2.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/glightbox/3.3.1/css/glightbox.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/style.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/animations.css?v=' . config('App')->assetVersion) ?>">
    <?= $this->renderSection('styles') ?>
    <script>
    (function() {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    })();
    </script>
</head>
<body>
    <?= $this->include('template/partial/navbar') ?>
    <?= $this->include('template/partial/sidebar') ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content sap-page-enter">
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->renderSection('modals') ?>

    <?= $this->include('template/partial/footer') ?>

    <input type="hidden" id="i" value="<?= csrf_token() ?>">

    <script src="<?= base_url('public/vendor/jquery/3.7.1/jquery.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/bootstrap/5.3.3/js/bootstrap.bundle.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/datatables/1.13.6/js/jquery.dataTables.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/datatables/1.13.6/js/dataTables.bootstrap5.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/datatables-responsive/2.5.0/js/dataTables.responsive.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/datatables-responsive/2.5.0/js/responsive.bootstrap5.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/datatables-buttons/2.4.2/js/dataTables.buttons.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/datatables-buttons/2.4.2/js/buttons.bootstrap5.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/datatables-buttons/2.4.2/js/buttons.html5.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/datatables-buttons/2.4.2/js/buttons.print.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/datatables-buttons/2.4.2/js/buttons.colVis.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/jszip/3.10.1/jszip.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/pdfmake/0.2.7/pdfmake.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/pdfmake/0.2.7/vfs_fonts.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/chart.js/4.4.0/chart.umd.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/toastr/2.1.4/js/toastr.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/sweetalert2/11.14.5/js/sweetalert2.all.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/select2/4.1.0-rc.0/js/select2.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/sortablejs/1.15.6/Sortable.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/vendor/glightbox/3.3.1/js/glightbox.min.js?v=' . config('App')->assetVersion) ?>"></script>

    <script>
        var base_url = '<?= rtrim(base_url(), '/') ?>';
        var site_url = '<?= rtrim(site_url(), '/') ?>';
        var userPermissions = <?= json_encode(session('permissions') ?? []) ?>;
    </script>

    <script src="<?= base_url('public/assets/js/global/gc.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/d.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/custom.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/populate.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/sanitize.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/secure-ajax.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/select2.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/toastr.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/sweetalert.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/c.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/e.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/f.js?v=' . config('App')->assetVersion) ?>"></script>
    <script src="<?= base_url('public/assets/js/global/h.js?v=' . config('App')->assetVersion) ?>"></script>

    <script src="<?= base_url('public/assets/js/global/app-shell.js?v=' . config('App')->assetVersion) ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
