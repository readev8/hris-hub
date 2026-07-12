<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Approval Center</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Review and approve pending requests</p>
    </div>
</div>

<div class="sap-tabs" id="approvalTabs">
    <a class="sap-tab active" href="#" data-type="tickets">
        <i class="fas fa-ticket-alt"></i>Pending Tickets
    </a>
    <a class="sap-tab" href="#" data-type="improvements">
        <i class="fas fa-rocket"></i>Pending Improvements
    </a>
</div>

<div id="approvalContent">
    <div class="sap-card">
        <div class="sap-card-body p-0">
            <table id="approval-table" class="sap-table mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title / Name</th>
                        <th>Status</th>
                        <th>Creator</th>
                        <th>Created</th>
                        <th style="width:100px">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
#approval-table_filter { display: none; }
.column-search { width: 100%; padding: 4px 6px; border: 1px solid var(--sap-border); border-radius: var(--sap-radius); font-size: 12px; background: var(--sap-bg); color: var(--sap-text); }
.column-search:focus { outline: none; border-color: var(--sap-brand); }
.dt-buttons > .btn { background: var(--sap-secondary-bg); border: 1px solid var(--sap-border); color: var(--sap-text); font-size: 13px; padding: 4px 12px; margin-right: 4px; }
.dt-buttons > .btn:hover { background: var(--sap-brand-hover); border-color: var(--sap-brand); }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function sapBadge(name) {
    var clsMap = {'Open':'open','Approved':'approved','In Progress':'in-progress','Resolved':'resolved','Closed':'closed','Rejected':'rejected',
        'Draft':'draft','Pending IT Approval':'pending','Pending Dept Approval':'pending'};
    var cls = clsMap[name] || 'closed';
    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

var currentType = 'tickets';
var table;

function loadApprovals(type) {
    currentType = type;
    if (table) { table.destroy(); table = null; }

    table = $('#approval-table').DataTable({
        processing: true,
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({ header: function(row) { return 'Details'; }}),
                renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'sap-table mb-0' })
            }
        },
        ajax: {
            url: site_url + '/approvals/ajax-list',
            data: { type: type },
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                render: function(d) {
                    return d ? '<span class="text-muted mono">' + d.slice(0,8) + '..</span>' : '-';
                }
            },
            {
                data: type === 'tickets' ? 'title' : 'name',
                render: function(d) { return d ? '<span class="fw-medium">' + d + '</span>' : '-'; }
            },
            { data: 'status_name', render: function(d) { return sapBadge(d); } },
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
                render: function(d) { return d ? '<span class="text-muted" style="font-size:13px">' + d + '</span>' : '-'; }
            },
            {
                data: 'id',
                orderable: false,
                render: function(d) {
                    var prefix = currentType === 'tickets' ? 'tickets' : 'improvements';
                    return d ? '<a href="' + site_url + '/' + prefix + '/' + d + '" class="sap-btn sap-btn-secondary sap-btn-sm" onclick="event.stopPropagation();"><i class="fas fa-eye"></i></a>' : '-';
                }
            }
        ],
        order: [[4, 'desc']],
        language: {
            emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-check-circle"></i><h4>No pending items</h4><p>All caught up!</p></div>'
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

    $('#approval-table thead tr').clone(true).appendTo('#approval-table thead');
    $('#approval-table thead tr:last th').each(function(i) {
        if (i === 5) {
            $(this).html('');
            return;
        }
        $(this).html('<input type="text" class="column-search" placeholder="Search ' + $('#approval-table thead tr:first th:eq(' + i + ')').text() + '..." data-col="' + i + '">');
    });

    $('#approval-table').off('keyup change', '.column-search');
    $('#approval-table').off('click', 'tbody tr');

    $('#approval-table').on('keyup change', '.column-search', function() {
        table.column($(this).data('col')).search(this.value).draw();
    });

    $('#approval-table tbody').on('click', 'tr', function() {
        var data = table.row(this).data();
        if (data && data.id) {
            var prefix = currentType === 'tickets' ? 'tickets' : 'improvements';
            window.location.href = site_url + '/' + prefix + '/' + data.id;
        }
    });
}

$(function() {
    loadApprovals('tickets');

    $('#approvalTabs a').on('click', function(e) {
        e.preventDefault();
        $('#approvalTabs a').removeClass('active');
        $(this).addClass('active');
        loadApprovals($(this).data('type'));
    });
});
</script>
<?= $this->endSection() ?>
