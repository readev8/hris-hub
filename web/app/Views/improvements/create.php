<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:900px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('improvements') ?>">Improvements</a>
        <span class="sep">/</span>
        <span class="active">Create</span>
    </div>

    <h1 class="mb-4">Create Improvement</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="improvementForm">
                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-info-circle me-1"></i> General Information
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="sap-input" required placeholder="e.g., Implement SSO Login">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="sap-input" rows="5" required placeholder="Describe the improvement in detail..." style="min-height:120px"></textarea>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-chart-line me-1"></i> Business Details
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Business Case <span class="text-danger">*</span></label>
                        <textarea name="business_case" class="sap-input" rows="3" required placeholder="Why is this improvement needed? What value will it bring?" style="min-height:80px"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="sap-label">Category</label>
                            <select name="category" class="sap-select">
                                <option value="">Select category...</option>
                                <option value="UI/UX">UI/UX</option>
                                <option value="Performance">Performance</option>
                                <option value="Security">Security</option>
                                <option value="New Feature">New Feature</option>
                                <option value="Integration">Integration</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Priority</label>
                            <select name="priority" class="sap-select">
                                <option value="0">Low</option>
                                <option value="1" selected>Medium</option>
                                <option value="2">High</option>
                                <option value="3">Critical</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-project-diagram me-1"></i> Scope & Assignment
                    </h5>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="sap-label">Project</label>
                            <select class="sap-select" id="projectSelect">
                                <option value="">Select Project...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="sap-label">Module</label>
                            <select class="sap-select" id="moduleSelect" disabled>
                                <option value="">Select Module...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="sap-label">Page</label>
                            <select class="sap-select" id="pageSelect" disabled>
                                <option value="">Select Page...</option>
                            </select>
                            <input type="hidden" name="page_id" id="pageIdValue">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="sap-label">Assignee / PIC</label>
                            <select name="assigned_to" class="sap-select" id="assigneeSelect">
                                <option value="">Select user...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Target Date</label>
                            <input type="date" name="target_date" class="sap-input">
                        </div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-paperclip me-1"></i> Attachments
                    </h5>
                    <label class="sap-label">Files <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 3, JPG/PNG/PDF, max 5MB each)</span></label>
                    <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:24px;text-align:center;cursor:pointer" id="dropzone">
                        <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:var(--sap-text-muted);display:block;margin-bottom:8px"></i>
                        <p class="mb-0 text-secondary" style="font-size:13px">Drop files here or click to browse</p>
                        <input type="file" name="images[]" accept="image/jpeg,image/png,application/pdf" multiple hidden>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2" id="filePreview"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                    <a href="<?= site_url('improvements') ?>" class="sap-btn sap-btn-secondary">
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
$(function() {
    // Load users for assignee dropdown
    $.get(site_url + '/users/ajax-list', function(res) {
        var users = res.data || [];
        var html = '<option value="">Select user...</option>';
        for (var i = 0; i < users.length; i++) {
            html += '<option value="' + users[i].id + '">' + (users[i].full_name || users[i].name) + '</option>';
        }
        $('#assigneeSelect').html(html);
    }).fail(function() {
        toastr.error('Failed to load users');
    });

    // Project cascade
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
            },
            error: function() {
                toastr.error('Failed to load projects');
                $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
            }
        });
    }
    loadProjects();

    var activeModuleReq = null;
    var activePageReq = null;

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

    // File upload dropzone
    $('#dropzone').on('click', function() {
        $(this).find('input[type="file"]').click();
    }).on('dragover', function(e) {
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

    $('#improvementForm input[name="images[]"]').on('change', function() {
        var preview = $('#filePreview');
        preview.empty();
        var files = this.files;
        if (files.length > 3) {
            toastr.warning('Maximum 3 files');
            $(this).val('');
            return;
        }
        for (var i = 0; i < files.length && i < 3; i++) {
            var file = files[i];
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.append('<img src="' + e.target.result + '" style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)">');
                };
                reader.readAsDataURL(file);
            } else {
                preview.append('<div style="padding:8px 12px;background:var(--sap-background);border-radius:6px;border:1px solid var(--sap-border);font-size:13px"><i class="fas fa-file-pdf" style="color:var(--sap-error);margin-right:6px"></i>' + file.name + '</div>');
            }
        }
    });

    $('#improvementForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Submitting...');

        $.ajax({
            url: site_url + '/improvements/create',
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status && res.redirect) {
                    toastr.success('Improvement created');
                    setTimeout(function() { window.location.href = res.redirect; }, 500);
                } else {
                    toastr.error(res.message || 'Failed to create');
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
                }
            },
            error: function() {
                toastr.error('Request failed');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
