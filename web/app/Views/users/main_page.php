<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Users</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Manage system users and roles</p>
    </div>
    <?php if (has_permission('users', 'can_create')): ?>
    <a href="<?= site_url('/users/add') ?>" class="sap-btn sap-btn-primary">
        <i class="fas fa-plus me-1"></i> Add User
    </a>
    <?php endif; ?>
</div>

<div class="sap-card">
    <div class="sap-card-body p-0">
        <table id="users-table" class="sap-table mb-0" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th style="width:120px">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Detail User Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--sap-card-bg);border:1px solid var(--sap-border);border-radius:16px">
            <div class="modal-header" style="border-bottom:1px solid var(--sap-border);padding:20px 24px">
                <h5 class="modal-title fw-semibold" style="font-size:16px">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:24px">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div id="detailAvatar" class="avatar-circle" style="width:56px;height:56px;font-size:22px;background:var(--sap-brand);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600">?</div>
                    <div>
                        <h5 id="detailName" class="mb-0 fw-semibold" style="font-size:17px">-</h5>
                        <span id="detailRole" class="sap-badge developer" style="font-size:12px"><span class="badge-dot"></span>-</span>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">User ID</small>
                        <span id="detailUserId" class="fw-medium" style="font-size:14px">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">Email</small>
                        <span id="detailEmail" class="fw-medium" style="font-size:14px">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">Status</small>
                        <span id="detailStatus">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">Created At</small>
                        <span id="detailCreated" class="fw-medium" style="font-size:14px">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--sap-border);padding:16px 24px">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--sap-card-bg);border:1px solid var(--sap-border);border-radius:16px">
            <div class="modal-header" style="border-bottom:1px solid var(--sap-border);padding:20px 24px">
                <h5 class="modal-title fw-semibold" style="font-size:16px">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:24px">
                <input type="hidden" id="editId">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">User ID</label>
                        <input type="text" class="form-control" id="editUserId" readonly style="background:var(--sap-surface);opacity:0.7">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="editRoleId">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= esc($r['raw_id']) ?>"><?= esc($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editFullName">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="editEmail">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--sap-border);padding:16px 24px;display:flex;gap:8px;justify-content:flex-end">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="sap-btn sap-btn-primary" id="btnSaveEdit" onclick="saveEdit()">
                    <i class="fas fa-check me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
#users-table_filter { display: none; }
.column-search { width: 100%; padding: 4px 6px; border: 1px solid var(--sap-border); border-radius: var(--sap-radius); font-size: 12px; background: var(--sap-bg); color: var(--sap-text); }
.column-search:focus { outline: none; border-color: var(--sap-brand); }

