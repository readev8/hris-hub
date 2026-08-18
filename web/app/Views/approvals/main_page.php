<?php
/**
 * ============================================================================
 * APPROVALS — MAIN PAGE
 * ============================================================================
 *
 * Description: Halaman approval center untuk review dan approve request.
 *
 * Required: $title
 * Optional: (none)
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Approval Center</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Review and approve pending requests</p>
    </div>
</div>

<div class="sap-tabs" id="approvalTabs">
    <a class="sap-tab active" href="#" data-type="tickets">
        <i class="fas fa-ticket-alt"></i>Pending Tickets
    </a>
    <a class="sap-tab" href="#" data-type="improvements">
        <i class="fas fa-rocket"></i>Pending Improvements
    </a>
</div>

<div id="approvalContent">
    <div class="sap-card">
        <div class="sap-card-body p-0">
            <table id="approval-table" class="sap-table mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title / Name</th>
                        <th>Status</th>
                        <th>Creator</th>
                        <th>Created</th>
                        <th style="width:100px">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/approvals/main_page.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('public/assets/js/page/_shared/badge-helpers.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/approvals/main_page.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
