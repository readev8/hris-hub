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
                        <div class="field-error" id="error-name" role="alert"></div>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="sap-input" rows="5" required placeholder="Describe the improvement in detail..." style="min-height:120px"></textarea>
                        <div class="field-error" id="error-description" role="alert"></div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-chart-line me-1"></i> Business Details
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Business Case <span class="text-danger">*</span></label>
                        <textarea name="business_case" class="sap-input" rows="3" required placeholder="Why is this improvement needed? What value will it bring?" style="min-height:80px"></textarea>
                        <div class="field-error" id="error-business_case" role="alert"></div>
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
                            <div class="field-error" id="error-priority" role="alert"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-calendar-alt me-1"></i> Details
                    </h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="sap-label">Target Date</label>
                            <input type="date" name="target_date" class="sap-input">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Approver <span style="font-weight:400;color:var(--sap-text-muted)">(optional)</span></label>
                            <select name="approver_id" class="sap-select" id="approverSelect">
                                <option value="">Role-based approval (default)</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= esc($u['id']) ?>"><?= esc($u['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted" style="font-size:11px">If set, only this user can approve. Otherwise, role-based approval applies.</small>
                        </div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-paperclip me-1"></i> Attachments
                    </h5>
                    <label class="sap-label">Files <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 5, JPG/PNG/GIF/WebP/PDF, max 500KB each)</span></label>
                    <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:24px;text-align:center;cursor:pointer" id="dropzone">
                        <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:var(--sap-text-muted);display:block;margin-bottom:8px"></i>
                        <p class="mb-0 text-secondary" style="font-size:13px">Drop files here or click to browse</p>
                        <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf" multiple hidden>
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

    $('#dropzone input[type="file"]').on('click', function(e) {
        e.stopPropagation();
    });

    var selectedFiles = [];

    $('#improvementForm input[name="images[]"]').on('change', function() {
        var newFiles = this.files;
        var maxSize = 500 * 1024;
        for (var i = 0; i < newFiles.length; i++) {
            if (newFiles[i].size > maxSize) {
                toastr.warning(newFiles[i].name + ' exceeds 500KB limit');
                continue;
            }
            var exists = selectedFiles.some(function(f) {
                return f.name === newFiles[i].name && f.size === newFiles[i].size;
            });
            if (!exists) {
                selectedFiles.push(newFiles[i]);
            }
        }
        if (selectedFiles.length > 5) {
            toastr.warning('Maximum 5 files');
            selectedFiles = selectedFiles.slice(0, 5);
        }
        renderPreview();
        $(this).val('');
    });

    function renderPreview() {
        var preview = $('#filePreview');
        preview.empty();
        for (var i = 0; i < selectedFiles.length; i++) {
            var file = selectedFiles[i];
            var idx = i;
            var wrapper = $('<div style="position:relative;display:inline-block"></div>');
            if (file.type.startsWith('image/')) {
                (function(f, w, index) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        w.append('<img src="' + e.target.result + '" style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)">');
                        w.append('<button type="button" class="btn-remove-file" data-idx="' + index + '" style="position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center">&times;</button>');
                    };
                    reader.readAsDataURL(f);
                })(file, wrapper, idx);
            } else {
                wrapper.append('<div style="padding:8px 12px;background:var(--sap-background);border-radius:6px;border:1px solid var(--sap-border);font-size:13px"><i class="fas fa-file-pdf" style="color:var(--sap-error);margin-right:6px"></i>' + file.name + '</div>');
                wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + idx + '" style="position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center">&times;</button>');
            }
            preview.append(wrapper);
        }
    }

    $('#filePreview').on('click', '.btn-remove-file', function() {
        selectedFiles.splice($(this).data('idx'), 1);
        renderPreview();
    });

    function clearFieldErrors() {
        $('.field-error').removeClass('visible').text('');
        $('.sap-input, .sap-select').removeClass('is-invalid');
    }

    $('[name="name"], [name="description"], [name="business_case"], [name="priority"]').on('input change', function() {
        $(this).removeClass('is-invalid');
        var fieldName = $(this).attr('name');
        $('#error-' + fieldName).removeClass('visible').text('');
    });

    $('#improvementForm').on('submit', function(e) {
        e.preventDefault();
        clearFieldErrors();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Submitting...');

        var formData = new FormData(this);
        for (var i = 0; i < selectedFiles.length; i++) {
            formData.append('images[]', selectedFiles[i]);
        }

        $.ajax({
            url: site_url + '/improvements/create',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status && res.redirect) {
                    toastr.success('Improvement created');
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
                        toastr.error(res.message || 'Failed to create');
                    }
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
