<?php
/**
 * ============================================================================
 * Improvements - Main Page
 * ============================================================================
 *
 * Description: List all improvements with DataTable
 *
 * Required: none
 * Optional: none
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Improvements</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Feature requests and change proposals</p>
    </div>
    <?php if (has_permission('improvements', 'can_create')): ?>
    <a href="<?= site_url('improvements/create') ?>" class="sap-btn sap-btn-primary">
        <i class="fas fa-plus"></i> New Improvement
    </a>
    <?php endif; ?>
</div>

<div class="dt-toolbar" id="improvements-toolbar" role="toolbar" aria-label="Alat ekspor"></div>

<div class="sap-card">
    <div class="sap-card-body p-0">
        <table id="improvements-table" class="sap-table mb-0" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Creator</th>
                    <th>Created</th>
                    <th style="width:80px">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= asset_url('public/assets/css/page/_shared/dt-toolbar.css') ?>">
<link rel="stylesheet" href="<?= asset_url('public/assets/css/page/improvements/main_page.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- MOVE to page JS file -->
<script defer src="<?= asset_url('public/assets/js/page/_shared/badge-helpers.js') ?>"></script>
<script defer src="<?= asset_url('public/assets/js/page/_shared/dt-toolbar.js') ?>"></script>
<script defer src="<?= asset_url('public/assets/js/page/improvements/main_page.js') ?>"></script>
<?= $this->endSection() ?>
