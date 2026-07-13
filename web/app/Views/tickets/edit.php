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
                        <div class="segmented-control" id="prioritySegments">
                            <button type="button" class="seg-option <?= ($ticket['priority'] ?? 1) == 0 ? '' : '' ?>" data-value="0">Low</button>
                            <button type="button" class="seg-option <?= ($ticket['priority'] ?? 1) == 1 ? 'active' : '' ?>" data-value="1">Medium</button>
                            <button type="button" class="seg-option <?= ($ticket['priority'] ?? 1) == 2 ? 'active' : '' ?>" data-value="2">High</button>
                            <button type="button" class="seg-option <?= ($ticket['priority'] ?? 1) == 3 ? 'active' : '' ?>" data-value="3">Critical</button>
                        </div>
                        <input type="hidden" name="priority" id="priorityValue" value="<?= esc($ticket['priority'] ?? 1) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="sap-label">Due Date</label>
                        <input type="date" name="due_date" class="sap-input" value="<?= esc($ticket['due_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="sap-label">Assignee</label>
                        <select name="assignee_id" class="sap-select" id="assigneeSelect">
                            <option value="">Unassigned</option>
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

                <?php if (!empty($ticket['attachments'])): ?>
                <div class="mb-4">
                    <label class="sap-label">Current Attachments</label>
                    <div class="d-flex flex-wrap gap-2" id="existingAttachments">
                        <?php foreach ($ticket['attachments'] as $att): ?>
                        <div class="attachment-item" data-id="<?= esc($att['id']) ?>" style="position:relative;display:inline-block">
                            <a href="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                               class="glightbox edit-attachment-link"
                               data-gallery="ticket-edit"
                               data-description="<?= esc($att['filename']) ?>">
                                <img src="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                                     alt="<?= esc($att['filename']) ?>"
                                     style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border);cursor:pointer"
                                     class="sap-hover-lift">
                            </a>
                            <button type="button" class="btn-remove-attachment" onclick="removeAttachment('<?= esc($att['id']) ?>', this)" title="Remove"
                                    style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:var(--sap-error);color:#fff;border:none;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label class="sap-label">Add Images <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 3, JPG/PNG, max 2MB each)</span></label>
                    <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:24px;text-align:center;transition:all var(--sap-transition);cursor:pointer" id="dropzone">
                        <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:var(--sap-text-muted);display:block;margin-bottom:8px"></i>
                        <p class="mb-2 text-secondary" style="font-size:13px">Drop images here or</p>
                        <label class="sap-btn sap-btn-secondary sap-btn-sm" style="cursor:pointer" onclick="event.stopPropagation()">
                            <i class="fas fa-images"></i> Choose Files
                            <input type="file" name="images[]" accept="image/jpeg,image/png" multiple hidden>
                        </label>
                        <p class="mb-0 mt-1 text-muted" style="font-size:11px">JPG or PNG, max 2MB each</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2" id="imagePreview"></div>
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
var token = '<?= esc($token) ?>';
var currentPageId = '<?= esc($ticket['page_id'] ?? '') ?>';
var currentAssigneeId = '<?= esc($ticket['assignee_raw_id'] ?? $ticket['assignee_id'] ?? '') ?>';
var projectsCache = null;
var activeModuleReq = null;
var activePageReq = null;

