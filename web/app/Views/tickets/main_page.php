<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Tickets</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Manage bug reports, issues, tasks, and change requests</p>
    </div>
    <?php if (has_permission('tickets', 'can_create')): ?>
    <a href="<?= site_url('tickets/create') ?>" class="sap-btn sap-btn-primary">
        <i class="fas fa-plus"></i> New Ticket
    </a>
    <?php endif; ?>
</div>

<div class="sap-card">
    <div class="sap-card-body p-0">
        <table id="tickets-table" class="sap-table mb-0" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Tracking</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Creator</th>
                    <th>Assignee</th>
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
#tickets-table tbody tr { cursor: pointer; transition: background var(--sap-transition); }
#tickets-table tbody tr:hover { background: var(--sap-brand-hover); }
#tickets-table tbody tr.row-status-open td:nth-child(3) .sap-badge { background: var(--sap-open-bg); color: var(--sap-open); }
#tickets-table tbody tr.row-status-closed td:nth-child(3) .sap-badge { background: var(--sap-closed-bg); color: var(--sap-closed); }
#tickets-table tbody tr.row-status-resolved td:nth-child(3) .sap-badge { background: var(--sap-resolved-bg); color: var(--sap-resolved); }
#tickets-table tbody tr.row-status-rejected td:nth-child(3) .sap-badge { background: var(--sap-rejected-bg); color: var(--sap-rejected); }
#tickets-table_filter { display: none; }
.column-search { width: 100%; padding: 4px 6px; border: 1px solid var(--sap-border); border-radius: var(--sap-radius); font-size: 12px; background: var(--sap-bg); color: var(--sap-text); }
.column-search:focus { outline: none; border-color: var(--sap-brand); }

</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var statusMap = {
    0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed', 5: 'Rejected'
};
var priorityMap = {
    0: 'Low', 1: 'Medium', 2: 'High', 3: 'Critical'
};

