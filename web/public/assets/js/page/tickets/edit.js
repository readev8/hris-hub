/**
 * Tickets Edit Page
 *
 * @package    App\Views\tickets
 * @file       edit.js
 * @version    1.0.0
 */

/* global $, site_url, toastr, Swal, GLightbox */

var TicketEdit = (function () {
    'use strict';

    // ── Page Data ──────────────────────────────────────────────
    var pageData        = window.PageData || {};
    var token           = pageData.token || '';
    var currentPageId   = pageData.currentPageId || '';
    var currentAssigneeId = pageData.currentAssigneeId || '';

    // ── State ──────────────────────────────────────────────────
    var projectsCache   = null;
    var activeModuleReq = null;
    var activePageReq   = null;

    // ── DOM Ready ──────────────────────────────────────────────
    $(function () {
        bindPriorityControl();
        loadAssignees();
        bindTicketTypeChange();
        bindProjectChange();
        bindModuleChange();
        bindPageChange();
        bindDropzone();
        bindImageInput();
        bindTicketForm();
        initExistingAttachments();

        if (['0', '3', '4', '5'].includes($('#ticketType').val())) {
            loadProjects();
        }

        initLightbox();
    });

    // ── Priority Segmented Control ─────────────────────────────
    function bindPriorityControl() {
        $('#prioritySegments .seg-option').on('click', function () {
            $('#prioritySegments .seg-option').removeClass('active');
            $(this).addClass('active');
            $('#priorityValue').val($(this).data('value'));
        });
    }

    // ── Assignees ──────────────────────────────────────────────
    function loadAssignees() {
        $.ajax({
            url: site_url + '/users/ajax-list',
            type: 'GET',
            timeout: 10000,
            success: function (res) {
                var users = res.data || [];
                var html = '<option value="">Unassigned</option>';
                for (var i = 0; i < users.length; i++) {
                    var selected = (String(users[i].id) === String(currentAssigneeId)) ? ' selected' : '';
                    html += '<option value="' + users[i].id + '"' + selected + '>' + (users[i].full_name || users[i].name) + '</option>';
                }
                $('#assigneeSelect').html(html);
            }
        });
    }

    // ── Ticket Type Change ─────────────────────────────────────
    function bindTicketTypeChange() {
        $('#ticketType').on('change', function () {
            if (['0', '3', '4', '5'].includes($(this).val())) {
                $('#bugTraceSection').slideDown(200);
                loadProjects();
            } else {
                $('#bugTraceSection').slideUp(200);
                $('#pageIdValue').val('');
            }
        });
    }

    // ── Project → Module → Page Cascading ──────────────────────
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
            success: function (res) {
                if (!Array.isArray(res)) {
                    $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
                    return;
                }
                projectsCache = res;
                populateProjects(res);
                if (currentPageId) resolveBugLocation();
            },
            error: function () {
                toastr.error('Failed to load projects');
                $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
            }
        });
    }

    function populateProjects(res) {
        var html = '<option value="">Select Project...</option>';
        for (var i = 0; i < res.length; i++) {
            html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
        }
        $('#projectSelect').html(html).prop('disabled', false);
    }

    function resolveBugLocation() {
        if (!currentPageId || !projectsCache) return;
        for (var p of projectsCache) {
            $.ajax({
                url: site_url + '/master-projects/' + p.id + '/modules',
                type: 'GET', timeout: 10000,
                success: function (modules) {
                    if (!Array.isArray(modules)) return;
                    for (var m of modules) {
                        $.ajax({
                            url: site_url + '/modules/' + m.id + '/pages',
                            type: 'GET', timeout: 10000,
                            success: function (pages) {
                                pages = Array.isArray(pages) ? pages : (pages && Array.isArray(pages.pages)) ? pages.pages : [];
                                if (!pages.length) return;
                                for (var pg of pages) {
                                    if (String(pg.id) === String(currentPageId)) {
                                        $('#projectSelect').val(p.id).trigger('change');
                                        setTimeout(function () {
                                            $('#moduleSelect').val(m.id).trigger('change');
                                            setTimeout(function () {
                                                $('#pageSelect').val(currentPageId);
                                                $('#pageIdValue').val(currentPageId);
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
    }

    function bindProjectChange() {
        $('#projectSelect').on('change', function () {
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
    }

    function bindModuleChange() {
        $('#moduleSelect').on('change', function () {
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
    }

    function bindPageChange() {
        $('#pageSelect').on('change', function () {
            $('#pageIdValue').val($(this).val());
        });
    }

    // ── Dropzone ───────────────────────────────────────────────
    function bindDropzone() {
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
    }

    // ── Image Input ────────────────────────────────────────────
    function bindImageInput() {
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
    }

    // ── Form Submit ────────────────────────────────────────────
    function bindTicketForm() {
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
                url: site_url + '/tickets/' + token + '/update',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        var files = $('#ticketForm input[name="images[]"]')[0].files;
                        if (files.length > 0) {
                            uploadAttachments(files, 0, btn);
                        } else {
                            toastr.success('Ticket updated');
                            setTimeout(function () { window.location.href = site_url + '/tickets/' + token; }, 500);
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
    }

    // ── Attachment Upload (recursive) ──────────────────────────
    function uploadAttachments(files, index, btn) {
        if (index >= files.length) {
            toastr.success('Ticket updated');
            setTimeout(function () { window.location.href = site_url + '/tickets/' + token; }, 500);
            return;
        }
        var fd = new FormData();
        fd.append('images[]', files[index]);
        $.ajax({
            url: site_url + '/tickets/' + token + '/upload-attachment',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function () {
                uploadAttachments(files, index + 1, btn);
            },
            error: function (xhr) {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
                if (res && res.redirect) {
                    window.location.href = res.redirect;
                    return;
                }
                toastr.warning('Some images failed to upload');
                uploadAttachments(files, index + 1, btn);
            }
        });
    }

    // ── Remove Existing Attachment ─────────────────────────────
    function removeAttachment(id, el) {
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
                    url: site_url + '/attachments/' + id + '/delete',
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
    }

    // ── Existing Attachments Lightbox ──────────────────────────
    function initExistingAttachments() {
        // Bind remove buttons for existing attachments
        $('.btn-remove-attachment').each(function () {
            var btn = $(this);
            var attId = btn.closest('.attachment-item').data('id');
            btn.attr('onclick', '');
            btn.on('click', function () {
                removeAttachment(attId, this);
            });
        });
    }

    // ── Lightbox ───────────────────────────────────────────────
    function initLightbox() {
        GLightbox({
            selector: '.edit-attachment-link',
            touchNavigation: true,
            keyboardNavigation: true,
            loop: false,
            preload: true
        });
    }

    // ── Public API ─────────────────────────────────────────────
    return {
        removeAttachment: removeAttachment
    };
})();
