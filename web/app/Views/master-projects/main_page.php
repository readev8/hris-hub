<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Master Projects</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Manage projects, modules, and pages for bug tracking</p>
    </div>
    <a href="<?= site_url('master-projects/create') ?>" class="sap-btn sap-btn-primary">
        <i class="fas fa-plus"></i> New Project
    </a>
</div>

<div class="sap-card">
    <div class="sap-card-body p-0">
        <table id="projects-table" class="sap-table mb-0" style="width:100%">
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
                    var desc = d.description ? '<br><span class="text-secondary" style="font-size:12px">' + d.description.slice(0, 80) + '</span>' : '';
                    return '<a href="' + site_url + '/master-projects/' + d.id + '" class="fw-medium" style="color:var(--sap-brand)">' + d.name + '</a>' + desc;
                }
            },
            {
                data: 'module_count',
                render: function(d) {
                    return '<span class="sap-badge info"><span class="badge-dot"></span>' + (d || 0) + '</span>';
                }
            },
            {
                data: 'bug_count',
                render: function(d) {
                    var count = d || 0;
                    var cls = count > 0 ? 'rejected' : 'closed';
                    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + count + '</span>';
                }
            },
            {
                data: 'status_name',
                render: function(d) {
                    var cls = d === 'Active' ? 'approved' : 'closed';
                    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + d + '</span>';
                }
            },
            {
                data: 'created_at',
                render: function(d) { return '<span class="text-muted" style="font-size:13px">' + (d || '-') + '</span>'; }
            },
            {
                data: 'id',
                orderable: false,
                render: function(d) {
                    return '<a href="' + site_url + '/master-projects/' + d + '" class="sap-btn sap-btn-secondary sap-btn-sm me-1"><i class="fas fa-eye"></i></a>' +
                           '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deleteProject(\'' + d + '\')"><i class="fas fa-trash-alt"></i></button>';
                }
            }
        ],
        order: [[4, 'desc']],
        language: {
            emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-project-diagram"></i><h4>No projects yet</h4><p>Create your first master project to start tracking bugs.</p></div>'
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
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
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
