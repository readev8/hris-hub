<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Users</h1>
            <p class="text-meta mb-0" style="font-size:13px">Manage system users and roles</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table id="users-table" class="table mb-0" style="width:100%">
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
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('#users-table').DataTable({
        processing: true,
        ajax: {
            url: site_url + '/users/ajax-list',
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                render: function(d) {
                    var initial = d.full_name ? d.full_name.charAt(0).toUpperCase() : '?';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:#0F4C81;color:#fff">' + initial + '</div><span class="fw-medium">' + d.full_name + '</span></div>';
                }
            },
            { data: 'email', render: function(d) { return '<span class="text-meta">' + d + '</span>'; } },
            {
                data: 'role_name',
                render: function(d) {
                    var clsMap = {'Developer':'info','Requester':'primary','Dept Head':'warning','IT Manager':'success','Admin':'danger'};
                    var cls = clsMap[d] || 'secondary';
                    return '<span class="status-badge ' + cls + '"><span class="badge-dot"></span>' + d + '</span>';
                }
            },
            {
                data: 'is_active',
                render: function(d) {
                    return d == 1
                        ? '<span class="status-badge approved"><span class="badge-dot"></span>Active</span>'
                        : '<span class="status-badge rejected"><span class="badge-dot"></span>Inactive</span>';
                }
            }
        ],
        order: [[0, 'asc']],
        language: {
            emptyTable: '<div class="empty-state" style="padding:48px 20px"><i class="bi bi-people"></i><h4>No users found</h4></div>'
        },
        dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>',
    });
});
</script>
<?= $this->endSection() ?>
