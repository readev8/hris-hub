<?php
/**
 * ============================================================================
 * BLUEPRINTS - DETAIL
 * ============================================================================
 *
 * Description: Detail view for a single blueprint with modules, scenarios, design pages, and actions
 *
 * Required: $token, $blueprint, $userPermissions
 * Optional: none
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container-fluid" style="max-width:1400px">
    <?php if (!$blueprint): ?>
        <div class="sap-empty">
            <i class="fas fa-exclamation-triangle" style="color:var(--sap-error)"></i>
            <h4>Blueprint not found</h4>
            <p>The blueprint you're looking for doesn't exist or has been removed.</p>
            <a href="<?= site_url('blueprints') ?>" class="sap-btn sap-btn-secondary mt-3">Back</a>
        </div>
    <?php else: ?>
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('blueprints') ?>">Blueprints</a>
        <span class="sep">/</span>
        <span class="active"><?= esc($blueprint['name']) ?></span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 style="font-size:22px" class="mb-1"><?= esc($blueprint['name']) ?></h1>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span id="blueprintStatusBadge"><?= status_badge($blueprint['status_name'] ?? '') ?></span>
            </div>
            <?php if (!empty($blueprint['description'])): ?>
            <p class="blueprint-description"><?= nl2br(esc($blueprint['description'])) ?></p>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('blueprints') ?>" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
            <button id="btnExportPdf" class="sap-btn sap-btn-primary sap-btn-sm" onclick="BlueprintDetail.exportPdf()"><i class="fas fa-download"></i> Download PDF</button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="sap-card mb-3">
                <div class="sap-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-puzzle-piece"></i> Modules</span>
                    <?php if (has_permission('blueprints', 'can_update')): ?>
                    <button class="sap-btn sap-btn-primary sap-btn-sm mb-0" onclick="BlueprintDetail.showAddModule()">
                        <i class="fas fa-plus"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="sap-card-body p-0">
                    <div id="modulesList" class="list-group list-group-flush" style="max-height:400px;overflow-y:auto">
                        <?php if (empty($blueprint['modules'])): ?>
                            <div class="sap-empty" style="padding:20px">
                                <i class="fas fa-puzzle-piece" style="font-size:24px"></i>
                                <p class="mb-0 mt-2" style="font-size:13px">No modules yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($blueprint['modules'] as $idx => $mod): ?>
                            <?php
                            $specCount = 0;
                            foreach ($mod['design_pages'] ?? [] as $dp) {
                                $specCount += count($dp['page_specifications'] ?? []);
                            }
                            ?>
                            <a href="#" class="list-group-item list-group-item-action module-item <?= $idx === 0 ? 'active' : '' ?>"
                               data-module-id="<?= esc($mod['id'], 'attr') ?>"
                               onclick="BlueprintDetail.selectModule('<?= esc($mod['id'], 'attr') ?>', this); return false;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium" style="font-size:13px"><?= esc($mod['name']) ?></span>
                                    <span class="text-muted" style="font-size:11px">
                                        <?= count($mod['business_scenarios'] ?? []) ?>S /
                                        <?= count($mod['design_pages'] ?? []) ?>D /
                                        <?= $specCount ?>P
                                    </span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="sap-card mb-3">
                <div class="sap-card-header">
                    <i class="fas fa-info-circle"></i> Details
                </div>
                <div class="sap-card-body" style="font-size:14px">
                    <dl class="row mb-0" style="gap:4px 0">
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Creator</dt>
                        <dd class="col-7"><?= esc($blueprint['creator_name'] ?? '') ?></dd>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Created</dt>
                        <dd class="col-7"><?= esc($blueprint['created_at']) ?></dd>
                        <?php if (!empty($blueprint['improvement_name'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Improvement</dt>
                        <dd class="col-7"><a href="<?= site_url('improvements/' . ($blueprint['improvement_token'] ?? '')) ?>" style="font-size:13px"><?= esc($blueprint['improvement_name']) ?></a></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="sap-card mb-3" style="display:none">
                <div class="sap-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-paperclip"></i> Attachments</span>
                    <?php if (has_permission('blueprints', 'can_update')): ?>
                    <label class="sap-btn sap-btn-secondary sap-btn-sm mb-0" style="cursor:pointer">
                        <i class="fas fa-plus"></i> Add
                        <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,.xlsx,.xls,.doc,.docx" multiple hidden id="attachmentInput">
                    </label>
                    <?php endif; ?>
                </div>
                <div id="attachmentsContainer" class="sap-card-body">
                    <?php if (!empty($blueprint['attachments'])): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($blueprint['attachments'] as $att): ?>
                            <?php if (strpos($att['mime_type'] ?? '', 'image/') === 0): ?>
                        <a href="<?= site_url('uploads/blueprints/' . $att['stored_name']) ?>"
                           class="glightbox blueprint-attachment-link"
                           data-gallery="blueprint-<?= $blueprint['id'] ?>"
                           data-description="<?= esc($att['filename']) ?>">
                            <img src="<?= site_url('uploads/blueprints/' . $att['stored_name']) ?>"
                                 alt="<?= esc($att['filename']) ?>"
                                 style="max-width:80px;max-height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border);cursor:pointer"
                                 class="sap-hover-lift">
                        </a>
                            <?php else: ?>
                        <?php
                        $mime = $att['mime_type'] ?? '';
                        $iconClass = 'fas fa-file';
                        $iconColor = 'var(--sap-text-muted)';
                        if ($mime === 'application/pdf') { $iconClass = 'fas fa-file-pdf'; $iconColor = 'var(--sap-error)'; }
                        elseif (in_array($mime, ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])) { $iconClass = 'fas fa-file-excel'; $iconColor = '#217346'; }
                        elseif (in_array($mime, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) { $iconClass = 'fas fa-file-word'; $iconColor = '#2B579A'; }
                        ?>
                        <a href="<?= site_url('uploads/blueprints/' . $att['stored_name']) ?>" target="_blank">
                            <div style="padding:8px 12px;background:var(--sap-background);border-radius:6px;border:1px solid var(--sap-border);font-size:12px">
                                <i class="<?= $iconClass ?>" style="color:<?= $iconColor ?>;margin-right:4px"></i>
                                <?= esc($att['filename']) ?>
                            </div>
                        </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0" style="font-size:13px">No attachments yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sap-card">
                <div class="sap-card-header">
                    <i class="fas fa-bolt"></i> Actions
                </div>
                <div id="blueprintActions" class="sap-card-body d-flex flex-column gap-2">
                    <?php
                    $bpPerms = (session('permissions') ?? [])['blueprints'] ?? [];
                    $bpCanUpdate  = !empty($bpPerms['can_update']);
                    $bpCanDelete  = !empty($bpPerms['can_delete']);
                    ?>
                    <?php if ($bpCanUpdate || $bpCanDelete): ?>
                    <?php if ($bpCanUpdate): ?>
                    <a href="<?= site_url('blueprints/' . $token . '/edit') ?>" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-edit"></i> Edit</a>
                    <?php endif; ?>
                    <?php if ($bpCanDelete): ?>
                    <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.confirmDelete()"><i class="fas fa-trash"></i> Delete</button>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="sap-card mb-3">
                <div class="sap-card-header">
                    <i class="fas fa-folder-open"></i> <span id="currentModuleName">Select a module</span>
                </div>
                <div class="sap-card-body">
                    <ul class="nav nav-tabs mb-3" id="moduleTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-tab="scenarios" href="#" onclick="BlueprintDetail.switchTab('scenarios'); return false;">
                                <i class="fas fa-briefcase me-1"></i> Business Scenarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-tab="design-pages" href="#" onclick="BlueprintDetail.switchTab('design-pages'); return false;">
                                <i class="fas fa-palette me-1"></i> Design Pages
                            </a>
                        </li>
                    </ul>

                    <div id="tab-content-scenarios" class="tab-content-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-secondary" style="font-size:13px">Business Scenarios</h6>
                            <?php if (has_permission('blueprints', 'can_update')): ?>
                            <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="BlueprintDetail.showAddScenario()">
                                <i class="fas fa-plus"></i> Add Scenario
                            </button>
                            <?php endif; ?>
                        </div>
                        <div id="scenariosContainer">
                            <div class="sap-empty" style="padding:40px">
                                <i class="fas fa-briefcase" style="font-size:36px"></i>
                                <h4>No scenarios</h4>
                                <p>Select a module and add business scenarios.</p>
                            </div>
                        </div>
                    </div>

                    <div id="tab-content-design-pages" class="tab-content-section" style="display:none">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-secondary" style="font-size:13px">Design Pages</h6>
                            <?php if (has_permission('blueprints', 'can_update')): ?>
                            <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="BlueprintDetail.showAddDesignPage()">
                                <i class="fas fa-plus"></i> Add Design Page
                            </button>
                            <?php endif; ?>
                        </div>
                        <div id="designPagesContainer">
                            <div class="sap-empty" style="padding:40px">
                                <i class="fas fa-palette" style="font-size:36px"></i>
                                <h4>No design pages</h4>
                                <p>Select a module and add design pages.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sap-card mb-3" style="display:none">
                <div class="sap-card-header">
                    <i class="fas fa-comment-dots"></i> Comments
                    <span id="commentCountBadge" class="sap-badge closed" style="font-size:11px;margin-left:4px"><?= count($blueprint['comments'] ?? []) ?></span>
                </div>
                <div id="commentsContainer" class="sap-card-body">
                    <?php if (empty($blueprint['comments'])): ?>
                        <div class="sap-empty" style="padding:20px">
                            <i class="fas fa-comment-dots" style="font-size:36px"></i>
                            <h4>No comments</h4>
                        </div>
                    <?php else: ?>
                        <?php foreach ($blueprint['comments'] as $c): ?>
                        <div class="sap-comment">
                            <div class="sap-comment-header">
                                <?= avatar_initials($c['full_name'] ?? '?', 'sm', '#758CA4') ?>
                                <span class="sap-comment-author"><?= esc($c['full_name'] ?? '') ?></span>
                                <span class="sap-comment-time"><?= esc($c['created_at']) ?></span>
                            </div>
                            <div class="sap-comment-body"><?= nl2br(esc($c['content'])) ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <form id="commentForm" class="mt-3" style="border-top:1px solid var(--sap-border-light);padding-top:16px">
                        <div class="mb-2">
                            <textarea class="sap-input" id="commentText" rows="2" placeholder="Write a comment..." style="min-height:60px"></textarea>
                        </div>
                        <button class="sap-btn sap-btn-primary sap-btn-sm" type="submit"><i class="fas fa-paper-plane"></i> Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('blueprints/_modal_add_module') ?>
<?= $this->include('blueprints/_modal_add_scenario') ?>
<?= $this->include('blueprints/_modal_add_design_page') ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/vendor/summernote/0.9.1/summernote-bs5.min.css') ?>?v=<?= config('App')->assetVersion ?>">
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/blueprints/detail.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode([
    'token'            => $token,
    'userPermissions'  => $userPermissions,
    'currentModuleId'  => $blueprint['modules'][0]['id'] ?? null,
    'modules'          => $blueprint['modules'] ?? [],
], JSON_HEX_TAG | JSON_HEX_APOS) ?>;</script>
<script src="<?= base_url('public/assets/js/page/blueprints/detail-ui.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/assets/js/page/blueprints/detail-dropzones.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/assets/js/page/blueprints/detail-crud.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/assets/js/page/blueprints/detail.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/vendor/html-to-pdfmake/2.5.20/html-to-pdfmake.browser.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/assets/js/page/blueprints/export.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/vendor/summernote/0.9.1/summernote-bs5.min.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
