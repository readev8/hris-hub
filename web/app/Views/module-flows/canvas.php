<?php
/**
 * ============================================================================
 * MODULE FLOWS - CANVAS
 * ============================================================================
 *
 * Description: Kanvas interaktif visualisasi flow antar modul lintas project.
 *
 * Required: $title
 * Optional: (none)
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/module-flows/canvas.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flow-page-wrap">
    <div class="flow-page-header">
        <div>
            <h1>Module Flows</h1>
            <p>Visualisasikan koneksi antar modul dari berbagai project</p>
        </div>
    </div>

    <div class="flow-toolbar">
        <button type="button" class="sap-btn sap-btn-primary sap-btn-sm" id="btnOpenAddModule">
            <i class="fas fa-plus me-1"></i> Add Module
        </button>
        <button type="button" class="sap-btn sap-btn-danger sap-btn-sm" id="btnRemoveNode" title="Hapus koneksi / modul terpilih">
            <i class="fas fa-trash"></i>
        </button>

        <div class="toolbar-separator"></div>

        <div class="toolbar-group">
            <select id="filterProject" class="form-select form-select-sm" style="width: 200px;" data-placeholder="Filter by project">
                <option value="">Filter by project</option>
            </select>
        </div>

        <div class="toolbar-separator"></div>

        <span id="saveStatus" class="save-status"></span>
    </div>

    <div id="projectLegend" class="project-legend"></div>

    <div id="flow-canvas"></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('module-flows/_modal_add_module') ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.PageData = <?= json_encode([
    'userPermissions' => session('permissions') ?? [],
], JSON_HEX_TAG | JSON_HEX_APOS) ?>;
</script>
<script src="<?= base_url('public/vendor/jsplumb/6.2.10/jsplumb.browser-ui.umd.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/module-flows/canvas.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
