<?= $this->extend('template/index') ?>
<?= $this->section('styles') ?>
<style>
/* Tab pane */
.tab-pane-container { position: relative; }
.tab-content { animation: fadeTabIn 250ms ease; }
@keyframes fadeTabIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

/* Kanban Board */
.kanban-board {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    min-height: 400px;
    align-items: flex-start;
}

.kanban-column {
    background: var(--sap-background);
    border: 1px solid var(--sap-border);
    border-radius: var(--sap-radius-lg);
    overflow: hidden;
    transition: box-shadow var(--sap-transition);
}

.kanban-column.sortable-drag-over {
    box-shadow: 0 0 0 2px var(--sap-brand), var(--sap-shadow-lg);
}

.kanban-column-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 700;
    color: var(--sap-text);
    border-bottom: 1px solid var(--sap-border-light);
    background: var(--sap-surface);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.kanban-status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.kanban-status-dot.open { background: var(--sap-warning); }
.kanban-status-dot.in_progress { background: var(--sap-info); }
.kanban-status-dot.resolved { background: var(--sap-success); }
.kanban-status-dot.closed { background: #94A3B8; }

.kanban-count {
    margin-left: auto;
    background: var(--sap-border-light);
    color: var(--sap-text-secondary);
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: var(--sap-radius-pill);
    min-width: 24px;
    text-align: center;
}

.kanban-cards {
    padding: 10px;
    min-height: 100px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Kanban Card */
.kanban-card {
    background: var(--sap-surface);
    border: 1px solid var(--sap-border);
    border-radius: var(--sap-radius);
    padding: 12px 14px;
    cursor: grab;
    transition: box-shadow var(--sap-transition), transform var(--sap-transition), border-color var(--sap-transition);
    position: relative;
}
.kanban-card:hover {
    box-shadow: var(--sap-shadow);
    border-color: var(--sap-brand);
}
.kanban-card:active {
    cursor: grabbing;
    box-shadow: var(--sap-shadow-lg);
    transform: rotate(1.5deg) scale(1.02);
}

.kanban-card.sortable-ghost {
    opacity: 0.4;
    border: 2px dashed var(--sap-brand);
}

.kanban-card.sortable-chosen {
    box-shadow: var(--sap-shadow-xl);
    z-index: 10;
}

.kanban-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.kanban-priority-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.kanban-priority-dot.critical { background: var(--sap-critical); box-shadow: 0 0 0 2px rgba(220,38,38,0.2); }
.kanban-priority-dot.high { background: var(--sap-high); }
.kanban-priority-dot.medium { background: var(--sap-medium); }
.kanban-priority-dot.low { background: var(--sap-low); }

.kanban-type-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: var(--sap-radius-pill);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.kanban-type-badge.bug { background: var(--sap-error-bg); color: var(--sap-error); }
.kanban-type-badge.issue { background: var(--sap-warning-bg); color: var(--sap-warning); }
.kanban-type-badge.task { background: var(--sap-info-bg); color: var(--sap-info); }
.kanban-type-badge.change-request { background: var(--sap-success-bg); color: var(--sap-success); }

.kanban-card-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--sap-text);
    line-height: 1.4;
    margin-bottom: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.kanban-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color var(--sap-transition);
}
.kanban-card-title a:hover { color: var(--sap-brand); }

.kanban-card-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: var(--sap-text-muted);
    flex-wrap: wrap;
}

.kanban-card-meta i { font-size: 10px; }

.kanban-card-assignee {
    display: flex;
    align-items: center;
    gap: 4px;
}

