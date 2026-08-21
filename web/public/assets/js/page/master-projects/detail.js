/**
 * ============================================================================
 * Master Projects Detail
 * ============================================================================
 *
 * Project detail page with module management, kanban board, and blueprint
 * module assignment.
 *
 * Dependencies: jQuery, Bootstrap, Toastr, SweetAlert2, Sortable
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const MasterProjectDetail = {

    // ===========================
    // CONSTANTS
    // ===========================

    _KANBAN_STATUS_MAP: { open: 0, in_progress: 2, resolved: 3, closed: 4 },
    _KANBAN_STATUS_LABELS: { 0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed' },

    // ===========================
    // STATE
    // ===========================

    _projectId: '',
    _kanbanData: {},
    _kanbanSortables: [],
    _moduleModalInstance: null,
    _blueprintModuleModalInstance: null,

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
        var self = MasterProjectDetail;
        $('#detailTabs .sap-tab').removeClass('active');
        $('#detailTabs .sap-tab[data-tab="' + tab + '"]').addClass('active');
        $('.tab-content').hide();
        $('#tab-' + tab).show();
        if (tab === 'kanban') self._loadKanban();
    },

    // ===========================
    // MODULE MODAL
    // ===========================

    _getModuleModal: function () {
        if (!MasterProjectDetail._moduleModalInstance) {
            MasterProjectDetail._moduleModalInstance = new bootstrap.Modal(document.getElementById('moduleModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return MasterProjectDetail._moduleModalInstance;
    },

    openModuleModal: function () {
        $('#moduleEditId').val('');
        $('#moduleName').val('');
        $('#moduleDesc').val('');
        $('#moduleModalTitle').text('Add Module');
        MasterProjectDetail._getModuleModal().show();
    },

    editModule: function (id, name, desc) {
        $('#moduleEditId').val(id);
        $('#moduleName').val(name);
        $('#moduleDesc').val(desc);
        $('#moduleModalTitle').text('Edit Module');
        MasterProjectDetail._getModuleModal().show();
    },

    _bindModuleForm: function () {
        $('#moduleForm').on('submit', function (e) {
            e.preventDefault();
            var editId = $('#moduleEditId').val();
            var isNew = !editId;
            var url = isNew
                ? site_url + '/master-projects/' + MasterProjectDetail._projectId + '/modules'
                : site_url + '/modules/' + editId + '/update';
            var data = $(this).serialize();
            $.post(url, data, function (res) {
                if (res.status) {
                    toastr.success(isNew ? 'Module created' : 'Module updated');
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
        Swal.fire({
            title: 'Delete this module?',
            text: 'All pages within will also be deleted.',
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
                        window.location.reload();
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
    // BLUEPRINT MODULE ASSIGNMENT
    // ===========================

    _getBlueprintModuleModal: function () {
        if (!MasterProjectDetail._blueprintModuleModalInstance) {
            MasterProjectDetail._blueprintModuleModalInstance = new bootstrap.Modal(document.getElementById('blueprintModuleModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return MasterProjectDetail._blueprintModuleModalInstance;
    },

    openAssignBlueprintModal: function (moduleId) {
        var self = MasterProjectDetail;
        $('#assignModuleId').val(moduleId);
        $('#blueprintModulesLoading').show();
        $('#blueprintModulesEmpty').hide();
        $('#blueprintModulesList').html('');
        self._getBlueprintModuleModal().show();

        $.get(site_url + '/blueprint-modules/available', function (res) {
            $('#blueprintModulesLoading').hide();
            if (!res || !res.length) {
                $('#blueprintModulesEmpty').show();
                return;
            }
            var html = '';
            for (var g = 0; g < res.length; g++) {
                var group = res[g];
                html += '<div class="blueprint-module-group">';
                html += '<div class="blueprint-module-group-header"><i class="fas fa-drafting-compass"></i> ' + self.escHtml(group.blueprint_name) + '</div>';
                for (var k = 0; k < group.modules.length; k++) {
                    var bm = group.modules[k];
                    html += '<div class="blueprint-module-row" data-id="' + bm.id + '">';
                    html += '<div class="blueprint-module-row-info">';
                    html += '<span class="blueprint-module-row-name">' + self.escHtml(bm.name) + '</span>';
                    html += '<span class="blueprint-module-row-meta">' + bm.scenario_count + ' scenarios / ' + bm.design_page_count + ' pages</span>';
                    html += '</div>';
                    html += '<button type="button" class="blueprint-module-row-action"><i class="fas fa-check"></i></button>';
                    html += '</div>';
                }
                html += '</div>';
            }
            $('#blueprintModulesList').html(html);
            $('#blueprintModulesList .blueprint-module-row').on('click', function () {
                var bpModId = $(this).data('id');
                self.assignBlueprintModule(bpModId);
            });
        }).fail(function () {
            $('#blueprintModulesLoading').hide();
            toastr.error('Failed to load blueprint modules');
        });
    },

    assignBlueprintModule: function (blueprintModuleId) {
        var moduleId = $('#assignModuleId').val();
        $.post(site_url + '/modules/' + moduleId + '/assign-blueprint', { blueprint_module_id: blueprintModuleId }, function (res) {
            if (res.status) {
                toastr.success('Blueprint module assigned');
                bootstrap.Modal.getInstance(document.getElementById('blueprintModuleModal')).hide();
                window.location.reload();
            } else {
                toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
            }
        }).fail(function (xhr) {
            toastr.error('Failed to assign blueprint module (HTTP ' + xhr.status + ')');
        });
    },

    unassignBlueprintModule: function (moduleId, bmId) {
        Swal.fire({
            title: 'Remove blueprint assignment?',
            text: 'This module will no longer be linked to this blueprint module.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4',
            confirmButtonText: 'Remove',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.post(site_url + '/modules/' + moduleId + '/unassign-blueprint', { blueprint_module_id: bmId }, function (res) {
                    if (res.status) {
                        toastr.success('Blueprint module unassigned');
                        window.location.reload();
                    } else {
                        toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
                    }
                }).fail(function (xhr) {
                    toastr.error('Failed to unassign blueprint module (HTTP ' + xhr.status + ')');
                });
            }
        });
    },

    // ===========================
    // UI RENDERING
    // ===========================

    _renderModules: function (modules) {
        var self = MasterProjectDetail;
        if (!modules || !modules.length) {
            $('#modulesList').html('<div class="sap-empty" style="padding:32px 20px"><i class="fas fa-puzzle-piece"></i><h4>No modules yet</h4><p>Add modules to organize your project pages.</p></div>');
            return;
        }
        var html = '<div class="module-grid">';
        for (var i = 0; i < modules.length; i++) {
            var m = modules[i];
            var pages = m.pages || [];
            var totalBugs = 0, openBugs = 0;
            for (var j = 0; j < pages.length; j++) {
                totalBugs += parseInt(pages[j].bug_total || 0);
                openBugs += parseInt(pages[j].bug_open || 0);
            }
            var moduleUrl = site_url + '/master-projects/' + self._projectId + '/modules/' + m.id;
            html += '<div class="module-card">';
            html += '<div class="module-card-body">';
            html += '<div class="d-flex align-items-start justify-content-between mb-2">';
            html += '<div class="d-flex align-items-center gap-2">';
            html += '<span class="module-card-icon"><i class="fas fa-puzzle-piece"></i></span>';
            html += '<h6 class="mb-0 fw-semibold">' + self.escHtml(m.name) + '</h6>';
            html += '</div>';
            html += '<div class="sap-btn-group">';
            html += '<button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="MasterProjectDetail.editModule(\'' + m.id + '\',\'' + self.escAttr(m.name) + '\',\'' + self.escAttr(m.description || '') + '\')" title="Edit module"><i class="fas fa-pencil-alt"></i></button>';
            html += '<button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="MasterProjectDetail.deleteModule(\'' + m.id + '\')" title="Delete module"><i class="fas fa-trash-alt"></i></button>';
            html += '</div>';
            html += '</div>';
            html += '<p class="text-secondary mb-3" style="font-size:12px;line-height:1.5">' + self.escHtml(m.description || 'No description') + '</p>';
            html += '<div class="d-flex align-items-center gap-3">';
            html += '<span class="module-card-stat"><i class="fas fa-file-alt"></i> ' + pages.length + ' pages</span>';
            if (totalBugs > 0) html += '<span class="module-card-stat"><i class="fas fa-bug"></i> ' + totalBugs + ' bugs</span>';
            if (openBugs > 0) html += '<span class="module-card-stat module-card-stat--open"><i class="fas fa-exclamation-circle"></i> ' + openBugs + ' open</span>';
            html += '</div>';
            html += '<div class="module-blueprint-bar mt-2">';
            var bpMods = m.blueprint_modules || [];
            if (bpMods.length) {
                html += '<div class="module-blueprint-list">';
                for (var b = 0; b < bpMods.length; b++) {
                    var bm = bpMods[b];
                    html += '<div class="module-blueprint-assigned">';
                    html += '<span class="module-blueprint-icon"><i class="fas fa-link"></i></span>';
                    html += '<span class="module-blueprint-label">' + self.escHtml(bm.bp_name || '') + ' &rarr; ' + self.escHtml(bm.bm_name || '') + '</span>';
                    html += '<button type="button" class="module-blueprint-remove" onclick="MasterProjectDetail.unassignBlueprintModule(\'' + m.id + '\',\'' + bm.bm_id + '\')" title="Remove assignment"><i class="fas fa-times"></i></button>';
                    html += '</div>';
                }
                html += '</div>';
            }
            html += '<button type="button" class="module-blueprint-unassigned mt-1" onclick="MasterProjectDetail.openAssignBlueprintModal(\'' + m.id + '\')">';
            html += '<i class="fas fa-link"></i> Assign Blueprint Module';
            html += '</button>';
            html += '</div>';
            html += '</div>';
            html += '<div class="module-card-footer">';
            html += '<a href="' + moduleUrl + '" class="module-card-link"><i class="fas fa-arrow-right"></i> View Pages</a>';
            html += '</div>';
            html += '</div>';
        }
        html += '</div>';
        $('#modulesList').html(html);
    },

    _refreshModules: function () {
        $.get(site_url + '/master-projects/' + MasterProjectDetail._projectId + '/detail-json', function (res) {
            if (res && res.modules) {
                MasterProjectDetail._renderModules(res.modules);
            }
        }).fail(function (xhr) {
            toastr.error('Failed to reload modules (HTTP ' + xhr.status + ')');
        });
    },

    // ===========================
    // KANBAN BOARD
    // ===========================

    _loadKanban: function () {
        var self = MasterProjectDetail;
        $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-spinner fa-spin"></i>Loading...</div>');
        $.ajax({
            url: site_url + '/master-projects/' + self._projectId + '/kanban',
            method: 'GET',
            timeout: 15000,
        })
        .done(function (res) {
            self._kanbanData = res || { open: [], in_progress: [], resolved: [], closed: [] };
            self._renderKanban();
            self._initKanbanSortables();
        })
        .fail(function (xhr, status, error) {
            console.error('Kanban load failed:', status, error, xhr.responseText);
            toastr.error('Failed to load kanban board');
            $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-exclamation-triangle"></i>Failed to load kanban. Please refresh the page.</div>');
        });
    },

    _renderKanban: function () {
        var self = MasterProjectDetail;
        var columns = ['open', 'in_progress', 'resolved', 'closed'];
        for (var c = 0; c < columns.length; c++) {
            var key = columns[c];
            var tickets = self._kanbanData[key] || [];
            var $col = $('#kanban-col-' + key);
            $('#kanban-count-' + key).text(tickets.length);

            if (!tickets.length) {
                $col.html('<div class="kanban-empty"><i class="fas fa-inbox"></i>No tickets</div>');
                continue;
            }

            var html = '';
            for (var i = 0; i < tickets.length; i++) {
                html += self._buildKanbanCard(tickets[i]);
            }
            $col.html(html);
        }
    },

    _buildKanbanCard: function (t) {
        var self = MasterProjectDetail;
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

    _initKanbanSortables: function () {
        var self = MasterProjectDetail;
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
        var self = MasterProjectDetail;
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
                self._renderKanban();
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
            self._renderKanban();
            self._initKanbanSortables();
        });
    },

    // ===========================
    // EVENTS
    // ===========================

    _bindModuleModalDismiss: function () {
        $('#moduleModal').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        });
    },

    _bindKanbanDoubleClick: function () {
        $(document).on('dblclick', '.kanban-card', function () {
            var cardId = $(this).data('id');
            var cardTitle = $(this).find('.kanban-card-title a').text();
            var cardUrl = site_url + '/tickets/' + cardId;

            Swal.fire({
                title: 'Open Ticket?',
                html: '<strong>' + MasterProjectDetail.escHtml(cardTitle) + '</strong>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0070F2',
                confirmButtonText: 'Open in New Tab',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.open(cardUrl, '_blank');
                }
            });
        });
    },

    // ===========================
    // PUBLIC API
    // ===========================

    init: function () {
        var self = MasterProjectDetail;
        var pageData = window.PageData || {};
        self._projectId = pageData.projectId || '';

        self._bindModuleForm();
        self._bindModuleModalDismiss();
        self._bindKanbanDoubleClick();
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    MasterProjectDetail.init();
});

// ===========================
// EXPORTS
// ===========================

window.MasterProjectDetail = MasterProjectDetail;
