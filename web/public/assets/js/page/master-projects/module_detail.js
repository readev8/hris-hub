/**
 * ============================================================================
 * Module Detail
 * ============================================================================
 *
 * Module detail page with page CRUD, module CRUD, kanban board, ticket
 * drawer, design page assignment, and design page import.
 *
 * Dependencies: jQuery, Bootstrap, Toastr, SweetAlert2, Sortable
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const ModuleDetail = {

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
    _pageIds: [],
    _kanbanData: {},
    _kanbanSortables: [],
    _pageModalInstance: null,
    _moduleModalInstance: null,
    _bugListModalInstance: null,
    _designPageModalInstance: null,
    _drawerTicketId: null,
    _filterPage: '',
    _filterType: '',

    // ===========================
    // HELPERS
    // ===========================

    escHtml: function (s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    escAttr: function (s) {
        return String(s || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    },

    // ===========================
    // TAB SWITCHING
    // ===========================

    switchDetailTab: function (tab) {
        $('#detailTabs .sap-tab').removeClass('active');
        $('#detailTabs .sap-tab[data-tab="' + tab + '"]').addClass('active');
        $('.tab-content').hide();
        $('#tab-' + tab).show();
        if (tab === 'kanban') ModuleDetail._loadKanban();
    },

    // ===========================
    // PAGE MODAL
    // ===========================

    _getPageModal: function () {
        if (!ModuleDetail._pageModalInstance) {
            ModuleDetail._pageModalInstance = new bootstrap.Modal(document.getElementById('pageModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return ModuleDetail._pageModalInstance;
    },

    openPageModal: function () {
        $('#pageModuleId').val(ModuleDetail._moduleId);
        $('#pageEditId').val('');
        $('#pageName').val('');
        $('#pageUrl').val('');
        $('#pageDesc').val('');
        $('#pageModalTitle').text('Add Page');
        ModuleDetail._getPageModal().show();
    },

    editPage: function (id, moduleIdVal, name, urlPath, description) {
        $('#pageModuleId').val(moduleIdVal);
        $('#pageEditId').val(id);
        $('#pageName').val(name);
        $('#pageUrl').val(urlPath);
        $('#pageDesc').val(description || '');
        $('#pageModalTitle').text('Edit Page');
        ModuleDetail._getPageModal().show();
    },

    _bindPageForm: function () {
        $('#pageForm').on('submit', function (e) {
            e.preventDefault();
            var editId = $('#pageEditId').val();
            var url = editId
                ? site_url + '/pages/' + editId + '/update'
                : site_url + '/modules/' + ModuleDetail._moduleId + '/pages';
            var data = $(this).serialize();
            $.post(url, data, function (res) {
                if (res.status) {
                    toastr.success(editId ? 'Page updated' : 'Page created');
                    bootstrap.Modal.getInstance(document.getElementById('pageModal')).hide();
                    window.location.reload();
                } else {
                    toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
                }
            }).fail(function (xhr) {
                toastr.error('Failed to save page (HTTP ' + xhr.status + ')');
            });
        });
    },

    deletePage: function (id) {
        Swal.fire({
            title: 'Delete this page?',
            text: 'Linked bug tickets will remain but page reference will be removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
            confirmButtonText: 'Delete',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.post(site_url + '/pages/' + id + '/delete', function (res) {
                    if (res.status) {
                        toastr.success('Page deleted');
                        window.location.reload();
                    } else {
                        toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
                    }
                }).fail(function (xhr) {
                    toastr.error('Failed to delete page (HTTP ' + xhr.status + ')');
                });
            }
        });
    },

    // ===========================
    // MODULE MODAL
    // ===========================

    _getModuleModal: function () {
        if (!ModuleDetail._moduleModalInstance) {
            ModuleDetail._moduleModalInstance = new bootstrap.Modal(document.getElementById('moduleModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return ModuleDetail._moduleModalInstance;
    },

    editModule: function (id, name, desc) {
        $('#moduleEditId').val(id);
        $('#moduleName').val(name);
        $('#moduleDesc').val(desc);
        ModuleDetail._getModuleModal().show();
    },

    _bindModuleForm: function () {
        $('#moduleForm').on('submit', function (e) {
            e.preventDefault();
            var editId = $('#moduleEditId').val();
            var url = site_url + '/modules/' + editId + '/update';
            var data = $(this).serialize();
            $.post(url, data, function (res) {
                if (res.status) {
                    toastr.success('Module updated');
                    bootstrap.Modal.getInstance(document.getElementById('moduleModal')).hide();
                    window.location.reload();
                } else {
                    toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
                }
            }).fail(function (xhr) {
                toastr.error('Failed to save module (HTTP ' + xhr.status + ')');
            });
        });
    },

    deleteModule: function (id) {
        var self = ModuleDetail;
        Swal.fire({
            title: 'Delete this module?',
            text: 'All pages within will also be deleted. You will be redirected to the project page.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
            confirmButtonText: 'Delete',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.post(site_url + '/modules/' + id + '/delete', function (res) {
                    if (res.status) {
                        toastr.success('Module deleted');
                        window.location.href = site_url + '/master-projects/' + self._projectId;
                    } else {
                        toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
                    }
                }).fail(function (xhr) {
                    toastr.error('Failed to delete module (HTTP ' + xhr.status + ')');
                });
            }
        });
    },

    // ===========================
    // BUG LIST MODAL
    // ===========================

    _getBugListModal: function () {
        if (!ModuleDetail._bugListModalInstance) {
            ModuleDetail._bugListModalInstance = new bootstrap.Modal(document.getElementById('bugListModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return ModuleDetail._bugListModalInstance;
    },

    showBugList: function (pageId, pageName) {
        $('#bugPageName').text(pageName);
        var modal = ModuleDetail._getBugListModal();
        $('#bugListBody').html('<div class="text-center p-4"><span class="sap-spinner"></span></div>');
        modal.show();
        $.get(site_url + '/pages/' + pageId + '/bugs', function (res) {
            $('#bugListBody').html(ModuleDetailUI.buildBugListHTML(res));
        }).fail(function (xhr) {
            $('#bugListBody').html('<div class="sap-empty" style="padding:32px"><i class="fas fa-exclamation-triangle"></i><h4>Failed to load bug data</h4><p>HTTP ' + xhr.status + '</p></div>');
            toastr.error('Failed to load bug data');
        });
    },

    _bindModalDismiss: function () {
        $('#pageModal, #bugListModal, #moduleModal, #designPageModal, #importDesignPagesModal').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        });
    },

    // ===========================
    // BLUEPRINT DESIGN PAGE ASSIGNMENT
    // ===========================

    _getDesignPageModal: function () {
        if (!ModuleDetail._designPageModalInstance) {
            ModuleDetail._designPageModalInstance = new bootstrap.Modal(document.getElementById('designPageModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return ModuleDetail._designPageModalInstance;
    },

    openAssignDesignPageModal: function (pageId) {
        var self = ModuleDetail;
        $('#assignDesignPageTargetId').val(pageId);
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
            var html = ModuleDetailUI.buildDesignPageListHTML(res);
            $('#designPagesList').html(html);
            $('#designPagesList .design-page-row').on('click', function () {
                var dpId = $(this).data('id');
                self._assignBlueprintDesignPage(dpId);
            });
        }).fail(function () {
            $('#designPagesLoading').hide();
            toastr.error('Failed to load design pages');
        });
    },

    _assignBlueprintDesignPage: function (designPageId) {
        var pageId = $('#assignDesignPageTargetId').val();
        $.post(site_url + '/pages/' + pageId + '/assign-design-page', { blueprint_design_page_id: designPageId }, function (res) {
            if (res.status) {
                toastr.success('Blueprint design page assigned');
                bootstrap.Modal.getInstance(document.getElementById('designPageModal')).hide();
                window.location.reload();
            } else {
                toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
            }
        }).fail(function (xhr) {
            toastr.error('Failed to assign design page (HTTP ' + xhr.status + ')');
        });
    },

    unassignBlueprintDesignPage: function (pageId) {
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
                $.post(site_url + '/pages/' + pageId + '/unassign-design-page', function (res) {
                    if (res.status) {
                        toastr.success('Design page unassigned');
                        window.location.reload();
                    } else {
                        toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
                    }
                }).fail(function (xhr) {
                    toastr.error('Failed to unassign design page (HTTP ' + xhr.status + ')');
                });
            }
        });
    },

    // ===========================
    // PAGE ROW NAVIGATION
    // ===========================

    navigateToPage: function (pageId) {
        var self = ModuleDetail;
        window.location.href = site_url + '/master-projects/' + self._projectId + '/modules/' + self._moduleId + '/pages/' + pageId;
    },

    // ===========================
    // KANBAN BOARD
    // ===========================

    _loadKanban: function () {
        var self = ModuleDetail;
        $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-spinner fa-spin"></i>Loading...</div>');
        $.ajax({
            url: site_url + '/master-projects/' + self._projectId + '/kanban?moduleId=' + self._moduleId,
            method: 'GET',
            timeout: 15000,
        })
        .done(function (res) {
            self._kanbanData = res || { open: [], in_progress: [], resolved: [], closed: [] };
            self._populatePageFilter();
            ModuleDetailUI.renderKanban(self._kanbanData, self._filterType);
            self._initKanbanSortables();
        })
        .fail(function () {
            toastr.error('Failed to load kanban board');
            $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-exclamation-triangle"></i>Failed to load kanban. Please refresh the page.</div>');
        });
    },

    _initKanbanSortables: function () {
        var self = ModuleDetail;
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
                        ModuleDetailUI.renderKanban(self._kanbanData, self._filterType);
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
        var self = ModuleDetail;
        var $card = $(cardEl);
        var originalBg = $card.css('background');
        $card.css('background', 'var(--sap-brand-hover)').css('opacity', '0.7');

        $.post(site_url + '/tickets/' + ticketId + '/move', { new_status: newStatus }, function (res) {
            if (res.status) {
                $card.attr('data-status', newStatus);
                $card.css('background', '').css('opacity', '');
                toastr.success('Ticket moved to ' + self._KANBAN_STATUS_LABELS[newStatus]);
            } else {
                $card.css('background', '').css('opacity', '');
                toastr.error(res.data && res.data.message ? res.data.message : 'Failed to move ticket');
                ModuleDetailUI.renderKanban(self._kanbanData, self._filterType);
                self._initKanbanSortables();
            }
        }).fail(function (xhr) {
            var res = null;
            try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
            if (res && res.redirect) {
                window.location.href = res.redirect;
                return;
            }
            $card.css('background', '').css('opacity', '');
            toastr.error('Failed to move ticket');
            ModuleDetailUI.renderKanban(self._kanbanData, self._filterType);
            self._initKanbanSortables();
        });
    },

    // ===========================
    // KANBAN CARD CLICK
    // ===========================

    _bindKanbanCardClick: function () {
        $(document).on('click', '.kanban-card', function (e) {
            if ($(e.target).closest('a').length) return;
            var cardId = $(this).data('id');
            ModuleDetail._openTicketDrawer(cardId);
        });
    },

    // ===========================
    // TICKET DETAIL DRAWER
    // ===========================

    _openTicketDrawer: function (ticketId) {
        var self = ModuleDetail;
        self._drawerTicketId = ticketId;
        var $scrim = $('#ticketDrawerScrim');
        var $drawer = $('#ticketDrawer');
        var $body = $('#ticketDrawerBody');

        $body.html(
            '<div class="ticket-drawer-skeleton">' +
            '<div class="skeleton-line skeleton-lg"></div>' +
            '<div class="skeleton-line skeleton-sm"></div>' +
            '<div class="skeleton-line skeleton-md"></div>' +
            '<div class="skeleton-line skeleton-sm"></div>' +
            '<div class="skeleton-line skeleton-lg"></div>' +
            '<div class="skeleton-line skeleton-md"></div>' +
            '</div>'
        );

        $scrim.addClass('open');
        $drawer.addClass('open');
        $('body').css('overflow', 'hidden');

        $.ajax({
            url: site_url + '/tickets/' + ticketId + '/detail-json',
            method: 'GET',
            timeout: 15000,
        })
        .done(function (ticket) {
            if (!ticket) {
                $body.html('<div class="sap-empty" style="padding:32px 20px"><i class="fas fa-exclamation-triangle"></i><h4>Ticket not found</h4></div>');
                return;
            }
            ModuleDetailUI.renderTicketDetail(ticket, self._drawerTicketId);
        })
        .fail(function (xhr) {
            $body.html('<div class="sap-empty" style="padding:32px 20px"><i class="fas fa-exclamation-triangle"></i><h4>Failed to load ticket</h4><p>HTTP ' + xhr.status + '</p></div>');
        });
    },

    _closeDrawer: function () {
        $('#ticketDrawerScrim').removeClass('open');
        $('#ticketDrawer').removeClass('open');
        $('body').css('overflow', '');
        ModuleDetail._drawerTicketId = null;
    },

    _bindDrawerEvents: function () {
        $('#ticketDrawerClose').on('click', ModuleDetail._closeDrawer);
        $('#ticketDrawerScrim').on('click', ModuleDetail._closeDrawer);
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && ModuleDetail._drawerTicketId) {
                ModuleDetail._closeDrawer();
            }
        });
    },

    // ===========================
    // KANBAN FILTERS
    // ===========================

    _populatePageFilter: function () {
        var pages = (window.PageData || {}).pages || [];
        var $sel = $('#kanbanPageFilter');
        $sel.find('option:gt(0)').remove();
        var seen = {};
        for (var i = 0; i < pages.length; i++) {
            var pg = pages[i];
            if (pg.name && !seen[pg.id]) {
                seen[pg.id] = true;
                $sel.append('<option value="' + pg.id + '">' + ModuleDetail.escHtml(pg.name) + '</option>');
            }
        }
    },

    _applyKanbanFilters: function () {
        var self = ModuleDetail;
        self._filterPage = $('#kanbanPageFilter').val() || '';
        self._filterType = $('#kanbanTypeFilter .kanban-filter-type-btn.active').data('type') || '';

        var hasFilter = self._filterPage || self._filterType;
        $('#kanbanFilterResetWrap').toggle(hasFilter);

        var columns = ['open', 'in_progress', 'resolved', 'closed'];
        for (var c = 0; c < columns.length; c++) {
            var key = columns[c];
            var tickets = self._kanbanData[key] || [];
            var filtered = [];
            for (var i = 0; i < tickets.length; i++) {
                var t = tickets[i];
                if (self._filterPage && t.page_id != self._filterPage) continue;
                if (self._filterType && String(t.type) !== String(self._filterType)) continue;
                filtered.push(t);
            }
            ModuleDetailUI.renderFilteredColumn(key, filtered);
            $('#kanban-count-' + key).text(filtered.length);
        }
    },

    resetKanbanFilters: function () {
        var self = ModuleDetail;
        $('#kanbanPageFilter').val('');
        $('#kanbanTypeFilter .kanban-filter-type-btn').removeClass('active');
        $('#kanbanTypeFilter .kanban-filter-type-btn[data-type=""]').addClass('active');
        self._filterPage = '';
        self._filterType = '';
        $('#kanbanFilterResetWrap').hide();
        ModuleDetailUI.renderKanban(self._kanbanData, self._filterType);
    },

    _bindFilterEvents: function () {
        var self = ModuleDetail;
        $('#kanbanPageFilter').on('change', function () { self._applyKanbanFilters(); });
        $('#kanbanTypeFilter').on('click', '.kanban-filter-type-btn', function () {
            $('#kanbanTypeFilter .kanban-filter-type-btn').removeClass('active');
            $(this).addClass('active');
            self._applyKanbanFilters();
        });
    },

    // ===========================
    // IMPORT DESIGN PAGES
    // ===========================

    _bindImportEvents: function () {
        $('#importSearchInput').on('keyup', function () {
            var search = $(this).val().toLowerCase();
            $('.import-design-item').each(function () {
                var name = $(this).find('.import-design-name').text().toLowerCase();
                var meta = $(this).find('.import-design-meta').text().toLowerCase();
                $(this).toggle(name.indexOf(search) !== -1 || meta.indexOf(search) !== -1);
            });
        });

        $('#importSelectAll').on('change', function () {
            var checked = $(this).is(':checked');
            $('.import-design-cb:not(:disabled)').prop('checked', checked);
            ModuleDetail._updateImportCount();
        });

        $(document).on('change', '.import-design-cb', function () {
            ModuleDetail._updateImportCount();
        });
    },

    showImportModal: function () {
        ModuleDetail._loadAvailableDesignPages();
        $('#importSearchInput').val('');
        $('#importSelectAll').prop('checked', false);
        $('#importCountBadge').text('0 selected');
        $('#importSelectedInfo').text('0 pages selected');
        $('#importBtn').prop('disabled', true);
        var modal = new bootstrap.Modal(document.getElementById('importDesignPagesModal'));
        modal.show();
    },

    _loadAvailableDesignPages: function () {
        var self = ModuleDetail;
        var $list = $('#importDesignPagesList');
        var $loading = $('#importDesignPagesLoading');
        var $empty = $('#importDesignPagesEmpty');
        var $skipped = $('#importDesignPagesSkipped');
        var $selectWrap = $('#importSelectAllWrap');

        $loading.show();
        $empty.hide();
        $skipped.hide();
        $selectWrap.hide();
        $list.html('');

        $.get(site_url + '/design-pages/available?module_id=' + self._moduleId, function (res) {
            $loading.hide();
            var groups = res || [];
            if (!groups.length) {
                $empty.show();
                $('#importModalSubtitle').text('No design pages available');
                return;
            }

            var importedIds = {};
            var pages = (window.PageData || {}).pages || [];
            for (var p = 0; p < pages.length; p++) {
                if (pages[p].blueprint_design_page_id) {
                    importedIds[pages[p].blueprint_design_page_id] = true;
                }
            }

            var result = ModuleDetailUI.buildImportDesignPagesHTML(groups, importedIds);
            $list.html(result.html);

            var subtitle = result.availableCount + ' available';
            if (result.skippedCount > 0) {
                subtitle += ' \u00b7 ' + result.skippedCount + ' already imported';
            }
            $('#importModalSubtitle').text(subtitle);

            if (result.availableCount > 0) {
                $selectWrap.show();
                $('#importSelectAllText').text('Select All (' + result.availableCount + ' available)');
            }

            if (result.skippedCount > 0) {
                $skipped.text(result.skippedCount + ' page(s) already imported (skipped)').show();
            }
        }).fail(function () {
            $loading.hide();
            $empty.show();
            $('#importModalSubtitle').text('Failed to load');
            toastr.error('Failed to load design pages');
        });
    },

    _updateImportCount: function () {
        var total = $('.import-design-cb:not(:disabled)').length;
        var checked = $('.import-design-cb:checked:not(:disabled)').length;
        $('#importCountBadge').text(checked + ' selected');
        $('#importSelectedInfo').text(checked + ' page(s) selected');
        $('#importBtn').prop('disabled', checked === 0);
        $('#importSelectAll').prop('checked', total > 0 && total === checked);
    },

    importSelectedDesignPages: function () {
        var selected = [];
        var names = [];
        $('.import-design-cb:checked:not(:disabled)').each(function () {
            selected.push($(this).val());
            var $item = $(this).closest('.import-design-item');
            names.push($item.find('.import-design-name').text());
        });

        if (!selected.length) {
            toastr.warning('Select at least one design page');
            return;
        }

        var confirmHtml = '<div style="text-align:left">';
        confirmHtml += '<p>Import ' + selected.length + ' design page(s)?</p>';
        confirmHtml += '<ul style="margin:8px 0 0 16px;font-size:13px">';
        for (var i = 0; i < names.length; i++) {
            confirmHtml += '<li>' + ModuleDetail.escHtml(names[i]) + '</li>';
        }
        confirmHtml += '</ul>';
        confirmHtml += '<p class="text-muted" style="margin-top:8px;font-size:12px">Pages will be created with links to original blueprint design pages.</p>';
        confirmHtml += '</div>';

        Swal.fire({
            title: 'Import Design Pages',
            html: confirmHtml,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0070F2',
            cancelButtonColor: '#758CA4',
            confirmButtonText: 'Yes, Import',
        }).then(function (result) {
            if (result.isConfirmed) {
                ModuleDetail._doImportDesignPages(selected);
            }
        });
    },

    _doImportDesignPages: function (selected) {
        var $btn = $('#importBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');

        $.post(site_url + '/modules/' + ModuleDetail._moduleId + '/import-design-pages', { design_page_ids: selected }, function (res) {
            if (res.status) {
                var msg = 'Successfully imported ' + (res.imported || 0) + ' design page(s)';
                if (res.skipped && res.skipped > 0) {
                    msg += ' (' + res.skipped + ' skipped)';
                }
                toastr.success(msg);
                bootstrap.Modal.getInstance(document.getElementById('importDesignPagesModal')).hide();
                window.location.reload();
            } else {
                toastr.error(res.message || 'Import failed');
                $btn.prop('disabled', false).html('<i class="fas fa-file-import"></i> Import Selected');
            }
        }).fail(function (xhr) {
            toastr.error('Import failed (HTTP ' + xhr.status + ')');
            $btn.prop('disabled', false).html('<i class="fas fa-file-import"></i> Import Selected');
        });
    },

    // ===========================
    // PUBLIC API
    // ===========================

    init: function () {
        var self = ModuleDetail;
        var pageData = window.PageData || {};
        self._projectId = pageData.projectId || '';
        self._moduleId = pageData.moduleId || '';
        self._pageIds = pageData.pageIds || [];

        self._bindPageForm();
        self._bindModuleForm();
        self._bindModalDismiss();
        self._bindKanbanCardClick();
        self._bindDrawerEvents();
        self._bindFilterEvents();
        self._bindImportEvents();
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    ModuleDetail.init();
});

// ===========================
// EXPORTS
// ===========================

window.ModuleDetail = ModuleDetail;
