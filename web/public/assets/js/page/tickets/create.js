/**
 * ============================================================================
 * Tickets Create
 * ============================================================================
 *
 * Description: Ticket creation form with cascading dropdowns, file upload, segmented control
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// STATE
// ===========================

var projectsCache = null;
var activeModuleReq = null;
var activePageReq = null;

// ===========================
// EVENTS
// ===========================

$(function() {
    // Segmented control
    $('#prioritySegments .seg-option').on('click', function() {
        $('#prioritySegments .seg-option').removeClass('active');
        $(this).addClass('active');
        $('#priorityValue').val($(this).data('value'));
    });

    // Approval checkbox - show/hide approver dropdown
    $('#needsApproval').on('change', function() {
        if ($(this).is(':checked')) {
            $('#approverSection').slideDown(200);
        } else {
            $('#approverSection').slideUp(200);
            $('#approverSelect').val('');
        }
    });

    // Type change - show/hide bug trace section
    $('#ticketType').on('change', function() {
        if (['0','3','4','5'].includes($(this).val())) {
            $('#bugTraceSection').slideDown(200);
            loadProjects();
        } else {
            $('#bugTraceSection').slideUp(200);
            $('#pageIdValue').val('');
            $('#projectSelect').val('');
            $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
        }
    });

    // Cascading dropdowns
    $('#projectSelect').on('change', function() {
        var pid = $(this).val();
        if (!pid) {
            $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            $('#pageIdValue').val('');
            return;
        }
        if (activeModuleReq) activeModuleReq.abort();
        $('#moduleSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        activeModuleReq = $.ajax({
            url: site_url + '/master-projects/' + pid + '/modules',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                if (!Array.isArray(res)) { $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', false); return; }
                var html = '<option value="">Select Module...</option>';
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
                }
                $('#moduleSelect').html(html).prop('disabled', false);
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
                $('#pageIdValue').val('');
            },
            error: function() {
                toastr.error('Failed to load modules');
                $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', false);
            }
        });
    });

    $('#moduleSelect').on('change', function() {
        var mid = $(this).val();
        if (!mid) {
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            $('#pageIdValue').val('');
            return;
        }
        if (activePageReq) activePageReq.abort();
        $('#pageSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        activePageReq = $.ajax({
            url: site_url + '/modules/' + mid + '/pages',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                var pages = Array.isArray(res) ? res : (res && Array.isArray(res.pages)) ? res.pages : [];
                if (!pages.length) { $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', false); return; }
                var html = '<option value="">Select Page...</option>';
                for (var i = 0; i < pages.length; i++) {
                    html += '<option value="' + pages[i].id + '">' + pages[i].name + '</option>';
                }
                $('#pageSelect').html(html).prop('disabled', false);
            },
            error: function() {
                toastr.error('Failed to load pages');
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', false);
            }
        });
    });

    $('#pageSelect').on('change', function() {
        $('#pageIdValue').val($(this).val());
    });

    // File dropzone
    $('#dropzone').on('dragover', function(e) {
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

    $('#ticketForm input[name="images[]"]').on('change', function() {
        var preview = $('#imagePreview');
        preview.empty();
        var files = this.files;
        if (files.length > 3) {
            toastr.warning('Maximum 3 images');
            $(this).val('');
            return;
        }
        for (var i = 0; i < files.length && i < 3; i++) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.append('<img src="' + e.target.result + '" style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)">');
            };
            reader.readAsDataURL(files[i]);
        }
    });

    // Form submission
    $('#ticketForm').on('submit', function(e) {
        e.preventDefault();
        var type = $('#ticketType').val();
        var pageId = $('#pageIdValue').val();
        if (['0','3','4','5'].includes(type) && !pageId) {
            toastr.warning('Untuk tipe ini, wajib memilih halaman di bagian Affected Page');
            $('#bugTraceSection').slideDown(200);
            return;
        }
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Submitting...');
        var formData = new FormData(this);
        $.ajax({
            url: site_url + '/tickets/create',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status && res.redirect) {
                    var msg = 'Ticket created successfully';
                    if (res.tracking_code) {
                        msg += '\nTracking code: ' + res.tracking_code;
                    }
                    toastr.success(msg, '', { timeOut: 5000 });
                    setTimeout(function() { window.location.href = res.redirect; }, 1500);
                } else {
                    var msg = res.message || 'Failed to create ticket';
                    if (res.errors && typeof res.errors === 'object') {
                        var details = Object.values(res.errors).join(', ');
                        msg += ': ' + details;
                    }
                    toastr.error(msg);
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

// ===========================
// HELPERS
// ===========================

function loadProjects() {
    if (projectsCache) {
        populateProjects(projectsCache);
        return;
    }
    $('#projectSelect').prop('disabled', true).html('<option value="">Loading...</option>');
    $.ajax({
        url: site_url + '/master-projects/active',
        type: 'GET',
        timeout: 10000,
        success: function(res) {
            if (!Array.isArray(res)) { $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false); return; }
            projectsCache = res;
            populateProjects(res);
        },
        error: function() {
            toastr.error('Failed to load projects');
            $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
            $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
        }
    });
}

function populateProjects(res) {
    var html = '<option value="">Select Project...</option>';
    for (var i = 0; i < res.length; i++) {
        html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
    }
    $('#projectSelect').html(html).prop('disabled', false);
    $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
    $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
    $('#pageIdValue').val('');
}
