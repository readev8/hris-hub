<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:900px">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('improvements') ?>">Improvements</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>

    <h1 class="mb-4">Create Improvement</h1>

    <div class="card">
        <div class="card-body">
            <form id="improvementForm">
                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--border)">
                    <h5 class="mb-3" style="color:var(--text-meta);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="bi bi-info-circle me-1"></i> General Information
                    </h5>
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Implement SSO Login">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="5" required placeholder="Describe the improvement in detail..."></textarea>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--border)">
                    <h5 class="mb-3" style="color:var(--text-meta);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="bi bi-graph-up me-1"></i> Business Details
                    </h5>
                    <div class="mb-3">
                        <label class="form-label">Business Case <span class="text-danger">*</span></label>
                        <textarea name="business_case" class="form-control" rows="3" required placeholder="Why is this improvement needed? What value will it bring?"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="0">Low</option>
                            <option value="1" selected>Medium</option>
                            <option value="2">High</option>
                            <option value="3">Critical</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Submit
                    </button>
                    <a href="<?= site_url('improvements') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Cancel
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
    $('#improvementForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting...');

        $.post(site_url + '/improvements/create', $(this).serialize(), function(res) {
            if (res.status && res.redirect) {
                toastr.success('Improvement created');
                setTimeout(function() { window.location.href = res.redirect; }, 500);
            } else {
                toastr.error(res.message || 'Failed to create');
                btn.prop('disabled', false).html('<i class="bi bi-send"></i> Submit');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
