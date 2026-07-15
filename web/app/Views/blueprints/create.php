<?= $this->extend('template/index') ?>

<?= $this->section('styles') ?>
<style>
.field-error {
    font-size: 12px;
    color: var(--sap-error);
    margin-top: 4px;
    display: none;
}
.field-error.visible { display: block; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="max-width:900px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('blueprints') ?>">Blueprints</a>
        <span class="sep">/</span>
        <span class="active">Create</span>
    </div>

    <h1 class="mb-4">Create Blueprint</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="blueprintForm">
                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-link me-1"></i> Select Improvement
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Improvement <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="sap-input" id="improvementDisplay"
                                   placeholder="Click search to select improvement..." disabled>
                            <input type="hidden" name="improvement_id" id="improvementId" value="">
                            <button type="button" class="sap-btn sap-btn-secondary" onclick="openImprovementModal()" style="white-space:nowrap">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button type="button" class="sap-btn sap-btn-secondary" onclick="clearImprovement()" style="white-space:nowrap">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="field-error" id="error-improvement_id" role="alert"></div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-info-circle me-1"></i> Blueprint Details
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="sap-input" required placeholder="e.g., SSO Login Implementation">
                        <div class="field-error" id="error-name" role="alert"></div>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" class="sap-input" rows="5" placeholder="Describe the blueprint scope and objectives..." style="min-height:120px"></textarea>
                        <div class="field-error" id="error-description" role="alert"></div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-paper-plane"></i> Create Blueprint
                    </button>
                    <a href="<?= site_url('blueprints') ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal fade sap-modal" id="improvementSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Search Improvement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table id="improvementSearchTable" class="sap-table mb-0" style="width:100%">
                    <thead>
                        <tr>
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
            <div class="modal-footer">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var improvementSearchTable = null;

function sapBadge(name) {
    var clsMap = {'Draft':'draft','Pending IT Approval':'pending','Pending Dept Approval':'pending','Approved':'approved','Rejected':'rejected'};
    var cls = clsMap[name] || 'closed';
    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

function openImprovementModal() {
    var modal = new bootstrap.Modal(document.getElementById('improvementSearchModal'));
    modal.show();
    if (!improvementSearchTable) {
        improvementSearchTable = $('#improvementSearchTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: site_url + '/improvements/ajax-list',
                data: { exclude_blueprint: 1 },
                dataSrc: 'data'
            },
            columns: [
                { data: 'name', render: function(d) { return '<span class="fw-medium">' + d + '</span>'; } },
                { data: 'status_name', render: function(d) { return sapBadge(d); } },
                { data: 'priority_name', defaultContent: '-' },
                { data: 'creator_name', defaultContent: '-' },
                { data: 'created_at', render: function(d) { return '<span class="text-muted" style="font-size:13px">' + d + '</span>'; } },
                {
                    data: 'id',
                    orderable: false,
                    render: function(d) {
                        return '<button class="sap-btn sap-btn-primary sap-btn-sm" onclick="selectImprovement(\'' + d + '\', this)"><i class="fas fa-check"></i></button>';
                    }
                }
            ],
            order: [[4, 'desc']],
            language: { searchPlaceholder: 'Search improvements...' }
        });
    } else {
        improvementSearchTable.ajax.reload();
    }
}

function selectImprovement(id, btn) {
    var rowData = improvementSearchTable.row($(btn).closest('tr')).data();
    $('#improvementId').val(id);
    $('#improvementDisplay').val(rowData.name);
    $('#error-improvement_id').removeClass('visible').text('');
    bootstrap.Modal.getInstance(document.getElementById('improvementSearchModal')).hide();
}

function clearImprovement() {
    $('#improvementId').val('');
    $('#improvementDisplay').val('');
}

$(function() {
    function clearFieldErrors() {
        $('.field-error').removeClass('visible').text('');
        $('.sap-input, .sap-select').removeClass('is-invalid');
    }

    $('[name="name"], [name="description"]').on('input change', function() {
        $(this).removeClass('is-invalid');
        var fieldName = $(this).attr('name');
        $('#error-' + fieldName).removeClass('visible').text('');
    });

    $('#blueprintForm').on('submit', function(e) {
        e.preventDefault();
        clearFieldErrors();

        if (!$('#improvementId').val()) {
            $('#error-improvement_id').text('Improvement wajib dipilih').addClass('visible');
            toastr.error('Please fix the errors below');
            return;
        }

        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Creating...');

        $.ajax({
            url: site_url + '/blueprints/create',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status && res.redirect) {
                    toastr.success('Blueprint created');
                    setTimeout(function() { window.location.href = res.redirect; }, 500);
                } else {
                    if (res.errors && typeof res.errors === 'object') {
                        Object.keys(res.errors).forEach(function(field) {
                            var input = $('[name="' + field + '"]');
                            if (input.length) {
                                input.addClass('is-invalid');
                                $('#error-' + field).text(res.errors[field]).addClass('visible');
                            }
                        });
                        toastr.error('Please fix the errors below');
                    } else {
                        toastr.error(res.message || 'Failed to create blueprint');
                    }
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Create Blueprint');
                }
            },
            error: function(xhr) {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch(e) {}
                if (res && res.redirect) {
                    window.location.href = res.redirect;
                    return;
                }
                var msg = res && res.message ? res.message : 'Request failed (HTTP ' + xhr.status + ')';
                toastr.error(msg);
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Create Blueprint');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