function sapBadge(name) {
    var clsMap = {'Open':'open','Approved':'approved','In Progress':'in-progress','Resolved':'resolved','Closed':'closed','Rejected':'rejected'};
    var cls = clsMap[name] || 'closed';
    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

function priorityDot(name) {
    var cls = name.toLowerCase();
    return '<span class="priority-dot ' + cls + '" title="' + name + '"></span>';
}

$(function() {
    var table = $('#tickets-table').DataTable({
        processing: true,
        serverSide: false,
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({ header: function(row) { return 'Details: ' + row.data().title; }}),
                renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'sap-table mb-0' })
            }
        },
        ajax: {
            url: site_url + '/tickets/ajax-list',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                render: function(d) {
                    return '<span class="text-muted mono">' + (d ? d.slice(0,8) : '') + '..</span>';
                }
            },
            {
                data: null,
                render: function(d) {
                    return '<div class="d-flex align-items-center gap-2"><span class="fw-medium" style="color:var(--sap-text)">' + d.title + '</span></div>';
                }
            },
            {
                data: 'tracking_code',
                orderable: false,
                render: function(d) {
                    if (!d) return '<span class="text-muted">-</span>';
                    return '<span class="d-inline-flex align-items-center gap-1" style="cursor:pointer" onclick="event.stopPropagation();showTrackingModal(\'' + d + '\')" title="Click to view & copy">' +
                        '<code style="font-size:12px;background:var(--sap-background);padding:1px 6px;border-radius:3px;font-family:monospace">' + d + '</code>' +
                        '<i class="fas fa-copy" style="font-size:10px;color:var(--sap-text-muted);opacity:0.6"></i>' +
                        '</span>';
                }
            },
            {
                data: 'status_name',
                render: function(d) { return sapBadge(d); }
            },
            { data: 'type_name' },
            {
                data: null,
                render: function(d) {
                    return '<span class="d-flex align-items-center gap-1">' + priorityDot(d.priority_name) + '<span class="text-secondary">' + d.priority_name + '</span></span>';
                }
            },
            {
                data: 'creator_name',
                defaultContent: '-',
                render: function(d) {
                    if (!d) return '-';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:var(--sap-brand);color:#fff">' + d.charAt(0).toUpperCase() + '</div><span>' + d + '</span></div>';
                }
            },
            {
                data: 'assignee_name',
                defaultContent: '-',
                render: function(d) {
                    if (!d) return '<span class="text-muted">-</span>';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:var(--sap-text-muted);color:#fff">' + d.charAt(0).toUpperCase() + '</div><span>' + d + '</span></div>';
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
                    return '<a href="' + site_url + '/tickets/' + d + '" class="sap-btn sap-btn-secondary sap-btn-sm" onclick="event.stopPropagation();"><i class="fas fa-eye"></i></a>';
                }
            }
        ],
        order: [[8, 'desc']],
        language: {
            search: '<i class="fas fa-search"></i>',
            searchPlaceholder: 'Search tickets...',
            emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-ticket-alt"></i><h4>No tickets found</h4><p>Create a new ticket to get started.</p></div>'
        },
        dom: '<"row mb-3"<"col-sm-12"B>>rt<"row mt-3"<"col-sm-4"l><"col-sm-4"i><"col-sm-4"p>>',
        buttons: [
            { extend: 'copy', text: '<i class="fas fa-copy"></i> Copy', className: 'btn-sm' },
            { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn-sm' },
            { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-sm' },
            { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-sm' },
            { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn-sm' },
            { extend: 'colvis', text: '<i class="fas fa-columns"></i> Columns', className: 'btn-sm' },
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

    $('#tickets-table thead tr').clone(true).appendTo('#tickets-table thead');
    $('#tickets-table thead tr:last th').each(function(i) {
        if (i === 9) {
            $(this).html('');
            return;
        }
        $(this).html('<input type="text" class="column-search" placeholder="Search ' + $('#tickets-table thead tr:first th:eq(' + i + ')').text() + '..." data-col="' + i + '">');
    });

    $('#tickets-table').on('keyup change', '.column-search', function() {
        table.column($(this).data('col')).search(this.value).draw();
    });

    $('#tickets-table tbody').on('click', 'tr', function() {
        var data = table.row(this).data();
        if (data && data.id) {
            window.location.href = site_url + '/tickets/' + data.id;
        }
    });
});
</script>

<div id="trackingModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);align-items:center;justify-content:center">
    <div class="sap-card" style="width:400px;max-width:90vw">
        <div class="sap-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-ticket-alt" style="color:var(--sap-brand)"></i> Ticket Tracking Code</span>
            <button onclick="closeTrackingModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--sap-text-muted)">&times;</button>
        </div>
        <div class="sap-card-body text-center">
            <p style="font-size:13px;color:var(--sap-text-secondary);margin-bottom:16px">Share this code to let others track ticket status</p>
            <div style="background:var(--sap-background);border-radius:8px;padding:16px;margin-bottom:16px">
                <code id="trackingCodeDisplay" style="font-size:20px;font-weight:600;letter-spacing:0.05em;font-family:'SF Mono',Monaco,Consolas,monospace;color:var(--sap-brand)"></code>
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <button class="sap-btn sap-btn-primary" onclick="copyTrackingCode()"><i class="fas fa-copy"></i> Copy Code</button>
                <button class="sap-btn sap-btn-secondary" onclick="copyTrackingLink()"><i class="fas fa-link"></i> Copy Link</button>
            </div>
        </div>
    </div>
</div>

<script>
var currentTrackingCode = '';

function showTrackingModal(code) {
    currentTrackingCode = code;
    $('#trackingCodeDisplay').text(code);
    $('#trackingModal').css('display', 'flex');
}

function closeTrackingModal() {
    $('#trackingModal').css('display', 'none');
    currentTrackingCode = '';
}

function copyTrackingCode() {
    navigator.clipboard.writeText(currentTrackingCode).then(function() {
        toastr.success('Tracking code copied!');
    });
}

function copyTrackingLink() {
    var link = site_url + '/track/' + currentTrackingCode;
    navigator.clipboard.writeText(link).then(function() {
        toastr.success('Tracking link copied!');
    });
}

$('#trackingModal').on('click', function(e) {
    if (e.target === this) closeTrackingModal();
});

$(document).on('keydown', function(e) {
    if (e.key === 'Escape') closeTrackingModal();
});
</script>
<?= $this->endSection() ?>
