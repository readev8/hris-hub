<?php
/**
 * ============================================================================
 * Roles & Permissions - Main Page
 * ============================================================================
 *
 * Description: List all roles with DataTable and role management modal
 *
 * Required: none
 * Optional: none
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('dashboard') ?>">Dashboard</a>
        <span class="sep">/</span>
        <span class="active">Roles & Permissions</span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1">Roles & Permissions</h1>
            <p class="text-secondary mb-0" style="font-size:13px">Manage user roles and their access permissions</p>
        </div>
        <button class="sap-btn sap-btn-primary" onclick="openRoleModal()">
            <i class="fas fa-plus"></i> Add Role
        </button>
    </div>

    <div class="sap-card">
        <div class="sap-card-body p-0">
            <table class="sap-table mb-0" id="rolesTable" style="width:100%">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th style="width:180px">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->section('modals') ?>
<?= $this->include('roles/_modal_role') ?>
<?= $this->endSection() ?>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/roles/main_page.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- MOVE to page JS file -->
<script src="<?= base_url('public/assets/js/page/roles/main_page.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
