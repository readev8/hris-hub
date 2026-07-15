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
                    <button class="sap-btn sap-btn-primary sap-btn-sm mb-0" onclick="showAddModule()">
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
                            <a href="#" class="list-group-item list-group-item-action module-item <?= $idx === 0 ? 'active' : '' ?>"
                               data-module-id="<?= esc($mod['id_encrypted'] ?? $mod['id'], 'attr') ?>"
                               onclick="selectModule('<?= esc($mod['id_encrypted'] ?? $mod['id'], 'attr') ?>', this); return false;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium" style="font-size:13px"><?= esc($mod['name']) ?></span>
                                    <span class="text-muted" style="font-size:11px">
                                        <?= count($mod['business_scenarios'] ?? []) ?>S /
                                        <?= count($mod['design_pages'] ?? []) ?>D /
                                        <?= count($mod['page_specifications'] ?? []) ?>P
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
                        <button class="sap-btn sap-btn-success sap-btn-sm" onclick="doAction('approve-it')"><i class="fas fa-check"></i> Approve (IT)</button>
                        <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptReject()"><i class="fas fa-times"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($st === 1 && $bpCanApprove): ?>
                        <button class="sap-btn sap-btn-success sap-btn-sm" onclick="doAction('approve-dept')"><i class="fas fa-check"></i> Approve (Dept)</button>
                        <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptReject()"><i class="fas fa-times"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($st === 3 && $bpCanCreate): ?>
                        <button class="sap-btn sap-btn-warning sap-btn-sm" onclick="doAction('resubmit')"><i class="fas fa-undo"></i> Resubmit</button>
                    <?php endif; ?>
                    <hr class="my-1">
                    <?php if (($st === -1 || $st === 0) && ($bpCanUpdate || $bpCanDelete)): ?>
                    <?php if ($bpCanUpdate): ?>
                    <a href="<?= site_url('blueprints/' . $token . '/edit') ?>" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-edit"></i> Edit</a>
                    <?php endif; ?>
                    <?php if ($bpCanDelete): ?>
                    <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="confirmDelete()"><i class="fas fa-trash"></i> Delete</button>
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
                            <a class="nav-link active" data-tab="scenarios" href="#" onclick="switchTab('scenarios'); return false;">
                                <i class="fas fa-briefcase me-1"></i> Business Scenarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-tab="design-pages" href="#" onclick="switchTab('design-pages'); return false;">
                                <i class="fas fa-palette me-1"></i> Design Pages
                            </a>
                        </li>
                    </ul>

                    <div id="tab-content-scenarios" class="tab-content-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-secondary" style="font-size:13px">Business Scenarios</h6>
                            <?php if (has_permission('blueprints', 'can_update')): ?>
                            <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="showAddScenario()">
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
                            <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="showAddDesignPage()">
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
<style>
.list-group-item.active { background: var(--sap-brand-hover); border-color: var(--sap-brand); color: var(--sap-text); }
.nav-tabs .nav-link { font-size: 13px; padding: 8px 16px; color: var(--sap-text-secondary); }
.nav-tabs .nav-link.active { color: var(--sap-brand); border-bottom: 2px solid var(--sap-brand); background: transparent; }
.tab-content-section { min-height: 200px; }
.card-item { border: 1px solid var(--sap-border); border-radius: var(--sap-radius); padding: 16px; margin-bottom: 12px; background: var(--sap-bg); }
.card-item:hover { border-color: var(--sap-brand); }
.card-item-title { font-weight: 600; font-size: 14px; margin-bottom: 4px; }
.card-item-desc { font-size: 13px; color: var(--sap-text-secondary); }
.card-item-actions { margin-top: 8px; display: flex; gap: 8px; }
.card-item-images { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
.card-item-images img { max-width: 100px; max-height: 75px; object-fit: cover; border-radius: 6px; border: 1px solid var(--sap-border); cursor: pointer; }
.field-error { font-size: 12px; color: var(--sap-error); margin-top: 4px; display: none; }
.field-error.visible { display: block; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var token = '<?= esc($token) ?>';
var userPermissions = <?= json_encode($userPermissions) ?>;
var currentModuleId = <?= json_encode($blueprint['modules'][0]['id_encrypted'] ?? $blueprint['modules'][0]['id'] ?? null) ?>;
var currentTab = 'scenarios';
var scenarioFiles = [];
var designPageFiles = [];
var blueprintModules = <?= json_encode($blueprint['modules'] ?? []) ?>;

function escHtml(str) {
    return $('<div>').text(str || '').html();
}

function selectModule(moduleId, el) {
    currentModuleId = moduleId;
    $('.module-item').removeClass('active');
    $(el).addClass('active');
    var name = $(el).find('.fw-medium').text();
    $('#currentModuleName').text(name);
    loadModuleContent(moduleId);
}

function loadModuleContent(moduleId) {
    if (!moduleId) return;
    var mod = blueprintModules.find(function(m) { return (m.id_encrypted || m.id) == moduleId; });
    if (!mod) return;
    renderScenarios(mod.business_scenarios || []);
    renderDesignPages(mod.design_pages || []);
}

// ============================================================
// AJAX Refresh System
// ============================================================
function refreshBlueprintDetail(callback) {
    $.ajax({
        url: site_url + '/blueprints/' + token + '/refresh',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#modulesList').css('opacity', '0.6');
        },
        success: function(res) {
            $('#modulesList').css('opacity', '');
            if (res.status) {
                blueprintModules = res.data.modules || [];
                updateModulesSidebar(res.data.modules);
                updateBlueprintHeader(res.data);
                updateCommentsSection(res.data.comments);
                updateAttachmentsSection(res.data.attachments);
                if (callback) callback(res.data);
            } else {
                toastr.error(res.message || 'Failed to refresh data');
            }
        },
        error: function() {
            $('#modulesList').css('opacity', '');
            toastr.error('Failed to refresh data');
        }
    });
}

function updateModulesSidebar(modules) {
    var container = $('#modulesList');
    container.empty();
    if (!modules || !modules.length) {
        container.html('<div class="sap-empty" style="padding:20px"><i class="fas fa-puzzle-piece" style="font-size:24px"></i><p class="mb-0 mt-2" style="font-size:13px">No modules yet</p></div>');
        return;
    }
    var canUpdate = userPermissions.blueprints && userPermissions.blueprints.can_update;
    modules.forEach(function(mod) {
        var moduleId = mod.id_encrypted || mod.id;
        var isActive = moduleId == currentModuleId;
        var scenarioCount = (mod.business_scenarios || []).length;
        var designCount = (mod.design_pages || []).length;
        var specCount = 0;
        (mod.design_pages || []).forEach(function(dp) { specCount += (dp.page_specifications || []).length; });
        var actionsHtml = '';
        if (canUpdate) {
            actionsHtml = '<span class="module-actions">' +
                '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="event.stopPropagation(); editModule(\'' + moduleId + '\')" style="padding:2px 6px;font-size:11px"><i class="fas fa-edit"></i></button> ' +
                '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="event.stopPropagation(); deleteModule(\'' + moduleId + '\')" style="padding:2px 6px;font-size:11px"><i class="fas fa-trash"></i></button>' +
                '</span>';
        }
        var moduleHtml = '<a href="#" class="list-group-item list-group-item-action module-item ' + (isActive ? 'active' : '') + '" ' +
            'data-module-id="' + moduleId + '" ' +
            'onclick="selectModule(\'' + moduleId + '\', this); return false;">' +
            '<div class="d-flex justify-content-between align-items-center">' +
            '<span class="fw-medium" style="font-size:13px">' + escHtml(mod.name) + '</span>' +
            '<span class="text-muted" style="font-size:11px">' + scenarioCount + 'S / ' + designCount + 'D / ' + specCount + 'P</span>' +
            '</div></a>';
        container.append(moduleHtml);
    });
    if (!currentModuleId && modules.length) {
        currentModuleId = modules[0].id_encrypted || modules[0].id;
    }
    if (currentModuleId) {
        loadModuleContent(currentModuleId);
    }
}

function updateBlueprintHeader(data) {
    var badge = $('#blueprintStatusBadge');
    if (badge.length) {
        badge.replaceWith(status_badge_js(data.status_name));
    }
    updateActionButtons(data);
}

function status_badge_js(statusName) {
    var map = {
        'Draft': 'closed',
        'Open': 'open',
        'Approved': 'approved',
        'In Progress': 'in-progress',
        'Resolved': 'resolved',
        'Closed': 'closed',
        'Rejected': 'rejected',
        'Pending': 'pending'
    };
    var cls = map[statusName] || 'closed';
    return '<span id="blueprintStatusBadge" class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + escHtml(statusName) + '</span>';
}

function updateActionButtons(data) {
    var container = $('#blueprintActions');
    if (!container.length) return;
    var actions = data.available_actions || [];
    var html = '';
    if (actions.indexOf('approve-it') !== -1) {
        html += '<button class="sap-btn sap-btn-success sap-btn-sm" onclick="doAction(\'approve-it\')"><i class="fas fa-check"></i> Approve (IT)</button>';
    }
    if (actions.indexOf('approve-dept') !== -1) {
        html += '<button class="sap-btn sap-btn-success sap-btn-sm" onclick="doAction(\'approve-dept\')"><i class="fas fa-check"></i> Approve (Dept)</button>';
    }
    if (actions.indexOf('reject') !== -1) {
        html += '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptReject()"><i class="fas fa-times"></i> Reject</button>';
    }
    if (actions.indexOf('resubmit') !== -1) {
        html += '<button class="sap-btn sap-btn-warning sap-btn-sm" onclick="doAction(\'resubmit\')"><i class="fas fa-undo"></i> Resubmit</button>';
    }
    if (html) html += '<hr class="my-1">';
    if (actions.indexOf('edit') !== -1) {
        html += '<a href="' + site_url + '/blueprints/' + token + '/edit" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-edit"></i> Edit</a>';
    }
    if (actions.indexOf('delete') !== -1) {
        html += '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="confirmDelete()"><i class="fas fa-trash"></i> Delete</button>';
    }
    container.html(html);
}

function updateCommentsSection(comments) {
    var container = $('#commentsContainer');
    if (!container.length) return;
    var badge = $('#commentCountBadge');
    if (badge.length) badge.text(comments ? comments.length : 0);
    var formHtml = '<form id="commentForm" class="mt-3" style="border-top:1px solid var(--sap-border-light);padding-top:16px">' +
        '<div class="mb-2"><textarea class="sap-input" id="commentText" rows="2" placeholder="Write a comment..." style="min-height:60px"></textarea></div>' +
        '<button class="sap-btn sap-btn-primary sap-btn-sm" type="submit"><i class="fas fa-paper-plane"></i> Send</button>' +
        '</form>';
    if (!comments || !comments.length) {
        container.html('<div class="sap-empty" style="padding:20px"><i class="fas fa-comment-dots" style="font-size:36px"></i><h4>No comments</h4></div>' + formHtml);
        bindCommentForm();
        return;
    }
    var html = '';
    comments.forEach(function(c) {
        html += '<div class="sap-comment">' +
            '<div class="sap-comment-header">' +
            '<div class="avatar-circle avatar-circle-sm" style="background:#758CA4;color:#fff">' + (c.full_name ? c.full_name.charAt(0).toUpperCase() : '?') + '</div>' +
            '<span class="sap-comment-author">' + escHtml(c.full_name || '') + '</span>' +
            '<span class="sap-comment-time">' + escHtml(c.created_at || '') + '</span>' +
            '</div>' +
            '<div class="sap-comment-body">' + (c.content || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') + '</div>' +
            '</div>';
    });
    container.html(html + formHtml);
    bindCommentForm();
}

function bindCommentForm() {
    $('#commentForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        var text = $('#commentText').val();
        if (!text.trim()) return;
        $.post(site_url + '/blueprints/' + token + '/comments', { content: text }, function(res) {
            if (res.status) {
                toastr.success('Comment added');
                $('#commentText').val('');
                refreshBlueprintDetail();
            } else {
                toastr.error(res.data.message || 'Failed');
            }
        }).fail(function(xhr) {
            toastr.error('Failed to add comment (HTTP ' + xhr.status + ')');
        });
    });
}

function updateAttachmentsSection(attachments) {
    var container = $('#attachmentsContainer');
    if (!container.length) return;
    if (!attachments || !attachments.length) {
        container.html('<p class="text-muted mb-0" style="font-size:13px">No attachments yet.</p>');
        return;
    }
    var html = '<div class="d-flex flex-wrap gap-2">';
    attachments.forEach(function(att) {
        var isImage = att.mime_type && att.mime_type.indexOf('image/') === 0;
        if (isImage) {
            html += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" class="glightbox blueprint-attachment-link" data-gallery="blueprint-attachments" data-description="' + escHtml(att.filename) + '">' +
                '<img src="' + site_url + '/uploads/blueprints/' + att.stored_name + '" alt="' + escHtml(att.filename) + '" style="max-width:80px;max-height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border);cursor:pointer" class="sap-hover-lift"></a>';
        } else {
            var mime = att.mime_type || '';
            var iconClass = 'fas fa-file';
            var iconColor = 'var(--sap-text-muted)';
            if (mime === 'application/pdf') { iconClass = 'fas fa-file-pdf'; iconColor = 'var(--sap-error)'; }
            else if (mime.indexOf('spreadsheet') !== -1 || mime === 'application/vnd.ms-excel') { iconClass = 'fas fa-file-excel'; iconColor = '#217346'; }
            else if (mime.indexOf('word') !== -1 || mime === 'application/msword') { iconClass = 'fas fa-file-word'; iconColor = '#2B579A'; }
            html += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" target="_blank">' +
                '<div style="padding:8px 12px;background:var(--sap-background);border-radius:6px;border:1px solid var(--sap-border);font-size:12px">' +
                '<i class="' + iconClass + '" style="color:' + iconColor + ';margin-right:4px"></i>' + escHtml(att.filename) + '</div></a>';
        }
    });
    html += '</div>';
    container.html(html);
    if (typeof GLightbox !== 'undefined') {
        GLightbox({ selector: '.blueprint-attachment-link', touchNavigation: true, keyboardNavigation: true, loop: false, preload: true });
    }
}

function switchTab(tab) {
    currentTab = tab;
    $('#moduleTabs .nav-link').removeClass('active');
    $('#moduleTabs .nav-link[data-tab="' + tab + '"]').addClass('active');
    $('.tab-content-section').hide();
    $('#tab-content-' + tab).show();
}

function renderScenarios(scenarios) {
    var container = $('#scenariosContainer');
    if (!scenarios.length) {
        container.html('<div class="sap-empty" style="padding:40px"><i class="fas fa-briefcase" style="font-size:36px"></i><h4>No scenarios</h4><p>Add business scenarios for this module.</p></div>');
        return;
    }
    var html = '';
    var canUpdate = userPermissions.blueprints && userPermissions.blueprints.can_update;
    scenarios.forEach(function(s) {
        var imagesHtml = '';
        if (s.attachments && s.attachments.length) {
            imagesHtml = '<div class="card-item-images">';
            s.attachments.forEach(function(att) {
                if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                    imagesHtml += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" class="glightbox scenario-image-link" data-gallery="scenario-' + s.id + '"><img src="' + site_url + '/uploads/blueprints/' + att.stored_name + '" alt="' + escHtml(att.filename) + '"></a>';
                } else {
                    imagesHtml += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" target="_blank" style="font-size:12px;color:var(--sap-brand)"><i class="fas fa-file me-1"></i>' + escHtml(att.filename) + '</a>';
                }
            });
            imagesHtml += '</div>';
        }
        html += '<div class="card-item">' +
            '<div class="card-item-title">' + escHtml(s.title) + '</div>' +
            '<div class="card-item-desc">' + escHtml(s.description || '') + '</div>' +
            imagesHtml +
            '<div class="card-item-actions">' +
            (canUpdate ? '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="editScenario(\'' + (s.id_encrypted || s.id) + '\')"><i class="fas fa-edit"></i></button>' : '') +
            (canUpdate ? '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deleteScenario(\'' + (s.id_encrypted || s.id) + '\')"><i class="fas fa-trash"></i></button>' : '') +
            '</div></div>';
    });
    container.html(html);
    GLightbox({ selector: '.scenario-image-link', touchNavigation: true, loop: false });
}

