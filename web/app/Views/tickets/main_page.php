<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Tickets</h1>
            <p class="text-meta mb-0" style="font-size:13px">Manage bug reports, issues, and tasks</p>
        </div>
        <a href="<?= site_url('tickets/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Ticket
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table id="tickets-table" class="table mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
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
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var statusMap = {
    0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed', 5: 'Rejected'
};
var priorityMap = {
    0: 'Low', 1: 'Medium', 2: 'High', 3: 'Critical'
};

function statusBadge(name) {
    var clsMap = {'Open':'open','Approved':'approved','In Progress':'in-progress','Resolved':'resolved','Closed':'closed','Rejected':'rejected'};
    var cls = clsMap[name] || 'closed';
    return '<span class="status-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

function priorityDot(name) {
    var cls = name.toLowerCase();
    return '<span class="priority-dot ' + cls + '" title="' + name + '"></span>';
}

$(function() {
    $('#tickets-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: site_url + '/tickets/ajax-list',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                render: function(d) {
                    return '<span class="text-meta" style="font-family:monospace;font-size:12px">' + d.slice(0,8) + '..</span>';
                }
            },
            {
                data: null,
                render: function(d) {
                    return '<div class="d-flex align-items-center gap-2"><span class="fw-medium" style="color:var(--text)">' + d.title + '</span></div>';
                }
            },
            {
                data: 'status_name',
                render: function(d) { return statusBadge(d); }
            },
            { data: 'type_name' },
            {
                data: null,
                render: function(d) {
                    return '<span class="d-flex align-items-center gap-1">' + priorityDot(d.priority_name) + '<span class="text-meta">' + d.priority_name + '</span></span>';
                }
            },
            {
                data: 'creator_name',
                defaultContent: '-',
                render: function(d) {
                    if (!d) return '-';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:#0F4C81;color:#fff">' + d.charAt(0).toUpperCase() + '</div><span>' + d + '</span></div>';
                }
            },
            {
                data: 'assignee_name',
                defaultContent: '-',
                render: function(d) {
                    if (!d) return '<span class="text-muted">-</span>';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:#64748B;color:#fff">' + d.charAt(0).toUpperCase() + '</div><span>' + d + '</span></div>';
                }
            },
            {
                data: 'created_at',
                render: function(d) { return '<span class="text-meta" style="font-size:13px">' + d + '</span>'; }
            },
            {
                data: 'id',
                orderable: false,
                render: function(d) {
                    return '<a href="' + site_url + '/tickets/' + d + '" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>';
                }
            }
        ],
        order: [[7, 'desc']],
        language: {
            search: '<i class="bi bi-search"></i>',
            searchPlaceholder: 'Search tickets...',
            emptyTable: '<div class="empty-state" style="padding:48px 20px"><i class="bi bi-ticket-perforated"></i><h4>No tickets found</h4><p>Create a new ticket to get started.</p></div>'
        },
        dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>',
    });
});
</script>
<?= $this->endSection() ?>
