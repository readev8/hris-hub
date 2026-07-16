/**
 * Master Projects Detail Page
 *
 * @package    App\Views\master-projects
 * @file       detail.js
 * @version    1.0.0
 */

/* global $, site_url, toastr, Swal, Sortable, bootstrap */

var MasterProjectDetail = (function () {
    'use strict';

    // ── Page Data ──────────────────────────────────────────────
    var pageData  = window.PageData || {};
    var projectId = pageData.projectId || '';

    // ── Kanban State ───────────────────────────────────────────
    var kanbanData     = {};
    var kanbanSortables = [];

    var KANBAN_STATUS_MAP    = { open: 0, in_progress: 2, resolved: 3, closed: 4 };
    var KANBAN_STATUS_LABELS = { 0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed' };

    // ── Module Modal State ─────────────────────────────────────
    var moduleModalInstance = null;

    // ── DOM Ready ──────────────────────────────────────────────
    $(function () {
        bindModuleForm();
        bindModuleModalDismiss();
        bindKanbanDoubleClick();
    });

    // ── Helpers ────────────────────────────────────────────────
    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function escAttr(s) {
        return String(s || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // ── Tab Switching ──────────────────────────────────────────
    function switchDetailTab(tab) {
        $('#detailTabs .sap-tab').removeClass('active');
        $('#detailTabs .sap-tab[data-tab="' + tab + '"]').addClass('active');
        $('.tab-content').hide();
        $('#tab-' + tab).show();
        if (tab === 'kanban') loadKanban();
    }

    // ── Module Modal ───────────────────────────────────────────
    function getModuleModal() {
        if (!moduleModalInstance) {
            moduleModalInstance = new bootstrap.Modal(document.getElementById('moduleModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return moduleModalInstance;
    }

    function openModuleModal() {
        $('#moduleEditId').val('');
        $('#moduleName').val('');
        $('#moduleDesc').val('');
        $('#moduleModalTitle').text('Add Module');
        getModuleModal().show();
    }

    function editModule(id, name, desc) {
        $('#moduleEditId').val(id);
        $('#moduleName').val(name);
        $('#moduleDesc').val(desc);
        $('#moduleModalTitle').text('Edit Module');
        getModuleModal().show();
    }

    function bindModuleForm() {
        $('#moduleForm').on('submit', function (e) {
            e.preventDefault();
            var editId = $('#moduleEditId').val();
            var isNew = !editId;
            var url = isNew
                ? site_url + '/master-projects/' + projectId + '/modules'
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
    }

    function deleteModule(id) {
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
    }

    function renderModules(modules) {
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
            var moduleUrl = site_url + '/master-projects/' + projectId + '/modules/' + m.id;
            html += '<div class="module-card">';
            html += '<div class="module-card-body">';
            html += '<div class="d-flex align-items-start justify-content-between mb-2">';
            html += '<div class="d-flex align-items-center gap-2">';
            html += '<span class="module-card-icon"><i class="fas fa-puzzle-piece"></i></span>';
            html += '<h6 class="mb-0 fw-semibold">' + escHtml(m.name) + '</h6>';
            html += '</div>';
            html += '<div class="sap-btn-group">';
            html += '<button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="MasterProjectDetail.editModule(\'' + m.id + '\',\'' + escAttr(m.name) + '\',\'' + escAttr(m.description || '') + '\')" title="Edit module"><i class="fas fa-pencil-alt"></i></button>';
            html += '<button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="MasterProjectDetail.deleteModule(\'' + m.id + '\')" title="Delete module"><i class="fas fa-trash-alt"></i></button>';
            html += '</div>';
            html += '</div>';
            html += '<p class="text-secondary mb-3" style="font-size:12px;line-height:1.5">' + escHtml(m.description || 'No description') + '</p>';
            html += '<div class="d-flex align-items-center gap-3">';
            html += '<span class="module-card-stat"><i class="fas fa-file-alt"></i> ' + pages.length + ' pages</span>';
            if (totalBugs > 0) html += '<span class="module-card-stat"><i class="fas fa-bug"></i> ' + totalBugs + ' bugs</span>';
            if (openBugs > 0) html += '<span class="module-card-stat module-card-stat--open"><i class="fas fa-exclamation-circle"></i> ' + openBugs + ' open</span>';
            html += '</div>';
            html += '</div>';
            html += '<div class="module-card-footer">';
            html += '<a href="' + moduleUrl + '" class="module-card-link"><i class="fas fa-arrow-right"></i> View Pages</a>';
            html += '</div>';
            html += '</div>';
        }
        html += '</div>';
        $('#modulesList').html(html);
    }

    function refreshModules() {
        $.get(site_url + '/master-projects/' + projectId + '/detail-json', function (res) {
            if (res && res.modules) {
                renderModules(res.modules);
            }
        }).fail(function (xhr) {
            toastr.error('Failed to reload modules (HTTP ' + xhr.status + ')');
        });
    }

    function bindModuleModalDismiss() {
        $('#moduleModal').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        });
    }

    // ── Kanban Board ───────────────────────────────────────────
    function loadKanban() {
        $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-spinner fa-spin"></i>Loading...</div>');
        $.ajax({
            url: site_url + '/master-projects/' + projectId + '/kanban',
            method: 'GET',
            timeout: 15000,
        })
        .done(function (res) {
            kanbanData = res || { open: [], in_progress: [], resolved: [], closed: [] };
            renderKanban();
            initKanbanSortables();
        })
        .fail(function (xhr, status, error) {
            console.error('Kanban load failed:', status, error, xhr.responseText);
            toastr.error('Failed to load kanban board');
            $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-exclamation-triangle"></i>Failed to load kanban. Please refresh the page.</div>');
        });
    }

    function renderKanban() {
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
                html += buildKanbanCard(tickets[i]);
            }
            $col.html(html);
        }
    }

    function buildKanbanCard(t) {
        var typeCls = t.type === 0 ? 'bug' : t.type === 1 ? 'issue' : t.type === 3 ? 'change-request' : 'task';
        var prioCls = t.priority_name ? t.priority_name.toLowerCase() : 'medium';

        var html = '<div class="kanban-card" data-id="' + t.id + '" data-status="' + t.status + '">';
        html += '<div class="kanban-card-header">';
        html += '<span class="kanban-priority-dot ' + prioCls + '" title="' + escHtml(t.priority_name) + '"></span>';
        html += '<span class="kanban-type-badge ' + typeCls + '">' + escHtml(t.type_name) + '</span>';
        html += '</div>';
        html += '<div class="kanban-card-title"><a href="' + site_url + '/tickets/' + t.id + '" target="_blank">' + escHtml(t.title) + '</a></div>';
        html += '<div class="kanban-card-meta">';
        if (t.assignee_name) {
            html += '<span class="kanban-card-assignee"><i class="fas fa-user-check"></i> ' + escHtml(t.assignee_name) + '</span>';
        }
        if (t.page_name) {
            html += '<span class="kanban-card-page" title="' + escHtml(t.page_name) + '"><i class="fas fa-file-alt"></i> ' + escHtml(t.page_name) + '</span>';
        }
        html += '</div>';
        html += '</div>';
        return html;
    }

    function initKanbanSortables() {
        for (var s = 0; s < kanbanSortables.length; s++) {
            kanbanSortables[s].destroy();
        }
        kanbanSortables = [];

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
                    var newStatus = KANBAN_STATUS_MAP[newColumn];
                    var oldStatus = parseInt(evt.item.getAttribute('data-status'));

                    if (newStatus === oldStatus) return;

                    var allowed = getAllowedTransitions(oldStatus);
                    if (allowed.indexOf(newStatus) === -1) {
                        toastr.warning('Transition not allowed');
                        renderKanban();
                        initKanbanSortables();
                        return;
                    }

                    moveTicket(ticketId, newStatus, oldStatus, evt.item);
                }
            });
            kanbanSortables.push(sortable);
        }
    }

    function getAllowedTransitions(currentStatus) {
        switch (currentStatus) {
            case 0: return [2, 4];
            case 1: return [2];
            case 2: return [3];
            case 3: return [0, 4];
            default: return [];
        }
    }

    function moveTicket(ticketId, newStatus, oldStatus, cardEl) {
        var $card = $(cardEl);
        var originalBg = $card.css('background');
        $card.css('background', 'var(--sap-brand-hover)').css('opacity', '0.7');

        $.ajax({
            url: site_url + '/tickets/' + ticketId + '/move',
            method: 'POST',
            data: { new_status: newStatus },
            success: function (res) {
                if (res.status) {
                    $card.attr('data-status', newStatus);
                    $card.css('background', '').css('opacity', '');
                    toastr.success('Ticket moved to ' + KANBAN_STATUS_LABELS[newStatus]);
                } else {
                    $card.css('background', '').css('opacity', '');
                    toastr.error(res.data && res.data.message ? res.data.message : 'Failed to move ticket');
                    renderKanban();
                    initKanbanSortables();
                }
            },
            error: function (xhr) {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
                if (res && res.redirect) {
                    window.location.href = res.redirect;
                    return;
                }
                $card.css('background', '').css('opacity', '');
                toastr.error('Failed to move ticket');
                renderKanban();
                initKanbanSortables();
            }
        });
    }

    function bindKanbanDoubleClick() {
        $(document).on('dblclick', '.kanban-card', function () {
            var cardId = $(this).data('id');
            var cardTitle = $(this).find('.kanban-card-title a').text();
            var cardUrl = site_url + '/tickets/' + cardId;

            Swal.fire({
                title: 'Open Ticket?',
                html: '<strong>' + escHtml(cardTitle) + '</strong>',
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
    }

    // ── Public API ─────────────────────────────────────────────
    return {
        switchDetailTab: switchDetailTab,
        openModuleModal: openModuleModal,
        editModule:      editModule,
        deleteModule:    deleteModule,
        refreshModules:  refreshModules
    };
})();
