<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('dashboard') ?>">Dashboard</a>
        <span class="sep">/</span>
        <span class="active">Roles & Permissions</span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1">Roles & Permissions</h1>
            <p class="text-secondary mb-0" style="font-size:13px">Manage user roles and their access permissions</p>
        </div>
        <button class="sap-btn sap-btn-primary" onclick="openRoleModal()">
            <i class="fas fa-plus"></i> Add Role
        </button>
    </div>

    <div class="sap-card">
        <div class="sap-card-body p-0">
            <table class="sap-table mb-0" id="rolesTable" style="width:100%">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th style="width:180px">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Role Modal -->
<div class="modal fade sap-modal" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalTitle">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="roleForm">
                <input type="hidden" name="edit_id" id="roleEditId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="sap-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="roleName" class="sap-input" required placeholder="e.g., QA Engineer">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="roleSlug" class="sap-input" required placeholder="e.g., qa_engineer" pattern="[a-z0-9_-]+">
                        <span class="sap-hint">Lowercase letters, numbers, dashes, underscores only</span>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" id="roleDesc" class="sap-input" rows="2"></textarea>
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
var roleDataTable = null;

var roleModalInstance = null;
function getRoleModal() {
    if (!roleModalInstance) {
        roleModalInstance = new bootstrap.Modal(document.getElementById('roleModal'), {
            backdrop: 'static',
            keyboard: false
        });
    }
    return roleModalInstance;
}

function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function loadRoles() {
    $.ajax({
        url: site_url + '/roles/ajax-list',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            renderRolesTable(res.data || []);
        }
    });
}

function renderRolesTable(data) {
    var html = '';
    for (var i = 0; i < data.length; i++) {
        var r = data[i];
        var statusBadge = r.is_active
            ? '<span class="sap-badge approved"><span class="badge-dot"></span>Active</span>'
            : '<span class="sap-badge closed"><span class="badge-dot"></span>Inactive</span>';
        var systemBadge = r.is_system ? ' <span class="sap-badge info" style="font-size:10px">System</span>' : '';

        html += '<tr>';
        html += '<td class="fw-medium">' + escHtml(r.name) + systemBadge + '</td>';
        html += '<td><code class="mono" style="font-size:12px">' + escHtml(r.slug) + '</code></td>';
        html += '<td style="font-size:13px;color:var(--sap-text-secondary)">' + escHtml(r.description || '-') + '</td>';
        html += '<td style="text-align:center">' + (r.user_count || 0) + '</td>';
        html += '<td>' + statusBadge + '</td>';
        html += '<td>';
        html += '<a href="' + site_url + '/roles/' + r.id + '/permissions" class="sap-btn sap-btn-secondary sap-btn-sm" title="Permissions"><i class="fas fa-key"></i></a> ';
        if (!r.is_system) {
            html += '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="editRole(\'' + r.id + '\',\'' + escHtml(r.name) + '\',\'' + escHtml(r.description || '') + '\')" title="Edit"><i class="fas fa-pencil-alt"></i></button> ';
            html += '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="toggleRole(\'' + r.id + '\')" title="Toggle">' + (r.is_active ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>') + '</button> ';
            html += '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deleteRole(\'' + r.id + '\')" title="Delete"><i class="fas fa-trash-alt"></i></button>';
        }
        html += '</td></tr>';
    }
    $('#rolesTable tbody').html(html || '<tr><td colspan="6" class="text-center text-muted" style="padding:24px">No roles found</td></tr>');
}

function openRoleModal() {
    $('#roleEditId').val('');
    $('#roleName').val('');
    $('#roleSlug').val('');
    $('#roleDesc').val('');
    $('#roleSlug').prop('readonly', false);
    $('#roleModalTitle').text('Add Role');
    getRoleModal().show();
}

function editRole(id, name, desc) {
    $('#roleEditId').val(id);
    $('#roleName').val(name);
    $('#roleSlug').prop('readonly', true);
    $('#roleDesc').val(desc);
    $('#roleModalTitle').text('Edit Role');
    getRoleModal().show();
}

$('#roleForm').on('submit', function(e) {
    e.preventDefault();
    var editId = $('#roleEditId').val();
    var url = editId
        ? site_url + '/roles/' + editId + '/update'
        : site_url + '/roles/create';
    var data = $(this).serialize();
    $.post(url, data, function(res) {
        if (res.status) {
            toastr.success(editId ? 'Role updated' : 'Role created');
            bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
            loadRoles();
        } else {
            toastr.error(res.data?.message || 'Failed');
        }
    });
});

function deleteRole(id) {
    Swal.fire({
        title: 'Delete this role?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
        confirmButtonText: 'Delete',
    }).then(function(r) {
        if (r.isConfirmed) {
            $.post(site_url + '/roles/' + id + '/delete', function(res) {
                if (res.status) { toastr.success('Role deleted'); loadRoles(); }
                else { toastr.error(res.data?.message || 'Failed'); }
            });
        }
    });
}

function toggleRole(id) {
    $.post(site_url + '/roles/' + id + '/toggle', function(res) {
        if (res.status) { toastr.success(res.data?.message || 'Updated'); loadRoles(); }
        else { toastr.error(res.data?.message || 'Failed'); }
    });
}

$(function() {
    loadRoles();
});
</script>
<?= $this->endSection() ?>
