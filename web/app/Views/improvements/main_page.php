<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Improvements</h1>
            <p class="text-meta mb-0" style="font-size:13px">Feature requests and change proposals</p>
        </div>
        <a href="<?= site_url('improvements/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Improvement
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table id="improvements-table" class="table mb-0" style="width:100%">
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
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function statusBadge(name) {
    var clsMap = {'Draft':'closed','Pending IT Approval':'pending','Pending Dept Approval':'pending','Approved':'approved','Rejected':'rejected'};
    var cls = clsMap[name] || 'closed';
    return '<span class="status-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

$(function() {
    $('#improvements-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: site_url + '/improvements/ajax-list',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                render: function(d) {
                    return '<span class="text-meta" style="font-family:monospace;font-size:12px">' + d.slice(0,8) + '..</span>';
                }
            },
            { data: 'name', render: function(d) { return '<span class="fw-medium">' + d + '</span>'; } },
            { data: 'status_name', render: function(d) { return statusBadge(d); } },
            { data: 'priority_name' },
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
                render: function(d) { return '<span class="text-meta" style="font-size:13px">' + d + '</span>'; }
            },
            {
                data: 'id',
                orderable: false,
                render: function(d) {
                    return '<a href="' + site_url + '/improvements/' + d + '" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>';
                }
            }
        ],
        order: [[5, 'desc']],
        language: {
            searchPlaceholder: 'Search improvements...',
            emptyTable: '<div class="empty-state" style="padding:48px 20px"><i class="bi bi-rocket-takeoff"></i><h4>No improvements found</h4><p>Submit a new improvement proposal to get started.</p></div>'
        },
        dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>',
    });
});
</script>
<?= $this->endSection() ?>
