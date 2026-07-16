/**
 * ============================================================================
 * Master Projects Edit
 * ============================================================================
 *
 * Description: Edit master project form with AJAX submission
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

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
