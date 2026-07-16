<?= $this->extend('template/index') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/master-projects/module_detail.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('master-projects') ?>">Master Projects</a>
        <span class="sep">/</span>
        <a href="<?= site_url('master-projects/' . $project['id']) ?>"><?= esc($project['name']) ?></a>
        <span class="sep">/</span>
        <span class="active"><?= esc($module['name']) ?></span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1"><?= esc($module['name']) ?></h1>
            <p class="text-secondary mb-0" style="font-size:13px">
                <?= esc($module['description'] ?? '') ?>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="ModuleDetail.editModule('<?= $module['id'] ?>', '<?= esc(addslashes($module['name'])) ?>', '<?= esc(addslashes($module['description'] ?? '')) ?>')">
                <i class="fas fa-pencil-alt"></i> Edit Module
            </button>
            <button class="sap-btn sap-btn-ghost sap-btn-sm sap-btn-danger-ghost" onclick="ModuleDetail.deleteModule('<?= $module['id'] ?>')">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>

    <div class="sap-tabs" id="detailTabs">
        <button class="sap-tab active" data-tab="pages" onclick="ModuleDetail.switchDetailTab('pages')">
            <i class="fas fa-file-alt"></i> Pages
            <span class="sap-badge closed" style="margin-left:6px"><?= count($module['pages']) ?></span>
        </button>
        <button class="sap-tab" data-tab="kanban" onclick="ModuleDetail.switchDetailTab('kanban')">
            <i class="fas fa-columns"></i> Kanban Board
        </button>
    </div>

    <div class="tab-pane-container">
        <div id="tab-pages" class="tab-content active">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0" style="font-size:15px;font-weight:600"><i class="fas fa-file-alt me-1"></i> Pages</h5>
                <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="ModuleDetail.openPageModal()">
                    <i class="fas fa-plus"></i> Add Page
                </button>
            </div>
            <div id="pagesList">
                <?php if (empty($module['pages'])): ?>
                <div class="sap-empty" style="padding:32px 20px">
                    <i class="fas fa-file-alt"></i>
                    <h4>No pages yet</h4>
                    <p>Add pages to this module to track bugs per page.</p>
                </div>
                <?php else: ?>
                <table class="sap-table sap-table-compact mb-0">
                    <thead>
                        <tr>
                            <th style="width:35%">Page Name</th>
                            <th style="width:25%">URL Path</th>
                            <th>Bugs</th>
                            <th style="width:130px;text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($module['pages'] as $pg): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="page-icon-wrapper"><i class="fas fa-file-alt"></i></span>
                                    <span class="fw-medium"><?= esc($pg['name']) ?></span>
                                </div>
                            </td>
                            <td><code class="mono table-code"><?= esc($pg['url_path'] ?? '-') ?></code></td>
                            <td>
                                <?php $totalBugs = (int) ($pg['bug_total'] ?? 0); ?>
                                <?php if ($totalBugs > 0): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ((int)$pg['bug_open'] > 0): ?>
                                    <span class="sap-badge rejected"><span class="badge-dot"></span><?= $pg['bug_open'] ?> open</span>
                                    <?php endif; ?>
                                    <?php if ((int)$pg['bug_resolved'] > 0): ?>
                                    <span class="sap-badge approved"><?= $pg['bug_resolved'] ?> resolved</span>
                                    <?php endif; ?>
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="ModuleDetail.showBugList('<?= $pg['id'] ?>', '<?= esc($pg['name']) ?>')" title="View bug list">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                </div>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:13px">No bugs</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="sap-btn-group">
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="ModuleDetail.editPage('<?= $pg['id'] ?>','<?= $module['id'] ?>','<?= esc($pg['name']) ?>','<?= esc($pg['url_path'] ?? '') ?>','<?= esc($pg['description'] ?? '') ?>')" title="Edit page"><i class="fas fa-pencil-alt"></i></button>
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="ModuleDetail.deletePage('<?= $pg['id'] ?>')" title="Delete page"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<!-- Page Modal -->
<div class="modal fade sap-modal" id="pageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageModalTitle">Add Page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="pageForm">
                <input type="hidden" name="module_id" id="pageModuleId" value="<?= $module['id'] ?>">
                <input type="hidden" name="edit_id" id="pageEditId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="sap-label">Page Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="pageName" class="sap-input" required placeholder="e.g., Login Page">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">URL Path</label>
                        <input type="text" name="url_path" id="pageUrl" class="sap-input" placeholder="e.g., /auth/login">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" id="pageDesc" class="sap-input" rows="2"></textarea>
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

<!-- Bug List Modal -->
<div class="modal fade sap-modal" id="bugListModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bugs for <span id="bugPageName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="bugListBody">
                <div class="text-center p-4"><span class="sap-spinner"></span></div>
            </div>
        </div>
    </div>
</div>

<!-- Module Modal (edit only) -->
<div class="modal fade sap-modal" id="moduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Module</h5>
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
    'moduleId'  => $module['id'] ?? '',
    'pageIds'   => array_column($module['pages'] ?? [], 'id'),
]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/master-projects/module_detail.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