function renderDesignPages(pages) {
    var container = $('#designPagesContainer');
    if (!pages.length) {
        container.html('<div class="sap-empty" style="padding:40px"><i class="fas fa-palette" style="font-size:36px"></i><h4>No design pages</h4><p>Add design pages for this module.</p></div>');
        return;
    }
    var html = '';
    var canUpdate = userPermissions.blueprints && userPermissions.blueprints.can_update;
    pages.forEach(function(p) {
        var imagesHtml = '';
        if (p.attachments && p.attachments.length) {
            imagesHtml = '<div class="card-item-images">';
            p.attachments.forEach(function(att) {
                if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                    imagesHtml += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" class="glightbox designpage-image-link" data-gallery="designpage-' + p.id + '"><img src="' + site_url + '/uploads/blueprints/' + att.stored_name + '" alt="' + escHtml(att.filename) + '"></a>';
                }
            });
            imagesHtml += '</div>';
        }
        var specCount = (p.page_specifications || []).length;
        html += '<div class="card-item">' +
            '<div class="card-item-title">' + escHtml(p.title) + '</div>' +
            '<div class="card-item-desc">' + escHtml(p.description || '') + '</div>' +
            imagesHtml +
            '<div class="card-item-actions">' +
            '<a href="' + site_url + '/blueprints/design-pages/' + (p.id_encrypted || p.id) + '/specifications" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-list-alt"></i> Manage Specs (' + specCount + ')</a>' +
            (canUpdate ? '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="editDesignPage(\'' + (p.id_encrypted || p.id) + '\')"><i class="fas fa-edit"></i></button>' : '') +
            (canUpdate ? '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deleteDesignPage(\'' + (p.id_encrypted || p.id) + '\')"><i class="fas fa-trash"></i></button>' : '') +
            '</div></div>';
    });
    container.html(html);
    GLightbox({ selector: '.designpage-image-link', touchNavigation: true, loop: false });
}

