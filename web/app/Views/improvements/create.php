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
                    <div class="mb-3">
                        <label class="sap-label">Priority</label>
                        <select name="priority" class="sap-select">
                            <option value="0">Low</option>
                            <option value="1" selected>Medium</option>
                            <option value="2">High</option>
                            <option value="3">Critical</option>
                        </select>
                    </div>
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
    $('#improvementForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Submitting...');

        $.post(site_url + '/improvements/create', $(this).serialize(), function(res) {
            if (res.status && res.redirect) {
                toastr.success('Improvement created');
                setTimeout(function() { window.location.href = res.redirect; }, 500);
            } else {
                toastr.error(res.message || 'Failed to create');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
