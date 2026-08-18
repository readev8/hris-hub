/**
 * ============================================================================
 * Module Detail UI
 * ============================================================================
 *
 * UI rendering functions for the module detail page. Extracted from
 * module_detail.js to separate rendering from orchestration.
 *
 * Dependencies: jQuery
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const ModuleDetailUI = {

    // ===========================
    // HELPERS
    // ===========================

    escHtml: function (s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    // ===========================
    // KANBAN RENDERING
    // ===========================

    renderKanban: function (kanbanData, filterType) {
        var self = ModuleDetailUI;
        var columns = ['open', 'in_progress', 'resolved', 'closed'];
        for (var c = 0; c < columns.length; c++) {
            var key = columns[c];
            var tickets = kanbanData[key] || [];
            var $col = $('#kanban-col-' + key);
            $('#kanban-count-' + key).text(tickets.length);

            if (!tickets.length) {
                $col.html('<div class="kanban-empty"><i class="fas fa-inbox"></i>No tickets</div>');
                continue;
            }

            var html = '';
            for (var i = 0; i < tickets.length; i++) {
                html += self.buildKanbanCard(tickets[i]);
            }
            $col.html(html);
        }
    },

    renderFilteredColumn: function (key, tickets) {
        var self = ModuleDetailUI;
        var $col = $('#kanban-col-' + key);
        if (!tickets.length) {
            $col.html('<div class="kanban-empty"><i class="fas fa-inbox"></i>No tickets</div>');
            return;
        }
        var html = '';
        for (var i = 0; i < tickets.length; i++) {
            html += self.buildKanbanCard(tickets[i]);
        }
        $col.html(html);
    },

    buildKanbanCard: function (t) {
        var self = ModuleDetailUI;
        var typeCls = t.type === 0 ? 'bug' : t.type === 1 ? 'issue' : t.type === 3 ? 'change-request' : 'task';
        var prioCls = t.priority_name ? t.priority_name.toLowerCase() : 'medium';

        var html = '<div class="kanban-card" data-id="' + t.id + '" data-status="' + t.status + '">';
        html += '<div class="kanban-card-header">';
        html += '<span class="kanban-priority-dot ' + prioCls + '" title="' + self.escHtml(t.priority_name) + '"></span>';
        html += '<span class="kanban-type-badge ' + typeCls + '">' + self.escHtml(t.type_name) + '</span>';
        html += '</div>';
        html += '<div class="kanban-card-title"><a href="' + site_url + '/tickets/' + t.id + '" target="_blank">' + self.escHtml(t.title) + '</a></div>';
        html += '<div class="kanban-card-meta">';
        if (t.assignee_name) {
            html += '<span class="kanban-card-assignee"><i class="fas fa-user-check"></i> ' + self.escHtml(t.assignee_name) + '</span>';
        }
        if (t.page_name) {
            html += '<span class="kanban-card-page" title="' + self.escHtml(t.page_name) + '"><i class="fas fa-file-alt"></i> ' + self.escHtml(t.page_name) + '</span>';
        }
        html += '</div>';
        html += '</div>';
        return html;
    },

    // ===========================
    // TICKET DETAIL DRAWER
    // ===========================

    renderTicketDetail: function (t, drawerTicketId) {
        var self = ModuleDetailUI;
        var statusCls = 'sap-badge open';
        if (t.status_name === 'In Progress') statusCls = 'sap-badge in-progress';
        else if (t.status_name === 'Resolved') statusCls = 'sap-badge resolved';
        else if (t.status_name === 'Closed') statusCls = 'sap-badge closed';

        var prioCls = t.priority_name ? t.priority_name.toLowerCase() : 'medium';
        var typeCls = t.type === 0 ? 'bug' : t.type === 1 ? 'issue' : t.type === 3 ? 'change-request' : 'task';

        var html = '';
        html += '<div class="drawer-detail-section">';
        html += '<div class="drawer-detail-badges">';
        html += '<span class="' + statusCls + '"><span class="badge-dot"></span>' + self.escHtml(t.status_name) + '</span>';
        html += '<span class="drawer-priority-dot ' + prioCls + '"></span>';
        html += '<span class="kanban-type-badge ' + typeCls + '">' + self.escHtml(t.type_name) + '</span>';
        html += '</div>';
        html += '</div>';

        html += '<div class="drawer-detail-section">';
        html += '<div class="drawer-detail-meta">';
        html += '<div class="drawer-meta-item"><span class="drawer-meta-label">Assignee</span><span class="drawer-meta-value">' + self.escHtml(t.assignee_name || '-') + '</span></div>';
        html += '<div class="drawer-meta-item"><span class="drawer-meta-label">Creator</span><span class="drawer-meta-value">' + self.escHtml(t.creator_name || '-') + '</span></div>';
        html += '<div class="drawer-meta-item"><span class="drawer-meta-label">Page</span><span class="drawer-meta-value">' + self.escHtml(t.page_name || '-') + '</span></div>';
        html += '<div class="drawer-meta-item"><span class="drawer-meta-label">Module</span><span class="drawer-meta-value">' + self.escHtml(t.module_name || '-') + '</span></div>';
        html += '<div class="drawer-meta-item"><span class="drawer-meta-label">Project</span><span class="drawer-meta-value">' + self.escHtml(t.project_name || '-') + '</span></div>';
        html += '<div class="drawer-meta-item"><span class="drawer-meta-label">Created</span><span class="drawer-meta-value">' + self.escHtml(t.created_at || '-') + '</span></div>';
        html += '<div class="drawer-meta-item"><span class="drawer-meta-label">Updated</span><span class="drawer-meta-value">' + self.escHtml(t.updated_at || '-') + '</span></div>';
        html += '</div>';
        html += '</div>';

        if (t.description) {
            html += '<div class="drawer-detail-section">';
            html += '<div class="drawer-detail-desc-header">Description</div>';
            html += '<div class="drawer-detail-desc">' + (typeof GlobalSanitize !== 'undefined' ? GlobalSanitize.sanitizeHtml(t.description) : self.escHtml(t.description)) + '</div>';
            html += '</div>';
        }

        if (t.comments && t.comments.length) {
            html += '<div class="drawer-detail-section">';
            html += '<div class="drawer-detail-desc-header"><i class="fas fa-comments"></i> Comments (' + t.comments.length + ')</div>';
            for (var i = 0; i < t.comments.length; i++) {
                var c = t.comments[i];
                html += '<div class="drawer-comment">';
                html += '<div class="drawer-comment-header"><span class="drawer-comment-author">' + self.escHtml(c.author_name || 'Unknown') + '</span><span class="drawer-comment-date">' + self.escHtml(c.created_at || '') + '</span></div>';
                html += '<div class="drawer-comment-body">' + (typeof GlobalSanitize !== 'undefined' ? GlobalSanitize.sanitizeHtml(c.content) : self.escHtml(c.content)) + '</div>';
                html += '</div>';
            }
            html += '</div>';
        }

        if (t.attachments && t.attachments.length) {
            html += '<div class="drawer-detail-section">';
            html += '<div class="drawer-detail-desc-header"><i class="fas fa-paperclip"></i> Attachments (' + t.attachments.length + ')</div>';
            html += '<div class="drawer-attachments">';
            for (var j = 0; j < t.attachments.length; j++) {
                var att = t.attachments[j];
                html += '<a href="' + site_url + '/uploads/tickets/' + att.stored_name + '" target="_blank" class="drawer-attachment-item">';
                html += '<i class="fas fa-file"></i> ' + self.escHtml(att.filename || att.stored_name);
                html += '</a>';
            }
            html += '</div>';
            html += '</div>';
        }

        $('#ticketDrawerBody').html(html);
        $('#drawerOpenFullPage').attr('href', site_url + '/tickets/' + drawerTicketId);
    },

    // ===========================
    // BUG LIST TABLE
    // ===========================

    buildBugListHTML: function (bugs) {
        var self = ModuleDetailUI;
        if (!bugs || !bugs.length) {
            return '<div class="sap-empty" style="padding:32px"><i class="fas fa-check-circle"></i><h4>No bugs</h4><p>No bugs reported for this page.</p></div>';
        }
        var html = '<table class="sap-table sap-table-compact mb-0"><thead><tr><th style="min-width:200px">Title</th><th style="width:120px">Status</th><th style="width:100px">Priority</th><th style="width:130px">Created</th></tr></thead><tbody>';
        for (var i = 0; i < bugs.length; i++) {
            var b = bugs[i];
            var statusCls = b.status_name === 'Open' ? 'sap-badge open' : b.status_name === 'Resolved' ? 'sap-badge resolved' : 'sap-badge closed';
            var priorityDot = 'priority-dot ' + (b.priority_name ? b.priority_name.toLowerCase() : 'medium');
            html += '<tr><td><a href="' + site_url + '/tickets/' + b.id + '" target="_blank" class="fw-medium" style="color:var(--sap-brand);text-decoration:none">' + self.escHtml(b.title) + '</a></td>'
                 + '<td><span class="' + statusCls + '"><span class="badge-dot"></span>' + self.escHtml(b.status_name) + '</span></td>'
                 + '<td><span class="' + priorityDot + '"></span> ' + self.escHtml(b.priority_name) + '</td>'
                 + '<td><span class="text-muted">' + self.escHtml(b.created_at) + '</span></td></tr>';
        }
        html += '</tbody></table>';
        return html;
    },

    // ===========================
    // DESIGN PAGE LIST
    // ===========================

    buildDesignPageListHTML: function (groups) {
        var self = ModuleDetailUI;
        if (!groups || !groups.length) {
            return '';
        }
        var html = '';
        for (var g = 0; g < groups.length; g++) {
            var group = groups[g];
            html += '<div class="design-page-group">';
            html += '<div class="design-page-group-header"><i class="fas fa-drafting-compass"></i> ' + self.escHtml(group.blueprint_name) + ' &rarr; ' + self.escHtml(group.blueprint_module_name) + '</div>';
            for (var k = 0; k < group.design_pages.length; k++) {
                var dp = group.design_pages[k];
                html += '<div class="design-page-row" data-id="' + dp.id + '">';
                html += '<div class="design-page-row-info">';
                html += '<span class="design-page-row-name">' + self.escHtml(dp.title) + '</span>';
                if (dp.description) {
                    html += '<span class="design-page-row-meta">' + self.escHtml(dp.description) + '</span>';
                }
                html += '</div>';
                html += '<span class="design-page-spec-count">' + dp.spec_count + ' specs</span>';
                html += '<button type="button" class="design-page-row-action"><i class="fas fa-check"></i></button>';
                html += '</div>';
            }
            html += '</div>';
        }
        return html;
    },

    // ===========================
    // IMPORT DESIGN PAGES
    // ===========================

    buildImportDesignPagesHTML: function (groups, importedIds) {
        var self = ModuleDetailUI;
        var html = '';
        var skippedCount = 0;
        var availableCount = 0;

        for (var g = 0; g < groups.length; g++) {
            var group = groups[g];
            var hasAvailable = false;
            for (var k = 0; k < group.design_pages.length; k++) {
                if (!importedIds[group.design_pages[k].id]) {
                    hasAvailable = true;
                    availableCount++;
                }
            }
            if (!hasAvailable) {
                skippedCount += group.design_pages.length;
                continue;
            }

            html += '<div class="import-design-group">';
            html += '<div class="import-design-group-header">';
            html += '<i class="fas fa-drafting-compass"></i> ';
            html += '<strong>' + self.escHtml(group.blueprint_name) + '</strong>';
            html += ' <span style="color:var(--sap-text-muted);margin:0 4px">&rarr;</span> ';
            html += '<span style="color:var(--sap-text-muted);font-weight:400;text-transform:none;letter-spacing:normal">' + self.escHtml(group.blueprint_module_name) + '</span>';
            html += '</div>';

            for (var k2 = 0; k2 < group.design_pages.length; k2++) {
                var dp = group.design_pages[k2];
                var isImported = importedIds[dp.id];

                html += '<div class="import-design-item' + (isImported ? ' imported' : '') + '">';
                html += '<input type="checkbox" class="form-check-input import-design-cb" value="' + dp.id + '"' + (isImported ? ' disabled checked' : '') + '>';
                html += '<div class="import-design-info">';
                html += '<span class="import-design-name">' + self.escHtml(dp.title) + '</span>';
                html += '<span class="import-design-meta">' + self.escHtml(group.blueprint_module_name) + ' &middot; ' + (dp.spec_count || 0) + ' specifications</span>';
                html += '</div>';
                if (isImported) {
                    html += '<span class="import-design-status imported"><i class="fas fa-check"></i> Imported</span>';
                } else {
                    html += '<span class="import-design-status available">Available</span>';
                }
                html += '</div>';
            }
            html += '</div>';
        }

        return { html: html, skippedCount: skippedCount, availableCount: availableCount };
    }
};

// ===========================
// EXPORTS
// ===========================

window.ModuleDetailUI = ModuleDetailUI;
