<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Approval Center</h1>
            <p class="text-meta mb-0" style="font-size:13px">Review and approve pending requests</p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="approvalTabs">
        <li class="nav-item">
            <a class="nav-link active" href="#" data-type="tickets">
                <i class="bi bi-ticket-perforated me-1"></i>Pending Tickets
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-type="improvements">
                <i class="bi bi-rocket-takeoff me-1"></i>Pending Improvements
            </a>
        </li>
    </ul>

    <div id="approvalContent">
        <div class="card">
            <div class="card-body p-0">
                <table id="approval-table" class="table mb-0" style="width:100%">
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
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function statusBadge(name) {
    var clsMap = {'Open':'open','Approved':'approved','In Progress':'in-progress','Resolved':'resolved','Closed':'closed','Rejected':'rejected',
        'Draft':'closed','Pending IT Approval':'pending','Pending Dept Approval':'pending'};
    var cls = clsMap[name] || 'closed';
    return '<span class="status-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

var currentType = 'tickets';
var table;

function loadApprovals(type) {
    currentType = type;
    if (table) { table.destroy(); table = null; }

    table = $('#approval-table').DataTable({
        processing: true,
        ajax: {
            url: site_url + '/approvals/ajax-list',
            data: { type: type },
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                render: function(d) {
                    return d ? '<span class="text-meta" style="font-family:monospace;font-size:12px">' + d.slice(0,8) + '..</span>' : '-';
                }
            },
            {
                data: type === 'tickets' ? 'title' : 'name',
                render: function(d) { return d ? '<span class="fw-medium">' + d + '</span>' : '-'; }
            },
            { data: 'status_name', render: function(d) { return statusBadge(d); } },
            {
                data: 'creator_name',
                defaultContent: '-',
                render: function(d) {
                    if (!d) return '-';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:#0F4C81;color:#fff">' + d.charAt(0).toUpperCase() + '</div><span>' + d + '</span></div>';
                }
            },
            {
                data: 'created_at',
                render: function(d) { return d ? '<span class="text-meta" style="font-size:13px">' + d + '</span>' : '-'; }
            },
            {
                data: 'id',
                orderable: false,
                render: function(d) {
                    var prefix = currentType === 'tickets' ? 'tickets' : 'improvements';
                    return d ? '<a href="' + site_url + '/' + prefix + '/' + d + '" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>' : '-';
                }
            }
        ],
        order: [[4, 'desc']],
        language: {
            emptyTable: '<div class="empty-state" style="padding:48px 20px"><i class="bi bi-check2-circle"></i><h4>No pending items</h4><p>All caught up! No items waiting for approval.</p></div>'
        },
        dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>',
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
