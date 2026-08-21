/**
 * ============================================================================
 * Tickets Edit
 * ============================================================================
 *
 * Description: Ticket edit page with cascading project/module/page dropdowns,
 * form submission, attachment upload, and lightbox preview.
 * Single cohesive module — all parts tightly coupled around the edit form state.
 *
 * Dependencies: jQuery, Bootstrap, Toastr, SweetAlert2, GLightbox
 * Date: 2026-08-18
 */

const TicketEdit = {

    // ===========================
    // CONSTANTS
    // ===========================

    API_ENDPOINTS: {
        UPDATE: site_url + '/tickets/',
        UPLOAD: site_url + '/tickets/',
        DELETE_ATTACHMENT: site_url + '/attachments/',
        USERS_LIST: site_url + '/users/ajax-list',
        PROJECTS: site_url + '/master-projects/active',
        MODULES: site_url + '/master-projects/',
        PAGES: site_url + '/modules/'
    },

    // ===========================
    // STATE
    // ===========================

    pageData: null,
    token: '',
    currentPageId: '',
    currentAssigneeId: '',
    projectsCache: null,
    activeModuleReq: null,
    activePageReq: null,

    // ===========================
    // INITIALIZATION
    // ===========================

    init: function () {
        this.pageData = window.PageData || {};
        this.token = this.pageData.token || '';
        this.currentPageId = this.pageData.currentPageId || '';
        this.currentAssigneeId = this.pageData.currentAssigneeId || '';

        this.bindPriorityControl();
        this.loadAssignees();
        this.bindTicketTypeChange();
        this.bindProjectChange();
        this.bindModuleChange();
        this.bindPageChange();
        this.bindDropzone();
        this.bindImageInput();
        this.bindTicketForm();
        this.initExistingAttachments();

        if (['0', '3', '4', '5'].includes($('#ticketType').val())) {
            this.loadProjects();
        }

        this.initLightbox();
    },

    // ===========================
    // PRIORITY CONTROL
    // ===========================

    bindPriorityControl: function () {
        $('#prioritySegments .seg-option').on('click', function () {
            $('#prioritySegments .seg-option').removeClass('active');
            $(this).addClass('active');
            $('#priorityValue').val($(this).data('value'));
        });
    },

    // ===========================
    // ASSIGNEES
    // ===========================

    loadAssignees: function () {
        var self = this;
        $.ajax({
            url: this.API_ENDPOINTS.USERS_LIST,
            type: 'GET',
            timeout: 10000,
            success: function (res) {
                var users = res.data || [];
                var html = '<option value="">Unassigned</option>';
                for (var i = 0; i < users.length; i++) {
                    var selected = (String(users[i].id) === String(self.currentAssigneeId)) ? ' selected' : '';
                    html += '<option value="' + users[i].id + '"' + selected + '>' + (users[i].full_name || users[i].name) + '</option>';
                }
                $('#assigneeSelect').html(html);
            }
        });
    },

    // ===========================
    // TICKET TYPE CHANGE
    // ===========================

    bindTicketTypeChange: function () {
        var self = this;
        $('#ticketType').on('change', function () {
            if (['0', '3', '4', '5'].includes($(this).val())) {
                $('#bugTraceSection').slideDown(200);
                self.loadProjects();
            } else {
                $('#bugTraceSection').slideUp(200);
                $('#pageIdValue').val('');
            }
        });
    },

    // ===========================
    // CASCADING DROPDOWNS
    // ===========================

    loadProjects: function () {
        var self = this;
        if (this.projectsCache) {
            this.populateProjects(this.projectsCache);
            return;
        }
        $('#projectSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        $.ajax({
            url: this.API_ENDPOINTS.PROJECTS,
            type: 'GET',
            timeout: 10000,
            success: function (res) {
                if (!Array.isArray(res)) {
                    $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
                    return;
                }
                self.projectsCache = res;
                self.populateProjects(res);
                if (self.currentPageId) self.resolveBugLocation();
            },
            error: function () {
                toastr.error('Failed to load projects');
                $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
            }
        });
    },

    populateProjects: function (res) {
        var html = '<option value="">Select Project...</option>';
        for (var i = 0; i < res.length; i++) {
            html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
        }
        $('#projectSelect').html(html).prop('disabled', false);
    },

    resolveBugLocation: function () {
        var self = this;
        if (!this.currentPageId || !this.projectsCache) return;
        for (var p of this.projectsCache) {
            $.ajax({
                url: this.API_ENDPOINTS.MODULES + p.id + '/modules',
                type: 'GET', timeout: 10000,
                success: function (modules) {
                    if (!Array.isArray(modules)) return;
                    for (var m of modules) {
                        $.ajax({
                            url: self.API_ENDPOINTS.PAGES + m.id + '/pages',
                            type: 'GET', timeout: 10000,
                            success: function (pages) {
                                pages = Array.isArray(pages) ? pages : (pages && Array.isArray(pages.pages)) ? pages.pages : [];
                                if (!pages.length) return;
                                for (var pg of pages) {
                                    if (String(pg.id) === String(self.currentPageId)) {
                                        $('#projectSelect').val(p.id).trigger('change');
                                        setTimeout(function () {
                                            $('#moduleSelect').val(m.id).trigger('change');
                                            setTimeout(function () {
                                                $('#pageSelect').val(self.currentPageId);
                                                $('#pageIdValue').val(self.currentPageId);
                                            }, 300);
                                        }, 300);
                                    }
                                }
                            }
                        });
                    }
                }
            });
        }
    },

    bindProjectChange: function () {
        var self = this;
        $('#projectSelect').on('change', function () {
            var pid = $(this).val();
            if (!pid) {
                $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
                $('#pageIdValue').val('');
                return;
            }
            if (self.activeModuleReq) self.activeModuleReq.abort();
            $('#moduleSelect').prop('disabled', true).html('<option value="">Loading...</option>');
            self.activeModuleReq = $.ajax({
                url: self.API_ENDPOINTS.MODULES + pid + '/modules',
                type: 'GET', timeout: 10000,
                success: function (res) {
                    if (!Array.isArray(res)) {
                        $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', false);
                        return;
                    }
                    var html = '<option value="">Select Module...</option>';
                    for (var i = 0; i < res.length; i++) {
                        html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
                    }
                    $('#moduleSelect').html(html).prop('disabled', false);
                    $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
                    $('#pageIdValue').val('');
                },
                error: function () {
                    $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', false);
                }
            });
        });
    },

    bindModuleChange: function () {
        var self = this;
        $('#moduleSelect').on('change', function () {
            var mid = $(this).val();
            if (!mid) {
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
                $('#pageIdValue').val('');
                return;
            }
            if (self.activePageReq) self.activePageReq.abort();
            $('#pageSelect').prop('disabled', true).html('<option value="">Loading...</option>');
            self.activePageReq = $.ajax({
                url: self.API_ENDPOINTS.PAGES + mid + '/pages',
                type: 'GET', timeout: 10000,
                success: function (res) {
                    var pages = Array.isArray(res) ? res : (res && Array.isArray(res.pages)) ? res.pages : [];
                    if (!pages.length) {
                        $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', false);
                        return;
                    }
                    var html = '<option value="">Select Page...</option>';
                    for (var i = 0; i < pages.length; i++) {
                        html += '<option value="' + pages[i].id + '">' + pages[i].name + '</option>';
                    }
                    $('#pageSelect').html(html).prop('disabled', false);
                },
                error: function () {
                    $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', false);
                }
            });
        });
    },

    bindPageChange: function () {
        $('#pageSelect').on('change', function () {
            $('#pageIdValue').val($(this).val());
        });
    },

    // ===========================
    // FILE UPLOAD
    // ===========================

    bindDropzone: function () {
        $('#dropzone').on('dragover', function (e) {
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
    },

    bindImageInput: function () {
        $('#ticketForm input[name="images[]"]').on('change', function () {
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
                reader.onload = function (e) {
                    preview.append('<img src="' + e.target.result + '" class="image-preview-thumb">');
                };
                reader.readAsDataURL(files[i]);
            }
        });
    },

    // ===========================
    // FORM SUBMISSION
    // ===========================

    bindTicketForm: function () {
        var self = this;
        $('#ticketForm').on('submit', function (e) {
            e.preventDefault();
            var btn = $(this).find('[type="submit"]');
            btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Updating...');

            var formData = {
                title: $('input[name="title"]').val(),
                description: $('textarea[name="description"]').val(),
                type: $('select[name="type"]').val(),
                priority: $('#priorityValue').val(),
                due_date: $('input[name="due_date"]').val(),
                assignee_id: $('select[name="assignee_id"]').val(),
                page_id: $('#pageIdValue').val(),
                approver_id: $('select[name="approver_id"]').val() || ''
            };

            $.ajax({
                url: self.API_ENDPOINTS.UPDATE + self.token + '/update',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        var files = $('#ticketForm input[name="images[]"]')[0].files;
                        if (files.length > 0) {
                            self.uploadAttachments(files, 0, btn);
                        } else {
                            toastr.success('Ticket updated');
                            setTimeout(function () { window.location.href = site_url + '/tickets/' + self.token; }, 500);
                        }
                    } else {
                        var msg = res.message || 'Failed to update';
                        if (res.errors && typeof res.errors === 'object') {
                            msg += ': ' + Object.values(res.errors).join(', ');
                        }
                        toastr.error(msg);
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
    },

    // ===========================
    // ATTACHMENT UPLOAD
    // ===========================

    uploadAttachments: function (files, index, btn) {
        var self = this;
        if (index >= files.length) {
            toastr.success('Ticket updated');
            setTimeout(function () { window.location.href = site_url + '/tickets/' + self.token; }, 500);
            return;
        }
        var fd = new FormData();
        fd.append('images[]', files[index]);
        $.ajax({
            url: self.API_ENDPOINTS.UPLOAD + self.token + '/upload-attachment',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function () {
                self.uploadAttachments(files, index + 1, btn);
            },
            error: function (xhr) {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
                if (res && res.redirect) {
                    window.location.href = res.redirect;
                    return;
                }
                toastr.warning('Some images failed to upload');
                self.uploadAttachments(files, index + 1, btn);
            }
        });
    },

    // ===========================
    // EXISTING ATTACHMENTS
    // ===========================

    removeAttachment: function (id, el) {
        Swal.fire({
            title: 'Remove this attachment?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Remove',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: TicketEdit.API_ENDPOINTS.DELETE_ATTACHMENT + id + '/delete',
                    type: 'POST',
                    success: function (res) {
                        if (res.status) {
                            $(el).closest('.attachment-item').remove();
                            toastr.success('Attachment removed');
                        } else {
                            toastr.error('Failed to remove');
                        }
                    },
                    error: function (xhr) {
                        var res = null;
                        try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
                        if (res && res.redirect) {
                            window.location.href = res.redirect;
                            return;
                        }
                        toastr.error('Failed to remove');
                    }
                });
            }
        });
    },

    initExistingAttachments: function () {
        var self = this;
        $('.btn-remove-attachment').each(function () {
            var btn = $(this);
            var attId = btn.closest('.attachment-item').data('id');
            btn.attr('onclick', '');
            btn.on('click', function () {
                self.removeAttachment(attId, this);
            });
        });
    },

    // ===========================
    // LIGHTBOX
    // ===========================

    initLightbox: function () {
        GLightbox({
            selector: '.edit-attachment-link',
            touchNavigation: true,
            keyboardNavigation: true,
            loop: false,
            preload: true
        });
    }
};

// ===========================
// BOOTSTRAP
// ===========================

$(function () {
    TicketEdit.init();
});

window.TicketEdit = TicketEdit;
