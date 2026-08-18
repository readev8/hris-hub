/**
 * ============================================================================
 * Blueprints Create
 * ============================================================================
 *
 * Create blueprint form with improvement selection modal and image upload
 *
 * Dependencies: jQuery, Bootstrap, Toastr, SweetAlert2
 * Date: 2026-08-18
 */

/* global $, site_url, toastr, sapBadge */

const BlueprintCreate = {

    // ===========================
    // API_ENDPOINTS
    // ===========================

    API_ENDPOINTS: {
        IMPROVEMENTS_LIST: function () { return site_url + '/improvements/ajax-list'; },
        CREATE: function () { return site_url + '/blueprints/create'; }
    },

    // ===========================
    // STATE
    // ===========================

    selectedImprovement: null,
    improvementTable: null,

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
        self.bindImprovementModal();
        self.bindDropzone();
        self.bindFileInput();
        self.bindFieldValidation();
        self.bindFormSubmit();
    },

    // ===========================
    // EVENT HANDLERS
    // ===========================

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
                            return '<button type="button" class="sap-btn sap-btn-primary sap-btn-sm" onclick=\'BlueprintCreate.selectImprovement(' + payload + ')\'>Select</button>';
                        }
                    }
                ],
                language: { emptyTable: 'No improvements found' },
                dom: 'rtip'
            });
        });
    },

    bindDropzone: function () {
        $('#dropzone').on('click', function () {
            $(this).find('input[type="file"]').click();
        }).on('dragover', function (e) {
            e.preventDefault();
            $(this).css('border-color', 'var(--sap-brand)').css('background', 'var(--sap-brand-hover)');
        }).on('dragleave', function () {
            $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
        }).on('drop', function (e) {
            e.preventDefault();
            $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
            var files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                var input = $(this).find('input[type="file"]')[0];
                input.files = files;
                $(input).trigger('change');
            }
        });

        $('#dropzone input[type="file"]').on('click', function (e) {
            e.stopPropagation();
        });
    },

    bindFileInput: function () {
        var selectedFiles = [];

        $('#blueprintForm input[name="images[]"]').on('change', function () {
            var newFiles = this.files;
            var maxSize = 500 * 1024;
            for (var i = 0; i < newFiles.length; i++) {
                if (newFiles[i].size > maxSize) {
                    toastr.warning(newFiles[i].name + ' exceeds 500KB limit');
                    continue;
                }
                var exists = selectedFiles.some(function (f) {
                    return f.name === newFiles[i].name && f.size === newFiles[i].size;
                });
                if (!exists) selectedFiles.push(newFiles[i]);
            }
            if (selectedFiles.length > 5) {
                toastr.warning('Maximum 5 files');
                selectedFiles = selectedFiles.slice(0, 5);
            }
            $(this).val('');
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
            btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Submitting...');

            var formData = new FormData(this);
            if (self.selectedImprovement) {
                formData.set('improvement_id', self.selectedImprovement.id);
            }

            $.ajax({
                url: self.API_ENDPOINTS.CREATE(),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status && res.redirect) {
                        toastr.success('Blueprint created');
                        setTimeout(function () { window.location.href = res.redirect; }, 500);
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
                            toastr.error(res.message || 'Failed to create');
                        }
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
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
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
                }
            });
        });
    }
};

window.BlueprintCreate = BlueprintCreate;

$(function () {
    BlueprintCreate.init();
});
