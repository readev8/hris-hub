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

<!-- Detail User Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--sap-card-bg);border:1px solid var(--sap-border);border-radius:16px">
            <div class="modal-header" style="border-bottom:1px solid var(--sap-border);padding:20px 24px">
                <h5 class="modal-title fw-semibold" style="font-size:16px">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:24px">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div id="detailAvatar" class="avatar-circle" style="width:56px;height:56px;font-size:22px;background:var(--sap-brand);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600">?</div>
                    <div>
                        <h5 id="detailName" class="mb-0 fw-semibold" style="font-size:17px">-</h5>
                        <span id="detailRole" class="sap-badge developer" style="font-size:12px"><span class="badge-dot"></span>-</span>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">User ID</small>
                        <span id="detailUserId" class="fw-medium" style="font-size:14px">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">Email</small>
                        <span id="detailEmail" class="fw-medium" style="font-size:14px">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">Status</small>
                        <span id="detailStatus">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">Created At</small>
                        <span id="detailCreated" class="fw-medium" style="font-size:14px">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--sap-border);padding:16px 24px">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--sap-card-bg);border:1px solid var(--sap-border);border-radius:16px">
            <div class="modal-header" style="border-bottom:1px solid var(--sap-border);padding:20px 24px">
                <h5 class="modal-title fw-semibold" style="font-size:16px">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:24px">
                <input type="hidden" id="editId">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">User ID</label>
                        <input type="text" class="form-control" id="editUserId" readonly style="background:var(--sap-surface);opacity:0.7">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="editRoleId">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= esc($r['raw_id']) ?>"><?= esc($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editFullName">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="editEmail">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--sap-border);padding:16px 24px;display:flex;gap:8px;justify-content:flex-end">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="sap-btn sap-btn-primary" id="btnSaveEdit" onclick="saveEdit()">
                    <i class="fas fa-check me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/users/main_page.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['rolesMap' => array_map(fn($r) => ['id' => $r['id'], 'name' => $r['name']], $roles)]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/_shared/badge-helpers.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/users/main_page.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
