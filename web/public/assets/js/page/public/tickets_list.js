/**
 * Anonymous Ticket List Page
 * @package App\Views\public
 * @file    tickets_list.js
 */
/* global $, site_url, toastr */

(function () {
    'use strict';

    var pageData = window.PageData || {};
    var baseUrl  = pageData.ajaxBaseUrl || site_url + '/public/tickets';
    var allTickets = [];
    var currentPage = 1;
    var perPage = 20;
    var searchTimer = null;

    $(function () {
        loadTickets();
        bindFilters();
        bindPagination();
    });

    function loadTickets(page) {
        page = page || 1;
        currentPage = page;

        var params = {
            page: currentPage,
            per_page: perPage,
            status: $('#statusFilter').val() || '',
            search: $.trim($('#searchInput').val()) || ''
        };

        $.get(baseUrl + '/ajax-list', params, function (res) {
            allTickets = res.data || [];
            renderTickets(allTickets);
            renderPagination(res.total || 0, res.page || 1, res.per_page || perPage);
        }).fail(function () {
            showEmpty();
        });
    }

    function renderTickets(tickets) {
        if (!tickets.length) {
            showEmpty();
            return;
        }
        $('#emptyState').hide();
        var $list = $('#ticketsList').empty().show();
        for (var i = 0; i < tickets.length; i++) {
            $list.append(buildCard(tickets[i]));
        }
    }

    function buildCard(t) {
        var statusCls = getStatusClass(t.status);
        var html = '<div class="anon-ticket-card" data-code="' + escAttr(t.tracking_code) + '">';
        html += '<div class="anon-ticket-card-main">';
        html += '<div class="anon-ticket-card-tracking">' + escHtml(t.tracking_code) + '</div>';
        html += '<div class="anon-ticket-card-title">' + escHtml(t.title) + '</div>';
        html += '<div class="anon-ticket-card-badges">';
        html += '<span class="anon-badge anon-badge--' + statusCls + '">' + escHtml(t.status_name) + '</span>';
        html += '<span class="anon-badge anon-badge--outline">' + escHtml(t.type_name) + '</span>';
        html += '<span class="anon-badge anon-badge--outline">' + escHtml(t.priority_name) + '</span>';
        html += '</div>';
        html += '<div class="anon-ticket-card-meta">' + formatDate(t.created_at) + '</div>';
        html += '</div>';
        html += '<div class="anon-ticket-card-actions">';
        html += '<a href="' + site_url + '/public/tickets/' + t.tracking_code + '" class="anon-btn anon-btn-primary anon-btn-sm">View <i class="fas fa-arrow-right"></i></a>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    function renderPagination(total, page, perPageVal) {
        var totalPages = Math.ceil(total / perPageVal);
        if (totalPages <= 1) {
            $('#paginationWrap').hide();
            return;
        }
        $('#paginationWrap').show();
        $('#pageInfo').text(page + ' / ' + totalPages);
        $('#prevPageBtn').prop('disabled', page <= 1);
        $('#nextPageBtn').prop('disabled', page >= totalPages);
    }

    function showEmpty() {
        $('#ticketsList').hide();
        $('#emptyState').show();
        $('#paginationWrap').hide();
    }

    function bindFilters() {
        $('#statusFilter').on('change', function () { loadTickets(1); });
        $('#searchInput').on('keyup', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { loadTickets(1); }, 300);
        });
    }

    function bindPagination() {
        $('#prevPageBtn').on('click', function () {
            if (currentPage > 1) loadTickets(currentPage - 1);
        });
        $('#nextPageBtn').on('click', function () {
            loadTickets(currentPage + 1);
        });
    }

    function getStatusClass(status) {
        var map = { 0: 'open', 1: 'approved', 2: 'in-progress', 3: 'resolved', 4: 'closed', 5: 'rejected' };
        return map[status] || 'open';
    }

    function formatDate(str) {
        if (!str) return '';
        var d = new Date(str);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function escAttr(s) {
        return String(s || '').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
    }
})();
