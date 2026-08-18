/**
 * ============================================================================
 * Shared Field Errors
 * ============================================================================
 *
 * Reusable field error display/clear helpers for forms. Provides
 * showMultipleErrors for bulk validation response handling.
 *
 * Dependencies: jQuery
 * Date: 2026-08-18
 */

// ===========================
// PUBLIC API
// ===========================

function clearFieldErrors() {
    $('.field-error').removeClass('visible').text('');
    $('.sap-input, .sap-select').removeClass('is-invalid');
}

function showFieldError(field, message) {
    const input = $('[name="' + field + '"]');
    if (input.length) {
        input.addClass('is-invalid');
        $('#error-' + field).text(message).addClass('visible');
    }
}

function showMultipleErrors(errors) {
    if (errors && typeof errors === 'object') {
        Object.keys(errors).forEach(function (field) {
            showFieldError(field, errors[field]);
        });
    }
}

// ===========================
// WINDOW EXPORTS
// ===========================

window.clearFieldErrors = clearFieldErrors;
window.showFieldError = showFieldError;
window.showMultipleErrors = showMultipleErrors;
