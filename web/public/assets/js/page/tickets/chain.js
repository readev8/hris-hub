/**
 * Ticket Chain Tracker
 *
 * Trace the referral chain of related tickets
 */

/* global $, site_url, toastr */

var ChainTracker = (function () {
    'use strict';

    // ── DOM Ready ──────────────────────────────────────────────
    $(function () {
        bindSearch();
    });

    // ── Helpers ────────────────────────────────────────────────
    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ── Search ─────────────────────────────────────────────────
    function bindSearch() {
        $('#chainSearchBtn').on('click', function () {
            var code = $('#chainSearchInput').val().trim();
            if (!code) {
                toastr.warning('Please enter a tracking code');
                return;
            }
            loadChain(code);
        });

        $('#chainSearchInput').on('keypress', function (e) {
            if (e.which === 13) {
                $('#chainSearchBtn').click();
            }
        });
    }

    // ── Load Chain Data ────────────────────────────────────────
    function loadChain(code) {
        $('#chainEmpty').hide();
        $('#chainResults').hide();

        var $summary = $('#chainSummary');
        var $tree = $('#chainTree');
        var $children = $('#childrenList');

        $summary.html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $tree.html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $children.html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

        $.get(site_url + '/tickets/ajax-chain', { code: code }, function (res) {
            var chain = res.chain || [];
            var children = res.children || [];

            if (!chain.length && !children.length) {
                $tree.html('<div class="text-center py-4 text-muted"><i class="fas fa-search" style="font-size:24px;opacity:0.5"></i><p class="mt-2">No tickets found for "' + escHtml(code) + '"</p></div>');
                $children.html('<div class="text-center py-4 text-muted"><i class="fas fa-inbox" style="font-size:24px;opacity:0.5"></i><p class="mt-2">No children found</p></div>');
                renderSummary(res);
                $('#chainResults').show();
                return;
            }

            renderChain(chain);
            renderChildren(children);
            renderSummary(res);
            $('#chainResults').show();
        }).fail(function () {
            toastr.error('Failed to load chain data');
            $tree.html('<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i><p class="mt-2">Failed to load chain data</p></div>');
            $children.html('<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i><p class="mt-2">Failed to load</p></div>');
        });
    }

    // ── Render Chain Tree ──────────────────────────────────────
    function renderChain(chain) {
        var $tree = $('#chainTree');
        if (!chain.length) {
            $tree.html('<div class="text-center py-4 text-muted"><i class="fas fa-search" style="font-size:24px;opacity:0.5"></i><p class="mt-2">No chain found</p></div>');
            return;
        }

        var html = '';
        for (var i = 0; i < chain.length; i++) {
            var t = chain[i];
            var isCurrent = t.is_current;
            var isLast = (i === chain.length - 1);
            var statusCls = getStatusClass(t.status);
            var priorityCls = getPriorityClass(t.priority);

            html += '<div class="chain-node' + (isCurrent ? ' chain-node--current' : '') + '">';
            html += '  <div class="chain-node-dot ' + (isCurrent ? 'chain-node-dot--current' : (i === 0 ? 'chain-node-dot--root' : '')) + '"></div>';
            html += '  <div class="chain-node-card">';
            html += '    <div class="chain-node-header">';
            html += '      <code class="chain-node-code">' + escHtml(t.tracking_code) + '</code>';
            if (isCurrent) {
                html += '      <span class="chain-badge chain-badge--current">Current</span>';
            } else if (i === 0) {
                html += '      <span class="chain-badge chain-badge--root">Root</span>';
            }
            html += '    </div>';
            html += '    <div class="chain-node-title">' + escHtml(t.title) + '</div>';
            html += '    <div class="chain-node-meta">';
            html += '      <span class="chain-status chain-status--' + statusCls + '">' + escHtml(t.status_name) + '</span>';
            html += '      <span class="chain-priority ' + priorityCls + '"></span>';
            html += '      <span class="chain-type">' + escHtml(t.type_name) + '</span>';
            html += '      <span class="chain-date">' + escHtml(t.created_at) + '</span>';
            html += '    </div>';
            html += '    <div class="chain-node-actions">';
            html += '      <a href="' + site_url + '/tickets/' + t.tracking_code + '" class="chain-view-btn">';
            html += '        <i class="fas fa-external-link-alt"></i> View Detail';
            html += '      </a>';
            html += '    </div>';
            html += '  </div>';
            if (!isLast) {
                html += '  <div class="chain-connector">';
                html += '    <div class="chain-connector-line"></div>';
                html += '    <div class="chain-connector-label">refers to</div>';
                html += '  </div>';
            }
            html += '</div>';
        }

        $tree.html(html);
    }

    // ── Render Children ────────────────────────────────────────
    function renderChildren(children) {
        var $list = $('#childrenList');
        var $count = $('#childrenCount');

        $count.text(children.length);

        if (!children.length) {
            $list.html('<div class="text-center py-4 text-muted"><i class="fas fa-inbox" style="font-size:24px;opacity:0.5"></i><p class="mt-2">No children found</p></div>');
            return;
        }

        var html = '';
        for (var i = 0; i < children.length; i++) {
            var t = children[i];
            var statusCls = getStatusClass(t.status);
            var priorityCls = getPriorityClass(t.priority);

            html += '<div class="chain-child-item">';
            html += '  <div class="chain-child-info">';
            html += '    <code class="chain-child-code">' + escHtml(t.tracking_code) + '</code>';
            html += '    <div class="chain-child-title">' + escHtml(t.title) + '</div>';
            html += '    <div class="chain-child-meta">';
            html += '      <span class="chain-status chain-status--' + statusCls + '">' + escHtml(t.status_name) + '</span>';
            html += '      <span class="chain-priority ' + priorityCls + '"></span>';
            html += '      <span class="chain-date">' + escHtml(t.created_at) + '</span>';
            html += '    </div>';
            html += '  </div>';
            html += '  <a href="' + site_url + '/tickets/' + t.tracking_code + '" class="chain-view-btn">';
            html += '    <i class="fas fa-external-link-alt"></i>';
            html += '  </a>';
            html += '</div>';
        }

        $list.html(html);
    }

    // ── Render Summary ─────────────────────────────────────────
    function renderSummary(res) {
        var $summary = $('#chainSummary');
        var chainCount = res.chain_count || 0;
        var depth = res.depth || 0;
        var childrenCount = res.children_count || 0;
        var root = res.root;
        var current = res.current;

        var html = '<div class="chain-summary-grid">';
        html += '  <div class="chain-summary-item">';
        html += '    <div class="chain-summary-value">' + chainCount + '</div>';
        html += '    <div class="chain-summary-label">Tickets in Chain</div>';
        html += '  </div>';
        html += '  <div class="chain-summary-item">';
        html += '    <div class="chain-summary-value">' + depth + '</div>';
        html += '    <div class="chain-summary-label">Depth Levels</div>';
        html += '  </div>';
        html += '  <div class="chain-summary-item">';
        html += '    <div class="chain-summary-value">' + childrenCount + '</div>';
        html += '    <div class="chain-summary-label">Children</div>';
        html += '  </div>';
        if (root) {
            html += '  <div class="chain-summary-item">';
            html += '    <div class="chain-summary-value" style="font-size:12px">' + escHtml(root.tracking_code) + '</div>';
            html += '    <div class="chain-summary-label">Root Ticket</div>';
            html += '  </div>';
        }
        if (current) {
            html += '  <div class="chain-summary-item">';
            html += '    <div class="chain-summary-value" style="font-size:12px">' + escHtml(current.tracking_code) + '</div>';
            html += '    <div class="chain-summary-label">Current Ticket</div>';
            html += '  </div>';
        }
        html += '</div>';

        $summary.html(html);
    }

    // ── Status/Priority Helpers ────────────────────────────────
    function getStatusClass(status) {
        var map = { 0: 'open', 1: 'approved', 2: 'in-progress', 3: 'resolved', 4: 'closed', 5: 'rejected' };
        return map[status] || 'open';
    }

    function getPriorityClass(priority) {
        var map = { 0: 'priority-low', 1: 'priority-medium', 2: 'priority-high', 3: 'priority-critical' };
        return map[priority] || 'priority-medium';
    }

    // ── Public API ─────────────────────────────────────────────
    return {
        loadChain: loadChain
    };
})();
