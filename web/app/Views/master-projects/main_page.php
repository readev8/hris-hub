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
                    <th style="width:120px">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/_shared/column-search.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    var table = $('#projects-table').DataTable({
        processing: true,
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({ header: function(row) { return 'Details'; }}),
                renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'sap-table mb-0' })
            }
        },
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
                    return '<a href="' + site_url + '/master-projects/' + d + '" class="sap-btn sap-btn-secondary sap-btn-sm me-1" onclick="event.stopPropagation();"><i class="fas fa-eye"></i></a>' +
                           '<a href="' + site_url + '/master-projects/' + d + '/edit" class="sap-btn sap-btn-secondary sap-btn-sm me-1" onclick="event.stopPropagation();"><i class="fas fa-pencil-alt"></i></a>' +
                           '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="event.stopPropagation(); deleteProject(\'' + d + '\')"><i class="fas fa-trash-alt"></i></button>';
                }
            }
        ],
        order: [[4, 'desc']],
        language: {
            emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-project-diagram"></i><h4>No projects yet</h4><p>Create your first master project to start tracking bugs.</p></div>'
        },
        dom: '<"row mb-3"<"col-sm-12"B>>rt<"row mt-3"<"col-sm-4"l><"col-sm-4"i><"col-sm-4"p>>',
        buttons: [
            { extend: 'copy', text: '<i class="fas fa-copy"></i> Copy', className: 'btn-sm' },
            { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn-sm' },
            { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-sm' },
            { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-sm' },
            { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn-sm' },
            { extend: 'colvis', text: '<i class="fas fa-columns"></i> Columns', className: 'btn-sm' },
        ]
    });

    AppEvent.on('project:created', function() { table.ajax.reload(); });
    AppEvent.on('project:deleted', function() { table.ajax.reload(); });
    AppEvent.on('project:updated', function() { table.ajax.reload(); });

    $('#projects-table thead tr').clone(true).appendTo('#projects-table thead');
    $('#projects-table thead tr:last th').each(function(i) {
        if (i === 5) {
            $(this).html('');
            return;
        }
        $(this).html('<input type="text" class="column-search" placeholder="Search ' + $('#projects-table thead tr:first th:eq(' + i + ')').text() + '..." data-col="' + i + '">');
    });

    $('#projects-table').on('keyup change', '.column-search', function() {
        table.column($(this).data('col')).search(this.value).draw();
    });

    $('#projects-table tbody').on('click', 'tr', function() {
        var data = table.row(this).data();
        if (data && data.id) {
            window.location.href = site_url + '/master-projects/' + data.id;
        }
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
                    AppEvent.dispatch('project:deleted');
                } else {
                    toastr.error(res.data.message || 'Failed');
                }
            }).fail(function(xhr) {
                toastr.error('Gagal menghapus project (HTTP ' + xhr.status + ')');
            });
        }
    });
};
</script>
<?= $this->endSection() ?>
