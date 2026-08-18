/**
 * ============================================================================
 * Blueprint Detail UI Rendering
 * ============================================================================
 *
 * UI rendering functions extracted from detail.js for blueprint detail page.
 * Handles module sidebar, scenarios, design pages, attachments, and comments rendering.
 *
 * Dependencies: jQuery, Bootstrap, Toastr, GLightbox
 * Date: 2026-08-18
 */

const BlueprintDetailUI = {

    // ===========================
    // HELPERS
    // ===========================

    escHtml: function (str) {
        return $('<div>').text(str || '').html();
    },

    // ===========================
    // MODULE SIDEBAR
    // ===========================

    updateModulesSidebar: function (modules, opts) {
        var container = $('#modulesList');
        var currentModuleId = opts.currentModuleId;
        var userPerms = opts.userPerms;

        container.empty();

        if (!modules || !modules.length) {
            container.html(GlobalSanitize.sanitizeHtml(
                '<div class="sap-empty" style="padding:20px"><i class="fas fa-puzzle-piece" style="font-size:24px"></i><p class="mb-0 mt-2" style="font-size:13px">No modules yet</p></div>'
            ));
            return;
        }

        var canUpdate = userPerms.blueprints && userPerms.blueprints.can_update;
        var self = this;

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
                '<span class="fw-medium" style="font-size:13px">' + self.escHtml(mod.name) + '</span>' +
                '<span class="text-muted" style="font-size:11px">' + scenarioCount + 'S / ' + designCount + 'D / ' + specCount + 'P</span>' +
                '</div>' + actionsHtml + '</a>';

            container.append(moduleHtml);
        });
    },

    // ===========================
    // BLUEPRINT HEADER
    // ===========================

    updateBlueprintHeader: function (data) {
        var badge = $('#blueprintStatusBadge');
        if (badge.length) {
            badge.replaceWith(this.buildStatusBadge(data.status_name));
        }
        this.updateActionButtons(data);
    },

    buildStatusBadge: function (statusName) {
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
        return '<span id="blueprintStatusBadge" class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + this.escHtml(statusName) + '</span>';
    },

    updateActionButtons: function (data, token) {
        var container = $('#blueprintActions');
        if (!container.length) return;
        var actions = data.available_actions || [];
        var html = '';
        if (actions.indexOf('edit') !== -1) {
            html += '<a href="' + site_url + '/blueprints/' + token + '/edit" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-edit"></i> Edit</a>';
        }
        if (actions.indexOf('delete') !== -1) {
            html += '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.confirmDelete()"><i class="fas fa-trash"></i> Delete</button>';
        }
        container.html(html);
    },

    // ===========================
    // SCENARIO RENDERING
    // ===========================

    renderScenarios: function (scenarios, opts) {
        var container = $('#scenariosContainer');
        var userPerms = opts.userPerms;
        var token = opts.token;

        if (!scenarios.length) {
            container.html(GlobalSanitize.sanitizeHtml(
                '<div class="sap-empty" style="padding:40px"><i class="fas fa-briefcase" style="font-size:36px"></i><h4>No scenarios</h4><p>Add business scenarios for this module.</p></div>'
            ));
            return;
        }

        var html = '';
        var canUpdate = userPerms.blueprints && userPerms.blueprints.can_update;
        var self = this;

        scenarios.forEach(function (s) {
            var imagesHtml = '';
            if (s.attachments && s.attachments.length) {
                imagesHtml = '<div class="card-item-images">';
                s.attachments.forEach(function (att) {
                    if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                        imagesHtml += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" class="glightbox scenario-image-link" data-gallery="scenario-' + s.id + '"><img src="' + site_url + '/uploads/blueprints/' + att.stored_name + '" alt="' + self.escHtml(att.filename) + '"></a>';
                    } else {
                        imagesHtml += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" target="_blank" style="font-size:12px;color:var(--sap-brand)"><i class="fas fa-file me-1"></i>' + self.escHtml(att.filename) + '</a>';
                    }
                });
                imagesHtml += '</div>';
            }

            var fieldsHtml = '';
            if (s.actors) fieldsHtml += '<div class="scenario-field"><span class="scenario-field-label">Actors:</span> ' + self.escHtml(s.actors) + '</div>';
            if (s.frequency) fieldsHtml += '<div class="scenario-field"><span class="scenario-field-label">Frequency:</span> <span class="sap-badge info" style="font-size:11px;padding:2px 8px">' + self.escHtml(s.frequency) + '</span></div>';
            if (s.pre_condition) fieldsHtml += '<div class="scenario-field"><span class="scenario-field-label">Pre Condition:</span> ' + self.escHtml(s.pre_condition) + '</div>';
            if (s.post_condition) fieldsHtml += '<div class="scenario-field"><span class="scenario-field-label">Post Condition:</span> ' + self.escHtml(s.post_condition) + '</div>';
            if (s.normal_course) fieldsHtml += '<div class="scenario-field"><span class="scenario-field-label">Normal Course:</span> ' + self.escHtml(s.normal_course) + '</div>';
            if (s.exception) fieldsHtml += '<div class="scenario-field"><span class="scenario-field-label">Exception:</span> ' + self.escHtml(s.exception) + '</div>';
            if (s.notes) fieldsHtml += '<div class="scenario-field"><span class="scenario-field-label">Notes:</span> ' + self.escHtml(s.notes) + '</div>';
            if (s.issue) fieldsHtml += '<div class="scenario-field"><span class="scenario-field-label">Issue:</span> ' + self.escHtml(s.issue) + '</div>';

            html += '<div class="card-item">' +
                '<div class="card-item-title">' + self.escHtml(s.title) + '</div>' +
                '<div class="card-item-desc">' + GlobalSanitize.sanitizeHtml(s.description || '') + '</div>' +
                fieldsHtml +
                imagesHtml +
                '<div class="card-item-actions">' +
                (canUpdate ? '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="BlueprintDetail.editScenario(\'' + (s.id_encrypted || s.id) + '\')"><i class="fas fa-edit"></i></button>' : '') +
                (canUpdate ? '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.deleteScenario(\'' + (s.id_encrypted || s.id) + '\')"><i class="fas fa-trash"></i></button>' : '') +
                '</div></div>';
        });

        container.html(html);
        GLightbox({ selector: '.scenario-image-link', touchNavigation: true, loop: false });
    },

    // ===========================
    // DESIGN PAGE RENDERING
    // ===========================

    renderDesignPages: function (pages, opts) {
        var container = $('#designPagesContainer');
        var userPerms = opts.userPerms;

        if (!pages.length) {
            container.html(GlobalSanitize.sanitizeHtml(
                '<div class="sap-empty" style="padding:40px"><i class="fas fa-palette" style="font-size:36px"></i><h4>No design pages</h4><p>Add design pages for this module.</p></div>'
            ));
            return;
        }

        var html = '';
        var canUpdate = userPerms.blueprints && userPerms.blueprints.can_update;
        var self = this;

        pages.forEach(function (p) {
            var imagesHtml = '';
            if (p.attachments && p.attachments.length) {
                imagesHtml = '<div class="card-item-images">';
                p.attachments.forEach(function (att) {
                    if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                        imagesHtml += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" class="glightbox designpage-image-link" data-gallery="designpage-' + p.id + '"><img src="' + site_url + '/uploads/blueprints/' + att.stored_name + '" alt="' + self.escHtml(att.filename) + '"></a>';
                    }
                });
                imagesHtml += '</div>';
            }

            var specCount = (p.page_specifications || []).length;
            html += '<div class="card-item">' +
                '<div class="card-item-title">' + self.escHtml(p.title) + '</div>' +
                '<div class="card-item-desc">' + GlobalSanitize.sanitizeHtml(p.description || '') + '</div>' +
                imagesHtml +
                '<div class="card-item-actions">' +
                '<a href="' + site_url + '/blueprints/design-pages/' + (p.id_encrypted || p.id) + '/specifications" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-list-alt"></i> Manage Specs (' + specCount + ')</a>' +
                (canUpdate ? '<button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="BlueprintDetail.editDesignPage(\'' + (p.id_encrypted || p.id) + '\')"><i class="fas fa-edit"></i></button>' : '') +
                (canUpdate ? '<button class="sap-btn sap-btn-danger sap-btn-sm" onclick="BlueprintDetail.deleteDesignPage(\'' + (p.id_encrypted || p.id) + '\')"><i class="fas fa-trash"></i></button>' : '') +
                '</div></div>';
        });

        container.html(html);
        GLightbox({ selector: '.designpage-image-link', touchNavigation: true, loop: false });
    },

    // ===========================
    // COMMENTS
    // ===========================

    updateCommentsSection: function (comments) {
        var container = $('#commentsContainer');
        if (!container.length) return;

        var badge = $('#commentCountBadge');
        if (badge.length) badge.text(comments ? comments.length : 0);

        var formHtml = '<form id="commentForm" class="mt-3" style="border-top:1px solid var(--sap-border-light);padding-top:16px">' +
            '<div class="mb-2"><textarea class="sap-input" id="commentText" rows="2" placeholder="Write a comment..." style="min-height:60px"></textarea></div>' +
            '<button class="sap-btn sap-btn-primary sap-btn-sm" type="submit"><i class="fas fa-paper-plane"></i> Send</button>' +
            '</form>';

        if (!comments || !comments.length) {
            container.html(GlobalSanitize.sanitizeHtml(
                '<div class="sap-empty" style="padding:20px"><i class="fas fa-comment-dots" style="font-size:36px"></i><h4>No comments</h4></div>'
            ) + formHtml);
            return;
        }

        var self = this;
        var html = '';
        comments.forEach(function (c) {
            var initial = c.full_name ? c.full_name.charAt(0).toUpperCase() : '?';
            html += '<div class="sap-comment">' +
                '<div class="sap-comment-header">' +
                '<div class="avatar-circle avatar-circle-sm" style="background:#758CA4;color:#fff">' + initial + '</div>' +
                '<span class="sap-comment-author">' + self.escHtml(c.full_name || '') + '</span>' +
                '<span class="sap-comment-time">' + self.escHtml(c.created_at || '') + '</span>' +
                '</div>' +
                '<div class="sap-comment-body">' + (c.content || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') + '</div>' +
                '</div>';
        });

        container.html(html + formHtml);
    },

    // ===========================
    // ATTACHMENTS
    // ===========================

    updateAttachmentsSection: function (attachments) {
        var container = $('#attachmentsContainer');
        if (!container.length) return;

        if (!attachments || !attachments.length) {
            container.html('<p class="text-muted mb-0" style="font-size:13px">No attachments yet.</p>');
            return;
        }

        var html = '<div class="d-flex flex-wrap gap-2">';
        var self = this;

        attachments.forEach(function (att) {
            var isImage = att.mime_type && att.mime_type.indexOf('image/') === 0;
            if (isImage) {
                html += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" class="glightbox blueprint-attachment-link" data-gallery="blueprint-attachments" data-description="' + self.escHtml(att.filename) + '">' +
                    '<img src="' + site_url + '/uploads/blueprints/' + att.stored_name + '" alt="' + self.escHtml(att.filename) + '" style="max-width:80px;max-height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border);cursor:pointer" class="sap-hover-lift"></a>';
            } else {
                var mime = att.mime_type || '';
                var iconClass = 'fas fa-file';
                var iconColor = 'var(--sap-text-muted)';
                if (mime === 'application/pdf') { iconClass = 'fas fa-file-pdf'; iconColor = 'var(--sap-error)'; }
                else if (mime.indexOf('spreadsheet') !== -1 || mime === 'application/vnd.ms-excel') { iconClass = 'fas fa-file-excel'; iconColor = '#217346'; }
                else if (mime.indexOf('word') !== -1 || mime === 'application/msword') { iconClass = 'fas fa-file-word'; iconColor = '#2B579A'; }
                html += '<a href="' + site_url + '/uploads/blueprints/' + att.stored_name + '" target="_blank">' +
                    '<div style="padding:8px 12px;background:var(--sap-background);border-radius:6px;border:1px solid var(--sap-border);font-size:12px">' +
                    '<i class="' + iconClass + '" style="color:' + iconColor + ';margin-right:4px"></i>' + self.escHtml(att.filename) + '</div></a>';
            }
        });

        html += '</div>';
        container.html(html);

        if (typeof GLightbox !== 'undefined') {
            GLightbox({ selector: '.blueprint-attachment-link', touchNavigation: true, keyboardNavigation: true, loop: false, preload: true });
        }
    },

    // ===========================
    // FILE PREVIEWS
    // ===========================

    renderScenarioFiles: function (scenarioFiles, scenarioExistingAttachments, scenarioDeletedAttachments) {
        var preview = $('#scenarioFilePreview');
        preview.empty();
        var baseUrl = site_url + '/uploads/blueprints/';
        var removeBtnStyle = 'position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center';
        var thumbStyle = 'width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)';
        var fileBoxStyle = 'width:80px;height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:6px;border:1px solid var(--sap-border);background:var(--sap-background);font-size:11px;text-align:center;padding:4px;overflow:hidden';
        var self = this;

        scenarioExistingAttachments.forEach(function (att) {
            if (scenarioDeletedAttachments.indexOf(att.id) !== -1) return;
            var wrapper = $('<div style="position:relative;display:inline-block"></div>');
            if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                wrapper.append('<img src="' + baseUrl + att.stored_name + '" style="' + thumbStyle + '">');
            } else {
                wrapper.append('<div style="' + fileBoxStyle + '"><i class="fas fa-file mb-1"></i><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70px">' + self.escHtml(att.filename) + '</span></div>');
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
                wrapper.append('<div style="' + fileBoxStyle + '"><i class="fas fa-file mb-1"></i><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70px">' + self.escHtml(file.name) + '</span></div>');
                wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + i + '" style="' + removeBtnStyle + '">&times;</button>');
            }
            preview.append(wrapper);
        });
    },

    renderDesignPageFiles: function (designPageFiles, designPageExistingAttachments, designPageDeletedAttachments) {
        var preview = $('#designPageFilePreview');
        preview.empty();
        var baseUrl = site_url + '/uploads/blueprints/';
        var removeBtnStyle = 'position:absolute;top:-6px;right:-6px;background:var(--sap-error);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:11px;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center';
        var thumbStyle = 'width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)';
        var fileBoxStyle = 'width:80px;height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:6px;border:1px solid var(--sap-border);background:var(--sap-background);font-size:11px;text-align:center;padding:4px;overflow:hidden';
        var self = this;

        designPageExistingAttachments.forEach(function (att) {
            if (designPageDeletedAttachments.indexOf(att.id) !== -1) return;
            var wrapper = $('<div style="position:relative;display:inline-block"></div>');
            if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                wrapper.append('<img src="' + baseUrl + att.stored_name + '" style="' + thumbStyle + '">');
            } else {
                wrapper.append('<div style="' + fileBoxStyle + '"><i class="fas fa-file mb-1"></i><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70px">' + self.escHtml(att.filename) + '</span></div>');
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
                wrapper.append('<div style="' + fileBoxStyle + '"><i class="fas fa-file mb-1"></i><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70px">' + self.escHtml(file.name) + '</span></div>');
                wrapper.append('<button type="button" class="btn-remove-file" data-idx="' + i + '" style="' + removeBtnStyle + '">&times;</button>');
            }
            preview.append(wrapper);
        });
    }
};

window.BlueprintDetailUI = BlueprintDetailUI;