function showAddModule() {
    $('#moduleFormId').val('');
    $('#moduleNameInput').val('');
    $('#moduleModal').modal('show');
}

function editModule(moduleId) {
    var mod = blueprintModules.find(function(m) { return (m.id_encrypted || m.id) == moduleId; });
    if (!mod) return;
    $('#moduleFormId').val(moduleId);
    $('#moduleNameInput').val(mod.name);
    $('#moduleModal').modal('show');
}

$('#moduleForm').on('submit', function(e) {
    e.preventDefault();
    var id = $('#moduleFormId').val();
    var url = id ? site_url + '/blueprints/modules/' + id + '/update' : site_url + '/blueprints/' + token + '/modules';
    $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.status) {
                toastr.success(id ? 'Module updated' : 'Module added');
                $('#moduleModal').modal('hide');
                refreshBlueprintDetail();
            } else {
                toastr.error(res.message || 'Failed');
            }
        },
        error: function() { toastr.error('Request failed'); }
    });
});

function deleteModule(moduleId) {
    Swal.fire({
        title: 'Delete Module?',
        text: 'This will remove all scenarios, pages, and specifications.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(site_url + '/blueprints/modules/' + moduleId + '/delete', {}, function(res) {
                if (res.status) {
                    toastr.success('Module deleted');
                    currentModuleId = null;
                    refreshBlueprintDetail();
                } else {
                    toastr.error(res.message || 'Failed');
                }
            });
        }
    });
}

