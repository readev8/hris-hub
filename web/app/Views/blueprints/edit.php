<?= $this->extend('template/index') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/blueprints/edit.css?v=' . config('App')->assetVersion) ?>">
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/blueprints/create.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="max-width:900px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('blueprints') ?>">Blueprints</a>
        <span class="sep">/</span>
        <a href="<?= site_url('blueprints/' . $token) ?>"><?= esc($blueprint['name'] ?? '') ?></a>
        <span class="sep">/</span>
        <span class="active">Edit</span>
    </div>

    <h1 class="mb-4">Edit Blueprint</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="blueprintForm">
                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-link me-1"></i> Select Improvement
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label" for="improvementDisplay">Improvement</label>
                        <div class="improvement-search-wrapper">
                            <div class="improvement-search-input-wrap">
                                <i class="fas fa-link improvement-search-icon"></i>
                                <input type="text" class="sap-input improvement-search-input" id="improvementDisplay"
                                       placeholder="Click search to select improvement..." readonly>
                                <input type="hidden" name="improvement_id" id="improvementId" value="<?= esc($blueprint['improvement_id'] ?? '', 'attr') ?>">
                                <button type="button" class="sap-btn sap-btn-primary improvement-search-btn" onclick="openImprovementModal()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="improvement-hint" id="improvementHint">Optional — click search to link an improvement</div>
                            <div id="improvementSelectedCard" class="improvement-selected-card" style="display:none">
                                <div class="improvement-selected-info">
                                    <div class="improvement-selected-icon"><i class="fas fa-lightbulb"></i></div>
                                    <div class="improvement-selected-text">
                                        <div class="improvement-selected-name" id="selectedImprovementName">-</div>
                                        <div class="improvement-selected-meta" id="selectedImprovementMeta">-</div>
                                    </div>
                                </div>
                                <button type="button" class="improvement-selected-remove" onclick="clearImprovement()" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-info-circle me-1"></i> Blueprint Details
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="sap-input" required value="<?= esc($blueprint['name'] ?? '') ?>" placeholder="e.g., SSO Login Implementation">
                        <div class="field-error" id="error-name" role="alert"></div>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" class="sap-input" rows="5" placeholder="Describe the blueprint scope and objectives..." style="min-height:120px"><?= esc($blueprint['description'] ?? '') ?></textarea>
                        <div class="field-error" id="error-description" role="alert"></div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="<?= site_url('blueprints/' . $token) ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal fade sap-modal" id="improvementSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Search Improvement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="improvementSearchLoading" class="text-center p-4" style="display:none">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--sap-brand)"></i>
                    <p class="mt-2 mb-0 text-secondary" style="font-size:13px">Loading improvements...</p>
                </div>
                <div id="improvementSearchEmpty" class="text-center p-4" style="display:none">
                    <i class="fas fa-inbox" style="font-size:36px;color:var(--sap-text-muted)"></i>
                    <h5 class="mt-2">No improvements found</h5>
                    <p class="mb-0 text-secondary" style="font-size:13px">No improvements found. Try a different search.</p>
                </div>
                <table id="improvementSearchTable" class="sap-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Creator</th>
                            <th>Created</th>
                            <th style="width:80px">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode([
    'token' => $token,
    'currentImprovementId' => $blueprint['improvement_id'] ?? null,
    'currentImprovementName' => $blueprint['improvement_name'] ?? null,
]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/_shared/badge-helpers.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/blueprints/edit.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
