/**
 * ============================================================================
 * Improvements Edit
 * ============================================================================
 *
 * Edit improvement form with AJAX submission.
 *
 * Dependencies: jQuery, Bootstrap, Toastr, Select2
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const ImprovementEdit = {

    // ===========================
    // STATE
    // ===========================

    _token: '',

    // ===========================
    // UI
    // ===========================

    clearFieldErrors: function () {
        $('.field-error').removeClass('visible').text('');
        $('.sap-input, .sap-select').removeClass('is-invalid');
    },

    // ===========================
    // EVENTS
    // ===========================

    init: function () {
        var self = ImprovementEdit;
        var data = window.PageData || {};
        self._token = data.token || '';

        $('#userTypeSelect').select2({
            placeholder: 'Pilih target pengguna...',
            allowClear: true,
            width: '100%'
        });

        $('input[name="ada_data_dianalisa"]').on('change', function () {
            if ($(this).val() === '1') {
                $('#dataAnalisaSection').slideDown(200);
            } else {
                $('#dataAnalisaSection').slideUp(200);
                $('#dataAnalisaSection input').val('');
            }
        });

        $('[name="name"], [name="description"], [name="business_case"], [name="priority"]').on('input change', function () {
            $(this).removeClass('is-invalid');
            var fieldName = $(this).attr('name');
            $('#error-' + fieldName).removeClass('visible').text('');
        });

        $('#improvementForm').on('submit', function (e) {
            e.preventDefault();
            self.clearFieldErrors();
            var btn = $(this).find('[type="submit"]');
            btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Updating...');

            $.post(site_url + '/improvements/' + self._token + '/update', $(this).serialize(), function (res) {
                if (res.status) {
                    toastr.success('Improvement updated');
                    setTimeout(function () { window.location.href = site_url + '/improvements/' + self._token; }, 500);
                } else {
                    if (res.errors && typeof res.errors === 'object') {
                        Object.keys(res.errors).forEach(function (field) {
                            var input = $('[name="' + field + '"]');
                            if (input.length) {
                                input.addClass('is-invalid');
                                $('#error-' + field).text(res.errors[field]).addClass('visible');
                            }
                        });
                        toastr.error('Please fix the errors below');
                    } else {
                        toastr.error(res.message || 'Failed to update');
                    }
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update');
                }
            }).fail(function (xhr) {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
                if (res && res.redirect) {
                    window.location.href = res.redirect;
                    return;
                }
                var msg = res && res.message ? res.message : 'Request failed (HTTP ' + xhr.status + ')';
                toastr.error(msg);
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update');
            });
        });
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    ImprovementEdit.init();
});

// ===========================
// EXPORTS
// ===========================

window.ImprovementEdit = ImprovementEdit;
