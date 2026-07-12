<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Users</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Manage system users and roles</p>
    </div>
</div>

<div class="sap-card">
    <div class="sap-card-body p-0">
        <table id="users-table" class="sap-table mb-0" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
#users-table_filter { display: none; }
.column-search { width: 100%; padding: 4px 6px; border: 1px solid var(--sap-border); border-radius: var(--sap-radius); font-size: 12px; background: var(--sap-bg); color: var(--sap-text); }
.column-search:focus { outline: none; border-color: var(--sap-brand); }
.dt-buttons > .btn { background: var(--sap-secondary-bg); border: 1px solid var(--sap-border); color: var(--sap-text); font-size: 13px; padding: 4px 12px; margin-right: 4px; }
.dt-buttons > .btn:hover { background: var(--sap-brand-hover); border-color: var(--sap-brand); }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    var table = $('#users-table').DataTable({
        processing: true,
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({ header: function(row) { return 'User Details'; }}),
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
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:var(--sap-brand);color:#fff">' + initial + '</div><span class="fw-medium">' + d.full_name + '</span></div>';
                }
            },
            { data: 'email', render: function(d) { return '<span class="text-secondary">' + d + '</span>'; } },
            {
                data: 'role_name',
                render: function(d) {
                    var clsMap = {'Developer':'developer','Requester':'requester','Dept Head':'dept-head','IT Manager':'it-manager','Admin':'admin'};
                    var cls = clsMap[d] || 'closed';
                    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + d + '</span>';
                }
            },
            {
                data: 'is_active',
                render: function(d) {
                    return d == 1
                        ? '<span class="sap-badge approved"><span class="badge-dot"></span>Active</span>'
                        : '<span class="sap-badge rejected"><span class="badge-dot"></span>Inactive</span>';
                }
            }
        ],
        order: [[0, 'asc']],
        language: {
            emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-users"></i><h4>No users found</h4></div>'
        },
        dom: '<"row mb-3"<"col-sm-4"B><"col-sm-4"l><"col-sm-4"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>',
        buttons: [
            { extend: 'colvis', text: '<i class="fas fa-columns"></i> Columns', className: 'btn-sm' },
            { extend: 'copy', text: '<i class="fas fa-copy"></i> Copy', className: 'btn-sm' },
            { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn-sm' },
            { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-sm' },
            { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-sm' },
            { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn-sm' },
        ]
    });

    $('#users-table thead tr').clone(true).appendTo('#users-table thead');
    $('#users-table thead tr:last th').each(function(i) {
        $(this).html('<input type="text" class="column-search" placeholder="Search ' + $('#users-table thead tr:first th:eq(' + i + ')').text() + '..." data-col="' + i + '">');
    });

    $('#users-table').on('keyup change', '.column-search', function() {
        table.column($(this).data('col')).search(this.value).draw();
    });
});
</script>
<?= $this->endSection() ?>
