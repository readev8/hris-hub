/**
 * ============================================================================
 * Dashboard Main Page
 * ============================================================================
 *
 * Description: Role-aware dashboard with KPI cards, charts, approval queue,
 *              workload, project health, pipeline, and activity feed.
 * Date: 2026-07-18
 * Standard: Full (object-literal module pattern)
 */

/* global $, site_url, toastr, Chart */

var Dashboard = (function () {
    'use strict';

    // ── Chart color tokens (read from CSS custom properties — Bug 4 fix) ──
    var TOKENS = {};
    function loadTokens() {
        var root = getComputedStyle(document.documentElement);
        TOKENS = {
            brand:    root.getPropertyValue('--sap-brand').trim()    || '#0D9488',
            brandDark:root.getPropertyValue('--sap-brand-dark').trim()|| '#0F766E',
            success:  root.getPropertyValue('--sap-success').trim()  || '#059669',
            warning:  root.getPropertyValue('--sap-warning').trim()  || '#D97706',
            error:    root.getPropertyValue('--sap-error').trim()    || '#DC2626',
            info:     root.getPropertyValue('--sap-info').trim()     || '#0D9488',
            critical: root.getPropertyValue('--sap-critical').trim() || '#DC2626',
            high:     root.getPropertyValue('--sap-high').trim()     || '#EA580C',
            medium:   root.getPropertyValue('--sap-medium').trim()   || '#0D9488',
            low:      root.getPropertyValue('--sap-low').trim()      || '#059669',
            chart1:   root.getPropertyValue('--sap-chart-1').trim()  || '#0D9488',
            chart2:   root.getPropertyValue('--sap-chart-2').trim()  || '#EA580C',
            chart3:   root.getPropertyValue('--sap-chart-3').trim()  || '#059669',
            chart4:   root.getPropertyValue('--sap-chart-4').trim()  || '#7C3AED',
            chart5:   root.getPropertyValue('--sap-chart-5').trim()  || '#2563EB',
            chart6:   root.getPropertyValue('--sap-chart-6').trim()  || '#DB2777',
            textMuted:root.getPropertyValue('--sap-text-muted').trim()|| '#6B7280',
            border:   root.getPropertyValue('--sap-border').trim()   || '#E5E7EB',
        };
    }

    var STATUS_COLORS = {
        'Open':        function () { return TOKENS.warning; },
        'Approved':    function () { return TOKENS.success; },
        'In Progress': function () { return TOKENS.brand; },
        'Resolved':    function () { return TOKENS.info; },
        'Closed':      function () { return TOKENS.textMuted; },
        'Rejected':    function () { return TOKENS.error; }
    };

    var PRIORITY_COLORS = {
        'Critical': function () { return TOKENS.critical; },
        'High':     function () { return TOKENS.high; },
        'Medium':   function () { return TOKENS.medium; },
        'Low':      function () { return TOKENS.low; }
    };

    var TYPE_PALETTE = function () { return [TOKENS.chart1, TOKENS.chart2, TOKENS.chart3, TOKENS.chart4, TOKENS.chart5, TOKENS.chart6]; };

    // ── State ──────────────────────────────────────────────────
    var charts = {};
    var autoRefresh = false;
    var refreshTimer = null;

    // ── Shared Chart.js defaults ───────────────────────────────
    function chartDefaults() {
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.color = TOKENS.textMuted;
    }

    function destroyChart(name) {
        if (charts[name]) { charts[name].destroy(); delete charts[name]; }
    }

    // ── Status Doughnut ────────────────────────────────────────
    function renderStatusChart(data) {
        var el = document.getElementById('statusChart');
        if (!el || !Object.keys(data).length) return;
        destroyChart('status');
        var keys = Object.keys(data);
        charts.status = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: keys,
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: keys.map(function (k) { return (STATUS_COLORS[k] || function () { return TOKENS.textMuted; })(); }),
                    borderWidth: 0, hoverOffset: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.label + ': ' + ctx.raw; } } }
                },
                cutout: '68%',
                animation: { animateRotate: true, duration: 800 },
                onClick: function (_evt, els) {
                    if (els.length) {
                        var label = keys[els[0].index];
                        window.location.href = site_url + '/tickets?status=' + encodeURIComponent(label);
                    }
                }
            }
        });
    }

    // ── Priority Doughnut ──────────────────────────────────────
    function renderPriorityChart(data) {
        var el = document.getElementById('priorityChart');
        if (!el || !Object.keys(data).length) return;
        destroyChart('priority');
        var keys = Object.keys(data);
        charts.priority = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: keys,
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: keys.map(function (k) { return (PRIORITY_COLORS[k] || function () { return TOKENS.textMuted; })(); }),
                    borderWidth: 0, hoverOffset: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 11 } } }
                },
                cutout: '68%',
                animation: { animateRotate: true, duration: 800 },
                onClick: function (_evt, els) {
                    if (els.length) {
                        var label = keys[els[0].index];
                        window.location.href = site_url + '/tickets?priority=' + encodeURIComponent(label);
                    }
                }
            }
        });
    }

    // ── Daily Trend (dual line: created vs resolved) ────────────
    function renderTrendChart(trendData) {
        var el = document.getElementById('trendChart');
        if (!el || !trendData.length) return;
        destroyChart('trend');
        charts.trend = new Chart(el, {
            type: 'line',
            data: {
                labels: trendData.map(function (t) { return t.date; }),
                datasets: [
                    {
                        label: 'Created',
                        data: trendData.map(function (t) { return t.created; }),
                        borderColor: TOKENS.brand,
                        backgroundColor: 'rgba(13,148,136,0.08)',
                        fill: true, tension: 0.3, pointRadius: 3, pointHoverRadius: 5, borderWidth: 2
                    },
                    {
                        label: 'Resolved',
                        data: trendData.map(function (t) { return t.resolved; }),
                        borderColor: TOKENS.success,
                        backgroundColor: 'rgba(5,150,105,0.08)',
                        fill: true, tension: 0.3, pointRadius: 3, pointHoverRadius: 5, borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 11 } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: TOKENS.border } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                },
                animation: { duration: 600 }
            }
        });
    }

    // ── Render all charts from a stats payload ─────────────────
    function renderAllCharts(stats) {
        renderStatusChart(stats.by_status || {});
        renderPriorityChart(stats.by_priority || {});
        renderTrendChart(stats.daily_trend || []);
    }

    // ── AJAX refresh (no full page reload) ─────────────────────
    function ajaxRefresh(startDate, endDate) {
        $.ajax({
            url: site_url + '/dashboard/ajax-stats',
            data: { start_date: startDate, end_date: endDate },
            method: 'GET',
            timeout: 15000
        }).done(function (res) {
            if (res && res.status) {
                renderAllCharts(res.stats || {});
                toastr.success('Dashboard refreshed');
            } else {
                toastr.error('Refresh failed');
            }
        }).fail(function () { toastr.error('Refresh failed'); });
    }

    // ── Filter button + auto-refresh handlers ──────────────────
    function bindControls() {
        $('#filterBtn').on('click', function () {
            location.href = site_url + '/dashboard?start_date=' + $('#startDate').val() + '&end_date=' + $('#endDate').val();
        });

        $('#refreshToggle').on('click', function () {
            autoRefresh = !autoRefresh;
            var $btn = $(this);
            if (autoRefresh) {
                $('#refreshLabel').text('ON');
                $btn.addClass('sap-btn-primary').removeClass('sap-btn-secondary');
                toastr.info('Auto-refresh enabled (30s)');
                refreshTimer = setInterval(function () {
                    ajaxRefresh($('#startDate').val(), $('#endDate').val());
                }, 30000);
            } else {
                $('#refreshLabel').text('Auto');
                $btn.removeClass('sap-btn-primary').addClass('sap-btn-secondary');
                if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }
                toastr.info('Auto-refresh disabled');
            }
        });
    }

    // ── INIT ──────────────────────────────────────────────────
    $(function () {
        loadTokens();
        chartDefaults();
        var data = window.PageData || {};
        renderAllCharts({
            by_status:    data.byStatus,
            by_priority:  data.byPriority,
            daily_trend:  data.dailyTrend
        });
        bindControls();
    });

    return { refresh: ajaxRefresh };
})();