function showAddScenario() {
    if (!currentModuleId) { toastr.warning('Select a module first'); return; }
    $('#scenarioFormId').val('');
    $('#scenarioTitleInput').val('');
    $('#scenarioDescInput').val('');
    scenarioFiles = [];
    renderScenarioFiles();
    $('#scenarioModal').modal('show');
}

function editScenario(scenarioId) {
    var mod = blueprintModules.find(function(m) { return (m.id_encrypted || m.id) == currentModuleId; });
    if (!mod) return;
    var scenarios = mod.business_scenarios || [];
    var s = scenarios.find(function(sc) { return (sc.id_encrypted || sc.id) == scenarioId; });
    if (!s) return;
    $('#scenarioFormId').val(scenarioId);
    $('#scenarioTitleInput').val(s.title);
    $('#scenarioDescInput').val(s.description);
    scenarioFiles = [];
    renderScenarioFiles();
    $('#scenarioModal').modal('show');
}

function deleteScenario(scenarioId) {
    Swal.fire({
        title: 'Delete Scenario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(site_url + '/blueprints/business-scenarios/' + scenarioId + '/delete', {}, function(res) {
                if (res.status) {
                    toastr.success('Scenario deleted');
                    refreshBlueprintDetail();
                } else {
                    toastr.error(res.message || 'Failed');
                }
            });
        }
    });
}

