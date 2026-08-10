/**
 * ============================================================================
 * Improvements Create
 * ============================================================================
 *
 * Description: Create improvement form with file upload dropzone
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// STATE
// ===========================

var selectedFiles = [];

// ===========================
// UI
// ===========================

function renderPreview() {
    var preview = $('#filePreview');
    preview.empty();
    for (var i = 0; i < selectedFiles.length; i++) {
        var file = selectedFiles[i];
        var idx = i;
        var wrapper = $('<div style="position:relative;display:inline-block"></div>');
        if (file.type.startsWith('image/')) {
            (function(f, w, index) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    w.append('<img src="' + e.target.result + '" style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)">');
                    w.append('<button type="button" class="btn-remove-file" data-idx="' + index + '" style="position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center">&times;</button>');
                };
                reader.readAsDataURL(f);
            })(file, wrapper, idx);
        } else {
            var ext = file.name.split('.').pop().toLowerCase();
            var iconClass = 'fas fa-file';
            var iconColor = 'var(--sap-text-muted)';
            if (ext === 'pdf') { iconClass = 'fas fa-file-pdf'; iconColor = 'var(--sap-error)'; }
            else if (ext === 'xlsx' || ext === 'xls') { iconClass = 'fas fa-file-excel'; iconColor = '#217346'; }
            else if (ext === 'doc' || ext === 'docx') { iconClass = 'fas fa-file-word'; iconColor = '#2B579A'; }
            wrapper.append('<div style="padding:8px 12px;background:var(--sap-background);border-radius:6px;border:1px solid var(--sap-border);font-size:13px"><i class="' + iconClass + '" style="color:' + iconColor + ';margin-right:6px"></i>' + file.name + '</div>');
            wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + idx + '" style="position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center">&times;</button>');
        }
        preview.append(wrapper);
    }
}

function clearFieldErrors() {
    $('.field-error').removeClass('visible').text('');
    $('.sap-input, .sap-select').removeClass('is-invalid');
}

// ===========================
// EVENTS
// ===========================

$(function() {
    $('#userTypeSelect').select2({
        placeholder: 'Pilih target pengguna...',
        allowClear: true,
        width: '100%'
    });

    // Toggle conditional fields for "Ada Data Dianalisa?"
    $('input[name="ada_data_dianalisa"]').on('change', function() {
        if ($(this).val() === '1') {
            $('#dataAnalisaSection').slideDown(200);
        } else {
            $('#dataAnalisaSection').slideUp(200);
            $('#dataAnalisaSection input').val('');
        }
    });

    $('#dropzone').on('click', function() {
        $(this).find('input[type="file"]').click();
    }).on('dragover', function(e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--sap-brand)').css('background', 'var(--sap-brand-hover)');
    }).on('dragleave', function() {
        $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            var input = $(this).find('input[type="file"]')[0];
            input.files = files;
            $(input).trigger('change');
        }
    });

    $('#dropzone input[type="file"]').on('click', function(e) {
        e.stopPropagation();
    });

    $('#improvementForm input[name="images[]"]').on('change', function() {
        var newFiles = this.files;
        var maxSize = 500 * 1024;
        for (var i = 0; i < newFiles.length; i++) {
            if (newFiles[i].size > maxSize) {
                toastr.warning(newFiles[i].name + ' exceeds 500KB limit');
                continue;
            }
            var exists = selectedFiles.some(function(f) {
                return f.name === newFiles[i].name && f.size === newFiles[i].size;
            });
            if (!exists) {
                selectedFiles.push(newFiles[i]);
            }
        }
        if (selectedFiles.length > 5) {
            toastr.warning('Maximum 5 files');
            selectedFiles = selectedFiles.slice(0, 5);
        }
        renderPreview();
        $(this).val('');
    });

    $('#filePreview').on('click', '.btn-remove-file', function() {
        selectedFiles.splice($(this).data('idx'), 1);
        renderPreview();
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
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Submitting...');

        var formData = new FormData(this);
        for (var i = 0; i < selectedFiles.length; i++) {
            formData.append('images[]', selectedFiles[i]);
        }

        $.ajax({
            url: site_url + '/improvements/create',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status && res.redirect) {
                    toastr.success('Improvement created');
                    setTimeout(function() { window.location.href = res.redirect; }, 500);
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
                        toastr.error(res.message || 'Failed to create');
                    }
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
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
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
            }
        });
    });
});
