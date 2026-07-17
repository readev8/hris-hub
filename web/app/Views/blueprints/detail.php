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
            <div class="d-flex align-items-center gap-2">
                <span id="blueprintStatusBadge"><?= status_badge($blueprint['status_name'] ?? '') ?></span>
            </div>
        </div>
        <a href="<?= site_url('blueprints') ?>" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="sap-card mb-4">
        <div class="sap-card-body">
            <div class="approval-stepper">
                <?php
                $st = (int) ($blueprint['status'] ?? -1);
                $steps = [
                    ['label' => 'Draft',      'key' => 'draft'],
                    ['label' => 'IT Manager',  'key' => 'it'],
                    ['label' => 'Dept Head',   'key' => 'dept'],
                    ['label' => 'Approved',    'key' => 'final'],
                ];
                $stepStates = ['draft' => 'completed'];
                if ($st === 0) { $stepStates['it'] = 'active'; $stepStates['dept'] = ''; $stepStates['final'] = ''; }
                elseif ($st === 1) { $stepStates['it'] = 'completed'; $stepStates['dept'] = 'active'; $stepStates['final'] = ''; }
                elseif ($st === 2 || $st === 4) { $stepStates['it'] = 'completed'; $stepStates['dept'] = 'completed'; $stepStates['final'] = 'active'; }
                elseif ($st === 3) {
                    $history = $blueprint['approval_history'] ?? [];
                    $rejectedStage = 0;
                    foreach ($history as $h) {
                        if ((int)($h['status'] ?? 0) === 2) {
                            $rejectedStage = (int)($h['stage_sequence'] ?? 0);
                            break;
                        }
                    }
                    if ($rejectedStage <= 1) { $stepStates['it'] = 'rejected'; $stepStates['dept'] = ''; $stepStates['final'] = ''; }
                    else { $stepStates['it'] = 'completed'; $stepStates['dept'] = 'rejected'; $stepStates['final'] = ''; }
                }
                $icons = ['draft' => 'fa-pencil-alt', 'it' => 'fa-laptop', 'dept' => 'fa-users', 'final' => 'fa-check-double'];
                foreach ($steps as $i => $s):
                    $state = $stepStates[$s['key']] ?? '';
                    $icon = $icons[$s['key']];
                ?>
                <div class="stepper-step <?= esc($state, 'attr') ?>">
                    <div class="stepper-node">
                        <?php if ($state === 'completed'): ?><i class="fas fa-check"></i>
                        <?php elseif ($state === 'rejected'): ?><i class="fas fa-times"></i>
                        <?php else: ?><?= $i + 1 ?>
                        <?php endif; ?>
                    </div>
                    <div class="stepper-label"><?= esc($s['label']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
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

            <div class="sap-card mb-3">
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
                    $bpCanApprove = !empty($bpPerms['can_approve']);
                    $bpCanCreate  = !empty($bpPerms['can_create']);
                    $bpCanUpdate  = !empty($bpPerms['can_update']);
                    $bpCanDelete  = !empty($bpPerms['can_delete']);
                    ?>
                    <?php if ($st === 0 && $bpCanApprove): ?>
                        <button class="sap-btn sap-btn-success sap-btn-sm" onclick="BlueprintDetail.doAction('approve-it')"><i class="fas fa-check"></i> Approve (IT)</button>
                        <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.promptReject()"><i class="fas fa-times"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($st === 1 && $bpCanApprove): ?>
                        <button class="sap-btn sap-btn-success sap-btn-sm" onclick="BlueprintDetail.doAction('approve-dept')"><i class="fas fa-check"></i> Approve (Dept)</button>
                        <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.promptReject()"><i class="fas fa-times"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($st === 3 && $bpCanCreate): ?>
                        <button class="sap-btn sap-btn-warning sap-btn-sm" onclick="BlueprintDetail.doAction('resubmit')"><i class="fas fa-undo"></i> Resubmit</button>
                    <?php endif; ?>
                    <hr class="my-1">
                    <?php if (($st === -1 || $st === 0) && ($bpCanUpdate || $bpCanDelete)): ?>
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

            <div class="sap-card mb-3">
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

<!-- Add Module Modal -->
<div class="modal fade sap-modal" id="moduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-puzzle-piece me-2"></i>Add Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="moduleForm">
                <div class="modal-body">
                    <input type="hidden" name="module_id" id="moduleFormId">
                    <div class="mb-3">
                        <label class="sap-label">Module Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="sap-input" id="moduleNameInput" required placeholder="e.g., Authentication Module">
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--sap-border)">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Scenario Modal -->
<div class="modal fade sap-modal" id="scenarioModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-briefcase me-2"></i>Add Business Scenario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scenarioForm">
                <div class="modal-body">
                    <input type="hidden" name="scenario_id" id="scenarioFormId">
                    <input type="hidden" name="blueprint_token" value="<?= esc($token, 'attr') ?>">
                    <div class="mb-3">
                        <label class="sap-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="sap-input" id="scenarioTitleInput" required placeholder="e.g., User Login">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" class="sap-input" rows="4" id="scenarioDescInput" placeholder="Describe the business scenario..." style="min-height:100px"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Attachments <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 5)</span></label>
                        <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:16px;text-align:center;cursor:pointer" id="scenarioDropzone">
                            <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:var(--sap-text-muted);display:block;margin-bottom:4px"></i>
                            <p class="mb-0 text-secondary" style="font-size:12px">Drop files here or click to browse</p>
                            <button type="button" class="sap-btn sap-btn-secondary sap-btn-sm mt-2" onclick="event.stopPropagation(); $(this).closest('.mb-3').find('input[type=file]').click();">
                                <i class="fas fa-upload"></i> Pilih File
                            </button>
                            <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf" multiple hidden>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2" id="scenarioFilePreview"></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--sap-border)">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Design Page Modal -->
<div class="modal fade sap-modal" id="designPageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-palette me-2"></i>Add Design Page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="designPageForm">
                <div class="modal-body">
                    <input type="hidden" name="design_page_id" id="designPageFormId">
                    <input type="hidden" name="blueprint_token" value="<?= esc($token, 'attr') ?>">
                    <div class="mb-3">
                        <label class="sap-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="sap-input" id="designPageTitleInput" required placeholder="e.g., Login Page">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" class="sap-input" rows="4" id="designPageDescInput" placeholder="Describe the design page..." style="min-height:100px"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Images <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 10, JPG/PNG/WebP)</span></label>
                        <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:16px;text-align:center;cursor:pointer" id="designPageDropzone">
                            <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:var(--sap-text-muted);display:block;margin-bottom:4px"></i>
                            <p class="mb-0 text-secondary" style="font-size:12px">Drop images here or click to browse</p>
                            <button type="button" class="sap-btn sap-btn-secondary sap-btn-sm mt-2" onclick="event.stopPropagation(); $(this).closest('.mb-3').find('input[type=file]').click();">
                                <i class="fas fa-image"></i> Pilih Gambar
                            </button>
                            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple hidden>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2" id="designPageFilePreview"></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--sap-border)">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/blueprints/detail.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/vendor/summernote/0.9.1/summernote-bs5.min.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
