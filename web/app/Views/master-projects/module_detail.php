<?php
/**
 * ============================================================================
 * Master Projects - Module Detail
 * ============================================================================
 *
 * Description: Module detail view with pages, kanban board, and spec coverage
 *
 * Required: $project, $module
 * Optional: none
 * Template: template/index
 */
?>
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
        <button class="sap-tab" data-tab="specs" onclick="ModuleDetail.switchDetailTab('specs')">
            <i class="fas fa-list-alt"></i> Specifications
            <?php
            $linkedCount = 0;
            $totalSpecs = 0;
            foreach ($module['pages'] as $pg) {
                if (!empty($pg['blueprint_design_page_id'])) {
                    $linkedCount++;
                    $totalSpecs += count($pg['blueprint_page_specs'] ?? []);
                }
            }
            ?>
            <span class="sap-badge closed" style="margin-left:6px" id="specsTabCount"><?= $totalSpecs ?></span>
        </button>
    </div>

    <div class="tab-pane-container">
        <div id="tab-pages" class="tab-content active">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0" style="font-size:15px;font-weight:600"><i class="fas fa-file-alt me-1"></i> Pages</h5>
                <div class="d-flex gap-2">
                    <button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="ModuleDetail.showImportModal()">
                        <i class="fas fa-file-import"></i> Import Design Pages
                    </button>
                    <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="ModuleDetail.openPageModal()">
                        <i class="fas fa-plus"></i> Add Page
                    </button>
                </div>
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
                            <th style="width:20%">URL Path</th>
                            <th style="width:20%">Blueprint Design Page</th>
                            <th>Bugs</th>
                            <th style="width:150px;text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($module['pages'] as $pg): ?>
                        <tr class="page-clickable-row" data-page-id="<?= $pg['id'] ?>" onclick="ModuleDetail.navigateToPage('<?= $pg['id'] ?>')" style="cursor:pointer">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="page-icon-wrapper"><i class="fas fa-file-alt"></i></span>
                                    <span class="fw-medium"><?= esc($pg['name']) ?></span>
                                </div>
                            </td>
                            <td><code class="mono table-code"><?= esc($pg['url_path'] ?? '-') ?></code></td>
                            <td>
                                <?php if (!empty($pg['blueprint_design_page_title'])): ?>
                                <div class="page-blueprint-badge">
                                    <span class="page-blueprint-icon"><i class="fas fa-link"></i></span>
                                    <span class="page-blueprint-label"><?= esc($pg['blueprint_design_page_title']) ?></span>
                                    <span class="page-blueprint-spec-count"><?= count($pg['blueprint_page_specs'] ?? []) ?> specs</span>
                                </div>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:12px">Not linked</span>
                                <?php endif; ?>
                            </td>
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
                                    <?php if (!empty($pg['blueprint_design_page_id'])): ?>
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="event.stopPropagation();ModuleDetail.unassignBlueprintDesignPage('<?= $pg['id'] ?>')" title="Unlink design page"><i class="fas fa-unlink"></i></button>
                                    <?php else: ?>
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="event.stopPropagation();ModuleDetail.openAssignDesignPageModal('<?= $pg['id'] ?>')" title="Assign design page"><i class="fas fa-link"></i></button>
                                    <?php endif; ?>
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="event.stopPropagation();ModuleDetail.editPage('<?= $pg['id'] ?>','<?= $module['id'] ?>','<?= esc($pg['name']) ?>','<?= esc($pg['url_path'] ?? '') ?>','<?= esc($pg['description'] ?? '') ?>')" title="Edit page"><i class="fas fa-pencil-alt"></i></button>
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="event.stopPropagation();ModuleDetail.deletePage('<?= $pg['id'] ?>')" title="Delete page"><i class="fas fa-trash-alt"></i></button>
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
            <div class="kanban-filter-bar">
                <div class="kanban-filter-group">
                    <label class="kanban-filter-label"><i class="fas fa-file-alt"></i> Page:</label>
                    <select id="kanbanPageFilter" class="sap-input kanban-filter-select">
                        <option value="">All Pages</option>
                    </select>
                </div>
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
                <div class="kanban-filter-group kanban-filter-reset-wrap" id="kanbanFilterResetWrap" style="display:none">
                    <button class="sap-btn sap-btn-ghost sap-btn-xs" id="kanbanFilterReset" onclick="ModuleDetail.resetKanbanFilters()">
                        <i class="fas fa-times-circle"></i> Reset Filters
                    </button>
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

        <div id="tab-specs" class="tab-content" style="display:none">
            <div class="specs-coverage-banner">
                <div class="specs-coverage-stats">
                    <div class="specs-coverage-stat">
                        <span class="specs-coverage-number"><?= $linkedCount ?></span>
                        <span class="specs-coverage-label">Pages Linked</span>
                    </div>
                    <div class="specs-coverage-divider"></div>
                    <div class="specs-coverage-stat">
                        <span class="specs-coverage-number"><?= count($module['pages']) ?></span>
                        <span class="specs-coverage-label">Total Pages</span>
                    </div>
                    <div class="specs-coverage-divider"></div>
                    <div class="specs-coverage-stat">
                        <span class="specs-coverage-number"><?= $totalSpecs ?></span>
                        <span class="specs-coverage-label">Spec Fields</span>
                    </div>
                    <div class="specs-coverage-divider"></div>
                    <div class="specs-coverage-stat">
                        <span class="specs-coverage-number"><?= count($module['pages']) - $linkedCount ?></span>
                        <span class="specs-coverage-label">Unlinked Pages</span>
                    </div>
                </div>
            </div>

            <div id="specsCardsGrid">
                <?php if (empty($module['pages'])): ?>
                <div class="sap-empty" style="padding:32px 20px">
                    <i class="fas fa-list-alt"></i>
                    <h4>No pages yet</h4>
                    <p>Add pages to this module to view specifications.</p>
                </div>
                <?php else: ?>
                <?php foreach ($module['pages'] as $pg): ?>
                <div class="specs-page-card">
                    <div class="specs-page-card-header">
                        <div class="specs-page-card-title">
                            <span class="specs-page-icon"><i class="fas fa-file-alt"></i></span>
                            <span class="specs-page-name"><?= esc($pg['name']) ?></span>
                        </div>
                        <?php if (!empty($pg['blueprint_design_page_id'])): ?>
                        <span class="specs-page-badge linked"><i class="fas fa-link"></i> <?= esc($pg['blueprint_design_page_title']) ?></span>
                        <?php else: ?>
                        <span class="specs-page-badge unlinked"><i class="fas fa-unlink"></i> Not linked</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($pg['blueprint_page_specs'])): ?>
                    <div class="specs-page-card-body">
                        <div class="specs-page-card-subtitle"><?= count($pg['blueprint_page_specs']) ?> spec fields</div>
                        <div class="spec-card-grid">
                            <?php foreach ($pg['blueprint_page_specs'] as $spec): ?>
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
                    </div>
                    <?php else: ?>
                    <div class="specs-page-card-empty">
                        <i class="fas fa-info-circle"></i>
                        No specification fields defined for this design page.
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Ticket Detail Drawer -->
<div class="ticket-drawer-scrim" id="ticketDrawerScrim"></div>
<aside class="ticket-drawer" id="ticketDrawer">
    <div class="ticket-drawer-header">
        <div class="ticket-drawer-header-left">
            <span class="ticket-drawer-id" id="drawerTicketId"></span>
            <span class="ticket-drawer-title" id="drawerTicketTitle"></span>
        </div>
        <button class="ticket-drawer-close" id="ticketDrawerClose" title="Close (Esc)">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="ticket-drawer-body" id="ticketDrawerBody">
        <div class="ticket-drawer-skeleton">
            <div class="skeleton-line skeleton-lg"></div>
            <div class="skeleton-line skeleton-sm"></div>
            <div class="skeleton-line skeleton-md"></div>
            <div class="skeleton-line skeleton-sm"></div>
            <div class="skeleton-line skeleton-lg"></div>
            <div class="skeleton-line skeleton-md"></div>
        </div>
    </div>
    <div class="ticket-drawer-footer">
        <a href="#" class="sap-btn sap-btn-secondary sap-btn-sm" id="drawerOpenFullPage" target="_blank">
            <i class="fas fa-external-link-alt"></i> Open Full Page
        </a>
    </div>
</aside>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= $this->include('master-projects/_modal_add_page') ?>
<?= $this->include('master-projects/_modal_bug_list') ?>
<?= $this->include('master-projects/_modal_edit_module') ?>
<?= $this->include('master-projects/_modal_assign_design_page') ?>
<?= $this->include('master-projects/_modal_import_design_pages') ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- MOVE to page JS file -->
<script>window.PageData = <?= json_encode([
    'projectId' => $project['id'] ?? '',
    'moduleId'  => $module['id'] ?? '',
    'pageIds'   => array_column($module['pages'] ?? [], 'id'),
    'pages'     => $module['pages'] ?? [],
], JSON_HEX_TAG | JSON_HEX_APOS) ?>;</script>
<script src="<?= base_url('public/assets/js/page/master-projects/module_detail.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
