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
        <a href="<?= site_url('blueprints') ?>">Blueprints</a>
        <span class="sep">/</span>
        <a href="<?= site_url('blueprints/' . $token) ?>"><?= esc($blueprint['name'] ?? '') ?></a>
        <span class="sep">/</span>
        <span class="active">Edit</span>
    </div>

    <h1 class="mb-4">Edit Blueprint</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="blueprintForm">
                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-info-circle me-1"></i> Blueprint Details
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="sap-input" required value="<?= esc($blueprint['name'] ?? '') ?>" placeholder="e.g., SSO Login Implementation">
                        <div class="field-error" id="error-name" role="alert"></div>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" class="sap-input" rows="5" placeholder="Describe the blueprint scope and objectives..." style="min-height:120px"><?= esc($blueprint['description'] ?? '') ?></textarea>
                        <div class="field-error" id="error-description" role="alert"></div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="<?= site_url('blueprints/' . $token) ?>" class="sap-btn sap-btn-secondary">
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

$(function() {
    function clearFieldErrors() {
        $('.field-error').removeClass('visible').text('');
        $('.sap-input, .sap-select').removeClass('is-invalid');
    }

    $('[name="name"], [name="description"]').on('input change', function() {
        $(this).removeClass('is-invalid');
        var fieldName = $(this).attr('name');
        $('#error-' + fieldName).removeClass('visible').text('');
    });

    $('#blueprintForm').on('submit', function(e) {
        e.preventDefault();
        clearFieldErrors();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Updating...');

        $.ajax({
            url: site_url + '/blueprints/' + token + '/update',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    toastr.success('Blueprint updated');
                    setTimeout(function() { window.location.href = site_url + '/blueprints/' + token; }, 500);
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
                        toastr.error(res.message || 'Failed to update blueprint');
                    }
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
</script>
<?= $this->endSection() ?>
