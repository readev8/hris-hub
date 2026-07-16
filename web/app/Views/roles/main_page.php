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

<!-- Role Modal -->
<div class="modal fade sap-modal" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalTitle">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="roleForm">
                <input type="hidden" name="edit_id" id="roleEditId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="sap-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="roleName" class="sap-input" required placeholder="e.g., QA Engineer">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="roleSlug" class="sap-input" required placeholder="e.g., qa_engineer" pattern="[a-z0-9_-]+">
                        <span class="sap-hint">Lowercase letters, numbers, dashes, underscores only</span>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" id="roleDesc" class="sap-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-check"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/roles/main_page.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('public/assets/js/page/roles/main_page.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
