/**
 * ============================================================================
 * Blueprints Edit
 * ============================================================================
 *
 * Edit blueprint form with improvement selection and AJAX submission
 *
 * Dependencies: jQuery, Bootstrap, Toastr, SweetAlert2
 * Date: 2026-08-18
 */

/* global $, site_url, toastr, sapBadge */

const BlueprintEdit = {

    // ===========================
    // API_ENDPOINTS
    // ===========================

    API_ENDPOINTS: {
        IMPROVEMENTS_LIST: function () { return site_url + '/improvements/ajax-list'; },
        UPDATE: function (token) { return site_url + '/blueprints/' + token + '/update'; }
    },

    // ===========================
    // STATE
    // ===========================

    pageData: window.PageData || {},
    selectedImprovement: null,
    improvementTable: null,

    get token() {
        return this.pageData.token || '';
    },

    // ===========================
    // UI
    // ===========================

    openImprovementModal: function () {
        var modal = new bootstrap.Modal(document.getElementById('improvementSearchModal'));
        modal.show();
    },

    selectImprovement: function (data) {
        this.selectedImprovement = data;
        $('#selectedImprovementName').text(data.name);
        $('#selectedImprovementMeta').text(data.priority_name + ' | ' + (data.creator_name || '') + ' | ' + (data.created_at || ''));
        $('#improvementSelectedCard').show();
        $('#improvementHint').hide();
        $('#improvementDisplay').val(data.name);
        bootstrap.Modal.getInstance(document.getElementById('improvementSearchModal')).hide();
    },

    clearImprovement: function () {
        this.selectedImprovement = null;
        $('#improvementSelectedCard').hide();
        $('#improvementHint').show();
        $('#improvementDisplay').val('');
        $('#improvementId').val('');
    },

    clearFieldErrors: function () {
        $('.field-error').removeClass('visible').text('');
        $('.sap-input, .sap-select').removeClass('is-invalid');
    },

    // ===========================
    // INITIALIZATION
    // ===========================

    init: function () {
        var self = this;
        self.prefillImprovement();
        self.bindImprovementModal();
        self.bindFieldValidation();
        self.bindFormSubmit();
    },

    // ===========================
    // EVENT HANDLERS
    // ===========================

    prefillImprovement: function () {
        if (this.pageData.currentImprovementId && this.pageData.currentImprovementName) {
            $('#improvementDisplay').val(this.pageData.currentImprovementName);
            $('#improvementHint').hide();
        }
    },

    bindImprovementModal: function () {
        var self = this;
        $('#improvementSearchModal').on('shown.bs.modal', function () {
            if (self.improvementTable) return;

            self.improvementTable = $('#improvementSearchTable').DataTable({
                processing: true,
                ajax: {
                    url: self.API_ENDPOINTS.IMPROVEMENTS_LIST(),
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
                            var payload = JSON.stringify({
                                id: row.id,
                                name: row.name,
                                priority_name: row.priority_name,
                                creator_name: row.creator_name,
                                created_at: row.created_at
                            });
                            return '<button type="button" class="sap-btn sap-btn-primary sap-btn-sm" onclick=\'BlueprintEdit.selectImprovement(' + payload + ')\'>Select</button>';
                        }
                    }
                ],
                language: { emptyTable: 'No improvements found' },
                dom: 'rtip'
            });
        });
    },

    bindFieldValidation: function () {
        $('[name="name"], [name="description"], [name="actors"], [name="pre_condition"], [name="post_condition"], [name="normal_course"], [name="exception"], [name="frequency"], [name="notes"], [name="issue"]').on('input change', function () {
            $(this).removeClass('is-invalid');
            $('#error-' + $(this).attr('name')).removeClass('visible').text('');
        });
    },

    bindFormSubmit: function () {
        var self = this;
        $('#blueprintForm').on('submit', function (e) {
            e.preventDefault();
            self.clearFieldErrors();
            var btn = $(this).find('[type="submit"]');
            btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Updating...');

            var formData = new FormData(this);
            if (self.selectedImprovement) {
                formData.set('improvement_id', self.selectedImprovement.id);
            } else {
                formData.set('improvement_id', $('#improvementId').val() || '');
            }

            $.ajax({
                url: self.API_ENDPOINTS.UPDATE(self.token),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status) {
                        toastr.success('Blueprint updated');
                        setTimeout(function () { window.location.href = site_url + '/blueprints/' + self.token; }, 500);
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
    }
};

window.BlueprintEdit = BlueprintEdit;

$(function () {
    BlueprintEdit.init();
});
