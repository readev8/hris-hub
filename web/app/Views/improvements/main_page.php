<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Improvements</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Feature requests and change proposals</p>
    </div>
    <a href="<?= site_url('improvements/create') ?>" class="sap-btn sap-btn-primary">
        <i class="fas fa-plus"></i> New Improvement
    </a>
</div>

<div class="sap-card">
    <div class="sap-card-body p-0">
        <table id="improvements-table" class="sap-table mb-0" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Creator</th>
                    <th>Created</th>
                    <th style="width:80px">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
#improvements-table tbody tr { cursor: pointer; transition: background var(--sap-transition); }
#improvements-table tbody tr:hover { background: var(--sap-brand-hover); }
#improvements-table_filter { display: none; }
.column-search { width: 100%; padding: 4px 6px; border: 1px solid var(--sap-border); border-radius: var(--sap-radius); font-size: 12px; background: var(--sap-bg); color: var(--sap-text); }
.column-search:focus { outline: none; border-color: var(--sap-brand); }
.dt-buttons > .btn { background: var(--sap-secondary-bg); border: 1px solid var(--sap-border); color: var(--sap-text); font-size: 13px; padding: 4px 12px; margin-right: 4px; }
.dt-buttons > .btn:hover { background: var(--sap-brand-hover); border-color: var(--sap-brand); }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function sapBadge(name) {
    var clsMap = {'Draft':'draft','Pending IT Approval':'pending','Pending Dept Approval':'pending','Approved':'approved','Rejected':'rejected'};
    var cls = clsMap[name] || 'closed';
    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

$(function() {
    var table = $('#improvements-table').DataTable({
        processing: true,
        serverSide: false,
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({ header: function(row) { return 'Details: ' + row.data().name; }}),
                renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'sap-table mb-0' })
            }
        },
        ajax: {
            url: site_url + '/improvements/ajax-list',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                render: function(d) {
                    return '<span class="text-muted mono">' + (d ? d.slice(0,8) : '') + '..</span>';
                }
            },
            { data: 'name', render: function(d) { return '<span class="fw-medium">' + d + '</span>'; } },
            { data: 'status_name', render: function(d) { return sapBadge(d); } },
            { data: 'priority_name' },
            {
                data: 'creator_name',
                defaultContent: '-',
                render: function(d) {
                    if (!d) return '-';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:var(--sap-brand);color:#fff">' + d.charAt(0).toUpperCase() + '</div><span>' + d + '</span></div>';
                }
            },
            {
                data: 'created_at',
                render: function(d) { return '<span class="text-muted" style="font-size:13px">' + d + '</span>'; }
            },
            {
                data: 'id',
                orderable: false,
                render: function(d) {
                    return '<a href="' + site_url + '/improvements/' + d + '" class="sap-btn sap-btn-secondary sap-btn-sm" onclick="event.stopPropagation();"><i class="fas fa-eye"></i></a>';
                }
            }
        ],
        order: [[5, 'desc']],
        language: {
            searchPlaceholder: 'Search improvements...',
            emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-rocket"></i><h4>No improvements found</h4><p>Submit a new improvement proposal to get started.</p></div>'
        },
        dom: '<"row mb-3"<"col-sm-4"B><"col-sm-4"l><"col-sm-4"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>',
        buttons: [
            { extend: 'colvis', text: '<i class="fas fa-columns"></i> Columns', className: 'btn-sm' },
            { extend: 'copy', text: '<i class="fas fa-copy"></i> Copy', className: 'btn-sm' },
            { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn-sm' },
            { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-sm' },
            { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-sm' },
            { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn-sm' },
        ],
        drawCallback: function() {
            var api = this.api();
            api.rows().every(function() {
                var status = this.data().status_name;
                if (status) {
                    var cls = status.toLowerCase().replace(/\s+/g, '-');
                    $(this.node()).addClass('row-status-' + cls);
                }
            });
        }
    });

    $('#improvements-table thead tr').clone(true).appendTo('#improvements-table thead');
    $('#improvements-table thead tr:last th').each(function(i) {
        if (i === 6) {
            $(this).html('');
            return;
        }
        $(this).html('<input type="text" class="column-search" placeholder="Search ' + $('#improvements-table thead tr:first th:eq(' + i + ')').text() + '..." data-col="' + i + '">');
    });

    $('#improvements-table').on('keyup change', '.column-search', function() {
        table.column($(this).data('col')).search(this.value).draw();
    });

    $('#improvements-table tbody').on('click', 'tr', function() {
        var data = table.row(this).data();
        if (data && data.id) {
            window.location.href = site_url + '/improvements/' + data.id;
        }
    });
});
</script>
<?= $this->endSection() ?>
