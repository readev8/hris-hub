<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:600px">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('master-projects') ?>">Master Projects</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>

    <h1 class="mb-4">Create Master Project</h1>

    <div class="card">
        <div class="card-body">
            <form id="projectForm">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g., E-Commerce Platform">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Brief description of the project..."></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
                    <a href="<?= site_url('master-projects') ?>" class="btn btn-outline-secondary"><i class="bi bi-x"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('#projectForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.post(site_url + '/master-projects/create', $(this).serialize(), function(res) {
            if (res.status && res.redirect) {
                toastr.success('Project created');
                setTimeout(function() { window.location.href = res.redirect; }, 500);
            } else {
                toastr.error(res.message || 'Failed to create');
                btn.prop('disabled', false).html('<i class="bi bi-save"></i> Save');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