function renderScenarioFiles() {
    var preview = $('#scenarioFilePreview');
    preview.empty();
    scenarioFiles.forEach(function(file, i) {
        var wrapper = $('<div style="position:relative;display:inline-block"></div>');
        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                wrapper.append('<img src="' + e.target.result + '" style="max-width:100px;max-height:75px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)">');
                wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + i + '" style="position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center">&times;</button>');
            };
            reader.readAsDataURL(file);
        } else {
            wrapper.append('<div style="padding:8px 12px;background:var(--sap-background);border-radius:6px;border:1px solid var(--sap-border);font-size:12px"><i class="fas fa-file" style="margin-right:4px"></i>' + file.name + '</div>');
            wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + i + '" style="position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center">&times;</button>');
        }
        preview.append(wrapper);
    });
}

$('#scenarioDropzone').on('click', function() { $(this).find('input[type="file"]').click(); })
.on('dragover', function(e) { e.preventDefault(); $(this).css('border-color', 'var(--sap-brand)'); })
.on('dragleave', function() { $(this).css('border-color', 'var(--sap-border)'); })
.on('drop', function(e) {
    e.preventDefault();
    $(this).css('border-color', 'var(--sap-border)');
    var files = e.originalEvent.dataTransfer.files;
    for (var i = 0; i < files.length; i++) { scenarioFiles.push(files[i]); }
    renderScenarioFiles();
});

