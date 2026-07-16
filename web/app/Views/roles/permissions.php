<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('dashboard') ?>">Dashboard</a>
        <span class="sep">/</span>
        <a href="<?= site_url('roles') ?>">Roles</a>
        <span class="sep">/</span>
        <span class="active">Permissions: <?= esc($role['name'] ?? '') ?></span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1">Permissions</h1>
            <p class="text-secondary mb-0" style="font-size:13px">
                Role: <strong><?= esc($role['name'] ?? '') ?></strong>
                <?php if (!empty($role['description'])): ?> &middot; <?= esc($role['description']) ?><?php endif; ?>
            </p>
        </div>
        <button class="sap-btn sap-btn-primary" onclick="saveAllPermissions()">
            <i class="fas fa-save"></i> Save Permissions
        </button>
    </div>

    <div class="sap-card">
        <div class="sap-card-body p-0">
            <table class="sap-table mb-0" id="permissionsTable" style="width:100%">
                <thead>
                    <tr>
                        <th style="width:200px">Module</th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>View</span>
                                <input type="checkbox" id="toggleAllCanView" class="form-check-input" onchange="toggleColumn('can_view', this.checked)">
                            </div>
                        </th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>Create</span>
                                <input type="checkbox" id="toggleAllCanCreate" class="form-check-input" onchange="toggleColumn('can_create', this.checked)">
                            </div>
                        </th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>Update</span>
                                <input type="checkbox" id="toggleAllCanUpdate" class="form-check-input" onchange="toggleColumn('can_update', this.checked)">
                            </div>
                        </th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>Delete</span>
                                <input type="checkbox" id="toggleAllCanDelete" class="form-check-input" onchange="toggleColumn('can_delete', this.checked)">
                            </div>
                        </th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>Approve</span>
                                <input type="checkbox" id="toggleAllCanApprove" class="form-check-input" onchange="toggleColumn('can_approve', this.checked)">
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody id="permissionsBody">
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/roles/permissions.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['role' => $role ?? [], 'modules' => $modules ?? []]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/roles/permissions.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
