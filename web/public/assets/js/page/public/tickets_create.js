/**
 * Anonymous Ticket Create Page
 * @package App\Views\public
 * @file    tickets_create.js
 */
/* global $, site_url, toastr, TrackingStore */

(function () {
    'use strict';

    var pageData = window.PageData || {};
    var baseUrl  = pageData.ajaxBaseUrl || site_url + '/public/tickets';

    var projectsCache   = null;
    var activeModuleReq = null;
    var activePageReq   = null;

    $(function () {
        bindPrioritySegments();
        bindTypeToggle();
        bindCascadingDropdowns();
        bindDropzone();
        bindFormSubmit();
    });

    function bindPrioritySegments() {
        var $options = $('#prioritySegments .seg-option');
        $options.on('click', function () {
            var val = $(this).data('value');
            $options.removeClass('active');
            $(this).addClass('active');
            $('#priorityValue').val(val);
        });
        $options.on('keydown', function (e) {
            var idx = $options.index(this);
            var next = -1;
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                next = (idx + 1) % $options.length;
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                next = (idx - 1 + $options.length) % $options.length;
            } else if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                $(this).trigger('click');
                return;
            }
            if (next >= 0) {
                e.preventDefault();
                $options.eq(next).focus().trigger('click');
            }
        });
    }

    function bindTypeToggle() {
        $('#ticketType').on('change', function () {
            var val = $(this).val();
            if (['0', '3', '4', '5'].indexOf(val) !== -1) {
                $('#bugTraceSection').slideDown(200);
                loadProjects();
            } else {
                $('#bugTraceSection').slideUp(200);
                $('#moduleSelect').prop('disabled', true).html('<option value="">-- Select Module --</option>');
                $('#pageSelect').prop('disabled', true).html('<option value="">-- Select Page --</option>');
                $('#pageIdValue').val('');
            }
        });
    }

    function loadProjects() {
        if (projectsCache) { populateProjects(projectsCache); return; }
        $.get(baseUrl + '/ajax/projects', function (res) {
            projectsCache = res || [];
            populateProjects(projectsCache);
        }).fail(function () {
            toastr.error('Gagal memuat proyek');
        });
    }

    function populateProjects(res) {
        var $sel = $('#projectSelect').empty().append('<option value="">-- Select Project --</option>');
        for (var i = 0; i < res.length; i++) {
            $sel.append('<option value="' + res[i].id + '">' + escHtml(res[i].name) + '</option>');
        }
        $sel.prop('disabled', false);
    }

    function bindCascadingDropdowns() {
        $('#projectSelect').on('change', function () {
            var pid = $(this).val();
            $('#moduleSelect').prop('disabled', true).html('<option value="">Loading...</option>');
            $('#pageSelect').prop('disabled', true).html('<option value="">-- Select Page --</option>');
            $('#pageIdValue').val('');
            if (!pid) return;
            if (activeModuleReq) activeModuleReq.abort();
            activeModuleReq = $.get(baseUrl + '/ajax/modules/' + pid, function (res) {
                var $sel = $('#moduleSelect').empty().append('<option value="">-- Select Module --</option>');
                var items = res || [];
                for (var i = 0; i < items.length; i++) {
                    $sel.append('<option value="' + items[i].id + '">' + escHtml(items[i].name) + '</option>');
                }
                $sel.prop('disabled', false);
            });
        });

        $('#moduleSelect').on('change', function () {
            var mid = $(this).val();
            $('#pageSelect').prop('disabled', true).html('<option value="">Loading...</option>');
            $('#pageIdValue').val('');
            if (!mid) return;
            if (activePageReq) activePageReq.abort();
            activePageReq = $.get(baseUrl + '/ajax/pages/' + mid, function (res) {
                var $sel = $('#pageSelect').empty().append('<option value="">-- Select Page --</option>');
                var items = Array.isArray(res) ? res : (res && res.pages ? res.pages : []);
                for (var i = 0; i < items.length; i++) {
                    $sel.append('<option value="' + items[i].id + '">' + escHtml(items[i].name) + '</option>');
                }
                $sel.prop('disabled', false);
            });
        });

        $('#pageSelect').on('change', function () {
            $('#pageIdValue').val($(this).val());
        });
    }

    function bindDropzone() {
        var $dz = $('#dropzone');
        var $fi = $('#fileInput');

        $dz.on('click', function (e) { if (e.target === $fi[0]) return; $fi.trigger('click'); });
        $dz.on('dragover', function (e) { e.preventDefault(); $dz.addClass('dragover'); });
        $dz.on('dragleave', function () { $dz.removeClass('dragover'); });
        $dz.on('drop', function (e) {
            e.preventDefault();
            $dz.removeClass('dragover');
            var files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                $fi[0].files = files;
                $fi.trigger('change');
            }
        });

        $fi.on('change', function () {
            var files = this.files;
            if (files.length > 3) {
                toastr.warning('Maksimal 3 file');
                $fi.val('');
                return;
            }
            for (var i = 0; i < files.length; i++) {
                if (files[i].size > 2 * 1024 * 1024) {
                    toastr.warning('File ' + files[i].name + ' melebihi batas 2MB');
                    $fi.val('');
                    return;
                }
            }
            renderPreviews(files);
        });
    }

    function renderPreviews(files) {
        var $grid = $('#imagePreview').empty();
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (!file.type.match(/^image\//)) {
                $grid.append('<div class="anon-preview-item" style="display:flex;align-items:center;justify-content:center;font-size:11px;color:var(--sap-text-muted)"><i class="fas fa-file"></i></div>');
                continue;
            }
            var reader = new FileReader();
            reader.onload = (function (f) {
                return function (e) {
                    $grid.append('<div class="anon-preview-item"><img src="' + e.target.result + '" alt="' + escHtml(f.name) + '"></div>');
                };
            })(file);
            reader.readAsDataURL(file);
        }
    }

    function bindFormSubmit() {
        $('#ticketForm').on('submit', function (e) {
            e.preventDefault();
            clearFieldErrors();

            var type = $('#ticketType').val();
            var pageId = $('#pageIdValue').val();
            if (['0', '3', '4', '5'].indexOf(type) !== -1 && !pageId) {
                toastr.warning('Untuk tipe ini, wajib memilih halaman di bagian Affected Page');
                $('#bugTraceSection').slideDown(200);
                return;
            }

            var $btn = $('#submitBtn');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');

            var formData = new FormData(this);
            $.ajax({
                url: baseUrl + '/create',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status && res.redirect) {
                        if (res.tracking_code) TrackingStore.addCode(res.tracking_code);
                        toastr.success('Ticket berhasil dibuat!');
                        setTimeout(function () { window.location.href = baseUrl; }, 1200);
                    } else {
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Ticket');
                        if (res.errors) {
                            showFieldErrors(res.errors);
                        } else {
                            toastr.error(res.message || 'Gagal membuat ticket');
                        }
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Ticket');
                    var res = null;
                    try { res = JSON.parse(xhr.responseText); } catch (ex) { /* ignore */ }
                    if (xhr.status === 429) {
                        toastr.error('Terlalu banyak pengajuan. Coba lagi dalam satu jam.');
                    } else {
                        toastr.error((res && res.message) || 'Gagal membuat ticket');
                    }
                }
            });
        });
    }

    function clearFieldErrors() {
        $('.anon-field-error').text('');
        $('.anon-input, .anon-select').removeClass('is-invalid');
    }

    function showFieldErrors(errors) {
        var firstField = null;
        for (var field in errors) {
            $('[data-field="' + field + '"]').text(errors[field]);
            var $input = $('[name="' + field + '"]');
            if ($input.length) {
                $input.addClass('is-invalid');
                if (!firstField) firstField = $input;
            }
        }
        if (firstField) firstField.focus();
    }

    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
})();
