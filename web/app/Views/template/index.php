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
    <meta name="color-scheme" content="light dark">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/fonts/plus-jakarta-sans.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/bootstrap/5.3.3/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/font-awesome/6.6.0/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/datatables/1.13.6/css/jquery.dataTables.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/datatables/1.13.6/css/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/datatables-responsive/2.5.0/css/responsive.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/datatables-buttons/2.4.2/css/buttons.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/toastr/2.1.4/css/toastr.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/sweetalert2/11.14.5/css/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/select2/4.1.0-rc.0/css/select2.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/vendor/glightbox/3.3.1/css/glightbox.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/assets/css/global/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('public/assets/css/global/animations.css') ?>">
    <?= $this->renderSection('styles') ?>
    <script>
    (function() {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
        var t = localStorage.getItem('theme');
        var dark = t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches);
        if (dark) {
            document.documentElement.classList.add('dark-mode');
            document.documentElement.dataset.bsTheme = 'dark';
        }
    })();
    </script>
</head>
<body>
    <?= $this->include('template/partial/navbar') ?>
    <?= $this->include('template/partial/sidebar') ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div id="sapPageLoader" class="sap-page-loader" aria-hidden="true" role="presentation">
        <div class="sap-loader-content">
            <div class="sap-loader-brand">
                <div class="sap-loader-icon"><i class="fas fa-layer-group"></i></div>
                <span class="sap-loader-text">HRIS-Hub</span>
            </div>
            <div class="sap-loader-bar-track">
                <div class="sap-loader-bar-fill"></div>
            </div>
        </div>
    </div>
    <div id="sapTopProgress" class="sap-top-progress" aria-hidden="true"></div>
    <div id="sapAjaxOverlay" class="sap-ajax-overlay" aria-hidden="true" role="status">
        <div class="sap-ajax-spinner"></div>
    </div>

    <main class="main-content sap-page-enter">
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->renderSection('modals') ?>

    <?= $this->include('template/partial/footer') ?>

    <input type="hidden" id="i" value="<?= csrf_token() ?>">

    <script src="<?= asset_url('public/vendor/jquery/3.7.1/jquery.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/bootstrap/5.3.3/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/datatables/1.13.6/js/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/datatables/1.13.6/js/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/datatables-responsive/2.5.0/js/dataTables.responsive.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/datatables-responsive/2.5.0/js/responsive.bootstrap5.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/datatables-buttons/2.4.2/js/dataTables.buttons.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/datatables-buttons/2.4.2/js/buttons.bootstrap5.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/datatables-buttons/2.4.2/js/buttons.html5.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/datatables-buttons/2.4.2/js/buttons.print.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/datatables-buttons/2.4.2/js/buttons.colVis.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/jszip/3.10.1/jszip.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/pdfmake/0.2.7/pdfmake.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/pdfmake/0.2.7/vfs_fonts.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/chart.js/4.4.0/chart.umd.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/toastr/2.1.4/js/toastr.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/sweetalert2/11.14.5/js/sweetalert2.all.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/select2/4.1.0-rc.0/js/select2.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/sortablejs/1.15.6/Sortable.min.js') ?>"></script>
    <script src="<?= asset_url('public/vendor/glightbox/3.3.1/js/glightbox.min.js') ?>"></script>

    <script>
        var base_url = '<?= rtrim(base_url(), '/') ?>';
        var site_url = '<?= rtrim(site_url(), '/') ?>';
        var userPermissions = <?= json_encode(session('permissions') ?? []) ?>;
    </script>

    <script src="<?= asset_url('public/assets/js/global/gc.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/d.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/custom.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/populate.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/sanitize.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/secure-ajax.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/select2.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/toastr.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/sweetalert.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/c.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/e.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/f.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/h.js') ?>"></script>

    <script src="<?= asset_url('public/assets/js/global/page-loader.js') ?>"></script>
    <script src="<?= asset_url('public/assets/js/global/app-shell.js') ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
