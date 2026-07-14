<?= $this->extend('template/index') ?>
<?= $this->section('styles') ?>
<style>
.tab-pane-container { position: relative; }
.tab-content { animation: fadeTabIn 250ms ease; }
@keyframes fadeTabIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

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
    border-radius: 99px;
}

.kanban-cards {
    padding: 10px;
    min-height: 120px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.kanban-card {
    background: var(--sap-surface);
    border: 1px solid var(--sap-border-light);
    border-radius: var(--sap-radius);
    padding: 12px;
    cursor: grab;
    transition: all var(--sap-transition);
    position: relative;
}
.kanban-card:hover {
    border-color: var(--sap-brand);
    box-shadow: var(--sap-shadow-sm);
    transform: translateY(-1px);
}
.kanban-card:active { cursor: grabbing; }
.kanban-card.sortable-ghost {
    opacity: 0.4;
    border: 2px dashed var(--sap-brand);
    background: var(--sap-brand-light);
}
.kanban-card.sortable-chosen {
    box-shadow: var(--sap-shadow-lg);
    border-color: var(--sap-brand);
}
.kanban-card.sortable-drag {
    opacity: 0.9;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}

.kanban-card-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.kanban-priority-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.kanban-priority-dot.critical { background: #DC2626; }
.kanban-priority-dot.high { background: #F97316; }
.kanban-priority-dot.medium { background: #EAB308; }
.kanban-priority-dot.low { background: #22C55E; }

.kanban-type-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.kanban-type-badge.bug { background: #FEE2E2; color: #991B1B; }
.kanban-type-badge.issue { background: #FEF3C7; color: #92400E; }
.kanban-type-badge.change-request { background: #DBEAFE; color: #1E40AF; }
.kanban-type-badge.task { background: #E0E7FF; color: #3730A3; }

.kanban-card-title {
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 8px;
}
.kanban-card-title a {
    color: var(--sap-text);
    text-decoration: none;
}
.kanban-card-title a:hover {
    color: var(--sap-brand);
}

.kanban-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 11px;
    color: var(--sap-text-secondary);
}
.kanban-card-assignee i,
.kanban-card-page i {
    margin-right: 3px;
}

.kanban-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 16px;
    color: var(--sap-text-tertiary);
    font-size: 13px;
    gap: 8px;
}
.kanban-empty i { font-size: 24px; opacity: 0.5; }

.page-icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: var(--sap-radius);
    background: var(--sap-brand-light);
    color: var(--sap-brand);
    font-size: 12px;
    flex-shrink: 0;
}

.table-code {
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 4px;
    background: var(--sap-surface);
    border: 1px solid var(--sap-border-light);
    color: var(--sap-text-secondary);
}

.module-footer {
    display: flex;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid var(--sap-border-light);
    margin-top: 8px;
}

@media (max-width: 991px) {
    .kanban-board { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 575px) {
    .kanban-board { grid-template-columns: 1fr; }
}
</style>
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
            <button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="editModule('<?= $module['id'] ?>', '<?= esc(addslashes($module['name'])) ?>', '<?= esc(addslashes($module['description'] ?? '')) ?>')">
                <i class="fas fa-pencil-alt"></i> Edit Module
            </button>
            <button class="sap-btn sap-btn-ghost sap-btn-sm sap-btn-danger-ghost" onclick="deleteModule('<?= $module['id'] ?>')">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>

    <div class="sap-tabs" id="detailTabs">
        <button class="sap-tab active" data-tab="pages" onclick="switchDetailTab('pages')">
            <i class="fas fa-file-alt"></i> Pages
            <span class="sap-badge closed" style="margin-left:6px"><?= count($module['pages']) ?></span>
        </button>
        <button class="sap-tab" data-tab="kanban" onclick="switchDetailTab('kanban')">
            <i class="fas fa-columns"></i> Kanban Board
        </button>
    </div>

    <div class="tab-pane-container">
        <div id="tab-pages" class="tab-content active">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0" style="font-size:15px;font-weight:600"><i class="fas fa-file-alt me-1"></i> Pages</h5>
                <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="openPageModal()">
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
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="showBugList('<?= $pg['id'] ?>', '<?= esc($pg['name']) ?>')" title="View bug list">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                </div>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:13px">No bugs</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="sap-btn-group">
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="editPage('<?= $pg['id'] ?>','<?= $module['id'] ?>','<?= esc($pg['name']) ?>','<?= esc($pg['url_path'] ?? '') ?>','<?= esc($pg['description'] ?? '') ?>')" title="Edit page"><i class="fas fa-pencil-alt"></i></button>
                                    <button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="deletePage('<?= $pg['id'] ?>')" title="Delete page"><i class="fas fa-trash-alt"></i></button>
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
<script>
var projectId = '<?= $project['id'] ?? '' ?>';
var moduleId = '<?= $module['id'] ?? '' ?>';

/* ── Tab Switching ─────────────────────────────── */
function switchDetailTab(tab) {
    $('#detailTabs .sap-tab').removeClass('active');
    $('#detailTabs .sap-tab[data-tab="' + tab + '"]').addClass('active');
    $('.tab-content').hide();
    $('#tab-' + tab).show();
    if (tab === 'kanban') loadKanban();
}

/* ── Helpers ────────────────────────────────────── */
function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) {
    return String(s || '').replace(/'/g,"\\'").replace(/"/g,'&quot;');
}

/* ── Page functions ─────────────────────────────── */
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

function editPage(id, moduleIdVal, name, urlPath, description) {
    $('#pageModuleId').val(moduleIdVal);
    $('#pageEditId').val(id);
    $('#pageName').val(name);
    $('#pageUrl').val(urlPath);
    $('#pageDesc').val(description || '');
    $('#pageModalTitle').text('Edit Page');
    getPageModal().show();
}

function openPageModal() {
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
    var url = editId
        ? site_url + '/pages/' + editId + '/update'
        : site_url + '/modules/' + moduleId + '/pages';
    var data = $(this).serialize();
    $.post(url, data, function(res) {
        if (res.status) {
            toastr.success(editId ? 'Page updated' : 'Page created');
            bootstrap.Modal.getInstance(document.getElementById('pageModal')).hide();
            window.location.reload();
        } else {
            toastr.error(res.data?.message || 'Failed');
        }
    }).fail(function(xhr) {
        toastr.error('Gagal menyimpan page (HTTP ' + xhr.status + ')');
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
                if (res.status) {
                    toastr.success('Page deleted');
                    window.location.reload();
                } else {
                    toastr.error(res.data?.message || 'Failed');
                }
            }).fail(function(xhr) {
                toastr.error('Gagal menghapus page (HTTP ' + xhr.status + ')');
            });
        }
    });
}

/* ── Module functions ───────────────────────────── */
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

function editModule(id, name, desc) {
    $('#moduleEditId').val(id);
    $('#moduleName').val(name);
    $('#moduleDesc').val(desc);
    getModuleModal().show();
}

$('#moduleForm').on('submit', function(e) {
    e.preventDefault();
    var editId = $('#moduleEditId').val();
    var url = site_url + '/modules/' + editId + '/update';
    var data = $(this).serialize();
    $.post(url, data, function(res) {
        if (res.status) {
            toastr.success('Module updated');
            bootstrap.Modal.getInstance(document.getElementById('moduleModal')).hide();
            window.location.reload();
        } else {
            toastr.error(res.data?.message || 'Failed');
        }
    }).fail(function(xhr) {
        toastr.error('Gagal menyimpan module (HTTP ' + xhr.status + ')');
    });
});

function deleteModule(id) {
    Swal.fire({
        title: 'Delete this module?',
        text: 'All pages within will also be deleted. You will be redirected to the project page.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
        confirmButtonText: 'Delete',
    }).then(function(r) {
        if (r.isConfirmed) {
            $.post(site_url + '/modules/' + id + '/delete', function(res) {
                if (res.status) {
                    toastr.success('Module deleted');
                    window.location.href = site_url + '/master-projects/' + projectId;
                } else {
                    toastr.error(res.data?.message || 'Failed');
                }
            }).fail(function(xhr) {
                toastr.error('Gagal menghapus module (HTTP ' + xhr.status + ')');
            });
        }
    });
}

/* ── Bug List ───────────────────────────────────── */
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
        var html = '<table class="sap-table sap-table-compact mb-0"><thead><tr><th style="min-width:200px">Title</th><th style="width:120px">Status</th><th style="width:100px">Priority</th><th style="width:130px">Created</th></tr></thead><tbody>';
        for (var i = 0; i < res.length; i++) {
            var b = res[i];
            var statusCls = b.status_name === 'Open' ? 'sap-badge open' : b.status_name === 'Resolved' ? 'sap-badge resolved' : 'sap-badge closed';
            var priorityDot = 'priority-dot ' + (b.priority_name ? b.priority_name.toLowerCase() : 'medium');
            html += '<tr><td><a href="' + site_url + '/tickets/' + b.id + '" target="_blank" class="fw-medium" style="color:var(--sap-brand);text-decoration:none">' + b.title + '</a></td>'
                 + '<td><span class="' + statusCls + '"><span class="badge-dot"></span>' + b.status_name + '</span></td>'
                 + '<td><span class="' + priorityDot + '"></span> ' + b.priority_name + '</td>'
                 + '<td><span class="text-muted">' + b.created_at + '</span></td></tr>';
        }
        html += '</tbody></table>';
        $('#bugListBody').html(html);
    }).fail(function(xhr) {
        $('#bugListBody').html('<div class="sap-empty" style="padding:32px"><i class="fas fa-exclamation-triangle"></i><h4>Gagal memuat data bug</h4><p>HTTP ' + xhr.status + '</p></div>');
        toastr.error('Gagal memuat data bug');
    });
}

