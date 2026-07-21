/**
 * Anonymous Ticket List Page
 * @package App\Views\public
 * @file    tickets_list.js
 */
/* global $, site_url, toastr, TrackingStore */

(function () {
    'use strict';

    var pageData = window.PageData || {};
    var baseUrl  = pageData.ajaxBaseUrl || site_url + '/public/tickets';
    var allTickets = [];

    $(function () {
        loadTickets();
        bindAddCode();
        bindClearAll();
        bindFilters();
    });

    function loadTickets() {
        var codes = TrackingStore.getCodes();
        if (!codes.length) {
            showEmpty();
            return;
        }
        $.get(baseUrl + '/ajax-batch', { codes: codes.join(',') }, function (res) {
            allTickets = res || [];
            renderTickets(allTickets);
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
        bindCardActions();
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
        html += '<button type="button" class="anon-ticket-card-remove" data-remove="' + escAttr(t.tracking_code) + '" title="Remove from list"><i class="fas fa-times"></i></button>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    function bindCardActions() {
        $('.anon-ticket-card-remove').off('click').on('click', function (e) {
            e.preventDefault();
            var code = $(this).data('remove');
            TrackingStore.removeCode(code);
            $(this).closest('.anon-ticket-card').fadeOut(200, function () {
                $(this).remove();
                if (!$('.anon-ticket-card').length) showEmpty();
            });
        });
    }

    function showEmpty() {
        $('#ticketsList').hide();
        $('#emptyState').show();
    }

    function bindAddCode() {
        $('#addCodeBtn').on('click', function () { addCode(); });
        $('#addCodeInput').on('keypress', function (e) {
            if (e.which === 13) { e.preventDefault(); addCode(); }
        });
    }

    function addCode() {
        var $input = $('#addCodeInput');
        var code = $.trim($input.val());
        if (!code) { toastr.warning('Masukkan kode pelacakan'); return; }
        if (!/^TKT-\d{8}-[A-F0-9]{4}$/i.test(code)) {
            toastr.warning('Format kode tidak valid. Contoh: TKT-20260721-A3F9');
            return;
        }
        code = code.toUpperCase();
        if (TrackingStore.hasCode(code)) {
            toastr.info('Kode sudah ada di daftar');
            $input.val('');
            return;
        }
        TrackingStore.addCode(code);
        $input.val('');
        toastr.success('Kode ditambahkan');
        loadTickets();
    }

    function bindClearAll() {
        $('#clearAllBtn').on('click', function () {
            if (!confirm('Hapus semua kode dari daftar ini? Tiket tidak akan terhapus.')) return;
            TrackingStore.clearAll();
            showEmpty();
            toastr.success('Daftar dikosongkan');
        });
    }

    function bindFilters() {
        $('#statusFilter').on('change', applyFilters);
        $('#searchInput').on('keyup', applyFilters);
    }

    function applyFilters() {
        var status = $('#statusFilter').val();
        var search = $.trim($('#searchInput').val()).toLowerCase();
        var filtered = [];
        for (var i = 0; i < allTickets.length; i++) {
            var t = allTickets[i];
            if (status !== '' && String(t.status) !== status) continue;
            if (search && t.title.toLowerCase().indexOf(search) === -1 && t.tracking_code.toLowerCase().indexOf(search) === -1) continue;
            filtered.push(t);
        }
        renderTickets(filtered);
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
