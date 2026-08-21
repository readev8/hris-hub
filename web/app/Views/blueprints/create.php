<?php
/**
 * ============================================================================
 * BLUEPRINTS - CREATE BLUEPRINT
 * ============================================================================
 *
 * Description: Form to create a new blueprint with optional improvement linking
 *
 * Required: none
 * Optional: none
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/blueprints/create.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="max-width:900px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('blueprints') ?>">Blueprints</a>
        <span class="sep">/</span>
        <span class="active">Create</span>
    </div>

    <h1 class="mb-4">Create Blueprint</h1>

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
                                <input type="hidden" name="improvement_id" id="improvementId" value="">
                                <button type="button" class="sap-btn sap-btn-primary improvement-search-btn" onclick="openImprovementModal()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="improvement-hint" id="improvementHint">Optional — click search to link an improvement</div>
                            <!-- Selected Improvement Card (hidden by default) -->
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
                        <div class="field-error" id="error-improvement_id" role="alert"></div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-info-circle me-1"></i> Blueprint Details
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="sap-input" required placeholder="e.g., SSO Login Implementation">
                        <div class="field-error" id="error-name" role="alert"></div>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" class="sap-input" rows="5" placeholder="Describe the blueprint scope and objectives..." style="min-height:120px"></textarea>
                        <div class="field-error" id="error-description" role="alert"></div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-paper-plane"></i> Create Blueprint
                    </button>
                    <a href="<?= site_url('blueprints') ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('blueprints/_modal_improvement_search') ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- MOVE to page JS file -->
<script src="<?= base_url('public/assets/js/page/_shared/badge-helpers.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/blueprints/create.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