$(function() {
    $('#prioritySegments .seg-option').on('click', function() {
        $('#prioritySegments .seg-option').removeClass('active');
        $(this).addClass('active');
        $('#priorityValue').val($(this).data('value'));
    });

    function loadAssignees() {
        $.ajax({
            url: site_url + '/users/ajax-list',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                var users = res.data || [];
                var html = '<option value="">Unassigned</option>';
                for (var i = 0; i < users.length; i++) {
                    var selected = (String(users[i].id) === String(currentAssigneeId)) ? ' selected' : '';
                    html += '<option value="' + users[i].id + '"' + selected + '>' + (users[i].full_name || users[i].name) + '</option>';
                }
                $('#assigneeSelect').html(html);
            }
        });
    }
    loadAssignees();

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
                if (currentPageId) resolveBugLocation();
            },
            error: function() {
                toastr.error('Failed to load projects');
                $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
            }
        });
    }

    function populateProjects(res) {
        var html = '<option value="">Select Project...</option>';
        for (var i = 0; i < res.length; i++) {
            html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
        }
        $('#projectSelect').html(html).prop('disabled', false);
    }

    function resolveBugLocation() {
        if (!currentPageId || !projectsCache) return;
        for (var p of projectsCache) {
            $.ajax({
                url: site_url + '/master-projects/' + p.id + '/modules',
                type: 'GET', timeout: 10000,
                success: function(modules) {
                    if (!Array.isArray(modules)) return;
                    for (var m of modules) {
                        $.ajax({
                            url: site_url + '/modules/' + m.id + '/pages',
                            type: 'GET', timeout: 10000,
                            success: function(pages) {
                                if (!Array.isArray(pages)) return;
                                for (var pg of pages) {
                                    if (String(pg.id) === String(currentPageId)) {
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
        if (activeModuleReq) activeModuleReq.abort();
        $('#moduleSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        activeModuleReq = $.ajax({
            url: site_url + '/master-projects/' + pid + '/modules',
            type: 'GET', timeout: 10000,
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
            type: 'GET', timeout: 10000,
            success: function(res) {
                if (!Array.isArray(res)) { $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', false); return; }
                var html = '<option value="">Select Page...</option>';
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
                }
                $('#pageSelect').html(html).prop('disabled', false);
            },
            error: function() {
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
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Updating...');

        var formData = {
            title: $('input[name="title"]').val(),
            description: $('textarea[name="description"]').val(),
            type: $('select[name="type"]').val(),
            priority: $('#priorityValue').val(),
            due_date: $('input[name="due_date"]').val(),
            assignee_id: $('select[name="assignee_id"]').val(),
            page_id: $('#pageIdValue').val()
        };

        $.ajax({
            url: site_url + '/tickets/' + token + '/update',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    var files = $('#ticketForm input[name="images[]"]')[0].files;
                    if (files.length > 0) {
                        uploadAttachments(files, 0, btn);
                    } else {
                        toastr.success('Ticket updated');
                        setTimeout(function() { window.location.href = site_url + '/tickets/' + token; }, 500);
                    }
                } else {
                    var msg = res.message || 'Failed to update';
                    if (res.errors && typeof res.errors === 'object') {
                        msg += ': ' + Object.values(res.errors).join(', ');
                    }
                    toastr.error(msg);
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update');
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
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update');
            }
        });
    });
});

function uploadAttachments(files, index, btn) {
    if (index >= files.length) {
        toastr.success('Ticket updated');
        setTimeout(function() { window.location.href = site_url + '/tickets/' + token; }, 500);
        return;
    }
    var fd = new FormData();
    fd.append('images[]', files[index]);
    $.ajax({
        url: site_url + '/tickets/' + token + '/upload-attachment',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function() {
            uploadAttachments(files, index + 1, btn);
        },
        error: function(xhr) {
            var res = null;
            try { res = JSON.parse(xhr.responseText); } catch(e) {}
            if (res && res.redirect) {
                window.location.href = res.redirect;
                return;
            }
            toastr.warning('Some images failed to upload');
            uploadAttachments(files, index + 1, btn);
        }
    });
}

function removeAttachment(id, el) {
    if (!confirm('Remove this attachment?')) return;
    $.ajax({
        url: site_url + '/attachments/' + id + '/delete',
        type: 'POST',
        success: function(res) {
            if (res.status) {
                $(el).closest('.attachment-item').remove();
                toastr.success('Attachment removed');
            } else {
                toastr.error('Failed to remove');
            }
        },
        error: function(xhr) {
            var res = null;
            try { res = JSON.parse(xhr.responseText); } catch(e) {}
            if (res && res.redirect) {
                window.location.href = res.redirect;
                return;
            }
            toastr.error('Failed to remove');
        }
    });
}

// GLightbox init for edit page
var editLightbox = GLightbox({
    selector: '.edit-attachment-link',
    touchNavigation: true,
    keyboardNavigation: true,
    loop: false,
    preload: true
});
</script>
<?= $this->endSection() ?>
