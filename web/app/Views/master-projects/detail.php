<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <?php if (!$project): ?>
        <div class="empty-state">
            <i class="bi bi-exclamation-triangle" style="color:var(--danger)"></i>
            <h4>Project not found</h4>
            <a href="<?= site_url('master-projects') ?>" class="btn btn-outline-secondary mt-3">Back</a>
        </div>
    <?php else: ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('master-projects') ?>">Master Projects</a></li>
            <li class="breadcrumb-item active"><?= esc($project['name']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1"><?= esc($project['name']) ?></h1>
            <p class="text-meta mb-0" style="font-size:13px">
                <?= esc($project['description']) ?>
                <?php if ($project['creator_name']): ?> &middot; Created by <?= esc($project['creator_name']) ?><?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <span class="status-badge <?= $project['status'] === 1 ? 'approved' : 'closed' ?>">
                <span class="badge-dot"></span><?= esc($project['status_name']) ?>
            </span>
        </div>
    </div>

    <div class="row g-3" id="moduleContainer">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-puzzle me-1"></i> Modules & Pages</h5>
                <button class="btn btn-sm btn-primary" onclick="openModuleModal()">
                    <i class="bi bi-plus-lg"></i> Add Module
                </button>
            </div>

            <div id="modulesList">
                <?php if (empty($project['modules'])): ?>
                <div class="empty-state" style="padding:32px 20px">
                    <i class="bi bi-puzzle"></i>
                    <h4>No modules yet</h4>
                    <p>Add modules to organize your project pages.</p>
                </div>
                <?php else: ?>
                <div class="accordion" id="moduleAccordion">
                    <?php foreach ($project['modules'] as $mi => $mod): ?>
                    <div class="accordion-item mb-2" style="border:1px solid var(--border);border-radius:var(--radius-sm)">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mod-<?= $mi ?>">
                                <span class="fw-medium"><?= esc($mod['name']) ?></span>
                                <span class="badge bg-secondary ms-2" style="font-size:11px"><?= count($mod['pages']) ?> pages</span>
                            </button>
                        </h2>
                        <div id="mod-<?= $mi ?>" class="accordion-collapse collapse" data-bs-parent="#moduleAccordion">
                            <div class="accordion-body p-0">
                                <?php if (empty($mod['pages'])): ?>
                                <div class="empty-state" style="padding:20px">
                                    <p class="mb-0 text-meta">No pages in this module</p>
                                </div>
                                <?php else: ?>
                                <table class="table mb-0">
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
                                            <td><code style="font-size:12px"><?= esc($pg['url_path'] ?? '-') ?></code></td>
                                            <td>
                                                <?php $totalBugs = (int) ($pg['bug_total'] ?? 0); ?>
                                                <?php if ($totalBugs > 0): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="status-badge danger"><span class="badge-dot"></span><?= $pg['bug_open'] ?> open</span>
                                                    <span class="status-badge approved" style="font-size:12px"><?= $pg['bug_resolved'] ?> resolved</span>
                                                    <button class="btn btn-sm btn-link p-0" onclick="showBugList('<?= $pg['id'] ?>', '<?= esc($pg['name']) ?>')">
                                                        <i class="bi bi-list"></i>
                                                    </button>
                                                </div>
                                                <?php else: ?>
                                                <span class="text-meta" style="font-size:13px">No bugs</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="openPageModal('<?= $mod['id'] ?>')"><i class="bi bi-plus"></i> Page</button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deletePage('<?= $pg['id'] ?>')"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php endif; ?>
                                <div class="p-2" style="border-top:1px solid var(--border);background:#F8FAFC">
                                    <button class="btn btn-sm btn-outline-primary" onclick="openPageModal('<?= $mod['id'] ?>')">
                                        <i class="bi bi-plus"></i> Add Page
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editModule('<?= $mod['id'] ?>', '<?= esc($mod['name']) ?>', '<?= esc(addslashes($mod['description'] ?? '')) ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteModule('<?= $mod['id'] ?>')">
                                        <i class="bi bi-trash"></i>
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
    </div>
    <?php endif; ?>
</div>

<!-- Module Modal -->
<div class="modal fade" id="moduleModal" tabindex="-1">
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
                        <label class="form-label">Module Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="moduleName" class="form-control" required placeholder="e.g., Authentication">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="moduleDesc" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Page Modal -->
<div class="modal fade" id="pageModal" tabindex="-1">
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
                        <label class="form-label">Page Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="pageName" class="form-control" required placeholder="e.g., Login Page">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Path</label>
                        <input type="text" name="url_path" id="pageUrl" class="form-control" placeholder="e.g., /auth/login">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="pageDesc" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bug List Modal -->
<div class="modal fade" id="bugListModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bugs for <span id="bugPageName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="bugListBody">
                <div class="text-center p-4"><span class="spinner-border spinner-border-sm"></span></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var projectId = '<?= $project['id'] ?? '' ?>';

// --- HTML helpers ---
function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) {
    return String(s || '').replace(/'/g,"\\'").replace(/"/g,'&quot;');
}

// --- Render & refresh ---
function renderModules(modules) {
    if (!modules || !modules.length) {
        $('#modulesList').html('<div class="empty-state" style="padding:32px 20px"><i class="bi bi-puzzle"></i><h4>No modules yet</h4><p>Add modules to organize your project pages.</p></div>');
        return;
    }
    var html = '<div class="accordion" id="moduleAccordion">';
    for (var i = 0; i < modules.length; i++) {
        var m = modules[i];
        var pages = m.pages || [];
        html += '<div class="accordion-item mb-2" style="border:1px solid var(--border);border-radius:var(--radius-sm)">';
        html += '<h2 class="accordion-header">';
        html += '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mod-' + i + '">';
        html += '<span class="fw-medium">' + escHtml(m.name) + '</span>';
        html += '<span class="badge bg-secondary ms-2" style="font-size:11px">' + pages.length + ' pages</span>';
        html += '</button></h2>';
        html += '<div id="mod-' + i + '" class="accordion-collapse collapse" data-bs-parent="#moduleAccordion">';
        html += '<div class="accordion-body p-0">';
        if (!pages.length) {
            html += '<div class="empty-state" style="padding:20px"><p class="mb-0 text-meta">No pages in this module</p></div>';
        } else {
            html += '<table class="table mb-0"><thead><tr><th>Page Name</th><th>URL Path</th><th>Bugs</th><th style="width:140px">Action</th></tr></thead><tbody>';
            for (var j = 0; j < pages.length; j++) {
                var p = pages[j];
                html += '<tr>';
                html += '<td class="fw-medium">' + escHtml(p.name) + '</td>';
                html += '<td><code style="font-size:12px">' + escHtml(p.url_path || '-') + '</code></td>';
                html += '<td>';
                if (p.bug_total > 0) {
                    html += '<div class="d-flex align-items-center gap-2">';
                    html += '<span class="status-badge danger"><span class="badge-dot"></span>' + p.bug_open + ' open</span>';
                    html += '<span class="status-badge approved" style="font-size:12px">' + p.bug_resolved + ' resolved</span>';
                    html += '<button class="btn btn-sm btn-link p-0" onclick="showBugList(\'' + p.id + '\',\'' + escAttr(p.name) + '\')"><i class="bi bi-list"></i></button>';
                    html += '</div>';
                } else {
                    html += '<span class="text-meta" style="font-size:13px">No bugs</span>';
                }
                html += '</td>';
                html += '<td>';
                html += '<button class="btn btn-sm btn-outline-primary" onclick="openPageModal(\'' + m.id + '\')"><i class="bi bi-plus"></i> Page</button>';
                html += '<button class="btn btn-sm btn-outline-danger" onclick="deletePage(\'' + p.id + '\')"><i class="bi bi-trash"></i></button>';
                html += '</td></tr>';
            }
            html += '</tbody></table>';
        }
        html += '<div class="p-2" style="border-top:1px solid var(--border);background:#F8FAFC">';
        html += '<button class="btn btn-sm btn-outline-primary" onclick="openPageModal(\'' + m.id + '\')"><i class="bi bi-plus"></i> Add Page</button>';
        html += '<button class="btn btn-sm btn-outline-secondary" onclick="editModule(\'' + m.id + '\',\'' + escAttr(m.name) + '\',\'' + escAttr(m.description || '') + '\')"><i class="bi bi-pencil"></i></button>';
        html += '<button class="btn btn-sm btn-outline-danger" onclick="deleteModule(\'' + m.id + '\')"><i class="bi bi-trash"></i></button>';
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

// --- Module CRUD ---
function openModuleModal() {
    $('#moduleEditId').val('');
    $('#moduleName').val('');
    $('#moduleDesc').val('');
    $('#moduleModalTitle').text('Add Module');
    new bootstrap.Modal(document.getElementById('moduleModal')).show();
}

function editModule(id, name, desc) {
    $('#moduleEditId').val(id);
    $('#moduleName').val(name);
    $('#moduleDesc').val(desc);
    $('#moduleModalTitle').text('Edit Module');
    new bootstrap.Modal(document.getElementById('moduleModal')).show();
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
            refreshModules();
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
        confirmButtonColor: '#E11D48',
        confirmButtonText: 'Delete',
    }).then(function(r) {
        if (r.isConfirmed) {
            $.post(site_url + '/modules/' + id + '/delete', function(res) {
                if (res.status) { toastr.success('Module deleted'); refreshModules(); }
                else { toastr.error(res.data.message || 'Failed'); }
            });
        }
    });
}

// --- Page CRUD ---
function openPageModal(moduleId) {
    $('#pageModuleId').val(moduleId);
    $('#pageEditId').val('');
    $('#pageName').val('');
    $('#pageUrl').val('');
    $('#pageDesc').val('');
    $('#pageModalTitle').text('Add Page');
    new bootstrap.Modal(document.getElementById('pageModal')).show();
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
            refreshModules();
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
        confirmButtonColor: '#E11D48',
        confirmButtonText: 'Delete',
    }).then(function(r) {
        if (r.isConfirmed) {
            $.post(site_url + '/pages/' + id + '/delete', function(res) {
                if (res.status) { toastr.success('Page deleted'); refreshModules(); }
                else { toastr.error(res.data.message || 'Failed'); }
            });
        }
    });
}

