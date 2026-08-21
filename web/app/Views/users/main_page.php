<?php
/**
 * ============================================================================
 * Users - Main Page
 * ============================================================================
 *
 * Description: List all users with DataTable and inline detail/edit modals
 *
 * Required: $roles
 * Optional: none
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Users</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Manage system users and roles</p>
    </div>
    <?php if (has_permission('users', 'can_create')): ?>
    <a href="<?= site_url('/users/add') ?>" class="sap-btn sap-btn-primary">
        <i class="fas fa-plus me-1"></i> Add User
    </a>
    <?php endif; ?>
</div>

<div class="sap-card">
    <div class="sap-card-body p-0">
        <table id="users-table" class="sap-table mb-0" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th style="width:120px">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<?= $this->section('modals') ?>
<?= $this->include('users/_modal_detail') ?>
<?= $this->include('users/_modal_edit_user') ?>
<?= $this->endSection() ?>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/users/main_page.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- MOVE to page JS file -->
<script>window.PageData = <?= json_encode(['rolesMap' => array_map(fn($r) => ['id' => $r['id'], 'name' => $r['name']], $roles)], JSON_HEX_TAG | JSON_HEX_APOS) ?>;</script>
<script src="<?= base_url('public/assets/js/page/_shared/badge-helpers.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/users/main_page.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
