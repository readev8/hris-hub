/**
 * ============================================================================
 * Master Projects Edit
 * ============================================================================
 *
 * Edit master project form with AJAX submission.
 *
 * Dependencies: jQuery, Bootstrap, Toastr
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const MasterProjectEdit = {

    // ===========================
    // EVENTS
    // ===========================

    init: function () {
        $('#projectForm').on('submit', this._handleSubmit.bind(this));
    },

    _handleSubmit: function (e) {
        e.preventDefault();
        var $form = $(e.currentTarget);
        var $btn = $form.find('[type="submit"]');
        $btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Saving...');

        $.post(window.location.href, $form.serialize(), function (res) {
            if (res.status && res.redirect) {
                toastr.success('Project updated');
                setTimeout(function () { window.location.href = res.redirect; }, 500);
            } else {
                toastr.error(res.message || 'Failed to update');
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
            }
        }).fail(function (xhr) {
            toastr.error('Failed to update project (HTTP ' + xhr.status + ')');
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
        });
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    MasterProjectEdit.init();
});

// ===========================
// EXPORTS
// ===========================

window.MasterProjectEdit = MasterProjectEdit;
