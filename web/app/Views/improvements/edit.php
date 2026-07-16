<?= $this->extend('template/index') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/_shared/field-errors.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="max-width:900px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('improvements') ?>">Improvements</a>
        <span class="sep">/</span>
        <a href="<?= site_url('improvements/' . $token) ?>"><?= esc($improvement['name'] ?? '') ?></a>
        <span class="sep">/</span>
        <span class="active">Edit</span>
    </div>

    <h1 class="mb-4">Edit Improvement</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="improvementForm">
                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-info-circle me-1"></i> General Information
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="sap-input" required value="<?= esc($improvement['name'] ?? '') ?>" placeholder="e.g., Implement SSO Login">
                        <div class="field-error" id="error-name" role="alert"></div>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="sap-input" rows="5" required placeholder="Describe the improvement in detail..." style="min-height:120px"><?= esc($improvement['description'] ?? '') ?></textarea>
                        <div class="field-error" id="error-description" role="alert"></div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-chart-line me-1"></i> Business Details
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Business Case <span class="text-danger">*</span></label>
                        <textarea name="business_case" class="sap-input" rows="3" required placeholder="Why is this improvement needed?" style="min-height:80px"><?= esc($improvement['business_case'] ?? '') ?></textarea>
                        <div class="field-error" id="error-business_case" role="alert"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="sap-label">Category</label>
                            <select name="category" class="sap-select">
                                <option value="">Select category...</option>
                                <option value="UI/UX" <?= ($improvement['category'] ?? '') == 'UI/UX' ? 'selected' : '' ?>>UI/UX</option>
                                <option value="Performance" <?= ($improvement['category'] ?? '') == 'Performance' ? 'selected' : '' ?>>Performance</option>
                                <option value="Security" <?= ($improvement['category'] ?? '') == 'Security' ? 'selected' : '' ?>>Security</option>
                                <option value="New Feature" <?= ($improvement['category'] ?? '') == 'New Feature' ? 'selected' : '' ?>>New Feature</option>
                                <option value="Integration" <?= ($improvement['category'] ?? '') == 'Integration' ? 'selected' : '' ?>>Integration</option>
                                <option value="Other" <?= ($improvement['category'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Priority</label>
                            <select name="priority" class="sap-select">
                                <option value="0" <?= ($improvement['priority'] ?? '') == 0 ? 'selected' : '' ?>>Low</option>
                                <option value="1" <?= ($improvement['priority'] ?? '') == 1 ? 'selected' : '' ?>>Medium</option>
                                <option value="2" <?= ($improvement['priority'] ?? '') == 2 ? 'selected' : '' ?>>High</option>
                                <option value="3" <?= ($improvement['priority'] ?? '') == 3 ? 'selected' : '' ?>>Critical</option>
                            </select>
                            <div class="field-error" id="error-priority" role="alert"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-calendar-alt me-1"></i> Details
                    </h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="sap-label">Target Date</label>
                            <input type="date" name="target_date" class="sap-input" value="<?= esc($improvement['target_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Approver <span style="font-weight:400;color:var(--sap-text-muted)">(optional)</span></label>
                            <select name="approver_id" class="sap-select" id="approverSelect">
                                <option value="">Role-based approval (default)</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= esc($u['id']) ?>" <?= (isset($improvement['approver_id']) && $improvement['approver_id'] == $u['id']) ? 'selected' : '' ?>><?= esc($u['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted" style="font-size:11px">If set, only this user can approve. Otherwise, role-based approval applies.</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="<?= site_url('improvements/' . $token) ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['token' => $token]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/improvements/edit.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
