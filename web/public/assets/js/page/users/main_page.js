/**
 * ============================================================================
 * Users Main Page
 * ============================================================================
 *
 * User management with DataTable, detail/edit modals, toggle, and delete.
 *
 * Dependencies: jQuery, Bootstrap, Toastr, SweetAlert2, DataTables
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const UserMainPage = {

    // ===========================
    // STATE
    // ===========================

    _rolesMap: [],

    // ===========================
    // PUBLIC API (called from onclick in view)
    // ===========================

    showDetail: function (encId) {
        $.ajax({
            url: site_url + '/users/ajax-detail',
            method: 'GET',
            data: { id: encId },
            success: function (res) {
                if (res.status && res.data) {
                    var d = res.data;
                    var initial = d.full_name ? d.full_name.charAt(0).toUpperCase() : '?';
                    $('#detailAvatar').text(initial);
                    $('#detailName').text(d.full_name || '-');
                    $('#detailUserId').text(d.user_id || '-');
                    $('#detailEmail').text(d.email || '-');
                    $('#detailCreated').text(d.created_at || '-');

                    var clsMap = { 'Developer': 'developer', 'Requester': 'requester', 'Dept Head': 'dept-head', 'IT Manager': 'it-manager', 'Admin': 'admin' };
                    var cls = clsMap[d.role_name] || 'closed';
                    $('#detailRole').attr('class', 'sap-badge ' + cls).html('<span class="badge-dot"></span>' + (d.role_name || '-'));

                    var statusBadge = d.is_active == 1
                        ? '<span class="sap-badge approved"><span class="badge-dot"></span>Active</span>'
                        : '<span class="sap-badge rejected"><span class="badge-dot"></span>Inactive</span>';
                    $('#detailStatus').html(statusBadge);

                    var modal = new bootstrap.Modal(document.getElementById('detailModal'));
                    modal.show();
                }
            },
            error: function () { toastr.error('Failed to load user detail'); }
        });
    },

    showEdit: function (encId) {
        $.ajax({
            url: site_url + '/users/ajax-detail',
            method: 'GET',
            data: { id: encId },
            success: function (res) {
                if (res.status && res.data) {
                    var d = res.data;
                    $('#editId').val(d.id);
                    $('#editUserId').val(d.user_id || '-');
                    $('#editFullName').val(d.full_name);
                    $('#editEmail').val(d.email);
                    $('#editRoleId').val(d.role_id);
                    var modal = new bootstrap.Modal(document.getElementById('editModal'));
                    modal.show();
                }
            },
            error: function () { toastr.error('Failed to load user data'); }
        });
    },

    saveEdit: function () {
        var id = $('#editId').val();
        var fullName = $('#editFullName').val().trim();
        var email = $('#editEmail').val().trim();
        var roleId = $('#editRoleId').val();

        if (!fullName) { toastr.warning('Nama wajib diisi'); return; }
        if (!email) { toastr.warning('Email wajib diisi'); return; }
        if (!roleId) { toastr.warning('Pilih role'); return; }

        var $btn = $('#btnSaveEdit');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: site_url + '/users/ajax-update',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: id, full_name: fullName, email: email, role_id: roleId }),
            success: function (res) {
                if (res.status) {
                    toastr.success('User updated');
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    $('#users-table').DataTable().ajax.reload();
                } else {
                    toastr.error(res.message || 'Failed to update user');
                }
            },
            error: function () { toastr.error('Failed to contact server'); },
            complete: function () { $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Simpan'); }
        });
    },

    toggleActive: function (encId) {
        Swal.fire({
            title: 'Toggle Status?',
            text: 'Are you sure you want to toggle this user status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0070F2',
            confirmButtonText: 'Yes, Toggle',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: site_url + '/users/ajax-toggle',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: encId }),
                    success: function (res) {
                        if (res.status) {
                            toastr.success(res.message);
                            $('#users-table').DataTable().ajax.reload();
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function () { toastr.error('Failed to toggle status'); }
                });
            }
        });
    },

    deleteUser: function (encId, name) {
        Swal.fire({
            title: 'Delete User?',
            html: 'Are you sure you want to delete <strong>' + name + '</strong>?<br><small class="text-secondary">This action cannot be undone.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ee5448',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: site_url + '/users/ajax-delete',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: encId }),
                    success: function (res) {
                        if (res.status) {
                            toastr.success(res.message);
                            $('#users-table').DataTable().ajax.reload();
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function () { toastr.error('Failed to delete user'); }
                });
            }
        });
    },

    // ===========================
    // DATA LOADING
    // ===========================

    init: function () {
        var self = UserMainPage;
        var pageData = window.PageData || {};
        self._rolesMap = pageData.rolesMap || [];

        var table = $('#users-table').DataTable({
            processing: true,
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({ header: function () { return 'User Details'; } }),
                    renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'sap-table mb-0' })
                }
            },
            ajax: {
                url: site_url + '/users/ajax-list',
                dataSrc: 'data'
            },
            columns: [
                {
                    data: null,
                    render: function (d) {
                        var initial = d.full_name ? d.full_name.charAt(0).toUpperCase() : '?';
                        return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:var(--sap-brand);color:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600">' + initial + '</div><span class="fw-medium">' + (d.full_name || '-') + '</span></div>';
                    }
                },
                { data: 'user_id', render: function (d) { return '<code style="font-size:12px;background:var(--sap-surface);padding:2px 6px;border-radius:4px">' + (d || '-') + '</code>'; } },
                { data: 'email', render: function (d) { return '<span class="text-secondary">' + (d || '-') + '</span>'; } },
                {
                    data: 'role_name',
                    render: function (d) {
                        var clsMap = { 'Developer': 'developer', 'Requester': 'requester', 'Dept Head': 'dept-head', 'IT Manager': 'it-manager', 'Admin': 'admin' };
                        var cls = clsMap[d] || 'closed';
                        return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + (d || '-') + '</span>';
                    }
                },
                {
                    data: 'is_active',
                    render: function (d) {
                        return d == 1
                            ? '<span class="sap-badge approved"><span class="badge-dot"></span>Active</span>'
                            : '<span class="sap-badge rejected"><span class="badge-dot"></span>Inactive</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (d) {
                        var actions = '';
                        actions += '<button class="action-btn view me-1" onclick="UserMainPage.showDetail(\'' + d.id + '\')" title="Detail"><i class="fas fa-eye"></i></button>';
                        actions += '<button class="action-btn me-1" onclick="UserMainPage.showEdit(\'' + d.id + '\')" title="Edit"><i class="fas fa-pen"></i></button>';
                        if (d.is_active == 1) {
                            actions += '<button class="action-btn danger me-1" onclick="UserMainPage.toggleActive(\'' + d.id + '\')" title="Deactivate"><i class="fas fa-ban"></i></button>';
                        } else {
                            actions += '<button class="action-btn success me-1" onclick="UserMainPage.toggleActive(\'' + d.id + '\')" title="Activate"><i class="fas fa-check"></i></button>';
                        }
                        actions += '<button class="action-btn danger" onclick="UserMainPage.deleteUser(\'' + d.id + '\', \'' + (d.full_name || '').replace(/'/g, "\\'") + '\')" title="Delete"><i class="fas fa-trash"></i></button>';
                        return actions;
                    }
                }
            ],
            order: [[0, 'asc']],
            language: {
                emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-users"></i><h4>No users found</h4></div>'
            },
            dom: '<"row mb-3"<"col-sm-12"B>>rt<"row mt-3"<"col-sm-4"l><"col-sm-4"i><"col-sm-4"p>>',
            buttons: [
                { extend: 'copy', text: '<i class="fas fa-copy"></i> Copy', className: 'btn-sm' },
                { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn-sm' },
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-sm' },
                { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-sm' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn-sm' },
                { extend: 'colvis', text: '<i class="fas fa-columns"></i> Columns', className: 'btn-sm' },
            ]
        });

        $('#users-table thead tr').clone(true).appendTo('#users-table thead');
        $('#users-table thead tr:last th').each(function (i) {
            $(this).html('<input type="text" class="column-search" placeholder="Search..." data-col="' + i + '">');
        });

        $('#users-table').on('keyup change', '.column-search', function () {
            table.column($(this).data('col')).search(this.value).draw();
        });
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    UserMainPage.init();
});

// ===========================
// EXPORTS
// ===========================

window.UserMainPage = UserMainPage;

// Backward compatibility for onclick handlers in views
window.showDetail = function (encId) { UserMainPage.showDetail(encId); };
window.showEdit = function (encId) { UserMainPage.showEdit(encId); };
window.saveEdit = function () { UserMainPage.saveEdit(); };
window.toggleActive = function (encId) { UserMainPage.toggleActive(encId); };
window.deleteUser = function (encId, name) { UserMainPage.deleteUser(encId, name); };