$('#pageModal, #bugListModal, #moduleModal').on('hidden.bs.modal', function() {
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
        kanbanData = { open: [], in_progress: [], resolved: [], closed: [] };
        var all = res || {};
        var pageIds = [];
        <?php foreach ($module['pages'] as $pg): ?>
        pageIds.push('<?= $pg['id'] ?>');
        <?php endforeach; ?>
        var keys = ['open', 'in_progress', 'resolved', 'closed'];
        for (var k = 0; k < keys.length; k++) {
            var col = all[keys[k]] || [];
            for (var i = 0; i < col.length; i++) {
                if (pageIds.indexOf(col[i].page_id) !== -1) {
                    kanbanData[keys[k]].push(col[i]);
                }
            }
        }
        renderKanban();
        initKanbanSortables();
    })
    .fail(function(xhr, status, error) {
        toastr.error('Gagal memuat kanban board');
        $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-exclamation-triangle"></i>Failed to load kanban. Please refresh the page.</div>');
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
        error: function(xhr) {
            var res = null;
            try { res = JSON.parse(xhr.responseText); } catch(e) {}
            if (res && res.redirect) {
                window.location.href = res.redirect;
                return;
            }
            $card.css('background', '').css('opacity', '');
            toastr.error('Gagal memindahkan ticket');
            renderKanban();
            initKanbanSortables();
        }
    });
}

$(document).on('dblclick', '.kanban-card', function() {
    var cardId = $(this).data('id');
    var cardTitle = $(this).find('.kanban-card-title a').text();
    var cardUrl = site_url + '/tickets/' + cardId;

    Swal.fire({
        title: 'Buka Ticket?',
        html: '<strong>' + escHtml(cardTitle) + '</strong>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0070F2',
        confirmButtonText: 'Buka di Tab Baru',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(cardUrl, '_blank');
        }
    });
});
</script>
<?= $this->endSection() ?>
