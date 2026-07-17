/**
 * Master Projects Module Detail Page
 *
 * @package    App\Views\master-projects
 * @file       module_detail.js
 * @version    1.0.0
 */

/* global $, site_url, toastr, Swal, Sortable, bootstrap */

var ModuleDetail = (function () {
    'use strict';

    // ── Page Data ──────────────────────────────────────────────
    var pageData  = window.PageData || {};
    var projectId = pageData.projectId || '';
    var moduleId  = pageData.moduleId || '';
    var pageIds   = pageData.pageIds || [];

    // ── Kanban State ───────────────────────────────────────────
    var kanbanData      = {};
    var kanbanSortables = [];

    var KANBAN_STATUS_MAP    = { open: 0, in_progress: 2, resolved: 3, closed: 4 };
    var KANBAN_STATUS_LABELS = { 0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed' };

    // ── Modal State ────────────────────────────────────────────
    var pageModalInstance      = null;
    var moduleModalInstance    = null;
    var bugListModalInstance   = null;
    var designPageModalInstance = null;

    // ── DOM Ready ──────────────────────────────────────────────
    $(function () {
        bindPageForm();
        bindModuleForm();
        bindModalDismiss();
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

    // ── Page Modal ─────────────────────────────────────────────
    function getPageModal() {
        if (!pageModalInstance) {
            pageModalInstance = new bootstrap.Modal(document.getElementById('pageModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return pageModalInstance;
    }

    function openPageModal() {
        $('#pageModuleId').val(moduleId);
        $('#pageEditId').val('');
        $('#pageName').val('');
        $('#pageUrl').val('');
        $('#pageDesc').val('');
        $('#pageModalTitle').text('Add Page');
        getPageModal().show();
    }

    function editPage(id, moduleIdVal, name, urlPath, description) {
        $('#pageModuleId').val(moduleIdVal);
        $('#pageEditId').val(id);
        $('#pageName').val(name);
        $('#pageUrl').val(urlPath);
        $('#pageDesc').val(description || '');
        $('#pageModalTitle').text('Edit Page');
        getPageModal().show();
    }

    function bindPageForm() {
        $('#pageForm').on('submit', function (e) {
            e.preventDefault();
            var editId = $('#pageEditId').val();
            var url = editId
                ? site_url + '/pages/' + editId + '/update'
                : site_url + '/modules/' + moduleId + '/pages';
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
    }

    function deletePage(id) {
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

    function editModule(id, name, desc) {
        $('#moduleEditId').val(id);
        $('#moduleName').val(name);
        $('#moduleDesc').val(desc);
        getModuleModal().show();
    }

    function bindModuleForm() {
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
    }

    function deleteModule(id) {
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
                        window.location.href = site_url + '/master-projects/' + projectId;
                    } else {
                        toastr.error(res.data && res.data.message ? res.data.message : 'Failed');
                    }
                }).fail(function (xhr) {
                    toastr.error('Failed to delete module (HTTP ' + xhr.status + ')');
                });
            }
        });
    }

    // ── Bug List Modal ─────────────────────────────────────────
    function getBugListModal() {
        if (!bugListModalInstance) {
            bugListModalInstance = new bootstrap.Modal(document.getElementById('bugListModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return bugListModalInstance;
    }

    function showBugList(pageId, pageName) {
        $('#bugPageName').text(pageName);
        var modal = getBugListModal();
        $('#bugListBody').html('<div class="text-center p-4"><span class="sap-spinner"></span></div>');
        modal.show();
        $.get(site_url + '/pages/' + pageId + '/bugs', function (res) {
            if (!res || !res.length) {
                $('#bugListBody').html('<div class="sap-empty" style="padding:32px"><i class="fas fa-check-circle"></i><h4>No bugs</h4><p>No bugs reported for this page.</p></div>');
                return;
            }
            var html = '<table class="sap-table sap-table-compact mb-0"><thead><tr><th style="min-width:200px">Title</th><th style="width:120px">Status</th><th style="width:100px">Priority</th><th style="width:130px">Created</th></tr></thead><tbody>';
            for (var i = 0; i < res.length; i++) {
                var b = res[i];
                var statusCls = b.status_name === 'Open' ? 'sap-badge open' : b.status_name === 'Resolved' ? 'sap-badge resolved' : 'sap-badge closed';
                var priorityDot = 'priority-dot ' + (b.priority_name ? b.priority_name.toLowerCase() : 'medium');
                html += '<tr><td><a href="' + site_url + '/tickets/' + b.id + '" target="_blank" class="fw-medium" style="color:var(--sap-brand);text-decoration:none">' + b.title + '</a></td>'
                     + '<td><span class="' + statusCls + '"><span class="badge-dot"></span>' + b.status_name + '</span></td>'
                     + '<td><span class="' + priorityDot + '"></span> ' + b.priority_name + '</td>'
                     + '<td><span class="text-muted">' + b.created_at + '</span></td></tr>';
            }
            html += '</tbody></table>';
            $('#bugListBody').html(html);
        }).fail(function (xhr) {
            $('#bugListBody').html('<div class="sap-empty" style="padding:32px"><i class="fas fa-exclamation-triangle"></i><h4>Failed to load bug data</h4><p>HTTP ' + xhr.status + '</p></div>');
            toastr.error('Failed to load bug data');
        });
    }

    function bindModalDismiss() {
        $('#pageModal, #bugListModal, #moduleModal, #designPageModal').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        });
    }

    // ── Blueprint Design Page Assignment ───────────────────────
    function getDesignPageModal() {
        if (!designPageModalInstance) {
            designPageModalInstance = new bootstrap.Modal(document.getElementById('designPageModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }
        return designPageModalInstance;
    }

    function openAssignDesignPageModal(pageId) {
        $('#assignDesignPageTargetId').val(pageId);
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
                assignBlueprintDesignPage(dpId);
            });
        }).fail(function () {
            $('#designPagesLoading').hide();
            toastr.error('Failed to load design pages');
        });
    }

    function assignBlueprintDesignPage(designPageId) {
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
    }

    function unassignBlueprintDesignPage(pageId) {
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
    }

    function toggleSpecs(pageId) {
        $('#specs-row-' + pageId).toggle();
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
            kanbanData = res || { open: [], in_progress: [], resolved: [], closed: [] };
            renderKanban();
            initKanbanSortables();
        })
        .fail(function (xhr, status, error) {
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
        switchDetailTab:              switchDetailTab,
        openPageModal:                openPageModal,
        editPage:                     editPage,
        deletePage:                   deletePage,
        editModule:                   editModule,
        deleteModule:                 deleteModule,
        showBugList:                  showBugList,
        openAssignDesignPageModal:    openAssignDesignPageModal,
        assignBlueprintDesignPage:    assignBlueprintDesignPage,
        unassignBlueprintDesignPage:  unassignBlueprintDesignPage,
        toggleSpecs:                  toggleSpecs
    };
})();
