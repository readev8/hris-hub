<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Add User</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Search for an HRIS user and assign a role</p>
    </div>
    <a href="<?= site_url('/users') ?>" class="sap-btn sap-btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Users
    </a>
</div>

<!-- Step 1: Search HRIS User -->
<div class="sap-card mb-4">
    <div class="sap-card-header">
        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Step 1 — Find HRIS User</h5>
    </div>
    <div class="sap-card-body">
        <div class="hris-search-wrapper">
            <label class="form-label fw-semibold">Search by name, username, or department</label>
            <div class="hris-search-input-wrap">
                <i class="fas fa-search hris-search-icon"></i>
                <input type="text" class="form-control hris-search-input" id="hrisSearch" 
                       placeholder="Type at least 2 characters..." autocomplete="off">
                <div class="hris-search-spinner" id="hrisSpinner" style="display:none">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
            <div class="hris-search-hint" id="hrisHint">Ketik minimal 2 karakter untuk mencari</div>
            <div class="hris-search-dropdown" id="hrisDropdown" style="display:none"></div>
        </div>

        <!-- Selected User Card -->
        <div id="selectedUserCard" class="hris-selected-card" style="display:none">
            <div class="hris-selected-header">
                <div class="hris-selected-avatar" id="selectedAvatar">?</div>
                <div class="hris-selected-info">
                    <div class="hris-selected-name" id="selectedName">-</div>
                    <div class="hris-selected-meta" id="selectedMeta">-</div>
                </div>
                <button type="button" class="hris-selected-remove" id="btnRemoveUser" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Step 2: Assign Role -->
<div class="sap-card mb-4" id="assignCard" style="display:none">
    <div class="sap-card-header">
        <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i>Step 2 — Assign Role</h5>
    </div>
    <div class="sap-card-body">
        <input type="hidden" id="addUserId">
        <input type="hidden" id="addUserName">
        <input type="hidden" id="addUserEmail">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">User ID</label>
                <input type="text" class="form-control" id="addUserIdDisplay" readonly style="background:var(--sap-surface)">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" class="form-control" id="addUserNameDisplay" readonly style="background:var(--sap-surface)">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                <select class="form-select" id="addRoleId">
                    <option value="">Select Role</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= esc($r['raw_id']) ?>"><?= esc($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="button" class="sap-btn sap-btn-primary" id="btnAddUser" onclick="addUser()">
                <i class="fas fa-plus me-1"></i> Add User
            </button>
            <button type="button" class="sap-btn sap-btn-secondary" onclick="resetForm()">
                <i class="fas fa-times me-1"></i> Cancel
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/users/add_user.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('public/assets/js/page/users/add_user.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
