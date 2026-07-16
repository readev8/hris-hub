/**
 * ============================================================================
 * Master Projects Create
 * ============================================================================
 *
 * Description: Create master project form with AJAX submission
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

$(function() {
    $('#projectForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Saving...');

        $.post(site_url + '/master-projects/create', $(this).serialize(), function(res) {
            if (res.status && res.redirect) {
                toastr.success('Project created');
                window.AppEvent.dispatch('project:created', { id: res.id, name: res.name });
                setTimeout(function() { window.location.href = res.redirect; }, 500);
            } else {
                toastr.error(res.message || 'Failed to create');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
            }
        }).fail(function(xhr) {
            toastr.error('Gagal membuat project (HTTP ' + xhr.status + ')');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
        });
    });
});
