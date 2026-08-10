<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:900px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('tickets') ?>">Tickets</a>
        <span class="sep">/</span>
        <span class="active">Chain Tracker</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Ticket Chain Tracker</h1>
            <p class="text-secondary mb-0" style="font-size:13px">Trace the referral chain of related tickets</p>
        </div>
    </div>

    <div class="sap-card mb-4">
        <div class="sap-card-body">
            <div class="d-flex gap-2">
                <div class="flex-grow-1">
                    <input type="text" id="chainSearchInput" class="sap-input" placeholder="Enter tracking code (e.g., TKT-20260729-A1B2)" style="font-size:14px">
                </div>
                <button type="button" id="chainSearchBtn" class="sap-btn sap-btn-primary">
                    <i class="fas fa-search"></i> Track
                </button>
            </div>
        </div>
    </div>

    <div id="chainResults" style="display:none">
        <div class="chain-summary mb-4" id="chainSummary"></div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="sap-card">
                    <div class="sap-card-header">
                        <i class="fas fa-project-diagram me-2"></i>
                        <strong>Chain from Current Ticket</strong>
                    </div>
                    <div class="sap-card-body p-0">
                        <div id="chainTree" class="chain-tree"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sap-card">
                    <div class="sap-card-header">
                        <i class="fas fa-sitemap me-2"></i>
                        <strong>Children</strong>
                        <span class="badge bg-secondary ms-auto" id="childrenCount">0</span>
                    </div>
                    <div class="sap-card-body p-0">
                        <div id="childrenList" class="chain-children-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="chainEmpty" class="sap-card">
        <div class="sap-card-body text-center py-5">
            <i class="fas fa-project-diagram" style="font-size:48px;color:var(--sap-text-muted);opacity:0.5"></i>
            <h4 class="mt-3">Ticket Chain Tracker</h4>
            <p class="text-secondary" style="font-size:14px">Enter a tracking code to trace the referral chain of related tickets.</p>
            <p class="text-muted" style="font-size:12px">Example: TKT-20260729-A1B2</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/tickets/chain.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('public/assets/js/page/tickets/chain.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
