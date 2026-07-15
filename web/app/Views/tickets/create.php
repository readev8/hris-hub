<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:800px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('tickets') ?>">Tickets</a>
        <span class="sep">/</span>
        <span class="active">Create</span>
    </div>

    <h1 class="mb-4">Create Ticket</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="ticketForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="sap-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="sap-input" required minlength="5" placeholder="Brief description of the issue or request">
                    <div class="sap-hint">Minimal 5 characters</div>
                </div>
                <div class="mb-3">
                    <label class="sap-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="sap-input" rows="6" required placeholder="Detailed description including steps to reproduce for bugs..." style="min-height:120px"></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="sap-label">Type</label>
                        <select name="type" class="sap-select" id="ticketType">
                            <option value="2">Task</option>
                            <option value="1">Issue</option>
                            <option value="0">Bug</option>
                            <option value="3">Change Request</option>
                            <option value="4">Data Request</option>
                            <option value="5">Change Data Request</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="sap-label">Priority</label>
                        <div class="segmented-control" id="prioritySegments">
                            <button type="button" class="seg-option" data-value="0">Low</button>
                            <button type="button" class="seg-option active" data-value="1">Medium</button>
                            <button type="button" class="seg-option" data-value="2">High</button>
                            <button type="button" class="seg-option" data-value="3">Critical</button>
                        </div>
                        <input type="hidden" name="priority" id="priorityValue" value="1">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="sap-label">Due Date</label>
                        <input type="date" name="due_date" class="sap-input">
                    </div>
                </div>

                <div class="mb-4" id="bugTraceSection" style="display:none">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-project-diagram me-1"></i> Affected Page <span class="text-danger">*</span>
                    </h5>
                    <p class="text-secondary" style="font-size:13px">Select the page affected by this ticket</p>
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
                            <input type="hidden" name="page_id" id="pageIdValue">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="sap-label">Images <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 3, JPG/PNG, max 2MB each)</span></label>
                    <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:24px;text-align:center;transition:all var(--sap-transition);cursor:pointer" id="dropzone">
                        <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:var(--sap-text-muted);display:block;margin-bottom:8px"></i>
                        <p class="mb-2 text-secondary" style="font-size:13px">Drop images here or</p>
                        <label class="sap-btn sap-btn-secondary sap-btn-sm" style="cursor:pointer" onclick="event.stopPropagation()">
                            <i class="fas fa-paperclip"></i> Choose Files
                            <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,.xlsx,.xls,.docx,.doc,.pptx,.ppt,.csv" multiple hidden>
                        </label>
                        <p class="mb-0 mt-1 text-muted" style="font-size:11px">Images, PDF, Excel, Word, PPT — max 5MB each</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2" id="imagePreview"></div>
                </div>
                <div class="mb-3" style="padding:12px 16px;border-radius:var(--sap-radius-sm);border:1px solid var(--sap-border);background:var(--sap-surface)">
                    <div class="form-check" style="margin:0">
                        <input type="checkbox" name="needs_approval" value="1" id="needsApproval" class="form-check-input" style="width:18px;height:18px;cursor:pointer">
                        <label for="needsApproval" class="form-check-label" style="cursor:pointer;margin-left:8px">
                            <strong style="color:var(--sap-text)">Requires Approval</strong>
                            <span class="text-muted d-block" style="font-size:12px;margin-top:2px">2-stage approval: IT Manager → Dept Head</span>
                        </label>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                    <a href="<?= site_url('tickets') ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.segmented-control .seg-option[data-value="0"]:hover { background: var(--sap-success-bg); color: var(--sap-success); }
