/**
 * ============================================================================
 * Shared Kanban Board
 * ============================================================================
 *
 * Description: Reusable kanban board with Sortable.js drag-and-drop
 * Date: 2026-07-16
 * Used by: master-projects/detail, master-projects/module_detail
 */

// ===========================
// CONSTANTS
// ===========================

var KanbanBoard = {
    STATUS_MAP: { open: 0, in_progress: 2, resolved: 3, closed: 4 },
    STATUS_LABELS: { 0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed' },

    // ===========================
    // STATE
    // ===========================

    _data: {},
    _sortables: [],
    _boardId: 'kanbanBoard',
    _onMoveCallback: null,

    // ===========================
    // INITIALIZATION
    // ===========================

    init: function(options) {
        options = options || {};
        this._boardId = options.boardId || 'kanbanBoard';
        this._onMoveCallback = options.onMove || null;
    },

    // ===========================
    // DATA LOADING
    // ===========================

    load: function(url) {
        var self = this;
        $('#' + self._boardId + ' .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-spinner fa-spin"></i>Loading...</div>');

        $.ajax({ url: url, method: 'GET', timeout: 15000 })
            .done(function(res) {
                self._data = res || { open: [], in_progress: [], resolved: [], closed: [] };
                self.render();
                self._initSortables();
            })
            .fail(function(xhr, status, error) {
                console.error('Kanban load failed:', status, error, xhr.responseText);
                toastr.error('Gagal memuat kanban board');
                $('#' + self._boardId + ' .kanban-cards').html('<div class="kanban-empty"><i class="fas fa-exclamation-triangle"></i>Failed to load kanban.</div>');
            });
    },

    // ===========================
    // UI RENDERING
    // ===========================

    render: function() {
        var self = this;
        var columns = ['open', 'in_progress', 'resolved', 'closed'];

        for (var c = 0; c < columns.length; c++) {
            var key = columns[c];
            var tickets = self._data[key] || [];
            var $col = $('#kanban-col-' + key);
            $('#kanban-count-' + key).text(tickets.length);

            if (!tickets.length) {
                $col.html('<div class="kanban-empty"><i class="fas fa-inbox"></i>No tickets</div>');
                continue;
            }

            var html = '';
            for (var i = 0; i < tickets.length; i++) {
                html += self._buildCard(tickets[i]);
            }
            $col.html(html);
        }
    },

    _buildCard: function(t) {
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
    },

    // ===========================
    // SORTABLE INITIALIZATION
    // ===========================

    _initSortables: function() {
        var self = this;

        for (var s = 0; s < self._sortables.length; s++) {
            self._sortables[s].destroy();
        }
        self._sortables = [];

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
                onStart: function(evt) {
                    $(evt.item).css('transition', 'none');
                },
                onEnd: function(evt) {
                    $(evt.item).css('transition', '');
                    var ticketId = evt.item.getAttribute('data-id');
                    var newColumn = evt.to.id.replace('kanban-col-', '');
                    var newStatus = self.STATUS_MAP[newColumn];
                    var oldStatus = parseInt(evt.item.getAttribute('data-status'));

                    if (newStatus === oldStatus) return;

                    var allowed = self._getAllowedTransitions(oldStatus);
                    if (allowed.indexOf(newStatus) === -1) {
                        toastr.warning('Transition tidak diizinkan');
                        self.render();
                        self._initSortables();
                        return;
                    }

                    self._moveTicket(ticketId, newStatus, oldStatus, evt.item);
                }
            });
            self._sortables.push(sortable);
        }
    },

    // ===========================
    // TRANSITION RULES
    // ===========================

    _getAllowedTransitions: function(currentStatus) {
        switch (currentStatus) {
            case 0: return [2, 4];
            case 1: return [2];
            case 2: return [3];
            case 3: return [0, 4];
            default: return [];
        }
    },

    // ===========================
    // MOVE TICKET
    // ===========================

    _moveTicket: function(ticketId, newStatus, oldStatus, cardEl) {
        var self = this;
        var $card = $(cardEl);
        $card.css('background', 'var(--sap-brand-hover)').css('opacity', '0.7');

        $.ajax({
            url: site_url + '/tickets/' + ticketId + '/move',
            method: 'POST',
            data: { new_status: newStatus },
            success: function(res) {
                if (res.status) {
                    $card.attr('data-status', newStatus);
                    $card.css('background', '').css('opacity', '');
                    toastr.success('Ticket dipindahkan ke ' + self.STATUS_LABELS[newStatus]);
                    if (self._onMoveCallback) self._onMoveCallback(ticketId, newStatus, oldStatus);
                } else {
                    $card.css('background', '').css('opacity', '');
                    toastr.error(res.data?.message || 'Gagal memindahkan ticket');
                    self.render();
                    self._initSortables();
                }
            },
            error: function(xhr) {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch(e) {}
                if (res && res.redirect) {
                    window.location.href = res.redirect;
                    return;
                }
                $card.css('background', '').css('opacity', '');
                toastr.error('Gagal memindahkan ticket');
                self.render();
                self._initSortables();
            }
        });
    }
};

// ===========================
// DOUBLE-CLICK HANDLER
// ===========================

$(document).on('dblclick', '.kanban-card', function() {
    var cardId = $(this).data('id');
    var cardTitle = $(this).find('.kanban-card-title a').text();
    var cardUrl = site_url + '/tickets/' + cardId;

    Swal.fire({
        title: 'Buka Ticket?',
        html: '<strong>' + escHtml(cardTitle) + '</strong>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0070F2',
        confirmButtonText: 'Buka di Tab Baru',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            window.open(cardUrl, '_blank');
        }
    });
});
