<?= $this->extend('template/anonymous') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/_shared/anonymous-layout.css') ?>?v=<?= config('App')->assetVersion ?>">
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/tickets_list.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="anon-container anon-container--wide">
    <div class="anon-page-header">
        <h1>My Tickets</h1>
        <p class="anon-subtitle">Tiket yang Anda simpan di browser ini. Data tersimpan secara lokal &mdash; tidak ada yang dikirim ke server.</p>
    </div>

    <div class="anon-add-code-bar">
        <div class="anon-add-code-input-wrap">
            <input type="text" id="addCodeInput" class="anon-input" placeholder="Add tracking code (e.g., TKT-20260721-A3F9)" maxlength="20">
            <button type="button" id="addCodeBtn" class="anon-btn anon-btn-primary anon-btn-sm">
                <i class="fas fa-plus"></i> Add
            </button>
        </div>
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
        <button type="button" id="clearAllBtn" class="anon-btn anon-btn-ghost anon-btn-sm">
            <i class="fas fa-trash-alt"></i> Clear All
        </button>
    </div>

    <div id="ticketsGrid" class="anon-tickets-grid">
        <div id="emptyState" class="anon-empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Belum ada tiket yang disimpan</h3>
            <p>Submit tiket baru atau tambahkan kode pelacakan yang ada di atas.</p>
            <a href="<?= site_url('public/tickets/create') ?>" class="anon-btn anon-btn-primary">
                <i class="fas fa-paper-plane"></i> Submit New Ticket
            </a>
        </div>
        <div id="ticketsList" class="anon-tickets-list" style="display:none"></div>
    </div>

    <div class="anon-list-footer">
        <p><i class="fas fa-info-circle"></i> Tiket disimpan secara lokal di browser Anda. Hapus cookies atau browser data akan menghapus daftar ini.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['ajaxBaseUrl' => site_url('public/tickets')]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/public/_shared/tracking-store.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/assets/js/page/public/tickets_list.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
