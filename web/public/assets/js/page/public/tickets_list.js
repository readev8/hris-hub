/**
 * ============================================================================
 * Public Ticket List
 * ============================================================================
 *
 * Description: Anonymous ticket list with status filter, search, and
 * client-side pagination.
 *
 * Dependencies: jQuery, Toastr
 * Date: 2026-08-18
 */

const TicketPublicList = {

    // ===========================
    // STATE
    // ===========================

    pageData: null,
    baseUrl: '',
    allTickets: [],
    currentPage: 1,
    perPage: 20,
    searchTimer: null,

    // ===========================
    // INITIALIZATION
    // ===========================

    init: function () {
        this.pageData = window.PageData || {};
        this.baseUrl = this.pageData.ajaxBaseUrl || site_url + '/public/tickets';

        this.loadTickets();
        this.bindFilters();
        this.bindPagination();
    },

    // ===========================
    // HELPERS
    // ===========================

    escHtml: function (s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    escAttr: function (s) {
        return String(s || '').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
    },

    getStatusClass: function (status) {
        var map = { 0: 'open', 1: 'approved', 2: 'in-progress', 3: 'resolved', 4: 'closed', 5: 'rejected' };
        return map[status] || 'open';
    },

    formatDate: function (str) {
        if (!str) return '';
        var d = new Date(str);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    },

    // ===========================
    // DATA LOADING
    // ===========================

    loadTickets: function (page) {
        var self = this;
        page = page || 1;
        this.currentPage = page;

        var params = {
            page: this.currentPage,
            per_page: this.perPage,
            status: $('#statusFilter').val() || '',
            search: $.trim($('#searchInput').val()) || ''
        };

        $.get(this.baseUrl + '/ajax-list', params, function (res) {
            self.allTickets = res.data || [];
            self.renderTickets(self.allTickets);
            self.renderPagination(res.total || 0, res.page || 1, res.per_page || self.perPage);
        }).fail(function () {
            self.showEmpty();
        });
    },

    // ===========================
    // UI RENDERING
    // ===========================

    renderTickets: function (tickets) {
        if (!tickets.length) {
            this.showEmpty();
            return;
        }
        $('#emptyState').hide();
        var $list = $('#ticketsList').empty().show();
        for (var i = 0; i < tickets.length; i++) {
            $list.append(this.buildCard(tickets[i]));
        }
    },

    buildCard: function (t) {
        var statusCls = this.getStatusClass(t.status);
        var html = '<div class="anon-ticket-card" data-code="' + this.escAttr(t.tracking_code) + '">';
        html += '<div class="anon-ticket-card-main">';
        html += '<div class="anon-ticket-card-tracking">' + this.escHtml(t.tracking_code) + '</div>';
        html += '<div class="anon-ticket-card-title">' + this.escHtml(t.title) + '</div>';
        html += '<div class="anon-ticket-card-badges">';
        html += '<span class="anon-badge anon-badge--' + statusCls + '">' + this.escHtml(t.status_name) + '</span>';
        html += '<span class="anon-badge anon-badge--outline">' + this.escHtml(t.type_name) + '</span>';
        html += '<span class="anon-badge anon-badge--outline">' + this.escHtml(t.priority_name) + '</span>';
        html += '</div>';
        html += '<div class="anon-ticket-card-meta">' + this.formatDate(t.created_at) + '</div>';
        html += '</div>';
        html += '<div class="anon-ticket-card-actions">';
        html += '<a href="' + site_url + '/public/tickets/' + t.tracking_code + '" class="anon-btn anon-btn-primary anon-btn-sm">View <i class="fas fa-arrow-right"></i></a>';
        html += '</div>';
        html += '</div>';
        return html;
    },

    renderPagination: function (total, page, perPageVal) {
        var totalPages = Math.ceil(total / perPageVal);
        if (totalPages <= 1) {
            $('#paginationWrap').hide();
            return;
        }
        $('#paginationWrap').show();
        $('#pageInfo').text(page + ' / ' + totalPages);
        $('#prevPageBtn').prop('disabled', page <= 1);
        $('#nextPageBtn').prop('disabled', page >= totalPages);
    },

    showEmpty: function () {
        $('#ticketsList').hide();
        $('#emptyState').show();
        $('#paginationWrap').hide();
    },

    // ===========================
    // EVENT HANDLERS
    // ===========================

    bindFilters: function () {
        var self = this;
        $('#statusFilter').on('change', function () { self.loadTickets(1); });
        $('#searchInput').on('keyup', function () {
            clearTimeout(self.searchTimer);
            self.searchTimer = setTimeout(function () { self.loadTickets(1); }, 300);
        });
    },

    bindPagination: function () {
        var self = this;
        $('#prevPageBtn').on('click', function () {
            if (self.currentPage > 1) self.loadTickets(self.currentPage - 1);
        });
        $('#nextPageBtn').on('click', function () {
            self.loadTickets(self.currentPage + 1);
        });
    }
};

// ===========================
// BOOTSTRAP
// ===========================

$(function () {
    TicketPublicList.init();
});

window.TicketPublicList = TicketPublicList;