.segmented-control .seg-option[data-value="0"].active { background: var(--sap-success); color: #fff; border-color: var(--sap-success); }
.segmented-control .seg-option[data-value="2"]:hover { background: var(--sap-warning-bg); color: var(--sap-warning); }
.segmented-control .seg-option[data-value="2"].active { background: var(--sap-warning); color: #fff; border-color: var(--sap-warning); }
.segmented-control .seg-option[data-value="3"]:hover { background: var(--sap-error-bg); color: var(--sap-error); }
.segmented-control .seg-option[data-value="3"].active { background: var(--sap-error); color: #fff; border-color: var(--sap-error); }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var projectsCache = null;
var activeModuleReq = null;
var activePageReq = null;

$(function() {
    $('#prioritySegments .seg-option').on('click', function() {
        $('#prioritySegments .seg-option').removeClass('active');
        $(this).addClass('active');
        $('#priorityValue').val($(this).data('value'));
    });

    $('#ticketType').on('change', function() {
        if (['0','3','4','5'].includes($(this).val())) {
            $('#bugTraceSection').slideDown(200);
            loadProjects();
        } else {
            $('#bugTraceSection').slideUp(200);
            $('#pageIdValue').val('');
            $('#projectSelect').val('');
            $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
        }
    });

    function loadProjects() {
        if (projectsCache) {
            populateProjects(projectsCache);
            return;
        }
        $('#projectSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        $.ajax({
            url: site_url + '/master-projects/active',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                if (!Array.isArray(res)) { $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false); return; }
                projectsCache = res;
                populateProjects(res);
            },
            error: function() {
                toastr.error('Failed to load projects');
                $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
                $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            }
        });
    }

    function populateProjects(res) {
        var html = '<option value="">Select Project...</option>';
        for (var i = 0; i < res.length; i++) {
            html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
        }
        $('#projectSelect').html(html).prop('disabled', false);
        $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
        $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
        $('#pageIdValue').val('');
    }

    $('#projectSelect').on('change', function() {
        var pid = $(this).val();
        if (!pid) {
            $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            $('#pageIdValue').val('');
            return;
        }
        if (activeModuleReq) activeModuleReq.abort();
        $('#moduleSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        activeModuleReq = $.ajax({
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
        if (activePageReq) activePageReq.abort();
        $('#pageSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        activePageReq = $.ajax({
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

    $('#dropzone').on('dragover', function(e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--sap-brand)').css('background', 'var(--sap-brand-hover)');
    }).on('dragleave', function() {
        $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            var input = $(this).find('input[type="file"]')[0];
            input.files = files;
            $(input).trigger('change');
        }
    });

    $('#ticketForm input[name="images[]"]').on('change', function() {
        var preview = $('#imagePreview');
        preview.empty();
        var files = this.files;
        if (files.length > 3) {
            toastr.warning('Maximum 3 images');
            $(this).val('');
            return;
        }
        for (var i = 0; i < files.length && i < 3; i++) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.append('<img src="' + e.target.result + '" style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)">');
            };
            reader.readAsDataURL(files[i]);
        }
    });

    $('#ticketForm').on('submit', function(e) {
        e.preventDefault();
        var type = $('#ticketType').val();
        var pageId = $('#pageIdValue').val();
        if (['0','3','4','5'].includes(type) && !pageId) {
            toastr.warning('Untuk tipe ini, wajib memilih halaman di bagian Affected Page');
            $('#bugTraceSection').slideDown(200);
            return;
        }
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Submitting...');
        var formData = new FormData(this);
        $.ajax({
            url: site_url + '/tickets/create',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status && res.redirect) {
                    var msg = 'Ticket created successfully';
                    if (res.tracking_code) {
                        msg += '\nTracking code: ' + res.tracking_code;
                    }
                    toastr.success(msg, '', { timeOut: 5000 });
                    setTimeout(function() { window.location.href = res.redirect; }, 1500);
                } else {
                    var msg = res.message || 'Failed to create ticket';
                    if (res.errors && typeof res.errors === 'object') {
                        var details = Object.values(res.errors).join(', ');
                        msg += ': ' + details;
                    }
                    toastr.error(msg);
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
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
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
