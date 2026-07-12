<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:800px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('tickets') ?>">Tickets</a>
        <span class="sep">/</span>
        <a href="<?= site_url('tickets/' . $token) ?>"><?= esc($ticket['title'] ?? '') ?></a>
        <span class="sep">/</span>
        <span class="active">Edit</span>
    </div>

    <h1 class="mb-4">Edit Ticket</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="ticketForm" enctype="multipart/form-data">
                <input type="hidden" name="_method" value="PUT">
                <div class="mb-3">
                    <label class="sap-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="sap-input" required minlength="5" value="<?= esc($ticket['title'] ?? '') ?>" placeholder="Brief description of the issue or request">
                    <div class="sap-hint">Minimal 5 characters</div>
                </div>
                <div class="mb-3">
                    <label class="sap-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="sap-input" rows="6" required placeholder="Detailed description including steps to reproduce for bugs..." style="min-height:120px"><?= esc($ticket['description'] ?? '') ?></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="sap-label">Type</label>
                        <select name="type" class="sap-select" id="ticketType">
                            <option value="2" <?= ($ticket['type'] ?? '') == 2 ? 'selected' : '' ?>>Task</option>
                            <option value="1" <?= ($ticket['type'] ?? '') == 1 ? 'selected' : '' ?>>Issue</option>
                            <option value="0" <?= ($ticket['type'] ?? '') == 0 ? 'selected' : '' ?>>Bug</option>
                            <option value="3" <?= ($ticket['type'] ?? '') == 3 ? 'selected' : '' ?>>Change Request</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="sap-label">Priority</label>
                        <select name="priority" class="sap-select">
                            <option value="0" <?= ($ticket['priority'] ?? '') == 0 ? 'selected' : '' ?>>Low</option>
                            <option value="1" <?= ($ticket['priority'] ?? '') == 1 ? 'selected' : '' ?>>Medium</option>
                            <option value="2" <?= ($ticket['priority'] ?? '') == 2 ? 'selected' : '' ?>>High</option>
                            <option value="3" <?= ($ticket['priority'] ?? '') == 3 ? 'selected' : '' ?>>Critical</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="sap-label">Due Date</label>
                        <input type="date" name="due_date" class="sap-input" value="<?= esc($ticket['due_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="sap-label">Status</label>
                        <select name="status" class="sap-select">
                            <option value="0" <?= ($ticket['status'] ?? '') == 0 ? 'selected' : '' ?>>Open</option>
                            <option value="1" <?= ($ticket['status'] ?? '') == 1 ? 'selected' : '' ?>>Approved</option>
                            <option value="2" <?= ($ticket['status'] ?? '') == 2 ? 'selected' : '' ?>>In Progress</option>
                            <option value="3" <?= ($ticket['status'] ?? '') == 3 ? 'selected' : '' ?>>Resolved</option>
                            <option value="4" <?= ($ticket['status'] ?? '') == 4 ? 'selected' : '' ?>>Closed</option>
                            <option value="5" <?= ($ticket['status'] ?? '') == 5 ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4" id="bugTraceSection" style="display:<?= ($ticket['type'] ?? '') == 0 ? 'block' : 'none' ?>">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-project-diagram me-1"></i> Bug Location
                    </h5>
                    <p class="text-secondary" style="font-size:13px">Select the page where this bug was found</p>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select class="sap-select" id="projectSelect">
                                <option value="">Select Project...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="sap-select" id="moduleSelect" disabled>
                                <option value="">Select Module...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="sap-select" id="pageSelect" disabled>
                                <option value="">Select Page...</option>
                            </select>
                            <input type="hidden" name="page_id" id="pageIdValue" value="<?= esc($ticket['page_id'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="<?= site_url('tickets/' . $token) ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var token = '<?= esc($token) ?>';
var currentPageId = '<?= esc($ticket['page_id'] ?? '') ?>';

$(function() {
    $('#ticketType').on('change', function() {
        if ($(this).val() === '0') {
            $('#bugTraceSection').slideDown(200);
            loadProjects();
        } else {
            $('#bugTraceSection').slideUp(200);
            $('#pageIdValue').val('');
        }
    });

    function loadProjects() {
        $('#projectSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        $.ajax({
            url: site_url + '/master-projects/active',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                if (!Array.isArray(res)) { $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false); return; }
                var html = '<option value="">Select Project...</option>';
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
                }
                $('#projectSelect').html(html).prop('disabled', false);
                $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);

                if (currentPageId && Array.isArray(res)) {
                    for (var p of res) {
                        $.ajax({
                            url: site_url + '/master-projects/' + p.id + '/modules',
                            type: 'GET',
                            timeout: 10000,
                            success: function(modules) {
                                if (!Array.isArray(modules)) return;
                                for (var m of modules) {
                                    $.ajax({
                                        url: site_url + '/modules/' + m.id + '/pages',
                                        type: 'GET',
                                        timeout: 10000,
                                        success: function(pages) {
                                            if (!Array.isArray(pages)) return;
                                            for (var pg of pages) {
                                                if (pg.id === currentPageId) {
                                                    $('#projectSelect').val(p.id).trigger('change');
                                                    setTimeout(function() {
                                                        $('#moduleSelect').val(m.id).trigger('change');
                                                        setTimeout(function() {
                                                            $('#pageSelect').val(currentPageId);
                                                            $('#pageIdValue').val(currentPageId);
                                                        }, 300);
                                                    }, 300);
                                                }
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            },
            error: function() {
                toastr.error('Failed to load projects');
                $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
            }
        });
    }

    if ($('#ticketType').val() === '0') {
        loadProjects();
    }

    $('#projectSelect').on('change', function() {
        var pid = $(this).val();
        if (!pid) {
            $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            $('#pageIdValue').val('');
            return;
        }
        $('#moduleSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        $.ajax({
            url: site_url + '/master-projects/' + pid + '/modules',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                if (!Array.isArray(res)) { $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', false); return; }
                var html = '<option value="">Select Module...</option>';
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
                }
                $('#moduleSelect').html(html).prop('disabled', false);
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
                $('#pageIdValue').val('');
            },
            error: function() {
                toastr.error('Failed to load modules');
                $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', false);
            }
        });
    });

    $('#moduleSelect').on('change', function() {
        var mid = $(this).val();
        if (!mid) {
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            $('#pageIdValue').val('');
            return;
        }
        $('#pageSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        $.ajax({
            url: site_url + '/modules/' + mid + '/pages',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                if (!Array.isArray(res)) { $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', false); return; }
                var html = '<option value="">Select Page...</option>';
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
                }
                $('#pageSelect').html(html).prop('disabled', false);
                $('#pageIdValue').val('');
            },
            error: function() {
                toastr.error('Failed to load pages');
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', false);
            }
        });
    });

    $('#pageSelect').on('change', function() {
        $('#pageIdValue').val($(this).val());
    });

    $('#ticketForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Updating...');
        $.ajax({
            url: site_url + '/tickets/' + token + '/update',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    toastr.success('Ticket updated');
                    setTimeout(function() { window.location.href = site_url + '/tickets/' + token; }, 500);
                } else {
                    toastr.error(res.message || 'Failed to update');
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update');
                }
            },
            error: function() {
                toastr.error('Request failed');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
