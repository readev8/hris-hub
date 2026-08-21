/**
 * ============================================================================
 * Master Projects Page Specifications
 * ============================================================================
 *
 * Page specification detail with kanban board, design page assignment,
 * and specification coverage display.
 *
 * Dependencies: jQuery, Bootstrap, Toastr, SweetAlert2, Sortable
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const PageSpecs = {

    // ===========================
    // CONSTANTS
    // ===========================

    _KANBAN_STATUS_MAP: { open: 0, in_progress: 2, resolved: 3, closed: 4 },
    _KANBAN_STATUS_LABELS: { 0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed' },

    // ===========================
    // STATE
    // ===========================

    _projectId: '',
    _moduleId: '',
    _pageId: '',
    _designPageModalInstance: null,
    _kanbanData: {},
    _kanbanSortables: [],
    _filterType: '',

    // ===========================
    // HELPERS
    // ===========================

    escHtml: function (s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    // ===========================
    // TAB SWITCHING
    // ===========================

    switchTab: function (tab) {
        var self = PageSpecs;
        $('#pageDetailTabs .sap-tab').removeClass('active');
        $('#pageDetailTabs .sap-tab[data-tab="' + tab + '"]').addClass('active');
        $('.tab-content').hide();
        $('#tab-' + tab).show();
        if (tab === 'kanban') self._loadKanban();
    },

    // ===========================
    // DESIGN PAGE MODAL
    // ===========================

    _getDesignPageModal: function () {
        if (!PageSpecs._designPageModalInstance) {
            PageSpecs._designPageModalInstance = new bootstrap.Modal(document.getElementById('designPageModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return PageSpecs._designPageModalInstance;
    },

    openAssignDesignPageModal: function () {
        var self = PageSpecs;
        $('#designPagesLoading').show();
        $('#designPagesEmpty').hide();
        $('#designPagesList').html('');
        self._getDesignPageModal().show();

        $.get(site_url + '/design-pages/available?module_id=' + self._moduleId, function (res) {
            $('#designPagesLoading').hide();
            if (!res || !res.length) {
                $('#designPagesEmpty').show();
                return;
            }
            var html = '';
            for (var g = 0; g < res.length; g++) {
                var group = res[g];
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
            $('#designPagesList').html(html);
            $('#designPagesList .design-page-row').on('click', function () {
                var dpId = $(this).data('id');
                self._assignDesignPage(dpId);
            });
        }).fail(function () {
            $('#designPagesLoading').hide();
            toastr.error('Failed to load design pages');
        });
    },

    _assignDesignPage: function (designPageId) {
        $.post(site_url + '/pages/' + PageSpecs._pageId + '/assign-design-page', { blueprint_design_page_id: designPageId }, function (res) {
            if (res.status) {
                toastr.success('Design page assigned');
                bootstrap.Modal.getInstance(document.getElementById('designPageModal')).hide();
                window.location.reload();
            } else {
                toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
            }
        }).fail(function (xhr) {
            toastr.error('Failed to assign design page (HTTP ' + xhr.status + ')');
        });
    },

    unlinkDesignPage: function () {
        Swal.fire({
            title: 'Remove design page assignment?',
            text: 'This page will no longer be linked to a blueprint design page.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
            confirmButtonText: 'Remove',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.post(site_url + '/pages/' + PageSpecs._pageId + '/unassign-design-page', function (res) {
                    if (res.status) {
                        toastr.success('Design page unassigned');
                        window.location.reload();
                    } else {
                        toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
                    }
                }).fail(function (xhr) {
                    toastr.error('Failed to unlink design page (HTTP ' + xhr.status + ')');
                });
            }
        });
    },

    // ===========================
    // KANBAN BOARD
    // ===========================

    _loadKanban: function () {
        var self = PageSpecs;
        $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-spinner fa-spin"></i>Loading...</div>');
        var params = 'pageId=' + encodeURIComponent(self._pageId);
        $.ajax({
            url: site_url + '/master-projects/' + self._projectId + '/kanban?' + params,
            method: 'GET',
            timeout: 15000,
        })
        .done(function (res) {
            self._kanbanData = res || { open: [], in_progress: [], resolved: [], closed: [] };
            self._renderKanban();
            self._initKanbanSortables();
        })
        .fail(function () {
            toastr.error('Failed to load kanban board');
            $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-exclamation-triangle"></i>Failed to load kanban.</div>');
        });
    },

    _renderKanban: function () {
        var self = PageSpecs;
        var columns = ['open', 'in_progress', 'resolved', 'closed'];
        for (var c = 0; c < columns.length; c++) {
            var key = columns[c];
            var tickets = self._kanbanData[key] || [];
            if (self._filterType) {
                tickets = [];
                for (var i = 0; i < (self._kanbanData[key] || []).length; i++) {
                    if (String(self._kanbanData[key][i].type) === String(self._filterType)) {
                        tickets.push(self._kanbanData[key][i]);
                    }
                }
            }
            var $col = $('#kanban-col-' + key);
            $('#kanban-count-' + key).text(tickets.length);

            if (!tickets.length) {
                $col.html('<div class="kanban-empty"><i class="fas fa-inbox"></i>No tickets</div>');
                continue;
            }

            var html = '';
            for (var j = 0; j < tickets.length; j++) {
                html += self._buildKanbanCard(tickets[j]);
            }
            $col.html(html);
        }
    },

    _buildKanbanCard: function (t) {
        var self = PageSpecs;
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
        html += '</div>';
        html += '</div>';
        return html;
    },

    _initKanbanSortables: function () {
        var self = PageSpecs;
        for (var s = 0; s < self._kanbanSortables.length; s++) {
            self._kanbanSortables[s].destroy();
        }
        self._kanbanSortables = [];

        var columns = ['open', 'in_progress', 'resolved', 'closed'];
        for (var c = 0; c < columns.length; c++) {
            var el = document.getElementById('kanban-col-' + columns[c]);
            if (!el) continue;
            var sortable = new Sortable(el, {
                group: 'kanban',
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
                onStart: function (evt) {
                    $(evt.item).css('transition', 'none');
                },
                onEnd: function (evt) {
                    $(evt.item).css('transition', '');
                    var ticketId = evt.item.getAttribute('data-id');
                    var newColumn = evt.to.id.replace('kanban-col-', '');
                    var newStatus = self._KANBAN_STATUS_MAP[newColumn];
                    var oldStatus = parseInt(evt.item.getAttribute('data-status'));

                    if (newStatus === oldStatus) return;

                    var allowed = self._getAllowedTransitions(oldStatus);
                    if (allowed.indexOf(newStatus) === -1) {
                        toastr.warning('Transition not allowed');
                        self._renderKanban();
                        self._initKanbanSortables();
                        return;
                    }

                    self._moveTicket(ticketId, newStatus, oldStatus, evt.item);
                }
            });
            self._kanbanSortables.push(sortable);
        }
    },

    _getAllowedTransitions: function (currentStatus) {
        switch (currentStatus) {
            case 0: return [2, 4];
            case 1: return [2];
            case 2: return [3];
            case 3: return [0, 4];
            default: return [];
        }
    },

    _moveTicket: function (ticketId, newStatus, oldStatus, cardEl) {
        var self = PageSpecs;
        var $card = $(cardEl);
        $card.css('background', 'var(--sap-brand-hover)').css('opacity', '0.7');

        $.post(site_url + '/tickets/' + ticketId + '/move', { new_status: newStatus }, function (res) {
            if (res.status) {
                $card.attr('data-status', newStatus);
                $card.css('background', '').css('opacity', '');
                toastr.success('Ticket moved to ' + self._KANBAN_STATUS_LABELS[newStatus]);
            } else {
                $card.css('background', '').css('opacity', '');
                toastr.error(res.data && res.data.message ? res.data.message : 'Failed to move ticket');
                self._renderKanban();
                self._initKanbanSortables();
            }
        }).fail(function () {
            $card.css('background', '').css('opacity', '');
            toastr.error('Failed to move ticket');
            self._renderKanban();
            self._initKanbanSortables();
        });
    },

    // ===========================
    // KANBAN FILTERS
    // ===========================

    _bindFilterEvents: function () {
        var self = PageSpecs;
        $('#kanbanTypeFilter').on('click', '.kanban-filter-type-btn', function () {
            $('#kanbanTypeFilter .kanban-filter-type-btn').removeClass('active');
            $(this).addClass('active');
            self._filterType = $(this).data('type') || '';
            self._renderKanban();
        });
    },

    _bindDesignPageModalDismiss: function () {
        $('#designPageModal').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        });
    },

    // ===========================
    // PUBLIC API
    // ===========================

    init: function () {
        var self = PageSpecs;
        var pageData = window.PageData || {};
        self._projectId = pageData.projectId || '';
        self._moduleId = pageData.moduleId || '';
        self._pageId = pageData.pageId || '';

        self._bindDesignPageModalDismiss();
        self._bindFilterEvents();
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    PageSpecs.init();
});

// ===========================
// EXPORTS
// ===========================

window.PageSpecs = PageSpecs;
