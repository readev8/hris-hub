<?= $this->extend('template/index') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/master-projects/page_specs.css') ?>?v=<?= config('App')->assetVersion ?>">
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/master-projects/module_detail.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('master-projects') ?>">Master Projects</a>
        <span class="sep">/</span>
        <a href="<?= site_url('master-projects/' . $project['id']) ?>"><?= esc($project['name']) ?></a>
        <span class="sep">/</span>
        <a href="<?= site_url('master-projects/' . $project['id'] . '/modules/' . $module['id']) ?>"><?= esc($module['name']) ?></a>
        <span class="sep">/</span>
        <span class="active"><?= esc($page['name']) ?></span>
    </div>

    <div class="page-specs-header">
        <div class="page-specs-header-top">
            <div class="page-specs-title-row">
                <span class="page-icon-wrapper"><i class="fas fa-file-alt"></i></span>
                <h1 class="page-specs-title"><?= esc($page['name']) ?></h1>
                <?php if (!empty($page['blueprint_design_page_id'])): ?>
                <span class="page-specs-linked-badge"><i class="fas fa-link"></i> <?= esc($page['blueprint_design_page_title']) ?></span>
                <?php endif; ?>
            </div>
            <a href="<?= site_url('master-projects/' . $project['id'] . '/modules/' . $module['id']) ?>" class="sap-btn sap-btn-secondary sap-btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Module
            </a>
        </div>
        <?php if (!empty($page['url_path'])): ?>
        <p class="page-specs-url"><code class="mono table-code"><?= esc($page['url_path']) ?></code></p>
        <?php endif; ?>
        <?php if (!empty($page['description'])): ?>
        <p class="page-specs-desc"><?= esc($page['description']) ?></p>
        <?php endif; ?>
    </div>

    <div class="sap-tabs" id="pageDetailTabs">
        <button class="sap-tab active" data-tab="specs" onclick="PageSpecs.switchTab('specs')">
            <i class="fas fa-list-alt"></i> Page Specifications
            <span class="sap-badge closed" style="margin-left:6px"><?= count($page['blueprint_page_specs'] ?? []) ?></span>
        </button>
        <button class="sap-tab" data-tab="kanban" onclick="PageSpecs.switchTab('kanban')">
            <i class="fas fa-columns"></i> Kanban
        </button>
    </div>

    <div class="tab-pane-container">
        <div id="tab-specs" class="tab-content active">
            <?php if (!empty($page['blueprint_design_page_id'])): ?>
            <div class="page-specs-linked-bar">
                <div class="page-specs-linked-info">
                    <span class="page-blueprint-icon"><i class="fas fa-link"></i></span>
                    <span>Linked to <strong><?= esc($page['blueprint_design_page_title']) ?></strong></span>
                    <span class="page-specs-spec-count"><?= count($page['blueprint_page_specs'] ?? []) ?> spec fields</span>
                </div>
                <button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="PageSpecs.unlinkDesignPage()" title="Unlink design page">
                    <i class="fas fa-unlink"></i> Unlink
                </button>
            </div>

            <?php if (!empty($page['blueprint_page_specs'])): ?>
            <div class="spec-card-grid">
                <?php foreach ($page['blueprint_page_specs'] as $spec): ?>
                <div class="spec-card">
                    <div class="spec-card-header">
                        <span class="spec-card-field-name"><i class="fas fa-database"></i> <?= esc($spec['field_name'] ?? '-') ?></span>
                    </div>
                    <div class="spec-card-body">
                        <div class="spec-card-row">
                            <span class="spec-card-label">Datatype</span>
                            <span class="spec-card-value"><?= esc($spec['datatype'] ?? '-') ?></span>
                        </div>
                        <div class="spec-card-row">
                            <span class="spec-card-label">Control</span>
                            <span class="spec-card-value"><?= esc($spec['control_type'] ?? '-') ?></span>
                        </div>
                        <div class="spec-card-row">
                            <span class="spec-card-label">Validation</span>
                            <span class="spec-card-value"><?= esc($spec['validation'] ?? '-') ?></span>
                        </div>
                        <div class="spec-card-row">
                            <span class="spec-card-label">Initial Data</span>
                            <span class="spec-card-value"><?= esc($spec['initial_data'] ?? '-') ?></span>
                        </div>
                        <div class="spec-card-row">
                            <span class="spec-card-label">Input Display</span>
                            <span class="spec-card-value"><?= esc($spec['input_display'] ?? '-') ?></span>
                        </div>
                        <div class="spec-card-row">
                            <span class="spec-card-label">Objective</span>
                            <span class="spec-card-value"><?= esc($spec['objective'] ?? '-') ?></span>
                        </div>
                        <div class="spec-card-row">
                            <span class="spec-card-label">Data</span>
                            <span class="spec-card-value"><?= esc($spec['data'] ?? '-') ?></span>
                        </div>
                        <div class="spec-card-row">
                            <span class="spec-card-label">Condition</span>
                            <span class="spec-card-value"><?= esc($spec['condition'] ?? '-') ?></span>
                        </div>
                        <div class="spec-card-row">
                            <span class="spec-card-label">UX</span>
                            <span class="spec-card-value"><?= esc($spec['ux'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="sap-empty" style="padding:32px 20px">
                <i class="fas fa-list-alt"></i>
                <h4>No spec fields</h4>
                <p>The linked design page has no specification fields defined yet.</p>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="page-specs-empty">
                <div class="page-specs-empty-icon">
                    <i class="fas fa-unlink"></i>
                </div>
                <h4>No specifications linked</h4>
                <p>This page is not linked to a blueprint design page yet.</p>
                <button class="sap-btn sap-btn-primary" onclick="PageSpecs.openAssignDesignPageModal()">
                    <i class="fas fa-link"></i> Assign Design Page
                </button>
            </div>
            <?php endif; ?>
        </div>

        <div id="tab-kanban" class="tab-content" style="display:none">
            <div class="kanban-filter-bar">
                <div class="kanban-filter-group">
                    <label class="kanban-filter-label"><i class="fas fa-tag"></i> Type:</label>
                    <div class="kanban-filter-type-group" id="kanbanTypeFilter">
                        <button class="kanban-filter-type-btn active" data-type="">All</button>
                        <button class="kanban-filter-type-btn" data-type="0"><span class="kanban-filter-dot bug"></span>Bug</button>
                        <button class="kanban-filter-type-btn" data-type="1"><span class="kanban-filter-dot issue"></span>Issue</button>
                        <button class="kanban-filter-type-btn" data-type="2"><span class="kanban-filter-dot task"></span>Task</button>
                        <button class="kanban-filter-type-btn" data-type="3"><span class="kanban-filter-dot change-request"></span>CR</button>
                    </div>
                </div>
            </div>
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

<!-- Design Page Modal -->
<div class="modal fade sap-modal" id="designPageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-link me-2"></i>Assign Blueprint Design Page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="designPagesLoading" class="text-center p-4">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--sap-brand)"></i>
                    <p class="mt-2 mb-0 text-secondary" style="font-size:13px">Loading design pages...</p>
                </div>
                <div id="designPagesEmpty" class="text-center p-4" style="display:none">
                    <i class="fas fa-inbox" style="font-size:36px;color:var(--sap-text-muted)"></i>
                    <h5 class="mt-2">No design pages found</h5>
                    <p class="mb-0 text-secondary" style="font-size:13px">Create design pages in a blueprint module first.</p>
                </div>
                <div id="designPagesList" style="max-height:60vh;overflow-y:auto"></div>
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
    'projectId'  => $project['id'] ?? '',
    'moduleId'   => $module['id'] ?? '',
    'pageId'     => $page['id'] ?? '',
    'pageName'   => $page['name'] ?? '',
    'moduleUrl'  => site_url('master-projects/' . ($project['id'] ?? '') . '/modules/' . ($module['id'] ?? '')),
]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/master-projects/page_specs.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