.kanban-card-page {
    font-size: 11px;
    color: var(--sap-text-muted);
    background: var(--sap-background);
    padding: 2px 6px;
    border-radius: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

.kanban-empty {
    text-align: center;
    padding: 24px 12px;
    color: var(--sap-text-muted);
    font-size: 13px;
}
.kanban-empty i {
    display: block;
    font-size: 24px;
    margin-bottom: 8px;
    opacity: 0.4;
}

@media (max-width: 1024px) {
    .kanban-board { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .kanban-board { grid-template-columns: 1fr; }
}
</style>
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
        <button class="sap-tab active" data-tab="modules" onclick="switchDetailTab('modules')">
            <i class="fas fa-puzzle-piece"></i> Modules & Pages
        </button>
        <button class="sap-tab" data-tab="kanban" onclick="switchDetailTab('kanban')">
            <i class="fas fa-columns"></i> Kanban Board
        </button>
    </div>

    <div class="tab-pane-container">
        <div id="tab-modules" class="tab-content active">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0" style="font-size:15px;font-weight:600"><i class="fas fa-puzzle-piece me-1"></i> Modules & Pages</h5>
                <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="openModuleModal()">
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
                <div class="accordion" id="moduleAccordion">
                    <?php foreach ($project['modules'] as $mi => $mod): ?>
                    <div class="sap-accordion-item">
                        <h2 class="accordion-header">
                            <button class="sap-accordion-header collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mod-<?= $mi ?>">
                                <span><span class="fw-medium"><?= esc($mod['name']) ?></span>
                                <span class="sap-badge closed" style="font-size:11px;margin-left:8px"><?= count($mod['pages']) ?> pages</span></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </h2>
                        <div id="mod-<?= $mi ?>" class="accordion-collapse collapse" data-bs-parent="#moduleAccordion">
                            <div class="sap-accordion-body">
                                <?php if (empty($mod['pages'])): ?>
                                <div class="sap-empty" style="padding:20px">
                                    <p class="mb-0 text-muted">No pages in this module</p>
                                </div>
                                <?php else: ?>
                                <table class="sap-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Page Name</th>
                                            <th>URL Path</th>
                                            <th>Bugs</th>
                                            <th style="width:140px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mod['pages'] as $pg): ?>
                                        <tr>
                                            <td class="fw-medium"><?= esc($pg['name']) ?></td>
                                            <td><code class="mono" style="font-size:12px"><?= esc($pg['url_path'] ?? '-') ?></code></td>
                                            <td>
                                                <?php $totalBugs = (int) ($pg['bug_total'] ?? 0); ?>
                                                <?php if ($totalBugs > 0): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="sap-badge rejected" style="font-size:12px"><span class="badge-dot"></span><?= $pg['bug_open'] ?> open</span>
                                                    <span class="sap-badge approved" style="font-size:12px"><?= $pg['bug_resolved'] ?> resolved</span>
                                                     <button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="showBugList('<?= $pg['id'] ?>', '<?= esc($pg['name']) ?>')">
                                                        <i class="fas fa-list"></i>
                                                    </button>
                                                </div>
                                                <?php else: ?>
                                                <span class="text-muted" style="font-size:13px">No bugs</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="openPageModal('<?= $mod['id'] ?>')"><i class="fas fa-plus"></i> Page</button>
                                                <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deletePage('<?= $pg['id'] ?>')"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php endif; ?>
                                <div class="p-2" style="border-top:1px solid var(--sap-border-light);background:var(--sap-background)">
                                    <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="openPageModal('<?= $mod['id'] ?>')">
                                        <i class="fas fa-plus"></i> Add Page
                                    </button>
                                    <button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="editModule('<?= $mod['id'] ?>', '<?= esc($mod['name']) ?>', '<?= esc(addslashes($mod['description'] ?? '')) ?>')">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deleteModule('<?= $mod['id'] ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
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

<!-- Page Modal -->
<div class="modal fade sap-modal" id="pageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageModalTitle">Add Page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="pageForm">
                <input type="hidden" name="module_id" id="pageModuleId">
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
</style>
<script>
var projectId = '<?= $project['id'] ?? '' ?>';

/* ── Tab Switching ─────────────────────────────── */
function switchDetailTab(tab) {
    $('#detailTabs .sap-tab').removeClass('active');
    $('#detailTabs .sap-tab[data-tab="' + tab + '"]').addClass('active');
    $('.tab-content').hide();
    $('#tab-' + tab).show();
    if (tab === 'kanban') loadKanban();
}

/* ── Module/Page functions ────────────────────── */
AppEvent.on('module:changed', function() { refreshModules(); });

function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) {
    return String(s || '').replace(/'/g,"\\'").replace(/"/g,'&quot;');
}

function renderModules(modules) {
    if (!modules || !modules.length) {
        $('#modulesList').html('<div class="sap-empty" style="padding:32px 20px"><i class="fas fa-puzzle-piece"></i><h4>No modules yet</h4><p>Add modules to organize your project pages.</p></div>');
        return;
    }
    var html = '<div class="accordion" id="moduleAccordion">';
    for (var i = 0; i < modules.length; i++) {
        var m = modules[i];
        var pages = m.pages || [];
        html += '<div class="sap-accordion-item">';
        html += '<h2 class="accordion-header">';
        html += '<button class="sap-accordion-header collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mod-' + i + '">';
        html += '<span><span class="fw-medium">' + escHtml(m.name) + '</span>';
        html += '<span class="sap-badge closed" style="font-size:11px;margin-left:8px">' + pages.length + ' pages</span></span>';
        html += '<i class="fas fa-chevron-down"></i>';
        html += '</button></h2>';
        html += '<div id="mod-' + i + '" class="accordion-collapse collapse" data-bs-parent="#moduleAccordion">';
        html += '<div class="sap-accordion-body">';
        if (!pages.length) {
            html += '<div class="sap-empty" style="padding:20px"><p class="mb-0 text-muted">No pages in this module</p></div>';
        } else {
            html += '<table class="sap-table mb-0"><thead><tr><th>Page Name</th><th>URL Path</th><th>Bugs</th><th style="width:140px">Action</th></tr></thead><tbody>';
            for (var j = 0; j < pages.length; j++) {
                var p = pages[j];
                html += '<tr>';
                html += '<td class="fw-medium">' + escHtml(p.name) + '</td>';
                html += '<td><code class="mono" style="font-size:12px">' + escHtml(p.url_path || '-') + '</code></td>';
                html += '<td>';
                if (p.bug_total > 0) {
                    html += '<div class="d-flex align-items-center gap-2">';
                    html += '<span class="sap-badge rejected" style="font-size:12px"><span class="badge-dot"></span>' + p.bug_open + ' open</span>';
                    html += '<span class="sap-badge approved" style="font-size:12px">' + p.bug_resolved + ' resolved</span>';
                        html += '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="showBugList(\'' + p.id + '\',\'' + escAttr(p.name) + '\')"><i class="fas fa-list"></i></button>';
                    html += '</div>';
                } else {
                    html += '<span class="text-muted" style="font-size:13px">No bugs</span>';
                }
                html += '</td>';
                html += '<td>';
                html += '<button class="sap-btn sap-btn-primary sap-btn-sm" onclick="openPageModal(\'' + m.id + '\')"><i class="fas fa-plus"></i> Page</button>';
                html += '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deletePage(\'' + p.id + '\')"><i class="fas fa-trash-alt"></i></button>';
                html += '</td></tr>';
            }
            html += '</tbody></table>';
        }
        html += '<div class="p-2" style="border-top:1px solid var(--sap-border-light);background:var(--sap-background)">';
        html += '<button class="sap-btn sap-btn-primary sap-btn-sm" onclick="openPageModal(\'' + m.id + '\')"><i class="fas fa-plus"></i> Add Page</button>';
        html += '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="editModule(\'' + m.id + '\',\'' + escAttr(m.name) + '\',\'' + escAttr(m.description || '') + '\')"><i class="fas fa-pencil-alt"></i></button>';
        html += '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deleteModule(\'' + m.id + '\')"><i class="fas fa-trash-alt"></i></button>';
        html += '</div>';
        html += '</div></div></div>';
    }
    html += '</div>';
    $('#modulesList').html(html);
}

function refreshModules() {
    $.get(site_url + '/master-projects/' + projectId + '/detail-json', function(res) {
        if (res && res.modules) {
            renderModules(res.modules);
        }
    });
}

var moduleModalInstance = null;
function getModuleModal() {
    if (!moduleModalInstance) {
        moduleModalInstance = new bootstrap.Modal(document.getElementById('moduleModal'), {
            backdrop: 'static',
            keyboard: false
        });
    }
    return moduleModalInstance;
}

function openModuleModal() {
    $('#moduleEditId').val('');
    $('#moduleName').val('');
    $('#moduleDesc').val('');
    $('#moduleModalTitle').text('Add Module');
    getModuleModal().show();
}

function editModule(id, name, desc) {
    $('#moduleEditId').val(id);
    $('#moduleName').val(name);
    $('#moduleDesc').val(desc);
    $('#moduleModalTitle').text('Edit Module');
    getModuleModal().show();
}

$('#moduleForm').on('submit', function(e) {
    e.preventDefault();
    var editId = $('#moduleEditId').val();
    var url = editId
        ? site_url + '/modules/' + editId + '/update'
        : site_url + '/master-projects/' + projectId + '/modules';
    var data = $(this).serialize();
    $.post(url, data, function(res) {
        if (res.status) {
            toastr.success(editId ? 'Module updated' : 'Module created');
            bootstrap.Modal.getInstance(document.getElementById('moduleModal')).hide();
            AppEvent.dispatch('module:changed');
        } else {
            toastr.error(res.data.message || 'Failed');
        }
    });
});

function deleteModule(id) {
    Swal.fire({
        title: 'Delete this module?',
        text: 'All pages within will also be deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
        confirmButtonText: 'Delete',
    }).then(function(r) {
        if (r.isConfirmed) {
            $.post(site_url + '/modules/' + id + '/delete', function(res) {
                if (res.status) { toastr.success('Module deleted'); AppEvent.dispatch('module:changed'); }
                else { toastr.error(res.data.message || 'Failed'); }
            });
        }
    });
}

var pageModalInstance = null;
function getPageModal() {
    if (!pageModalInstance) {
        pageModalInstance = new bootstrap.Modal(document.getElementById('pageModal'), {
            backdrop: 'static',
            keyboard: false
        });
    }
    return pageModalInstance;
}

function openPageModal(moduleId) {
    $('#pageModuleId').val(moduleId);
    $('#pageEditId').val('');
    $('#pageName').val('');
    $('#pageUrl').val('');
    $('#pageDesc').val('');
    $('#pageModalTitle').text('Add Page');
    getPageModal().show();
}

$('#pageForm').on('submit', function(e) {
    e.preventDefault();
    var editId = $('#pageEditId').val();
    var moduleId = $('#pageModuleId').val();
    var url = editId
        ? site_url + '/pages/' + editId + '/update'
        : site_url + '/modules/' + moduleId + '/pages';
    var data = $(this).serialize();
    $.post(url, data, function(res) {
        if (res.status) {
            toastr.success(editId ? 'Page updated' : 'Page created');
            bootstrap.Modal.getInstance(document.getElementById('pageModal')).hide();
            AppEvent.dispatch('module:changed');
        } else {
            toastr.error(res.data.message || 'Failed');
        }
    });
});

function deletePage(id) {
    Swal.fire({
        title: 'Delete this page?',
        text: 'Linked bug tickets will remain but page reference will be removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
        confirmButtonText: 'Delete',
    }).then(function(r) {
        if (r.isConfirmed) {
            $.post(site_url + '/pages/' + id + '/delete', function(res) {
                if (res.status) { toastr.success('Page deleted'); AppEvent.dispatch('module:changed'); }
                else { toastr.error(res.data.message || 'Failed'); }
            });
        }
    });
}

var bugListModalInstance = null;
function getBugListModal() {
    if (!bugListModalInstance) {
        bugListModalInstance = new bootstrap.Modal(document.getElementById('bugListModal'), {
            backdrop: 'static',
            keyboard: false
        });
    }
    return bugListModalInstance;
}

function showBugList(pageId, pageName) {
    $('#bugPageName').text(pageName);
    var modal = getBugListModal();
    $('#bugListBody').html('<div class="text-center p-4"><span class="sap-spinner"></span></div>');
    modal.show();
    $.get(site_url + '/pages/' + pageId + '/bugs', function(res) {
        if (!res || !res.length) {
            $('#bugListBody').html('<div class="sap-empty" style="padding:32px"><i class="fas fa-check-circle"></i><h4>No bugs</h4><p>No bugs reported for this page.</p></div>');
            return;
        }
        var html = '<table class="sap-table mb-0"><thead><tr><th>Title</th><th>Status</th><th>Priority</th><th>Created</th></tr></thead><tbody>';
        for (var i = 0; i < res.length; i++) {
            var b = res[i];
            var statusCls = b.status_name === 'Open' ? 'sap-badge open' : b.status_name === 'Resolved' ? 'sap-badge resolved' : 'sap-badge closed';
            var priorityDot = 'priority-dot ' + (b.priority_name ? b.priority_name.toLowerCase() : 'medium');
            html += '<tr><td><a href="' + site_url + '/tickets/' + b.id + '" target="_blank" class="fw-medium" style="color:var(--sap-brand)">' + b.title + '</a></td>'
                 + '<td><span class="' + statusCls + '"><span class="badge-dot"></span>' + b.status_name + '</span></td>'
                 + '<td><span class="' + priorityDot + '"></span> ' + b.priority_name + '</td>'
                 + '<td><span class="text-muted" style="font-size:13px">' + b.created_at + '</span></td></tr>';
        }
        html += '</tbody></table>';
        $('#bugListBody').html(html);
    });
}

$('#moduleModal, #pageModal, #bugListModal').on('hidden.bs.modal', function() {
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
});

/* ── Kanban Board ─────────────────────────────── */
var kanbanData = {};
var kanbanSortables = [];

var KANBAN_STATUS_MAP = { open: 0, in_progress: 2, resolved: 3, closed: 4 };
var KANBAN_STATUS_LABELS = { 0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed' };

function loadKanban() {
    $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-spinner fa-spin"></i>Loading...</div>');
    $.ajax({
        url: site_url + '/master-projects/' + projectId + '/kanban',
        method: 'GET',
        timeout: 15000,
    })
    .done(function(res) {
        kanbanData = res || { open: [], in_progress: [], resolved: [], closed: [] };
        renderKanban();
        initKanbanSortables();
    })
    .fail(function(xhr, status, error) {
        console.error('Kanban load failed:', status, error, xhr.responseText);
        $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-exclamation-triangle"></i>Failed to load kanban. Check console.</div>');
    });
}

function renderKanban() {
    var columns = ['open', 'in_progress', 'resolved', 'closed'];
    for (var c = 0; c < columns.length; c++) {
        var key = columns[c];
        var tickets = kanbanData[key] || [];
        var $col = $('#kanban-col-' + key);
        $('#kanban-count-' + key).text(tickets.length);

        if (!tickets.length) {
            $col.html('<div class="kanban-empty"><i class="fas fa-inbox"></i>No tickets</div>');
            continue;
        }

        var html = '';
        for (var i = 0; i < tickets.length; i++) {
            html += buildKanbanCard(tickets[i]);
        }
        $col.html(html);
    }
}

function buildKanbanCard(t) {
    var typeCls = t.type === 0 ? 'bug' : t.type === 1 ? 'issue' : t.type === 3 ? 'change-request' : 'task';
    var prioCls = t.priority_name ? t.priority_name.toLowerCase() : 'medium';

    var html = '<div class="kanban-card" data-id="' + t.id + '" data-status="' + t.status + '">';
    html += '<div class="kanban-card-header">';
    html += '<span class="kanban-priority-dot ' + prioCls + '" title="' + escHtml(t.priority_name) + '"></span>';
    html += '<span class="kanban-type-badge ' + typeCls + '">' + escHtml(t.type_name) + '</span>';
    html += '</div>';
    html += '<div class="kanban-card-title"><a href="' + site_url + '/tickets/' + t.id + '" target="_blank">' + escHtml(t.title) + '</a></div>';
    html += '<div class="kanban-card-meta">';
    if (t.assignee_name) {
        html += '<span class="kanban-card-assignee"><i class="fas fa-user-check"></i> ' + escHtml(t.assignee_name) + '</span>';
    }
    if (t.page_name) {
        html += '<span class="kanban-card-page" title="' + escHtml(t.page_name) + '"><i class="fas fa-file-alt"></i> ' + escHtml(t.page_name) + '</span>';
    }
    html += '</div>';
    html += '</div>';
    return html;
}

function initKanbanSortables() {
    for (var s = 0; s < kanbanSortables.length; s++) {
        kanbanSortables[s].destroy();
    }
    kanbanSortables = [];

    var columns = ['open', 'in_progress', 'resolved', 'closed'];
    for (var c = 0; c < columns.length; c++) {
        var el = document.getElementById('kanban-col-' + columns[c]);
        if (!el) continue;
        var sortable = new Sortable(el, {
            group: 'kanban',
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
            onStart: function(evt) {
                $(evt.item).css('transition', 'none');
            },
            onEnd: function(evt) {
                $(evt.item).css('transition', '');
                var ticketId = evt.item.getAttribute('data-id');
                var newColumn = evt.to.id.replace('kanban-col-', '');
                var newStatus = KANBAN_STATUS_MAP[newColumn];
                var oldStatus = parseInt(evt.item.getAttribute('data-status'));

                if (newStatus === oldStatus) return;

                var allowed = getAllowedTransitions(oldStatus);
                if (allowed.indexOf(newStatus) === -1) {
                    toastr.warning('Transition tidak diizinkan');
                    renderKanban();
                    initKanbanSortables();
                    return;
                }

                moveTicket(ticketId, newStatus, oldStatus, evt.item);
            }
        });
        kanbanSortables.push(sortable);
    }
}

function getAllowedTransitions(currentStatus) {
    switch (currentStatus) {
        case 0: return [2, 4];
        case 1: return [2];
        case 2: return [3];
        case 3: return [0, 4];
        default: return [];
    }
}

function moveTicket(ticketId, newStatus, oldStatus, cardEl) {
    var $card = $(cardEl);
    var originalBg = $card.css('background');
    $card.css('background', 'var(--sap-brand-hover)').css('opacity', '0.7');

    $.ajax({
        url: site_url + '/tickets/' + ticketId + '/move',
        method: 'POST',
        data: { new_status: newStatus },
        success: function(res) {
            if (res.status) {
                $card.attr('data-status', newStatus);
                $card.css('background', '').css('opacity', '');
                toastr.success('Ticket dipindahkan ke ' + KANBAN_STATUS_LABELS[newStatus]);
            } else {
                $card.css('background', '').css('opacity', '');
                toastr.error(res.data?.message || 'Gagal memindahkan ticket');
                renderKanban();
                initKanbanSortables();
            }
        },
        error: function() {
            $card.css('background', '').css('opacity', '');
            toastr.error('Gagal memindahkan ticket');
            renderKanban();
            initKanbanSortables();
        }
    });
}
</script>
<?= $this->endSection() ?>
