<?= $this->extend('template/index') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/master-projects/detail.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">
    <?php if (!$project): ?>
        <div class="sap-empty">
            <i class="fas fa-exclamation-triangle" style="color:var(--sap-error)"></i>
            <h4>Project not found</h4>
            <a href="<?= site_url('master-projects') ?>" class="sap-btn sap-btn-secondary mt-3">Back</a>
        </div>
    <?php else: ?>
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('master-projects') ?>">Master Projects</a>
        <span class="sep">/</span>
        <span class="active"><?= esc($project['name']) ?></span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1"><?= esc($project['name']) ?></h1>
            <p class="text-secondary mb-0" style="font-size:13px">
                <?= esc($project['description']) ?>
                <?php if ($project['creator_name']): ?> &middot; Created by <?= esc($project['creator_name']) ?><?php endif; ?>
            </p>
        </div>
        <div>
            <span class="sap-badge <?= $project['status'] === 1 ? 'approved' : 'closed' ?>">
                <span class="badge-dot"></span><?= esc($project['status_name']) ?>
            </span>
        </div>
    </div>

    <div class="sap-tabs" id="detailTabs">
        <button class="sap-tab active" data-tab="modules" onclick="MasterProjectDetail.switchDetailTab('modules')">
            <i class="fas fa-puzzle-piece"></i> Modules & Pages
        </button>
        <button class="sap-tab" data-tab="kanban" onclick="MasterProjectDetail.switchDetailTab('kanban')">
            <i class="fas fa-columns"></i> Kanban Board
        </button>
    </div>

    <div class="tab-pane-container">
        <div id="tab-modules" class="tab-content active">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0" style="font-size:15px;font-weight:600"><i class="fas fa-puzzle-piece me-1"></i> Modules & Pages</h5>
                <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="MasterProjectDetail.openModuleModal()">
                    <i class="fas fa-plus"></i> Add Module
                </button>
            </div>
            <div id="modulesList">
                <?php if (empty($project['modules'])): ?>
                <div class="sap-empty" style="padding:32px 20px">
                    <i class="fas fa-puzzle-piece"></i>
                    <h4>No modules yet</h4>
                    <p>Add modules to organize your project pages.</p>
                </div>
                <?php else: ?>
                <div class="module-grid">
                    <?php foreach ($project['modules'] as $mod): ?>
                    <div class="module-card">
                        <div class="module-card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="module-card-icon"><i class="fas fa-puzzle-piece"></i></span>
                                    <h6 class="mb-0 fw-semibold"><?= esc($mod['name']) ?></h6>
                                </div>
                                <div class="sap-btn-group">
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="MasterProjectDetail.editModule('<?= $mod['id'] ?>','<?= esc(addslashes($mod['name'])) ?>','<?= esc(addslashes($mod['description'] ?? '')) ?>')" title="Edit module"><i class="fas fa-pencil-alt"></i></button>
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="MasterProjectDetail.deleteModule('<?= $mod['id'] ?>')" title="Delete module"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                            <p class="text-secondary mb-3" style="font-size:12px;line-height:1.5"><?= esc($mod['description'] ?? 'No description') ?></p>
                            <div class="d-flex align-items-center gap-3">
                                <span class="module-card-stat"><i class="fas fa-file-alt"></i> <?= count($mod['pages']) ?> pages</span>
                                <?php $totalBugs = 0; $openBugs = 0; foreach ($mod['pages'] as $pg) { $totalBugs += (int)($pg['bug_total'] ?? 0); $openBugs += (int)($pg['bug_open'] ?? 0); } ?>
                                <?php if ($totalBugs > 0): ?>
                                <span class="module-card-stat"><i class="fas fa-bug"></i> <?= $totalBugs ?> bugs</span>
                                <?php endif; ?>
                                <?php if ($openBugs > 0): ?>
                                <span class="module-card-stat module-card-stat--open"><i class="fas fa-exclamation-circle"></i> <?= $openBugs ?> open</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="module-card-footer">
                            <a href="<?= site_url('master-projects/' . $project['id'] . '/modules/' . $mod['id']) ?>" class="module-card-link">
                                <i class="fas fa-arrow-right"></i> View Pages
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="tab-kanban" class="tab-content" style="display:none">
            <div class="kanban-board" id="kanbanBoard">
                <div class="kanban-column" data-status="open">
                    <div class="kanban-column-header">
                        <span class="kanban-status-dot open"></span>
                        <span>Open</span>
                        <span class="kanban-count" id="kanban-count-open">0</span>
                    </div>
                    <div class="kanban-cards" id="kanban-col-open"></div>
                </div>
                <div class="kanban-column" data-status="in_progress">
                    <div class="kanban-column-header">
                        <span class="kanban-status-dot in_progress"></span>
                        <span>In Progress</span>
                        <span class="kanban-count" id="kanban-count-in_progress">0</span>
                    </div>
                    <div class="kanban-cards" id="kanban-col-in_progress"></div>
                </div>
                <div class="kanban-column" data-status="resolved">
                    <div class="kanban-column-header">
                        <span class="kanban-status-dot resolved"></span>
                        <span>Resolved</span>
                        <span class="kanban-count" id="kanban-count-resolved">0</span>
                    </div>
                    <div class="kanban-cards" id="kanban-col-resolved"></div>
                </div>
                <div class="kanban-column" data-status="closed">
                    <div class="kanban-column-header">
                        <span class="kanban-status-dot closed"></span>
                        <span>Closed</span>
                        <span class="kanban-count" id="kanban-count-closed">0</span>
                    </div>
                    <div class="kanban-cards" id="kanban-col-closed"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<!-- Module Modal -->
<div class="modal fade sap-modal" id="moduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moduleModalTitle">Add Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="moduleForm">
                <input type="hidden" name="edit_id" id="moduleEditId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="sap-label">Module Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="moduleName" class="sap-input" required placeholder="e.g., Authentication">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" id="moduleDesc" class="sap-input" rows="2"></textarea>
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

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode([
    'projectId' => $project['id'] ?? '',
]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/master-projects/detail.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
