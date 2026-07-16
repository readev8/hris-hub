/**
 * ============================================================================
 * Roles Main Page
 * ============================================================================
 *
 * Description: Role management with DataTable, modal create/edit, toggle, delete
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// HELPERS
// ===========================

function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ===========================
// MODAL
// ===========================

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

// ===========================
// DATA
// ===========================

function loadRoles() {
    $.ajax({
        url: site_url + '/roles/ajax-list',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            renderRolesTable(res.data || []);
        },
        error: function(xhr) {
            toastr.error('Gagal memuat daftar role (HTTP ' + xhr.status + ')');
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

// ===========================
// ACTIONS
// ===========================

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
            }).fail(function(xhr) {
                toastr.error('Gagal menghapus role (HTTP ' + xhr.status + ')');
            });
        }
    });
}

function toggleRole(id) {
    $.post(site_url + '/roles/' + id + '/toggle', function(res) {
        if (res.status) { toastr.success(res.data?.message || 'Updated'); loadRoles(); }
        else { toastr.error(res.data?.message || 'Failed'); }
    }).fail(function(xhr) {
        toastr.error('Gagal mengubah status role (HTTP ' + xhr.status + ')');
    });
}

// ===========================
// INITIALIZATION
// ===========================

$(function() {
    loadRoles();

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
        }).fail(function(xhr) {
            toastr.error('Gagal menyimpan role (HTTP ' + xhr.status + ')');
        });
    });
});