// --- Bug List ---
function showBugList(pageId, pageName) {
    $('#bugPageName').text(pageName);
    var modal = new bootstrap.Modal(document.getElementById('bugListModal'));
    $('#bugListBody').html('<div class="text-center p-4"><span class="spinner-border spinner-border-sm"></span></div>');
    modal.show();

    $.get(site_url + '/pages/' + pageId + '/bugs', function(res) {
        if (!res || !res.length) {
            $('#bugListBody').html('<div class="empty-state" style="padding:32px"><i class="bi bi-check2-circle"></i><h4>No bugs</h4><p>No bugs reported for this page.</p></div>');
            return;
        }
        var html = '<table class="table mb-0"><thead><tr><th>Title</th><th>Status</th><th>Priority</th><th>Created</th></tr></thead><tbody>';
        for (var i = 0; i < res.length; i++) {
            var b = res[i];
            var statusCls = b.status_name === 'Open' ? 'status-badge open' : b.status_name === 'Resolved' ? 'status-badge resolved' : 'status-badge closed';
            var priorityDot = 'priority-dot ' + (b.priority_name ? b.priority_name.toLowerCase() : 'medium');
            html += '<tr><td><a href="' + site_url + '/tickets/' + b.id + '" target="_blank" class="fw-medium">' + b.title + '</a></td>'
                 + '<td><span class="' + statusCls + '"><span class="badge-dot"></span>' + b.status_name + '</span></td>'
                 + '<td><span class="' + priorityDot + '"></span> ' + b.priority_name + '</td>'
                 + '<td><span class="text-meta" style="font-size:13px">' + b.created_at + '</span></td></tr>';
        }
        html += '</tbody></table>';
        $('#bugListBody').html(html);
    });
}
</script>
<?= $this->endSection() ?>
