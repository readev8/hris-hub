<?php
/**
 * ============================================================================
 * PUBLIC TICKETS - LIST
 * ============================================================================
 *
 * Description: Public ticket listing with status filter and pagination (public access)
 *
 * Required: none
 * Optional: none
 * Template: template/anonymous
 */
?>
<?= $this->extend('template/anonymous') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/_shared/anonymous-layout.css') ?>?v=<?= config('App')->assetVersion ?>">
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/tickets_list.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="anon-container anon-container--wide">
    <div class="anon-page-header">
        <h1>Public Tickets</h1>
        <p class="anon-subtitle">Semua tiket yang dibuat secara anonim.</p>
    </div>

    <div class="anon-list-controls">
        <select id="statusFilter" class="anon-select anon-select--sm">
            <option value="">All Statuses</option>
            <option value="0">Open</option>
            <option value="2">In Progress</option>
            <option value="3">Resolved</option>
            <option value="4">Closed</option>
            <option value="5">Rejected</option>
        </select>
        <input type="text" id="searchInput" class="anon-input anon-input--sm" placeholder="Search tickets...">
    </div>

    <div id="ticketsGrid" class="anon-tickets-grid">
        <div id="emptyState" class="anon-empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Belum ada tiket</h3>
            <p>Belum ada tiket anonim yang tersedia.</p>
            <a href="<?= site_url('public/tickets/create') ?>" class="anon-btn anon-btn-primary">
                <i class="fas fa-paper-plane"></i> Submit New Ticket
            </a>
        </div>
        <div id="ticketsList" class="anon-tickets-list" style="display:none"></div>
    </div>

    <div id="paginationWrap" class="anon-list-controls" style="display:none; justify-content:center; gap:8px; margin-top:16px;">
        <button type="button" id="prevPageBtn" class="anon-btn anon-btn-ghost anon-btn-sm" disabled>
            <i class="fas fa-chevron-left"></i> Prev
        </button>
        <span id="pageInfo" class="anon-badge anon-badge--outline"></span>
        <button type="button" id="nextPageBtn" class="anon-btn anon-btn-ghost anon-btn-sm">
            Next <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <div class="anon-list-footer">
        <p><i class="fas fa-info-circle"></i> Tiket anonim ditampilkan dari server secara real-time.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['ajaxBaseUrl' => site_url('public/tickets')], JSON_HEX_TAG | JSON_HEX_APOS) ?>;</script>
<script src="<?= base_url('public/assets/js/page/public/tickets_list.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
