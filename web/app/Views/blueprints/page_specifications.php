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
<div class="modal fade sap-modal" id="specModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="specModalTitle"><i class="fas fa-list-alt me-2"></i>Add Page Specification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="specForm">
                <input type="hidden" name="blueprint_token" value="<?= esc($designPage['blueprint_id_encrypted'] ?? '', 'attr') ?>">
                <input type="hidden" name="spec_id" id="specFormId">
                <input type="hidden" name="existing_ux_att_id" id="specFormExistingAttId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="sap-label">Field Name <span class="text-danger">*</span></label>
                            <input type="text" name="field_name" class="sap-input" id="specFieldNameInput" required placeholder="e.g., Username">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Data</label>
                            <input type="text" name="data" class="sap-input" id="specDataInput" placeholder="e.g., varchar(100)">
                        </div>
                        <div class="col-md-12">
                            <label class="sap-label">Objective</label>
                            <input type="text" name="objective" class="sap-input" id="specObjectiveInput" placeholder="e.g., User identification">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Initial Data</label>
                            <input type="text" name="initial_data" class="sap-input" id="specInitialDataInput" placeholder="e.g., Empty">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Condition</label>
                            <input type="text" name="condition" class="sap-input" id="specConditionInput" placeholder="e.g., Required for login">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Validation</label>
                            <input type="text" name="validation" class="sap-input" id="specValidationInput" placeholder="e.g., Min 6 chars">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Input/Display</label>
                            <select name="input_display" class="sap-select" id="specInputDisplayInput">
                                <option value="Input">Input</option>
                                <option value="Display">Display</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Datatype</label>
                            <select name="datatype" class="sap-select" id="specDatatypeInput">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="datetime">DateTime</option>
                                <option value="time">Time</option>
                                <option value="image">Image</option>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Control Type</label>
                            <select name="control_type" class="sap-select" id="specControlTypeInput">
                                <option value="text">Text</option>
                                <option value="password">Password</option>
                                <option value="date">Date</option>
                                <option value="datetime">DateTime</option>
                                <option value="combobox">Combobox</option>
                                <option value="radiobutton">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="multipleselect">Multiple Select</option>
                                <option value="uploadfile">Upload File</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="sap-label">UX Image <span class="text-secondary" style="font-weight:400;font-size:12px">(optional, max 500KB, JPG/PNG/WebP)</span></label>
                            <div class="spec-ux-dropzone" id="specUxDropzone">
                                <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:var(--sap-text-muted);display:block;margin-bottom:4px"></i>
                                <p class="mb-0 text-secondary" style="font-size:12px">Drop image here or click to browse</p>
                                <button type="button" class="sap-btn sap-btn-secondary sap-btn-sm mt-2" onclick="event.stopPropagation(); $('#specUxFile').click();">
                                    <i class="fas fa-image"></i> Pilih Gambar
                                </button>
                                <input type="file" name="ux_image[]" accept="image/jpeg,image/png,image/webp" hidden id="specUxFile">
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2" id="specUxPreview"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--sap-border)">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-save"></i> <span id="specSubmitText">Save</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade sap-modal" id="uxEnlargedModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:transparent;border:none;box-shadow:none">
            <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" style="z-index:10;filter:invert(1)"></button>
            <img src="" id="uxEnlargedImg" class="w-100" style="border-radius:8px;max-height:80vh;object-fit:contain">
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['token' => $token, 'designPage' => $designPage]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/_shared/badge-helpers.js?v=' . config('App')->assetVersion) ?>"></script>
<script src="<?= base_url('public/assets/js/page/blueprints/page_specifications.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
