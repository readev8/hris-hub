/**
 * ============================================================================
 * Blueprints Page Specifications
 * ============================================================================
 *
 * Modal-based specification CRUD with UX image upload
 *
 * Dependencies: jQuery, Bootstrap, Toastr, SweetAlert2
 * Date: 2026-08-18
 */

/* global $, site_url, toastr, Swal */

const PageSpecs = {

    // ===========================
    // API_ENDPOINTS
    // ===========================

    API_ENDPOINTS: {
        DELETE: function (specId) { return site_url + '/blueprints/page-specifications/' + specId + '/delete'; },
        UPDATE: function (specId) { return site_url + '/blueprints/page-specifications/' + specId + '/update'; },
        CREATE: function (token) { return site_url + '/blueprints/design-pages/' + token + '/page-specifications'; }
    },

    // ===========================
    // STATE
    // ===========================

    data: window.PageData || {},
    uxFile: null,
    uxRemoveFlag: false,
    isEditMode: false,

    get pageData() {
        return this.data.designPage || {};
    },

    get baseUrl() {
        return site_url + '/uploads/blueprints/';
    },

    get token() {
        return this.data.token || '';
    },

    // ===========================
    // INITIALIZATION
    // ===========================

    init: function () {
        this.bindSpecUxDropzone();
        this.initFormHandlers();
        this.initModalDismiss();
    },

    // ===========================
    // PUBLIC API
    // ===========================

    showAddSpec: function () {
        this.isEditMode = false;
        this.uxFile = null;
        this.uxRemoveFlag = false;
        $('#specForm')[0].reset();
        $('#specFormId').val('');
        $('#specFormExistingAttId').val('');
        $('#specModalTitle').html('<i class="fas fa-list-alt me-2"></i>Add Page Specification');
        $('#specSubmitText').text('Save');
        this.renderUxPreview();
        $('#specModal').modal('show');
    },

    editSpec: function (el) {
        this.isEditMode = true;
        this.uxFile = null;
        this.uxRemoveFlag = false;
        var row = $(el).closest('tr');
        var specId = row.data('spec-id');
        var spec = this.findSpec(specId);
        if (!spec) { toastr.error('Specification not found'); return; }

        $('#specFormId').val(specId);
        $('#specFormExistingAttId').val(spec.ux_attachment ? spec.ux_attachment.id : '');
        $('#specFieldNameInput').val(spec.field_name || '');
        $('#specDataInput').val(spec.data || '');
        $('#specObjectiveInput').val(spec.objective || '');
        $('#specInitialDataInput').val(spec.initial_data || '');
        $('#specConditionInput').val(spec.condition || '');
        $('#specValidationInput').val(spec.validation || '');
        $('#specInputDisplayInput').val(spec.input_display || 'Input');
        $('#specDatatypeInput').val(spec.datatype || 'text');
        $('#specControlTypeInput').val(spec.control_type || 'text');

        $('#specModalTitle').html('<i class="fas fa-pencil-alt me-2"></i>Edit Specification');
        $('#specSubmitText').text('Update');
        this.renderUxPreview();
        $('#specModal').modal('show');
    },

    deleteSpec: function (el) {
        var self = this;
        var row = $(el).closest('tr');
        var specId = row.data('spec-id');
        Swal.fire({
            title: 'Delete Specification?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post(self.API_ENDPOINTS.DELETE(specId), {}, function (res) {
                    if (res.status) {
                        toastr.success('Specification deleted');
                        row.fadeOut(300, function () {
                            $(this).remove();
                            self.updateSpecCount();
                        });
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                }).fail(function () { toastr.error('Request failed'); });
            }
        });
    },

    enlargeUx: function (img) {
        var src = $(img).data('full') || $(img).attr('src');
        $('#uxEnlargedImg').attr('src', src);
        $('#uxEnlargedModal').modal('show');
    },

    removeUxFile: function () {
        this.uxFile = null;
        this.uxRemoveFlag = true;
        $('#specFormExistingAttId').val('');
        this.renderUxPreview();
    },

    // ===========================
    // PRIVATE HELPERS
    // ===========================

    findSpec: function (encryptedId) {
        var specs = this.pageData.page_specifications || [];
        for (var i = 0; i < specs.length; i++) {
            if ((specs[i].id_encrypted || specs[i].id) === encryptedId) {
                return specs[i];
            }
        }
        return null;
    },

    updateSpecCount: function () {
        var count = $('#specsContainer tbody tr').length;
        $('#specCountBadge').text(count + ' field' + (count !== 1 ? 's' : ''));
        if (count === 0) {
            $('#specsContainer').html(GlobalSanitize.sanitizeHtml(
                '<div class="sap-empty" style="padding:40px"><i class="fas fa-list-alt" style="font-size:36px"></i><h4>No specifications</h4><p>Add page specifications for this design page.</p></div>'
            ));
        }
    },

    // ===========================
    // UX IMAGE UPLOAD
    // ===========================

    bindSpecUxDropzone: function () {
        var self = this;
        var $zone = $('#specUxDropzone');
        var $file = $('#specUxFile');

        $zone.on('click', function (e) {
            if (e.target !== this && !$(e.target).is('p, i')) return;
            $file.click();
        }).on('dragover', function (e) {
            e.preventDefault();
            $zone.addClass('dragover');
        }).on('dragleave', function () {
            $zone.removeClass('dragover');
        }).on('drop', function (e) {
            e.preventDefault();
            $zone.removeClass('dragover');
            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) { self.setUxFile(files[0]); }
        });

        $file.on('change', function () {
            if (this.files.length > 0) { self.setUxFile(this.files[0]); }
            $(this).val('');
        });
    },

    setUxFile: function (file) {
        if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
            toastr.error('Only JPG, PNG, WebP images are allowed');
            return;
        }
        if (file.size > 500 * 1024) {
            toastr.error('Image size must be under 500KB');
            return;
        }
        this.uxFile = file;
        this.uxRemoveFlag = false;
        this.renderUxPreview();
    },

    renderUxPreview: function () {
        var $preview = $('#specUxPreview');
        $preview.empty();

        if (this.uxFile) {
            var reader = new FileReader();
            var self = this;
            reader.onload = function (e) {
                $preview.html(
                    '<div class="spec-ux-preview">' +
                    '<img src="' + e.target.result + '" alt="Preview">' +
                    '<button type="button" class="btn-remove" onclick="PageSpecs.removeUxFile()"><i class="fas fa-times"></i></button>' +
                    '</div>'
                );
            };
            reader.readAsDataURL(this.uxFile);
        } else if (this.isEditMode && !this.uxRemoveFlag) {
            var specId = $('#specFormId').val();
            var spec = this.findSpec(specId);
            if (spec && spec.ux_attachment) {
                var url = this.baseUrl + spec.ux_attachment.stored_name;
                $preview.html(
                    '<div class="spec-ux-preview">' +
                    '<img src="' + url + '" alt="Current UX">' +
                    '<button type="button" class="btn-remove" onclick="PageSpecs.removeUxFile()"><i class="fas fa-times"></i></button>' +
                    '</div>'
                );
            }
        }
    },

    // ===========================
    // FORM SUBMIT
    // ===========================

    serializeFormToFD: function (form) {
        var fd = new FormData();
        fd.append('blueprint_token', $(form).find('[name="blueprint_token"]').val());

        var fields = ['field_name', 'data', 'objective', 'initial_data', 'condition', 'validation', 'input_display', 'datatype', 'control_type'];
        for (var i = 0; i < fields.length; i++) {
            var val = $(form).find('[name="' + fields[i] + '"]').val();
            if (val !== undefined && val !== null) fd.append(fields[i], val);
        }

        var specId = $(form).find('#specFormId').val();
        if (specId) fd.append('spec_id', specId);

        if (this.uxFile) {
            fd.append('ux_image[]', this.uxFile);
        }

        if (this.isEditMode && this.uxRemoveFlag) {
            fd.append('remove_ux', '1');
            var existingAttId = $(form).find('#specFormExistingAttId').val();
            if (existingAttId) fd.append('existing_ux_att_id', existingAttId);
        }

        return fd;
    },

    initFormHandlers: function () {
        var self = this;
        $('#specForm').on('submit', function (e) {
            e.preventDefault();
            var specId = $('#specFormId').val();
            var url = self.isEditMode
                ? self.API_ENDPOINTS.UPDATE(specId)
                : self.API_ENDPOINTS.CREATE(self.token);

            var fd = self.serializeFormToFD(this);

            var $btn = $(this).find('[type="submit"]');
            $btn.prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status) {
                        toastr.success(self.isEditMode ? 'Specification updated' : 'Specification added');
                        $('#specModal').modal('hide');
                        location.reload();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                },
                error: function () { toastr.error('Request failed'); },
                complete: function () { $btn.prop('disabled', false); }
            });
        });
    },

    // ===========================
    // MODAL CLEANUP
    // ===========================

    initModalDismiss: function () {
        $('#specModal, #uxEnlargedModal').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        });
    }
};

window.PageSpecs = PageSpecs;
window.showAddSpec = PageSpecs.showAddSpec;
window.editSpec = PageSpecs.editSpec;
window.deleteSpec = PageSpecs.deleteSpec;
window.enlargeUx = PageSpecs.enlargeUx;

$(function () {
    PageSpecs.init();
});
