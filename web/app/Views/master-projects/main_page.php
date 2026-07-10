<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Master Projects</h1>
            <p class="text-meta mb-0" style="font-size:13px">Manage projects, modules, and pages for bug tracking</p>
        </div>
        <a href="<?= site_url('master-projects/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Project
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table id="projects-table" class="table mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Modules</th>
                        <th>Bugs</th>
                        <th>Status</th>
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
$(function() {
    $('#projects-table').DataTable({
        processing: true,
        ajax: {
            url: site_url + '/master-projects/ajax-list',
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                render: function(d) {
                    var desc = d.description ? '<br><span class="text-meta" style="font-size:12px">' + d.description.slice(0, 80) + '</span>' : '';
                    return '<a href="' + site_url + '/master-projects/' + d.id + '" class="fw-medium">' + d.name + '</a>' + desc;
                }
            },
            {
                data: 'module_count',
                render: function(d) {
                    return '<span class="status-badge info"><span class="badge-dot"></span>' + (d || 0) + '</span>';
                }
            },
            {
                data: 'bug_count',
                render: function(d) {
                    var count = d || 0;
                    var cls = count > 0 ? 'danger' : 'closed';
                    return '<span class="status-badge ' + cls + '"><span class="badge-dot"></span>' + count + '</span>';
                }
            },
            {
                data: 'status_name',
                render: function(d) {
                    var cls = d === 'Active' ? 'approved' : 'closed';
                    return '<span class="status-badge ' + cls + '"><span class="badge-dot"></span>' + d + '</span>';
                }
            },
            {
                data: 'created_at',
                render: function(d) { return '<span class="text-meta" style="font-size:13px">' + (d || '-') + '</span>'; }
            },
            {
                data: 'id',
                orderable: false,
                render: function(d) {
                    return '<a href="' + site_url + '/master-projects/' + d + '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></a>' +
                           '<button class="btn btn-sm btn-outline-danger" onclick="deleteProject(\'' + d + '\')"><i class="bi bi-trash"></i></button>';
                }
            }
        ],
        order: [[4, 'desc']],
        language: {
            emptyTable: '<div class="empty-state" style="padding:48px 20px"><i class="bi bi-diagram-3"></i><h4>No projects yet</h4><p>Create your first master project to start tracking bugs.</p></div>'
        },
        dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>',
    });
});

window.deleteProject = function(id) {
    Swal.fire({
        title: 'Delete project?',
        text: 'This will also delete all modules and pages within it.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E11D48',
        confirmButtonText: 'Delete',
    }).then(function(r) {
        if (r.isConfirmed) {
            $.post(site_url + '/master-projects/' + id + '/delete', function(res) {
                if (res.status) {
                    toastr.success('Project deleted');
                    $('#projects-table').DataTable().ajax.reload();
                } else {
                    toastr.error(res.data.message || 'Failed');
                }
            });
        }
    });
};
</script>
<?= $this->endSection() ?>
