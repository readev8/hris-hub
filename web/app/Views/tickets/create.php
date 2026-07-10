<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:800px">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('tickets') ?>">Tickets</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>

    <h1 class="mb-4">Create Ticket</h1>

    <div class="card">
        <div class="card-body">
            <form id="ticketForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required minlength="5" placeholder="Brief description of the issue or request">
                    <div class="form-text">Minimal 5 characters</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="6" required placeholder="Detailed description including steps to reproduce for bugs..."></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" id="ticketType">
                            <option value="2">Task</option>
                            <option value="1">Issue</option>
                            <option value="0">Bug</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Priority</label>
                        <div class="segmented-control" id="prioritySegments">
                            <button type="button" class="seg-option" data-value="0">Low</button>
                            <button type="button" class="seg-option active" data-value="1">Medium</button>
                            <button type="button" class="seg-option" data-value="2">High</button>
                            <button type="button" class="seg-option" data-value="3">Critical</button>
                        </div>
                        <input type="hidden" name="priority" id="priorityValue" value="1">
                    </div>
                </div>

                <div class="mb-4" id="bugTraceSection" style="display:none">
                    <h5 class="mb-3" style="color:var(--text-meta);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="bi bi-diagram-3 me-1"></i> Bug Location <span class="text-danger">*</span>
                    </h5>
                    <p class="text-meta" style="font-size:13px">Select the page where this bug was found</p>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select class="form-select" id="projectSelect">
                                <option value="">Select Project...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="moduleSelect" disabled>
                                <option value="">Select Module...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="pageSelect" disabled>
                                <option value="">Select Page...</option>
                            </select>
                            <input type="hidden" name="page_id" id="pageIdValue">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Images <span class="text-muted" style="font-weight:400">(optional, max 3, JPG/PNG, max 2MB each)</span></label>
                    <div style="border:2px dashed var(--border);border-radius:var(--radius-sm);padding:24px;text-align:center;transition:border-color var(--transition);cursor:pointer" id="dropzone">
                        <i class="bi bi-cloud-upload" style="font-size:32px;color:var(--text-muted);display:block;margin-bottom:8px"></i>
                        <p class="mb-0 text-meta" style="font-size:13px">Drop images here or click to browse</p>
                        <input type="file" name="images[]" accept="image/jpeg,image/png" multiple hidden>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2" id="imagePreview"></div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Submit
                    </button>
                    <a href="<?= site_url('tickets') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.segmented-control .seg-option[data-value="0"]:hover { background: #D1FAE5; color: #065F46; }
.segmented-control .seg-option[data-value="0"].active { background: #10B981; color: #fff; border-color: #10B981; }
.segmented-control .seg-option[data-value="2"]:hover { background: #FEF3C7; color: #92400E; }
.segmented-control .seg-option[data-value="2"].active { background: #F59E0B; color: #fff; border-color: #F59E0B; }
.segmented-control .seg-option[data-value="3"]:hover { background: #FEF2F2; color: #991B1B; }
.segmented-control .seg-option[data-value="3"].active { background: #E11D48; color: #fff; border-color: #E11D48; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    // Segmented control for priority
    $('#prioritySegments .seg-option').on('click', function() {
        $('#prioritySegments .seg-option').removeClass('active');
        $(this).addClass('active');
        $('#priorityValue').val($(this).data('value'));
    });

    // Type change - show/hide bug location
    $('#ticketType').on('change', function() {
        if ($(this).val() === '0') {
            $('#bugTraceSection').slideDown(200);
            loadProjects();
        } else {
            $('#bugTraceSection').slideUp(200);
            $('#pageIdValue').val('');
        }
    });

    // Cascading dropdowns
    function loadProjects() {
        $('#projectSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        $.get(site_url + '/master-projects/active', function(res) {
            var html = '<option value="">Select Project...</option>';
            for (var i = 0; i < res.length; i++) {
                html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
            }
            $('#projectSelect').html(html).prop('disabled', false);
            $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            $('#pageIdValue').val('');
        });
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
        $.get(site_url + '/master-projects/' + pid + '/modules', function(res) {
            var html = '<option value="">Select Module...</option>';
            for (var i = 0; i < res.length; i++) {
                html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
            }
            $('#moduleSelect').html(html).prop('disabled', false);
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            $('#pageIdValue').val('');
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
        $.get(site_url + '/modules/' + mid + '/pages', function(res) {
            var html = '<option value="">Select Page...</option>';
            for (var i = 0; i < res.length; i++) {
                html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
            }
            $('#pageSelect').html(html).prop('disabled', false);
            $('#pageIdValue').val('');
        });
    });

    $('#pageSelect').on('change', function() {
        $('#pageIdValue').val($(this).val());
    });

    // Dropzone click
    $('#dropzone').on('click', function() {
        $(this).find('input[type="file"]').click();
    }).on('dragover', function(e) {
        e.preventDefault();
        $(this).css('border-color', '#0F4C81').css('background', 'var(--primary-light)');
    }).on('dragleave', function() {
        $(this).css('border-color', 'var(--border)').css('background', 'transparent');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--border)').css('background', 'transparent');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            var input = $(this).find('input[type="file"]')[0];
            input.files = files;
            $(input).trigger('change');
        }
    });

    // Image preview
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
                preview.append('<img src="' + e.target.result + '" style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">');
            };
            reader.readAsDataURL(files[i]);
        }
    });

    // Form submit with validation
    $('#ticketForm').on('submit', function(e) {
        e.preventDefault();

        var type = $('#ticketType').val();
        var pageId = $('#pageIdValue').val();

        if (type === '0' && !pageId) {
            toastr.warning('For Bug type, please select the affected page in Bug Location section');
            $('#bugTraceSection').slideDown(200);
            return;
        }

        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting...');

        var formData = new FormData(this);

        $.ajax({
            url: site_url + '/tickets/create',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status && res.redirect) {
                    toastr.success('Ticket created successfully');
                    setTimeout(function() { window.location.href = res.redirect; }, 500);
                } else {
                    toastr.error(res.message || 'Failed to create ticket');
                    btn.prop('disabled', false).html('<i class="bi bi-send"></i> Submit');
                }
            },
            error: function() {
                toastr.error('Request failed');
                btn.prop('disabled', false).html('<i class="bi bi-send"></i> Submit');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
