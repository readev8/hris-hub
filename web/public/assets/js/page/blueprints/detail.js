/**
 * Blueprints Detail Page
 *
 * @package    App\Views\blueprints
 * @file       detail.js
 * @version    1.0.0
 */

/* global $, site_url, toastr, Swal, GLightbox */

var BlueprintDetail = (function () {
    'use strict';

    // ── Page Data ──────────────────────────────────────────────
    var pageData    = window.PageData || {};
    var token       = pageData.token || '';
    var userPerms   = pageData.userPermissions || {};
    var blueprintModules = pageData.modules || [];

    // ── State ──────────────────────────────────────────────────
    var currentModuleId = pageData.currentModuleId || null;
    var currentTab      = 'scenarios';

    var scenarioFiles              = [];
    var scenarioExistingAttachments = [];
    var scenarioDeletedAttachments  = [];

    var designPageFiles              = [];
    var designPageExistingAttachments = [];
    var designPageDeletedAttachments  = [];

    // ── DOM Ready ──────────────────────────────────────────────
    $(function () {
        if (currentModuleId) {
            var firstModule = $('.module-item[data-module-id="' + currentModuleId + '"]');
            if (firstModule.length) {
                selectModule(currentModuleId, firstModule[0]);
            }
        }
        bindScenarioDropzone();
        bindDesignPageDropzone();
        bindAttachmentInput();
        initLightbox();
    });

    // ── Helpers ────────────────────────────────────────────────
    function escHtml(str) {
        return $('<div>').text(str || '').html();
    }

    function getEncryptedModuleId(rawId) {
        var mod = blueprintModules.find(function (m) { return m.id == rawId; });
        return mod ? (mod.id_encrypted || mod.id) : null;
    }

    // ── Module Selection ───────────────────────────────────────
    function selectModule(moduleId, el) {
        currentModuleId = moduleId;
        $('.module-item').removeClass('active');
        $(el).addClass('active');
        var name = $(el).find('.fw-medium').text();
        $('#currentModuleName').text(name);
        loadModuleContent(moduleId);
    }

    function loadModuleContent(moduleId) {
        if (!moduleId) return;
        var mod = blueprintModules.find(function (m) { return m.id == moduleId; });
        if (!mod) return;
        renderScenarios(mod.business_scenarios || []);
        renderDesignPages(mod.design_pages || []);
    }

    // ── AJAX Refresh System ────────────────────────────────────
    function refreshBlueprintDetail(callback) {
        $.ajax({
            url: site_url + '/blueprints/' + token + '/refresh',
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                $('#modulesList').css('opacity', '0.6');
            },
            success: function (res) {
                $('#modulesList').css('opacity', '');
                if (res.status) {
                    blueprintModules = res.data.modules || [];
                    updateModulesSidebar(res.data.modules);
                    updateBlueprintHeader(res.data);
                    updateCommentsSection(res.data.comments);
                    updateAttachmentsSection(res.data.attachments);
                    if (callback) callback(res.data);
                } else {
                    toastr.error(res.message || 'Failed to refresh data');
                }
            },
            error: function () {
                $('#modulesList').css('opacity', '');
                toastr.error('Failed to refresh data');
            }
        });
    }

    function updateModulesSidebar(modules) {
        var container = $('#modulesList');
        container.empty();
        if (!modules || !modules.length) {
            container.html('<div class="sap-empty" style="padding:20px"><i class="fas fa-puzzle-piece" style="font-size:24px"></i><p class="mb-0 mt-2" style="font-size:13px">No modules yet</p></div>');
            return;
        }
        var canUpdate = userPerms.blueprints && userPerms.blueprints.can_update;
        modules.forEach(function (mod) {
            var moduleId = mod.id;
            var encModuleId = mod.id_encrypted;
            var isActive = moduleId == currentModuleId;
            var scenarioCount = (mod.business_scenarios || []).length;
            var designCount = (mod.design_pages || []).length;
            var specCount = 0;
            (mod.design_pages || []).forEach(function (dp) { specCount += (dp.page_specifications || []).length; });
            var actionsHtml = '';
            if (canUpdate) {
                actionsHtml = '<span class="module-actions">' +
                    '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="event.stopPropagation(); BlueprintDetail.editModule(\'' + encModuleId + '\')" style="padding:2px 6px;font-size:11px"><i class="fas fa-edit"></i></button> ' +
                    '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="event.stopPropagation(); BlueprintDetail.deleteModule(\'' + encModuleId + '\')" style="padding:2px 6px;font-size:11px"><i class="fas fa-trash"></i></button>' +
                    '</span>';
            }
            var moduleHtml = '<a href="#" class="list-group-item list-group-item-action module-item ' + (isActive ? 'active' : '') + '" ' +
                'data-module-id="' + moduleId + '" ' +
                'onclick="BlueprintDetail.selectModule(' + moduleId + ', this); return false;">' +
                '<div class="d-flex justify-content-between align-items-center">' +
                '<span class="fw-medium" style="font-size:13px">' + escHtml(mod.name) + '</span>' +
                '<span class="text-muted" style="font-size:11px">' + scenarioCount + 'S / ' + designCount + 'D / ' + specCount + 'P</span>' +
                '</div>' + actionsHtml + '</a>';
            container.append(moduleHtml);
        });
        if (!currentModuleId && modules.length) {
            currentModuleId = modules[0].id;
        }
        if (currentModuleId) {
            loadModuleContent(currentModuleId);
        }
    }

    function updateBlueprintHeader(data) {
        var badge = $('#blueprintStatusBadge');
        if (badge.length) {
            badge.replaceWith(buildStatusBadge(data.status_name));
        }
        updateActionButtons(data);
    }

    function buildStatusBadge(statusName) {
        var map = {
            'Draft': 'closed',
            'Open': 'open',
            'Approved': 'approved',
            'In Progress': 'in-progress',
            'Resolved': 'resolved',
            'Closed': 'closed',
            'Rejected': 'rejected',
            'Pending': 'pending'
        };
        var cls = map[statusName] || 'closed';
        return '<span id="blueprintStatusBadge" class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + escHtml(statusName) + '</span>';
    }

    function updateActionButtons(data) {
        var container = $('#blueprintActions');
        if (!container.length) return;
        var actions = data.available_actions || [];
        var html = '';
        if (actions.indexOf('approve-it') !== -1) {
            html += '<button class="sap-btn sap-btn-success sap-btn-sm" onclick="BlueprintDetail.doAction(\'approve-it\')"><i class="fas fa-check"></i> Approve (IT)</button>';
        }
        if (actions.indexOf('approve-dept') !== -1) {
            html += '<button class="sap-btn sap-btn-success sap-btn-sm" onclick="BlueprintDetail.doAction(\'approve-dept\')"><i class="fas fa-check"></i> Approve (Dept)</button>';
        }
        if (actions.indexOf('reject') !== -1) {
            html += '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.promptReject()"><i class="fas fa-times"></i> Reject</button>';
        }
        if (actions.indexOf('resubmit') !== -1) {
            html += '<button class="sap-btn sap-btn-warning sap-btn-sm" onclick="BlueprintDetail.doAction(\'resubmit\')"><i class="fas fa-undo"></i> Resubmit</button>';
        }
        if (html) html += '<hr class="my-1">';
        if (actions.indexOf('edit') !== -1) {
            html += '<a href="' + site_url + '/blueprints/' + token + '/edit" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-edit"></i> Edit</a>';
        }
        if (actions.indexOf('delete') !== -1) {
            html += '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.confirmDelete()"><i class="fas fa-trash"></i> Delete</button>';
        }
        container.html(html);
    }

    function updateCommentsSection(comments) {
        var container = $('#commentsContainer');
        if (!container.length) return;
        var badge = $('#commentCountBadge');
        if (badge.length) badge.text(comments ? comments.length : 0);
        var formHtml = '<form id="commentForm" class="mt-3" style="border-top:1px solid var(--sap-border-light);padding-top:16px">' +
            '<div class="mb-2"><textarea class="sap-input" id="commentText" rows="2" placeholder="Write a comment..." style="min-height:60px"></textarea></div>' +
            '<button class="sap-btn sap-btn-primary sap-btn-sm" type="submit"><i class="fas fa-paper-plane"></i> Send</button>' +
            '</form>';
        if (!comments || !comments.length) {
            container.html('<div class="sap-empty" style="padding:20px"><i class="fas fa-comment-dots" style="font-size:36px"></i><h4>No comments</h4></div>' + formHtml);
            bindCommentForm();
            return;
        }
        var html = '';
        comments.forEach(function (c) {
            html += '<div class="sap-comment">' +
                '<div class="sap-comment-header">' +
                '<div class="avatar-circle avatar-circle-sm" style="background:#758CA4;color:#fff">' + (c.full_name ? c.full_name.charAt(0).toUpperCase() : '?') + '</div>' +
                '<span class="sap-comment-author">' + escHtml(c.full_name || '') + '</span>' +
                '<span class="sap-comment-time">' + escHtml(c.created_at || '') + '</span>' +
                '</div>' +
                '<div class="sap-comment-body">' + (c.content || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') + '</div>' +
                '</div>';
        });
        container.html(html + formHtml);
        bindCommentForm();
    }

    function bindCommentForm() {
        $('#commentForm').off('submit').on('submit', function (e) {
            e.preventDefault();
            var text = $('#commentText').val();
            if (!text.trim()) return;
            $.post(site_url + '/blueprints/' + token + '/comments', { content: text }, function (res) {
                if (res.status) {
                    toastr.success('Comment added');
                    $('#commentText').val('');
                    refreshBlueprintDetail();
                } else {
                    toastr.error(res.data.message || 'Failed');
                }
            }).fail(function (xhr) {
                toastr.error('Failed to add comment (HTTP ' + xhr.status + ')');
            });
        });
    }

    function updateAttachmentsSection(attachments) {
        var container = $('#attachmentsContainer');
        if (!container.length) return;
        if (!attachments || !attachments.length) {
            container.html('<p class="text-muted mb-0" style="font-size:13px">No attachments yet.</p>');
            return;
        }
        var html = '<div class="d-flex flex-wrap gap-2">';
        attachments.forEach(function (att) {
            var isImage = att.mime_type && att.mime_type.indexOf('image/') === 0;
            if (isImage) {
                html += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" class="glightbox blueprint-attachment-link" data-gallery="blueprint-attachments" data-description="' + escHtml(att.filename) + '">' +
                    '<img src="' + site_url + '/uploads/blueprints/' + att.stored_name + '" alt="' + escHtml(att.filename) + '" style="max-width:80px;max-height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border);cursor:pointer" class="sap-hover-lift"></a>';
            } else {
                var mime = att.mime_type || '';
                var iconClass = 'fas fa-file';
                var iconColor = 'var(--sap-text-muted)';
                if (mime === 'application/pdf') { iconClass = 'fas fa-file-pdf'; iconColor = 'var(--sap-error)'; }
                else if (mime.indexOf('spreadsheet') !== -1 || mime === 'application/vnd.ms-excel') { iconClass = 'fas fa-file-excel'; iconColor = '#217346'; }
                else if (mime.indexOf('word') !== -1 || mime === 'application/msword') { iconClass = 'fas fa-file-word'; iconColor = '#2B579A'; }
                html += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" target="_blank">' +
                    '<div style="padding:8px 12px;background:var(--sap-background);border-radius:6px;border:1px solid var(--sap-border);font-size:12px">' +
                    '<i class="' + iconClass + '" style="color:' + iconColor + ';margin-right:4px"></i>' + escHtml(att.filename) + '</div></a>';
            }
        });
        html += '</div>';
        container.html(html);
        if (typeof GLightbox !== 'undefined') {
            GLightbox({ selector: '.blueprint-attachment-link', touchNavigation: true, keyboardNavigation: true, loop: false, preload: true });
        }
    }

    // ── Tab Switching ──────────────────────────────────────────
    function switchTab(tab) {
        currentTab = tab;
        $('#moduleTabs .nav-link').removeClass('active');
        $('#moduleTabs .nav-link[data-tab="' + tab + '"]').addClass('active');
        $('.tab-content-section').hide();
        $('#tab-content-' + tab).show();
    }

    // ── Scenario Rendering ─────────────────────────────────────
    function renderScenarios(scenarios) {
        var container = $('#scenariosContainer');
        if (!scenarios.length) {
            container.html('<div class="sap-empty" style="padding:40px"><i class="fas fa-briefcase" style="font-size:36px"></i><h4>No scenarios</h4><p>Add business scenarios for this module.</p></div>');
            return;
        }
        var html = '';
        var canUpdate = userPerms.blueprints && userPerms.blueprints.can_update;
        scenarios.forEach(function (s) {
            var imagesHtml = '';
            if (s.attachments && s.attachments.length) {
                imagesHtml = '<div class="card-item-images">';
                s.attachments.forEach(function (att) {
                    if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                        imagesHtml += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" class="glightbox scenario-image-link" data-gallery="scenario-' + s.id + '"><img src="' + site_url + '/uploads/blueprints/' + att.stored_name + '" alt="' + escHtml(att.filename) + '"></a>';
                    } else {
                        imagesHtml += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" target="_blank" style="font-size:12px;color:var(--sap-brand)"><i class="fas fa-file me-1"></i>' + escHtml(att.filename) + '</a>';
                    }
                });
                imagesHtml += '</div>';
            }
            html += '<div class="card-item">' +
                '<div class="card-item-title">' + escHtml(s.title) + '</div>' +
                '<div class="card-item-desc">' + escHtml(s.description || '') + '</div>' +
                imagesHtml +
                '<div class="card-item-actions">' +
                (canUpdate ? '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="BlueprintDetail.editScenario(\'' + (s.id_encrypted || s.id) + '\')"><i class="fas fa-edit"></i></button>' : '') +
                (canUpdate ? '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.deleteScenario(\'' + (s.id_encrypted || s.id) + '\')"><i class="fas fa-trash"></i></button>' : '') +
                '</div></div>';
        });
        container.html(html);
        GLightbox({ selector: '.scenario-image-link', touchNavigation: true, loop: false });
    }

    // ── Design Page Rendering ──────────────────────────────────
    function renderDesignPages(pages) {
        var container = $('#designPagesContainer');
        if (!pages.length) {
            container.html('<div class="sap-empty" style="padding:40px"><i class="fas fa-palette" style="font-size:36px"></i><h4>No design pages</h4><p>Add design pages for this module.</p></div>');
            return;
        }
        var html = '';
        var canUpdate = userPerms.blueprints && userPerms.blueprints.can_update;
        pages.forEach(function (p) {
            var imagesHtml = '';
            if (p.attachments && p.attachments.length) {
                imagesHtml = '<div class="card-item-images">';
                p.attachments.forEach(function (att) {
                    if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                        imagesHtml += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" class="glightbox designpage-image-link" data-gallery="designpage-' + p.id + '"><img src="' + site_url + '/uploads/blueprints/' + att.stored_name + '" alt="' + escHtml(att.filename) + '"></a>';
                    }
                });
                imagesHtml += '</div>';
            }
            var specCount = (p.page_specifications || []).length;
            html += '<div class="card-item">' +
                '<div class="card-item-title">' + escHtml(p.title) + '</div>' +
                '<div class="card-item-desc">' + escHtml(p.description || '') + '</div>' +
                imagesHtml +
                '<div class="card-item-actions">' +
                '<a href="' + site_url + '/blueprints/design-pages/' + (p.id_encrypted || p.id) + '/specifications" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-list-alt"></i> Manage Specs (' + specCount + ')</a>' +
                (canUpdate ? '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="BlueprintDetail.editDesignPage(\'' + (p.id_encrypted || p.id) + '\')"><i class="fas fa-edit"></i></button>' : '') +
                (canUpdate ? '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.deleteDesignPage(\'' + (p.id_encrypted || p.id) + '\')"><i class="fas fa-trash"></i></button>' : '') +
                '</div></div>';
        });
        container.html(html);
        GLightbox({ selector: '.designpage-image-link', touchNavigation: true, loop: false });
    }

    // ── Module CRUD ────────────────────────────────────────────
    function showAddModule() {
        $('#moduleFormId').val('');
        $('#moduleNameInput').val('');
        $('#moduleModal').modal('show');
    }

    function editModule(moduleId) {
        var mod = blueprintModules.find(function (m) { return (m.id_encrypted || m.id) == moduleId; });
        if (!mod) return;
        $('#moduleFormId').val(moduleId);
        $('#moduleNameInput').val(mod.name);
        $('#moduleModal').modal('show');
    }

    function bindModuleForm() {
        $('#moduleForm').on('submit', function (e) {
            e.preventDefault();
            var id = $('#moduleFormId').val();
            var url = id ? site_url + '/blueprints/modules/' + id + '/update' : site_url + '/blueprints/' + token + '/modules';
            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    if (res.status) {
                        toastr.success(id ? 'Module updated' : 'Module added');
                        $('#moduleModal').modal('hide');
                        refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                },
                error: function () { toastr.error('Request failed'); }
            });
        });
    }

    function deleteModule(moduleId) {
        Swal.fire({
            title: 'Delete Module?',
            text: 'This will remove all scenarios, pages, and specifications.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post(site_url + '/blueprints/modules/' + moduleId + '/delete', {}, function (res) {
                    if (res.status) {
                        toastr.success('Module deleted');
                        currentModuleId = null;
                        refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                });
            }
        });
    }

    // ── Scenario CRUD ──────────────────────────────────────────
    function showAddScenario() {
        if (!currentModuleId) { toastr.warning('Select a module first'); return; }
        $('#scenarioFormId').val('');
        $('#scenarioTitleInput').val('');
        $('#scenarioDescInput').val('');
        scenarioExistingAttachments = [];
        scenarioDeletedAttachments = [];
        scenarioFiles = [];
        $('#scenarioModal .modal-title').html('<i class="fas fa-briefcase me-2"></i>Add Business Scenario');
        renderScenarioFiles();
        $('#scenarioModal').modal('show');
    }

    function editScenario(scenarioId) {
        var mod = blueprintModules.find(function (m) { return m.id == currentModuleId; });
        if (!mod) return;
        var scenarios = mod.business_scenarios || [];
        var s = scenarios.find(function (sc) { return (sc.id_encrypted || sc.id) == scenarioId; });
        if (!s) return;
        $('#scenarioFormId').val(scenarioId);
        $('#scenarioTitleInput').val(s.title);
        $('#scenarioDescInput').val(s.description);
        scenarioExistingAttachments = (s.attachments || []).map(function (a) { return Object.assign({}, a); });
        scenarioDeletedAttachments = [];
        scenarioFiles = [];
        $('#scenarioModal .modal-title').html('<i class="fas fa-briefcase me-2"></i>Edit Business Scenario');
        renderScenarioFiles();
        $('#scenarioModal').modal('show');
    }

    function deleteScenario(scenarioId) {
        Swal.fire({
            title: 'Delete Scenario?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post(site_url + '/blueprints/business-scenarios/' + scenarioId + '/delete', {}, function (res) {
                    if (res.status) {
                        toastr.success('Scenario deleted');
                        refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                });
            }
        });
    }

    function renderScenarioFiles() {
        var preview = $('#scenarioFilePreview');
        preview.empty();
        var baseUrl = site_url + '/uploads/blueprints/';
        var removeBtnStyle = 'position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center';
        var thumbStyle = 'width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)';
        var fileBoxStyle = 'width:80px;height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:6px;border:1px solid var(--sap-border);background:var(--sap-background);font-size:11px;text-align:center;padding:4px;overflow:hidden';

        scenarioExistingAttachments.forEach(function (att) {
            if (scenarioDeletedAttachments.indexOf(att.id) !== -1) return;
            var wrapper = $('<div style="position:relative;display:inline-block"></div>');
            if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                wrapper.append('<img src="' + baseUrl + att.stored_name + '" style="' + thumbStyle + '">');
            } else {
                wrapper.append('<div style="' + fileBoxStyle + '"><i class="fas fa-file mb-1"></i><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70px">' + escHtml(att.filename) + '</span></div>');
            }
            wrapper.append('<button type="button" class="btn-remove-existing-attachment" data-att-id="' + att.id + '" style="' + removeBtnStyle + '">&times;</button>');
            preview.append(wrapper);
        });

        scenarioFiles.forEach(function (file, i) {
            var wrapper = $('<div style="position:relative;display:inline-block"></div>');
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    wrapper.append('<img src="' + e.target.result + '" style="' + thumbStyle + '">');
                    wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + i + '" style="' + removeBtnStyle + '">&times;</button>');
                };
                reader.readAsDataURL(file);
            } else {
                wrapper.append('<div style="' + fileBoxStyle + '"><i class="fas fa-file mb-1"></i><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70px">' + escHtml(file.name) + '</span></div>');
                wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + i + '" style="' + removeBtnStyle + '">&times;</button>');
            }
            preview.append(wrapper);
        });
    }

    function bindScenarioDropzone() {
        $('#scenarioDropzone').on('click', function (e) {
            if (e.target !== this) return;
            $(this).find('input[type="file"]').click();
        })
        .on('dragover', function (e) { e.preventDefault(); $(this).css('border-color', 'var(--sap-brand)'); })
        .on('dragleave', function () { $(this).css('border-color', 'var(--sap-border)'); })
        .on('drop', function (e) {
            e.preventDefault();
            $(this).css('border-color', 'var(--sap-border)');
            var files = e.originalEvent.dataTransfer.files;
            for (var i = 0; i < files.length; i++) { scenarioFiles.push(files[i]); }
            renderScenarioFiles();
        });

        $('#scenarioForm input[name="images[]"]').on('change', function () {
            for (var i = 0; i < this.files.length; i++) { scenarioFiles.push(this.files[i]); }
            renderScenarioFiles();
            $(this).val('');
        });

        $('#scenarioFilePreview').on('click', '.btn-remove-file', function () {
            scenarioFiles.splice($(this).data('idx'), 1);
            renderScenarioFiles();
        });

        $('#scenarioFilePreview').on('click', '.btn-remove-existing-attachment', function () {
            var attId = $(this).data('att-id');
            if (scenarioDeletedAttachments.indexOf(attId) === -1) {
                scenarioDeletedAttachments.push(attId);
            }
            renderScenarioFiles();
        });

        $('#scenarioForm').on('submit', function (e) {
            e.preventDefault();
            var id = $('#scenarioFormId').val();
            var encModId = getEncryptedModuleId(currentModuleId);
            var url = id ? site_url + '/blueprints/business-scenarios/' + id + '/update' : site_url + '/blueprints/modules/' + encModId + '/business-scenarios';
            var fd = new FormData(this);
            fd.delete('images[]');
            scenarioFiles.forEach(function (f) { fd.append('images[]', f); });
            if (scenarioDeletedAttachments.length) {
                fd.append('deleted_attachments', JSON.stringify(scenarioDeletedAttachments));
            }
            $.ajax({
                url: url,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status) {
                        toastr.success(id ? 'Scenario updated' : 'Scenario added');
                        $('#scenarioModal').modal('hide');
                        refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                },
                error: function () { toastr.error('Request failed'); }
            });
        });
    }

    // ── Design Page CRUD ───────────────────────────────────────
    function showAddDesignPage() {
        if (!currentModuleId) { toastr.warning('Select a module first'); return; }
        $('#designPageFormId').val('');
        $('#designPageTitleInput').val('');
        $('#designPageDescInput').val('');
        designPageExistingAttachments = [];
        designPageDeletedAttachments = [];
        designPageFiles = [];
        $('#designPageModal .modal-title').html('<i class="fas fa-palette me-2"></i>Add Design Page');
        renderDesignPageFiles();
        $('#designPageModal').modal('show');
    }

    function editDesignPage(pageId) {
        var mod = blueprintModules.find(function (m) { return m.id == currentModuleId; });
        if (!mod) return;
        var pages = mod.design_pages || [];
        var p = pages.find(function (dp) { return (dp.id_encrypted || dp.id) == pageId; });
        if (!p) return;
        $('#designPageFormId').val(pageId);
        $('#designPageTitleInput').val(p.title);
        $('#designPageDescInput').val(p.description);
        designPageExistingAttachments = (p.attachments || []).map(function (a) { return Object.assign({}, a); });
        designPageDeletedAttachments = [];
        designPageFiles = [];
        $('#designPageModal .modal-title').html('<i class="fas fa-palette me-2"></i>Edit Design Page');
        renderDesignPageFiles();
        $('#designPageModal').modal('show');
    }

    function deleteDesignPage(pageId) {
        Swal.fire({
            title: 'Delete Design Page?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post(site_url + '/blueprints/design-pages/' + pageId + '/delete', {}, function (res) {
                    if (res.status) {
                        toastr.success('Design page deleted');
                        refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                });
            }
        });
    }

    function renderDesignPageFiles() {
        var preview = $('#designPageFilePreview');
        preview.empty();
        var baseUrl = site_url + '/uploads/blueprints/';
        var removeBtnStyle = 'position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center';
        var thumbStyle = 'width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)';
        var fileBoxStyle = 'width:80px;height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:6px;border:1px solid var(--sap-border);background:var(--sap-background);font-size:11px;text-align:center;padding:4px;overflow:hidden';

        designPageExistingAttachments.forEach(function (att) {
            if (designPageDeletedAttachments.indexOf(att.id) !== -1) return;
            var wrapper = $('<div style="position:relative;display:inline-block"></div>');
            if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                wrapper.append('<img src="' + baseUrl + att.stored_name + '" style="' + thumbStyle + '">');
            } else {
                wrapper.append('<div style="' + fileBoxStyle + '"><i class="fas fa-file mb-1"></i><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70px">' + escHtml(att.filename) + '</span></div>');
            }
            wrapper.append('<button type="button" class="btn-remove-existing-attachment" data-att-id="' + att.id + '" style="' + removeBtnStyle + '">&times;</button>');
            preview.append(wrapper);
        });

        designPageFiles.forEach(function (file, i) {
            var wrapper = $('<div style="position:relative;display:inline-block"></div>');
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    wrapper.append('<img src="' + e.target.result + '" style="' + thumbStyle + '">');
                    wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + i + '" style="' + removeBtnStyle + '">&times;</button>');
                };
                reader.readAsDataURL(file);
            } else {
                wrapper.append('<div style="' + fileBoxStyle + '"><i class="fas fa-file mb-1"></i><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70px">' + escHtml(file.name) + '</span></div>');
                wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + i + '" style="' + removeBtnStyle + '">&times;</button>');
            }
            preview.append(wrapper);
        });
    }

    function bindDesignPageDropzone() {
        $('#designPageDropzone').on('click', function (e) {
            if (e.target !== this) return;
            $(this).find('input[type="file"]').click();
        })
        .on('dragover', function (e) { e.preventDefault(); $(this).css('border-color', 'var(--sap-brand)'); })
        .on('dragleave', function () { $(this).css('border-color', 'var(--sap-border)'); })
        .on('drop', function (e) {
            e.preventDefault();
            $(this).css('border-color', 'var(--sap-border)');
            var files = e.originalEvent.dataTransfer.files;
            for (var i = 0; i < files.length; i++) { designPageFiles.push(files[i]); }
            renderDesignPageFiles();
        });

        $('#designPageForm input[name="images[]"]').on('change', function () {
            for (var i = 0; i < this.files.length; i++) { designPageFiles.push(this.files[i]); }
            renderDesignPageFiles();
            $(this).val('');
        });

        $('#designPageFilePreview').on('click', '.btn-remove-file', function () {
            designPageFiles.splice($(this).data('idx'), 1);
            renderDesignPageFiles();
        });

        $('#designPageFilePreview').on('click', '.btn-remove-existing-attachment', function () {
            var attId = $(this).data('att-id');
            if (designPageDeletedAttachments.indexOf(attId) === -1) {
                designPageDeletedAttachments.push(attId);
            }
            renderDesignPageFiles();
        });

        $('#designPageForm').on('submit', function (e) {
            e.preventDefault();
            var id = $('#designPageFormId').val();
            var encModId = getEncryptedModuleId(currentModuleId);
            var url = id ? site_url + '/blueprints/design-pages/' + id + '/update' : site_url + '/blueprints/modules/' + encModId + '/design-pages';
            var fd = new FormData(this);
            fd.delete('images[]');
            designPageFiles.forEach(function (f) { fd.append('images[]', f); });
            if (designPageDeletedAttachments.length) {
                fd.append('deleted_attachments', JSON.stringify(designPageDeletedAttachments));
            }
            $.ajax({
                url: url,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status) {
                        toastr.success(id ? 'Design page updated' : 'Design page added');
                        $('#designPageModal').modal('hide');
                        refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                },
                error: function () { toastr.error('Request failed'); }
            });
        });
    }

    // ── Approval Actions ───────────────────────────────────────
    function doAction(action) {
        $.post(site_url + '/blueprints/' + token + '/' + action, {}, function (res) {
            if (res.status) {
                toastr.success(res.data.message);
                refreshBlueprintDetail();
            } else {
                toastr.error(res.data.message || 'Action failed');
            }
        }).fail(function (xhr) {
            toastr.error('Action failed (HTTP ' + xhr.status + ')');
        });
    }

    function promptReject() {
        Swal.fire({
            title: 'Rejection Notes',
            input: 'textarea',
            inputPlaceholder: 'Enter reason for rejection...',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
        }).then(function (result) {
            if (result.isConfirmed && result.value) {
                $.post(site_url + '/blueprints/' + token + '/reject', { notes: result.value }, function (res) {
                    if (res.status) {
                        toastr.success(res.data.message);
                        refreshBlueprintDetail();
                    } else {
                        toastr.error(res.data.message || 'Failed');
                    }
                }).fail(function (xhr) {
                    toastr.error('Reject failed (HTTP ' + xhr.status + ')');
                });
            }
        });
    }

    function confirmDelete() {
        Swal.fire({
            title: 'Delete Blueprint?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post(site_url + '/blueprints/' + token + '/delete', {}, function (res) {
                    if (res.status) {
                        toastr.success('Blueprint deleted');
                        setTimeout(function () { window.location.href = site_url + '/blueprints'; }, 800);
                    } else {
                        toastr.error(res.data.message || 'Failed to delete');
                    }
                }).fail(function (xhr) {
                    toastr.error('Delete failed (HTTP ' + xhr.status + ')');
                });
            }
        });
    }

    // ── Attachment Upload ──────────────────────────────────────
    function bindAttachmentInput() {
        $('#attachmentInput').on('change', function () {
            var files = this.files;
            if (!files.length) return;

            var maxSize = 500 * 1024;
            var fd = new FormData();
            var skipped = 0;
            for (var i = 0; i < files.length; i++) {
                if (files[i].size > maxSize) {
                    toastr.warning(files[i].name + ' exceeds 500KB limit');
                    skipped++;
                    continue;
                }
                fd.append('images[]', files[i]);
            }

            if (skipped >= files.length) { $(this).val(''); return; }

            var btn = $(this).closest('label');
            btn.css('pointer-events', 'none').css('opacity', '0.6');

            $.ajax({
                url: site_url + '/blueprints/' + token + '/attachments',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status) {
                        toastr.success('Attachment(s) uploaded');
                        refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed to upload');
                        btn.css('pointer-events', '').css('opacity', '');
                        $('#attachmentInput').val('');
                    }
                },
                error: function (xhr) {
                    var res = null;
                    try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
                    if (res && res.redirect) { window.location.href = res.redirect; return; }
                    toastr.error(res && res.message ? res.message : 'Upload failed (HTTP ' + xhr.status + ')');
                    btn.css('pointer-events', '').css('opacity', '');
                    $('#attachmentInput').val('');
                }
            });
            $(this).val('');
        });
    }

    // ── Lightbox ───────────────────────────────────────────────
    function initLightbox() {
        GLightbox({
            selector: '.blueprint-attachment-link',
            touchNavigation: true,
            keyboardNavigation: true,
            loop: false,
            preload: true
        });
    }

    // ── Bind module form on DOM ready ──────────────────────────
    $(function () {
        bindModuleForm();
    });

    // ── Public API ─────────────────────────────────────────────
    return {
        selectModule:        selectModule,
        showAddModule:       showAddModule,
        editModule:          editModule,
        deleteModule:        deleteModule,
        showAddScenario:     showAddScenario,
        editScenario:        editScenario,
        deleteScenario:      deleteScenario,
        showAddDesignPage:   showAddDesignPage,
        editDesignPage:      editDesignPage,
        deleteDesignPage:    deleteDesignPage,
        switchTab:           switchTab,
        doAction:            doAction,
        promptReject:        promptReject,
        confirmDelete:       confirmDelete,
        refreshBlueprintDetail: refreshBlueprintDetail
    };
})();
