<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:600px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('master-projects') ?>">Master Projects</a>
        <span class="sep">/</span>
        <a href="<?= site_url('master-projects/' . $project['id']) ?>"><?= esc($project['name']) ?></a>
        <span class="sep">/</span>
        <span class="active">Edit</span>
    </div>

    <h1 class="mb-4">Edit Master Project</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="projectForm">
                <div class="mb-3">
                    <label class="sap-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="sap-input" required placeholder="e.g., E-Commerce Platform" value="<?= esc($project['name']) ?>">
                </div>
                <div class="mb-3">
                    <label class="sap-label">Description</label>
                    <textarea name="description" class="sap-input" rows="4" placeholder="Brief description of the project..." style="min-height:100px"><?= esc($project['description'] ?? '') ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-save"></i> Save</button>
                    <a href="<?= site_url('master-projects/' . $project['id']) ?>" class="sap-btn sap-btn-secondary"><i class="fas fa-times"></i> Cancel</a>
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
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Saving...');

        $.post(window.location.href, $(this).serialize(), function(res) {
            if (res.status && res.redirect) {
                toastr.success('Project updated');
                setTimeout(function() { window.location.href = res.redirect; }, 500);
            } else {
                toastr.error(res.message || 'Failed to update');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
            }
        }).fail(function(xhr) {
            toastr.error('Gagal mengupdate project (HTTP ' + xhr.status + ')');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
        });
    });
});
</script>
<?= $this->endSection() ?>
