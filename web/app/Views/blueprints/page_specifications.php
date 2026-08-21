<?php
/**
 * ============================================================================
 * BLUEPRINTS - PAGE SPECIFICATIONS
 * ============================================================================
 *
 * Description: Specification listing and management for a blueprint design page
 *
 * Required: $token, $designPage
 * Optional: none
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/blueprints/page_specifications.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid" style="max-width:1400px">
    <?php if (!$designPage): ?>
        <div class="sap-empty">
            <i class="fas fa-exclamation-triangle" style="color:var(--sap-error)"></i>
            <h4>Design page not found</h4>
            <p>The design page you're looking for doesn't exist or has been removed.</p>
            <a href="<?= site_url('blueprints') ?>" class="sap-btn sap-btn-secondary mt-3">Back</a>
        </div>
    <?php else: ?>
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('blueprints') ?>">Blueprints</a>
        <span class="sep">/</span>
        <a href="<?= site_url('blueprints/' . ($designPage['blueprint_id_encrypted'] ?? '')) ?>"><?= esc($designPage['blueprint_name'] ?? '') ?></a>
        <span class="sep">/</span>
        <span class="active"><?= esc($designPage['title'] ?? '') ?> — Specifications</span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 style="font-size:22px" class="mb-1"><i class="fas fa-list-alt me-2 text-secondary"></i><?= esc($designPage['title'] ?? '') ?> — Specifications</h1>
            <p class="text-secondary mb-0" style="font-size:13px">
                Module: <?= esc($designPage['module_name'] ?? '') ?>
                <span class="mx-1">·</span>
                <span id="specCountBadge"><?= count($designPage['page_specifications'] ?? []) ?> fields</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <?php if (has_permission('blueprints', 'can_update')): ?>
            <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="showAddSpec()">
                <i class="fas fa-plus"></i> Add Specification
            </button>
            <?php endif; ?>
            <a href="<?= site_url('blueprints/' . ($designPage['blueprint_id_encrypted'] ?? '')) ?>" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-arrow-left"></i> Back to Blueprint</a>
        </div>
    </div>

    <div class="sap-card">
        <div class="sap-card-body">
            <div id="specsContainer">
                <?php if (empty($designPage['page_specifications'])): ?>
                <div class="sap-empty" style="padding:40px">
                    <i class="fas fa-list-alt" style="font-size:36px"></i>
                    <h4>No specifications</h4>
                    <p>Add page specifications for this design page.</p>
                </div>
                <?php else: ?>
                <div style="overflow-x:auto">
                    <table class="sap-table spec-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Field Name</th>
                                <th>Data</th>
                                <th>Objective</th>
                                <th>Initial Data</th>
                                <th>Condition</th>
                                <th>Validation</th>
                                <th>I/D</th>
                                <th>Datatype</th>
                                <th>Control</th>
                                <th>UX</th>
                                <?php if (has_permission('blueprints', 'can_update')): ?>
                                <th style="width:80px">Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($designPage['page_specifications'] as $sp): ?>
                            <tr data-spec-id="<?= esc($sp['id_encrypted'] ?? $sp['id'], 'attr') ?>">
                                <td><span class="spec-value"><?= esc($sp['field_name'] ?? '') ?></span></td>
                                <td><span class="spec-value"><?= esc($sp['data'] ?? '') ?></span></td>
                                <td><span class="spec-value"><?= esc($sp['objective'] ?? '') ?></span></td>
                                <td><span class="spec-value"><?= esc($sp['initial_data'] ?? '') ?></span></td>
                                <td><span class="spec-value"><?= esc($sp['condition'] ?? '') ?></span></td>
                                <td><span class="spec-value"><?= esc($sp['validation'] ?? '') ?></span></td>
                                <td><span class="spec-value"><?= esc($sp['input_display'] ?? '') ?></span></td>
                                <td><span class="spec-value"><?= esc($sp['datatype'] ?? '') ?></span></td>
                                <td><span class="spec-value"><?= esc($sp['control_type'] ?? '') ?></span></td>
                                <td>
                                    <?php if (!empty($sp['ux_attachment'])): ?>
                                    <img src="<?= site_url('uploads/blueprints/' . esc($sp['ux_attachment']['stored_name'] ?? '', 'attr')) ?>"
                                         class="spec-ux-thumb" data-full="<?= site_url('uploads/blueprints/' . esc($sp['ux_attachment']['stored_name'] ?? '', 'attr')) ?>"
                                         alt="UX" onclick="enlargeUx(this)">
                                    <?php else: ?>
                                    <span class="spec-ux-placeholder"><i class="fas fa-image"></i></span>
                                    <?php endif; ?>
                                </td>
                                <?php if (has_permission('blueprints', 'can_update')): ?>
                                <td>
                                    <button class="sap-btn sap-btn-secondary sap-btn-sm me-1" onclick="editSpec(this)" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                                    <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deleteSpec(this)" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?php if ($designPage): ?>
<?= $this->include('blueprints/_modal_spec_form') ?>
<?= $this->include('blueprints/_modal_ux_enlarged') ?>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['token' => $token, 'designPage' => $designPage], JSON_HEX_TAG | JSON_HEX_APOS) ?>;</script>
<script src="<?= base_url('public/assets/js/page/_shared/badge-helpers.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/blueprints/page_specifications.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
