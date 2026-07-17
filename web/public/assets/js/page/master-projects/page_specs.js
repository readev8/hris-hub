/**
 * Master Projects Page Specifications Detail
 *
 * @package    App\Views\master-projects
 * @file       page_specs.js
 * @version    1.0.0
 */

/* global $, site_url, toastr, Swal, Sortable, bootstrap */

var PageSpecs = (function () {
    'use strict';

    var pageData = window.PageData || {};
    var projectId  = pageData.projectId || '';
    var moduleId   = pageData.moduleId || '';
    var pageId     = pageData.pageId || '';

    var designPageModalInstance = null;

    var kanbanData      = {};
    var kanbanSortables = [];
    var filterType      = '';

    var KANBAN_STATUS_MAP    = { open: 0, in_progress: 2, resolved: 3, closed: 4 };
    var KANBAN_STATUS_LABELS = { 0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed' };

    $(function () {
        bindDesignPageModalDismiss();
        bindFilterEvents();
    });

    // ── Helpers ────────────────────────────────────────────────
    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ── Tab Switching ──────────────────────────────────────────
    function switchTab(tab) {
        $('#pageDetailTabs .sap-tab').removeClass('active');
        $('#pageDetailTabs .sap-tab[data-tab="' + tab + '"]').addClass('active');
        $('.tab-content').hide();
        $('#tab-' + tab).show();
        if (tab === 'kanban') loadKanban();
    }

    // ── Design Page Modal ──────────────────────────────────────
    function getDesignPageModal() {
        if (!designPageModalInstance) {
            designPageModalInstance = new bootstrap.Modal(document.getElementById('designPageModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return designPageModalInstance;
    }

    function openAssignDesignPageModal() {
        $('#designPagesLoading').show();
        $('#designPagesEmpty').hide();
        $('#designPagesList').html('');
        getDesignPageModal().show();

        $.get(site_url + '/design-pages/available?module_id=' + moduleId, function (res) {
            $('#designPagesLoading').hide();
            if (!res || !res.length) {
                $('#designPagesEmpty').show();
                return;
            }
            var html = '';
            for (var g = 0; g < res.length; g++) {
                var group = res[g];
                html += '<div class="design-page-group">';
                html += '<div class="design-page-group-header"><i class="fas fa-drafting-compass"></i> ' + escHtml(group.blueprint_name) + ' → ' + escHtml(group.blueprint_module_name) + '</div>';
                for (var k = 0; k < group.design_pages.length; k++) {
                    var dp = group.design_pages[k];
                    html += '<div class="design-page-row" data-id="' + dp.id + '">';
                    html += '<div class="design-page-row-info">';
                    html += '<span class="design-page-row-name">' + escHtml(dp.title) + '</span>';
                    if (dp.description) {
                        html += '<span class="design-page-row-meta">' + escHtml(dp.description) + '</span>';
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
                assignDesignPage(dpId);
            });
        }).fail(function () {
            $('#designPagesLoading').hide();
            toastr.error('Failed to load design pages');
        });
    }

    function assignDesignPage(designPageId) {
        $.post(site_url + '/pages/' + pageId + '/assign-design-page', { blueprint_design_page_id: designPageId }, function (res) {
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
    }

    function unlinkDesignPage() {
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
                    toastr.error('Failed to unlink design page (HTTP ' + xhr.status + ')');
                });
            }
        });
    }

    function bindDesignPageModalDismiss() {
        $('#designPageModal').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        });
    }

    // ── Kanban Board ───────────────────────────────────────────
    function loadKanban() {
        $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-spinner fa-spin"></i>Loading...</div>');
        $.ajax({
            url: site_url + '/master-projects/' + projectId + '/kanban?moduleId=' + moduleId,
            method: 'GET',
            timeout: 15000,
        })
        .done(function (res) {
            var allData = res || { open: [], in_progress: [], resolved: [], closed: [] };
            kanbanData = filterByPage(allData);
            renderKanban();
            initKanbanSortables();
        })
        .fail(function () {
            toastr.error('Failed to load kanban board');
            $('#kanbanBoard .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-exclamation-triangle"></i>Failed to load kanban.</div>');
        });
    }

    function filterByPage(allData) {
        var filtered = {};
        var columns = ['open', 'in_progress', 'resolved', 'closed'];
        for (var c = 0; c < columns.length; c++) {
            var key = columns[c];
            var tickets = allData[key] || [];
            filtered[key] = [];
            for (var i = 0; i < tickets.length; i++) {
                if (tickets[i].page_id == pageId) {
                    filtered[key].push(tickets[i]);
                }
            }
        }
        return filtered;
    }

    function renderKanban() {
        var columns = ['open', 'in_progress', 'resolved', 'closed'];
        for (var c = 0; c < columns.length; c++) {
            var key = columns[c];
            var tickets = kanbanData[key] || [];
            if (filterType) {
                tickets = [];
                for (var i = 0; i < (kanbanData[key] || []).length; i++) {
                    if (String(kanbanData[key][i].type) === String(filterType)) {
                        tickets.push(kanbanData[key][i]);
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
                html += buildKanbanCard(tickets[j]);
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
            error: function () {
                $card.css('background', '').css('opacity', '');
                toastr.error('Failed to move ticket');
                renderKanban();
                initKanbanSortables();
            }
        });
    }

    // ── Kanban Filters ─────────────────────────────────────────
    function bindFilterEvents() {
        $('#kanbanTypeFilter').on('click', '.kanban-filter-type-btn', function () {
            $('#kanbanTypeFilter .kanban-filter-type-btn').removeClass('active');
            $(this).addClass('active');
            filterType = $(this).data('type') || '';
            renderKanban();
        });
    }

    // ── Public API ─────────────────────────────────────────────
    return {
        switchTab:                  switchTab,
        openAssignDesignPageModal:  openAssignDesignPageModal,
        unlinkDesignPage:           unlinkDesignPage
    };
})();
