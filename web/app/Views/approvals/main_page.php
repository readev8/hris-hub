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
                    return d ? '<a href="' + site_url + '/' + prefix + '/' + d + '" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-eye"></i></a>' : '-';
                }
            }
        ],
        order: [[4, 'desc']],
        language: {
            emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-check-circle"></i><h4>No pending items</h4><p>All caught up!</p></div>'
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