.action-btn { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--sap-border); background: var(--sap-bg); color: var(--sap-text); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; cursor: pointer; transition: all 150ms ease; }
.action-btn:hover { background: var(--sap-brand-hover); border-color: var(--sap-brand); color: var(--sap-brand); }
.action-btn.danger:hover { background: #fef1f1; border-color: #ee5448; color: #ee5448; }
.action-btn.success:hover { background: #f1fdf6; border-color: #36b37e; color: #36b37e; }
.action-btn.view:hover { background: #f0f5ff; border-color: #0070f2; color: #0070f2; }
.form-control, .form-select { border-radius: var(--sap-radius); border: 1.5px solid var(--sap-border-input); font-size: 14px; padding: 8px 12px; height: 42px; transition: border-color 200ms ease, box-shadow 200ms ease; }
.form-control:focus, .form-select:focus { border-color: var(--sap-brand); box-shadow: 0 0 0 3px rgba(0,112,242,0.12); outline: none; }
.form-control[readonly] { background: var(--sap-surface) !important; opacity: 0.7; }
.form-label { font-size: 13px; font-weight: 600; color: var(--sap-text); margin-bottom: 4px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var rolesMap = <?= json_encode(array_map(fn($r) => ['id' => $r['id'], 'name' => $r['name']], $roles)) ?>;

function showDetail(encId) {
    $.ajax({
        url: site_url + '/users/ajax-detail',
        method: 'GET',
        data: { id: encId },
        success: function(res) {
            if (res.status && res.data) {
                var d = res.data;
                var initial = d.full_name ? d.full_name.charAt(0).toUpperCase() : '?';
                $('#detailAvatar').text(initial);
                $('#detailName').text(d.full_name || '-');
                $('#detailUserId').text(d.user_id || '-');
                $('#detailEmail').text(d.email || '-');
                $('#detailCreated').text(d.created_at || '-');

                var clsMap = {'Developer':'developer','Requester':'requester','Dept Head':'dept-head','IT Manager':'it-manager','Admin':'admin'};
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
        error: function() { toastr.error('Gagal memuat detail user'); }
    });
}

function showEdit(encId) {
    $.ajax({
        url: site_url + '/users/ajax-detail',
        method: 'GET',
        data: { id: encId },
        success: function(res) {
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
        error: function() { toastr.error('Gagal memuat data user'); }
    });
}

function saveEdit() {
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
        success: function(res) {
            if (res.status) {
                toastr.success('User berhasil diperbarui');
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                $('#users-table').DataTable().ajax.reload();
            } else {
                toastr.error(res.message || 'Gagal memperbarui user');
            }
        },
        error: function() { toastr.error('Gagal menghubungi server'); },
        complete: function() { $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Simpan'); }
    });
}

function toggleActive(encId) {
    Swal.fire({
        title: 'Ubah Status?',
        text: 'Apakah Anda yakin ingin mengubah status user ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0070F2',
        confirmButtonText: 'Ya, Ubah',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: site_url + '/users/ajax-toggle',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id: encId }),
                success: function(res) {
                    if (res.status) {
                        toastr.success(res.message);
                        $('#users-table').DataTable().ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function() { toastr.error('Gagal mengubah status'); }
            });
        }
    });
}

function deleteUser(encId, name) {
    Swal.fire({
        title: 'Hapus User?',
        html: 'Apakah Anda yakin ingin menghapus <strong>' + name + '</strong>?<br><small class="text-secondary">Tindakan ini tidak dapat dibatalkan.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ee5448',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: site_url + '/users/ajax-delete',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id: encId }),
                success: function(res) {
                    if (res.status) {
                        toastr.success(res.message);
                        $('#users-table').DataTable().ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function() { toastr.error('Gagal menghapus user'); }
            });
        }
    });
}

$(function() {
    var table = $('#users-table').DataTable({
        processing: true,
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({ header: function() { return 'User Details'; }}),
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
                render: function(d) {
                    var initial = d.full_name ? d.full_name.charAt(0).toUpperCase() : '?';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:var(--sap-brand);color:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600">' + initial + '</div><span class="fw-medium">' + (d.full_name || '-') + '</span></div>';
                }
            },
            { data: 'user_id', render: function(d) { return '<code style="font-size:12px;background:var(--sap-surface);padding:2px 6px;border-radius:4px">' + (d || '-') + '</code>'; } },
            { data: 'email', render: function(d) { return '<span class="text-secondary">' + (d || '-') + '</span>'; } },
            {
                data: 'role_name',
                render: function(d) {
                    var clsMap = {'Developer':'developer','Requester':'requester','Dept Head':'dept-head','IT Manager':'it-manager','Admin':'admin'};
                    var cls = clsMap[d] || 'closed';
                    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + (d || '-') + '</span>';
                }
            },
            {
                data: 'is_active',
                render: function(d) {
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
                render: function(d) {
                    var actions = '';
                    actions += '<button class="action-btn view me-1" onclick="showDetail(\'' + d.id + '\')" title="Detail"><i class="fas fa-eye"></i></button>';
                    actions += '<button class="action-btn me-1" onclick="showEdit(\'' + d.id + '\')" title="Edit"><i class="fas fa-pen"></i></button>';
                    if (d.is_active == 1) {
                        actions += '<button class="action-btn danger me-1" onclick="toggleActive(\'' + d.id + '\')" title="Deactivate"><i class="fas fa-ban"></i></button>';
                    } else {
                        actions += '<button class="action-btn success me-1" onclick="toggleActive(\'' + d.id + '\')" title="Activate"><i class="fas fa-check"></i></button>';
                    }
                    actions += '<button class="action-btn danger" onclick="deleteUser(\'' + d.id + '\', \'' + (d.full_name || '').replace(/'/g, "\\'") + '\')" title="Delete"><i class="fas fa-trash"></i></button>';
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
    $('#users-table thead tr:last th').each(function(i) {
        $(this).html('<input type="text" class="column-search" placeholder="Search..." data-col="' + i + '">');
    });

    $('#users-table').on('keyup change', '.column-search', function() {
        table.column($(this).data('col')).search(this.value).draw();
    });
});
</script>
<?= $this->endSection() ?>
