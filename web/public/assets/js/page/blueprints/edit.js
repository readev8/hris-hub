/**
 * ============================================================================
 * Blueprints Edit
 * ============================================================================
 *
 * Description: Edit blueprint form with improvement selection and AJAX submission
 * Date: 2026-07-17
 * Standard: Mini (<400 lines)
 */

// ===========================
// STATE
// ===========================

var pageData = window.PageData || {};
var token = pageData.token || '';
var selectedImprovement = null;
var improvementTable = null;

// ===========================
// UI
// ===========================

function openImprovementModal() {
    var modal = new bootstrap.Modal(document.getElementById('improvementSearchModal'));
    modal.show();
}

function selectImprovement(data) {
    selectedImprovement = data;
    $('#selectedImprovementName').text(data.name);
    $('#selectedImprovementMeta').text(data.priority_name + ' | ' + (data.creator_name || '') + ' | ' + (data.created_at || ''));
    $('#improvementSelectedCard').show();
    $('#improvementHint').hide();
    $('#improvementDisplay').val(data.name);
    bootstrap.Modal.getInstance(document.getElementById('improvementSearchModal')).hide();
}

function clearImprovement() {
    selectedImprovement = null;
    $('#improvementSelectedCard').hide();
    $('#improvementHint').show();
    $('#improvementDisplay').val('');
    $('#improvementId').val('');
}

function clearFieldErrors() {
    $('.field-error').removeClass('visible').text('');
    $('.sap-input, .sap-select').removeClass('is-invalid');
}

// ===========================
// INIT
// ===========================

$(function () {
    // Pre-fill improvement display if already linked
    if (pageData.currentImprovementId && pageData.currentImprovementName) {
        $('#improvementDisplay').val(pageData.currentImprovementName);
        $('#improvementHint').hide();
    }

    // Bind improvement search modal
    $('#improvementSearchModal').on('shown.bs.modal', function () {
        if (improvementTable) return;

        improvementTable = $('#improvementSearchTable').DataTable({
            processing: true,
            ajax: {
                url: site_url + '/improvements/ajax-list',
                dataSrc: 'data'
            },
            columns: [
                { data: 'name' },
                { data: 'status_name', render: function (d) { return sapBadge(d); } },
                { data: 'priority_name', render: function (d) { return sapBadge(d); } },
                { data: 'creator_name' },
                { data: 'created_at' },
                {
                    data: 'id',
                    orderable: false,
                    render: function (d, t, row) {
                        return '<button type="button" class="sap-btn sap-btn-primary sap-btn-sm" onclick=\'selectImprovement(' + JSON.stringify({ id: row.id, name: row.name, priority_name: row.priority_name, creator_name: row.creator_name, created_at: row.created_at }) + ')\'>Select</button>';
                    }
                }
            ],
            language: { emptyTable: 'No improvements found' },
            dom: 'rtip'
        });
    });

    // Clear field errors on input
    $('[name="name"], [name="description"], [name="actors"], [name="pre_condition"], [name="post_condition"], [name="normal_course"], [name="exception"], [name="frequency"], [name="notes"], [name="issue"]').on('input change', function () {
        $(this).removeClass('is-invalid');
        $('#error-' + $(this).attr('name')).removeClass('visible').text('');
    });

    // Form submission
    $('#blueprintForm').on('submit', function (e) {
        e.preventDefault();
        clearFieldErrors();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Updating...');

        var formData = new FormData(this);
        if (selectedImprovement) {
            formData.set('improvement_id', selectedImprovement.id);
        } else {
            formData.set('improvement_id', $('#improvementId').val() || '');
        }

        $.ajax({
            url: site_url + '/blueprints/' + token + '/update',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status) {
                    toastr.success('Blueprint updated');
                    setTimeout(function () { window.location.href = site_url + '/blueprints/' + token; }, 500);
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
            },
            error: function (xhr) {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
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
