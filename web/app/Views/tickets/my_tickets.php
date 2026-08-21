<?php
/**
 * ============================================================================
 * TICKETS - MY TICKETS
 * ============================================================================
 *
 * Description: Personal ticket dashboard with summary cards and DataTable
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
        <h1 class="mb-1">My Taken Tickets</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Tickets you have taken and are working on</p>
    </div>
    <?php if (has_permission('tickets', 'can_create')): ?>
    <a href="<?= site_url('tickets/create') ?>" class="sap-btn sap-btn-primary">
        <i class="fas fa-plus"></i> New Ticket
    </a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="summary-card summary-card--open" id="cardOpen">
            <div class="summary-card-icon"><i class="fas fa-folder-open"></i></div>
            <div class="summary-card-info">
                <span class="summary-card-value" id="countOpen">0</span>
                <span class="summary-card-label">Open</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-card summary-card--inprogress" id="cardInProgress">
            <div class="summary-card-icon"><i class="fas fa-spinner"></i></div>
            <div class="summary-card-info">
                <span class="summary-card-value" id="countInProgress">0</span>
                <span class="summary-card-label">In Progress</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-card summary-card--resolved" id="cardResolved">
            <div class="summary-card-icon"><i class="fas fa-check-circle"></i></div>
            <div class="summary-card-info">
                <span class="summary-card-value" id="countResolved">0</span>
                <span class="summary-card-label">Resolved</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-card summary-card--closed" id="cardClosed">
            <div class="summary-card-icon"><i class="fas fa-archive"></i></div>
            <div class="summary-card-info">
                <span class="summary-card-value" id="countClosed">0</span>
                <span class="summary-card-label">Closed</span>
            </div>
        </div>
    </div>
</div>

<div class="tickets-filter-bar mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <label class="sap-label mb-0" for="statusFilter" style="font-size:13px">Status</label>
        <select id="statusFilter" class="sap-select" style="width:auto;min-width:140px">
            <option value="">All Statuses</option>
            <option value="0">Open</option>
            <option value="1">Approved</option>
            <option value="2">In Progress</option>
            <option value="3">Resolved</option>
            <option value="4">Closed</option>
            <option value="5">Rejected</option>
        </select>
        <label class="d-flex align-items-center gap-1 mb-0" style="font-size:13px;cursor:pointer">
            <input type="checkbox" id="overdueFilter"> Overdue only
        </label>
        <button class="sap-btn sap-btn-secondary sap-btn-sm" id="clearFiltersBtn" title="Clear filters">
            <i class="fas fa-times"></i> Clear
        </button>
    </div>
</div>

<div class="sap-card">
    <div class="sap-card-body p-0">
        <table id="myTicketsTable" class="sap-table mb-0" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Tracking</th>
                    <th>Status</th>
                    <th>Type</th>
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

<?= $this->section('modals') ?>
<div id="trackingModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);align-items:center;justify-content:center">
    <div class="sap-card" style="width:400px;max-width:90vw">
        <div class="sap-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-ticket-alt" style="color:var(--sap-brand)"></i> Ticket Tracking Code</span>
            <button onclick="closeTrackingModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--sap-text-muted)">&times;</button>
        </div>
        <div class="sap-card-body text-center">
            <p style="font-size:13px;color:var(--sap-text-secondary);margin-bottom:16px">Share this code to let others track ticket status</p>
            <div style="background:var(--sap-background);border-radius:8px;padding:16px;margin-bottom:16px">
                <code id="trackingCodeDisplay" style="font-size:20px;font-weight:600;letter-spacing:0.05em;font-family:'SF Mono',Monaco,Consolas,monospace;color:var(--sap-brand)"></code>
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <button class="sap-btn sap-btn-primary" onclick="copyTrackingCode()"><i class="fas fa-copy"></i> Copy Code</button>
                <button class="sap-btn sap-btn-secondary" onclick="copyTrackingLink()"><i class="fas fa-link"></i> Copy Link</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/tickets/my_tickets.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('public/assets/js/page/_shared/badge-helpers.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/tickets/my_tickets.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
