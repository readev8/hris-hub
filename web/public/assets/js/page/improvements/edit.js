/**
 * ============================================================================
 * Improvements Edit
 * ============================================================================
 *
 * Description: Edit improvement form with AJAX submission
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// UI
// ===========================

function clearFieldErrors() {
    $('.field-error').removeClass('visible').text('');
    $('.sap-input, .sap-select').removeClass('is-invalid');
}

// ===========================
// EVENTS
// ===========================

$(function() {
    var data = window.PageData || {};

    $('#userTypeSelect').select2({
        placeholder: 'Pilih target pengguna...',
        allowClear: true,
        width: '100%'
    });

    $('[name="name"], [name="description"], [name="business_case"], [name="priority"]').on('input change', function() {
        $(this).removeClass('is-invalid');
        var fieldName = $(this).attr('name');
        $('#error-' + fieldName).removeClass('visible').text('');
    });

    $('#improvementForm').on('submit', function(e) {
        e.preventDefault();
        clearFieldErrors();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Updating...');

        $.ajax({
            url: site_url + '/improvements/' + data.token + '/update',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    toastr.success('Improvement updated');
                    setTimeout(function() { window.location.href = site_url + '/improvements/' + data.token; }, 500);
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
                        toastr.error(res.message || 'Failed to update');
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
