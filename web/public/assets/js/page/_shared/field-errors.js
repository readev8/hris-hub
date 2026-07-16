/**
 * ============================================================================
 * Shared Field Errors
 * ============================================================================
 *
 * Description: Reusable field error display/clear helpers for forms
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

function clearFieldErrors() {
    $('.field-error').removeClass('visible').text('');
    $('.sap-input, .sap-select').removeClass('is-invalid');
}

function showFieldError(field, message) {
    var input = $('[name="' + field + '"]');
    if (input.length) {
        input.addClass('is-invalid');
        $('#error-' + field).text(message).addClass('visible');
    }
}

function showMultipleErrors(errors) {
    if (errors && typeof errors === 'object') {
        Object.keys(errors).forEach(function(field) {
            showFieldError(field, errors[field]);
        });
    }
}