$('#scenarioForm input[name="images[]"]').on('change', function() {
    for (var i = 0; i < this.files.length; i++) { scenarioFiles.push(this.files[i]); }
    renderScenarioFiles();
    $(this).val('');
});

$('#scenarioFilePreview').on('click', '.btn-remove-file', function() {
    scenarioFiles.splice($(this).data('idx'), 1);
    renderScenarioFiles();
});

$('#scenarioForm').on('submit', function(e) {
    e.preventDefault();
    var id = $('#scenarioFormId').val();
    var url = id ? site_url + '/blueprints/business-scenarios/' + id + '/update' : site_url + '/blueprints/modules/' + currentModuleId + '/business-scenarios';
    var fd = new FormData(this);
    fd.delete('images[]');
    scenarioFiles.forEach(function(f) { fd.append('images[]', f); });
    $.ajax({
        url: url,
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status) {
                toastr.success(id ? 'Scenario updated' : 'Scenario added');
                $('#scenarioModal').modal('hide');
                refreshBlueprintDetail();
            } else {
                toastr.error(res.message || 'Failed');
            }
        },
        error: function() { toastr.error('Request failed'); }
    });
});

function showAddDesignPage() {
    if (!currentModuleId) { toastr.warning('Select a module first'); return; }
    $('#designPageFormId').val('');
    $('#designPageTitleInput').val('');
    $('#designPageDescInput').val('');
    designPageFiles = [];
    renderDesignPageFiles();
    $('#designPageModal').modal('show');
}

function editDesignPage(pageId) {
    var mod = blueprintModules.find(function(m) { return (m.id_encrypted || m.id) == currentModuleId; });
    if (!mod) return;
    var pages = mod.design_pages || [];
    var p = pages.find(function(dp) { return (dp.id_encrypted || dp.id) == pageId; });
    if (!p) return;
    $('#designPageFormId').val(pageId);
    $('#designPageTitleInput').val(p.title);
    $('#designPageDescInput').val(p.description);
    designPageFiles = [];
    renderDesignPageFiles();
    $('#designPageModal').modal('show');
}

function deleteDesignPage(pageId) {
    Swal.fire({
        title: 'Delete Design Page?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(site_url + '/blueprints/design-pages/' + pageId + '/delete', {}, function(res) {
                if (res.status) {
                    toastr.success('Design page deleted');
                    refreshBlueprintDetail();
                } else {
                    toastr.error(res.message || 'Failed');
                }
            });
        }
    });
}

function renderDesignPageFiles() {
    var preview = $('#designPageFilePreview');
    preview.empty();
    designPageFiles.forEach(function(file, i) {
        var wrapper = $('<div style="position:relative;display:inline-block"></div>');
        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                wrapper.append('<img src="' + e.target.result + '" style="max-width:100px;max-height:75px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)">');
                wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + i + '" style="position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center">&times;</button>');
            };
            reader.readAsDataURL(file);
        }
        preview.append(wrapper);
    });
}

$('#designPageDropzone').on('click', function() { $(this).find('input[type="file"]').click(); })
.on('dragover', function(e) { e.preventDefault(); $(this).css('border-color', 'var(--sap-brand)'); })
.on('dragleave', function() { $(this).css('border-color', 'var(--sap-border)'); })
.on('drop', function(e) {
    e.preventDefault();
    $(this).css('border-color', 'var(--sap-border)');
    var files = e.originalEvent.dataTransfer.files;
    for (var i = 0; i < files.length; i++) { designPageFiles.push(files[i]); }
    renderDesignPageFiles();
});

$('#designPageForm input[name="images[]"]').on('change', function() {
    for (var i = 0; i < this.files.length; i++) { designPageFiles.push(this.files[i]); }
    renderDesignPageFiles();
    $(this).val('');
});

