/**
 * ============================================================================
 * Roles Main Page
 * ============================================================================
 *
 * Role management with DataTable, modal create/edit, toggle, and delete.
 *
 * Dependencies: jQuery, Bootstrap, Toastr, SweetAlert2
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const RoleMainPage = {

    // ===========================
    // STATE
    // ===========================

    _modalInstance: null,

    // ===========================
    // HELPERS
    // ===========================

    escHtml: function (s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    // ===========================
    // MODAL
    // ===========================

    _getModal: function () {
        if (!RoleMainPage._modalInstance) {
            RoleMainPage._modalInstance = new bootstrap.Modal(document.getElementById('roleModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return RoleMainPage._modalInstance;
    },

    openRoleModal: function () {
        $('#roleEditId').val('');
        $('#roleName').val('');
        $('#roleSlug').val('');
        $('#roleDesc').val('');
        $('#roleSlug').prop('readonly', false);
        $('#roleModalTitle').text('Add Role');
        RoleMainPage._getModal().show();
    },

    editRole: function (id, name, desc) {
        $('#roleEditId').val(id);
        $('#roleName').val(name);
        $('#roleSlug').prop('readonly', true);
        $('#roleDesc').val(desc);
        $('#roleModalTitle').text('Edit Role');
        RoleMainPage._getModal().show();
    },

    // ===========================
    // DATA LOADING
    // ===========================

    loadRoles: function () {
        $.ajax({
            url: site_url + '/roles/ajax-list',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                RoleMainPage._renderTable(res.data || []);
            },
            error: function (xhr) {
                toastr.error('Failed to load roles (HTTP ' + xhr.status + ')');
            }
        });
    },

    _renderTable: function (data) {
        var self = RoleMainPage;
        var html = '';
        for (var i = 0; i < data.length; i++) {
            var r = data[i];
            var statusBadge = r.is_active
                ? '<span class="sap-badge approved"><span class="badge-dot"></span>Active</span>'
                : '<span class="sap-badge closed"><span class="badge-dot"></span>Inactive</span>';
            var systemBadge = r.is_system ? ' <span class="sap-badge info" style="font-size:10px">System</span>' : '';

            html += '<tr>';
            html += '<td class="fw-medium">' + self.escHtml(r.name) + systemBadge + '</td>';
            html += '<td><code class="mono" style="font-size:12px">' + self.escHtml(r.slug) + '</code></td>';
            html += '<td style="font-size:13px;color:var(--sap-text-secondary)">' + self.escHtml(r.description || '-') + '</td>';
            html += '<td style="text-align:center">' + (r.user_count || 0) + '</td>';
            html += '<td>' + statusBadge + '</td>';
            html += '<td>';
            html += '<a href="' + site_url + '/roles/' + r.id + '/permissions" class="sap-btn sap-btn-secondary sap-btn-sm" title="Permissions"><i class="fas fa-key"></i></a> ';
            if (!r.is_system) {
                html += '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="RoleMainPage.editRole(\'' + r.id + '\',\'' + self.escHtml(r.name) + '\',\'' + self.escHtml(r.description || '') + '\')" title="Edit"><i class="fas fa-pencil-alt"></i></button> ';
                html += '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="RoleMainPage.toggleRole(\'' + r.id + '\')" title="Toggle">' + (r.is_active ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>') + '</button> ';
                html += '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="RoleMainPage.deleteRole(\'' + r.id + '\')" title="Delete"><i class="fas fa-trash-alt"></i></button>';
            }
            html += '</td></tr>';
        }
        $('#rolesTable tbody').html(html || '<tr><td colspan="6" class="text-center text-muted" style="padding:24px">No roles found</td></tr>');
    },

    // ===========================
    // ACTIONS
    // ===========================

    deleteRole: function (id) {
        Swal.fire({
            title: 'Delete this role?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
            confirmButtonText: 'Delete',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.post(site_url + '/roles/' + id + '/delete', function (res) {
                    if (res.status) { toastr.success('Role deleted'); RoleMainPage.loadRoles(); }
                    else { toastr.error(res.data && res.data.message ? res.data.message : 'Failed'); }
                }).fail(function (xhr) {
                    toastr.error('Failed to delete role (HTTP ' + xhr.status + ')');
                });
            }
        });
    },

    toggleRole: function (id) {
        $.post(site_url + '/roles/' + id + '/toggle', function (res) {
            if (res.status) { toastr.success(res.data && res.data.message ? res.data.message : 'Updated'); RoleMainPage.loadRoles(); }
            else { toastr.error(res.data && res.data.message ? res.data.message : 'Failed'); }
        }).fail(function (xhr) {
            toastr.error('Failed to toggle role (HTTP ' + xhr.status + ')');
        });
    },

    // ===========================
    // EVENTS
    // ===========================

    init: function () {
        var self = RoleMainPage;
        self.loadRoles();

        $('#roleForm').on('submit', function (e) {
            e.preventDefault();
            var editId = $('#roleEditId').val();
            var url = editId
                ? site_url + '/roles/' + editId + '/update'
                : site_url + '/roles/create';
            var data = $(this).serialize();
            $.post(url, data, function (res) {
                if (res.status) {
                    toastr.success(editId ? 'Role updated' : 'Role created');
                    bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
                    self.loadRoles();
                } else {
                    toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
                }
            }).fail(function (xhr) {
                toastr.error('Failed to save role (HTTP ' + xhr.status + ')');
            });
        });
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    RoleMainPage.init();
});

// ===========================
// EXPORTS
// ===========================

window.RoleMainPage = RoleMainPage;

// Backward compatibility for onclick handlers in views
window.openRoleModal = function () { RoleMainPage.openRoleModal(); };
window.editRole = function (id, name, desc) { RoleMainPage.editRole(id, name, desc); };
window.toggleRole = function (id) { RoleMainPage.toggleRole(id); };
window.deleteRole = function (id) { RoleMainPage.deleteRole(id); };
