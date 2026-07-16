<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Blueprints</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Development blueprints from improvements</p>
    </div>
    <?php if (has_permission('blueprints', 'can_create')): ?>
    <a href="<?= site_url('blueprints/create') ?>" class="sap-btn sap-btn-primary">
        <i class="fas fa-plus"></i> New Blueprint
    </a>
    <?php endif; ?>
</div>

<div class="sap-card">
    <div class="sap-card-body p-0">
        <table id="blueprints-table" class="sap-table mb-0" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Improvement</th>
                    <th>Modules</th>
                    <th>Status</th>
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
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/blueprints/main_page.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('public/assets/js/page/_shared/badge-helpers.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/blueprints/main_page.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