$('#designPageFilePreview').on('click', '.btn-remove-file', function() {
    designPageFiles.splice($(this).data('idx'), 1);
    renderDesignPageFiles();
});

$('#designPageForm').on('submit', function(e) {
    e.preventDefault();
    var id = $('#designPageFormId').val();
    var url = id ? site_url + '/blueprints/design-pages/' + id + '/update' : site_url + '/blueprints/modules/' + currentModuleId + '/design-pages';
    var fd = new FormData(this);
    fd.delete('images[]');
    designPageFiles.forEach(function(f) { fd.append('images[]', f); });
    $.ajax({
        url: url,
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status) {
                toastr.success(id ? 'Design page updated' : 'Design page added');
                $('#designPageModal').modal('hide');
                refreshBlueprintDetail();
            } else {
                toastr.error(res.message || 'Failed');
            }
        },
        error: function() { toastr.error('Request failed'); }
    });
});

function doAction(action) {
    $.post(site_url + '/blueprints/' + token + '/' + action, {}, function(res) {
        if (res.status) {
            toastr.success(res.data.message);
            refreshBlueprintDetail();
        } else {
            toastr.error(res.data.message || 'Action failed');
        }
    }).fail(function(xhr) {
        toastr.error('Action failed (HTTP ' + xhr.status + ')');
    });
}

function promptReject() {
    Swal.fire({
        title: 'Rejection Notes',
        input: 'textarea',
        inputPlaceholder: 'Enter reason for rejection...',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed && result.value) {
            $.post(site_url + '/blueprints/' + token + '/reject', { notes: result.value }, function(res) {
                if (res.status) {
                    toastr.success(res.data.message);
                    refreshBlueprintDetail();
                } else {
                    toastr.error(res.data.message || 'Failed');
                }
            }).fail(function(xhr) {
                toastr.error('Reject failed (HTTP ' + xhr.status + ')');
            });
        }
    });
}

function confirmDelete() {
    Swal.fire({
        title: 'Delete Blueprint?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(site_url + '/blueprints/' + token + '/delete', {}, function(res) {
                if (res.status) {
                    toastr.success('Blueprint deleted');
                    setTimeout(function() { window.location.href = site_url + '/blueprints'; }, 800);
                } else {
                    toastr.error(res.data.message || 'Failed to delete');
                }
            }).fail(function(xhr) {
                toastr.error('Delete failed (HTTP ' + xhr.status + ')');
            });
        }
    });
}

$('#attachmentInput').on('change', function() {
    var files = this.files;
    if (!files.length) return;

    var maxSize = 500 * 1024;
    var fd = new FormData();
    var skipped = 0;
    for (var i = 0; i < files.length; i++) {
        if (files[i].size > maxSize) {
            toastr.warning(files[i].name + ' exceeds 500KB limit');
            skipped++;
            continue;
        }
        fd.append('images[]', files[i]);
    }

    if (skipped >= files.length) { $(this).val(''); return; }

    var btn = $(this).closest('label');
    btn.css('pointer-events', 'none').css('opacity', '0.6');

    $.ajax({
        url: site_url + '/blueprints/' + token + '/attachments',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status) {
                toastr.success('Attachment(s) uploaded');
                refreshBlueprintDetail();
            } else {
                toastr.error(res.message || 'Failed to upload');
                btn.css('pointer-events', '').css('opacity', '');
                $('#attachmentInput').val('');
            }
        },
        error: function(xhr) {
            var res = null;
            try { res = JSON.parse(xhr.responseText); } catch(e) {}
            if (res && res.redirect) { window.location.href = res.redirect; return; }
            toastr.error(res && res.message ? res.message : 'Upload failed (HTTP ' + xhr.status + ')');
            btn.css('pointer-events', '').css('opacity', '');
            $('#attachmentInput').val('');
        }
    });
    $(this).val('');
});

var blueprintLightbox = GLightbox({
    selector: '.blueprint-attachment-link',
    touchNavigation: true,
    keyboardNavigation: true,
    loop: false,
    preload: true
});

$(function() {
    if (currentModuleId) {
        var firstModule = $('.module-item[data-module-id="' + currentModuleId + '"]');
        if (firstModule.length) {
            selectModule(currentModuleId, firstModule[0]);
        }
    }
});
</script>
<?= $this->endSection() ?>
