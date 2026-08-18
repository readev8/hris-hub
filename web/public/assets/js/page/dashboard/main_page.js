/**
 * ============================================================================
 * Dashboard Main Page
 * ============================================================================
 *
 * Role-aware dashboard with KPI cards, charts, approval queue, workload,
 * project health, pipeline, and activity feed. Supports auto-refresh.
 *
 * Dependencies: jQuery, Chart.js, Toastr
 * Date: 2026-08-18
 */

/* global $, site_url, toastr, Chart */

const Dashboard = {
    // ===========================
    // API ENDPOINTS
    // ===========================

    API_ENDPOINTS: {
        AJAX_STATS: site_url + '/dashboard/ajax-stats'
    },

    // ===========================
    // CHART TOKENS (read from CSS custom properties)
    // ===========================

    _tokens: {},

    _loadTokens: function () {
        var root = getComputedStyle(document.documentElement);
        this._tokens = {
            brand:     root.getPropertyValue('--sap-brand').trim()     || '#0D9488',
            brandDark: root.getPropertyValue('--sap-brand-dark').trim() || '#0F766E',
            success:   root.getPropertyValue('--sap-success').trim()   || '#059669',
            warning:   root.getPropertyValue('--sap-warning').trim()   || '#D97706',
            error:     root.getPropertyValue('--sap-error').trim()     || '#DC2626',
            info:      root.getPropertyValue('--sap-info').trim()      || '#0D9488',
            critical:  root.getPropertyValue('--sap-critical').trim()  || '#DC2626',
            high:      root.getPropertyValue('--sap-high').trim()      || '#EA580C',
            medium:    root.getPropertyValue('--sap-medium').trim()    || '#0D9488',
            low:       root.getPropertyValue('--sap-low').trim()       || '#059669',
            chart1:    root.getPropertyValue('--sap-chart-1').trim()   || '#0D9488',
            chart2:    root.getPropertyValue('--sap-chart-2').trim()   || '#EA580C',
            chart3:    root.getPropertyValue('--sap-chart-3').trim()   || '#059669',
            chart4:    root.getPropertyValue('--sap-chart-4').trim()   || '#7C3AED',
            chart5:    root.getPropertyValue('--sap-chart-5').trim()   || '#2563EB',
            chart6:    root.getPropertyValue('--sap-chart-6').trim()   || '#DB2777',
            textMuted: root.getPropertyValue('--sap-text-muted').trim() || '#6B7280',
            border:    root.getPropertyValue('--sap-border').trim()    || '#E5E7EB'
        };
    },

    _statusColors: {
        'Open':        function (t) { return t.warning; },
        'Approved':    function (t) { return t.success; },
        'In Progress': function (t) { return t.brand; },
        'Resolved':    function (t) { return t.info; },
        'Closed':      function (t) { return t.textMuted; },
        'Rejected':    function (t) { return t.error; }
    },

    _priorityColors: {
        'Critical': function (t) { return t.critical; },
        'High':     function (t) { return t.high; },
        'Medium':   function (t) { return t.medium; },
        'Low':      function (t) { return t.low; }
    },

    // ===========================
    // STATE
    // ===========================

    _charts: {},
    _autoRefresh: false,
    _refreshTimer: null,

    // ===========================
    // CHART HELPERS
    // ===========================

    _chartDefaults: function () {
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.color = this._tokens.textMuted;
    },

    _destroyChart: function (name) {
        if (this._charts[name]) {
            this._charts[name].destroy();
            delete this._charts[name];
        }
    },

    // ===========================
    // UI RENDERING — CHARTS
    // ===========================

    _renderStatusChart: function (data) {
        var el = document.getElementById('statusChart');
        if (!el || !Object.keys(data).length) return;
        this._destroyChart('status');
        var self = this;
        var keys = Object.keys(data);
        this._charts.status = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: keys,
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: keys.map(function (k) {
                        return (self._statusColors[k] || function (t) { return t.textMuted; })(self._tokens);
                    }),
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
    },

    _renderPriorityChart: function (data) {
        var el = document.getElementById('priorityChart');
        if (!el || !Object.keys(data).length) return;
        this._destroyChart('priority');
        var self = this;
        var keys = Object.keys(data);
        this._charts.priority = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: keys,
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: keys.map(function (k) {
                        return (self._priorityColors[k] || function (t) { return t.textMuted; })(self._tokens);
                    }),
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
    },

    _renderTrendChart: function (trendData) {
        var el = document.getElementById('trendChart');
        if (!el || !trendData.length) return;
        this._destroyChart('trend');
        var t = this._tokens;
        this._charts.trend = new Chart(el, {
            type: 'line',
            data: {
                labels: trendData.map(function (d) { return d.date; }),
                datasets: [
                    {
                        label: 'Created',
                        data: trendData.map(function (d) { return d.created; }),
                        borderColor: t.brand,
                        backgroundColor: 'rgba(13,148,136,0.08)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        borderWidth: 2
                    },
                    {
                        label: 'Resolved',
                        data: trendData.map(function (d) { return d.resolved; }),
                        borderColor: t.success,
                        backgroundColor: 'rgba(5,150,105,0.08)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 11 } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: t.border } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                },
                animation: { duration: 600 }
            }
        });
    },

    _renderAllCharts: function (stats) {
        this._renderStatusChart(stats.by_status || {});
        this._renderPriorityChart(stats.by_priority || {});
        this._renderTrendChart(stats.daily_trend || []);
    },

    // ===========================
    // DATA LOADING
    // ===========================

    _ajaxRefresh: function (startDate, endDate) {
        var self = this;
        $.ajax({
            url: self.API_ENDPOINTS.AJAX_STATS,
            data: { start_date: startDate, end_date: endDate },
            method: 'GET',
            timeout: 15000
        }).done(function (res) {
            if (res && res.status) {
                self._renderAllCharts(res.stats || {});
                toastr.success('Dashboard refreshed');
            } else {
                toastr.error('Refresh failed');
            }
        }).fail(function () {
            toastr.error('Refresh failed');
        });
    },

    // ===========================
    // EVENT HANDLERS
    // ===========================

    _bindControls: function () {
        var self = this;

        $('#filterBtn').on('click', function () {
            location.href = site_url + '/dashboard?start_date=' + $('#startDate').val() + '&end_date=' + $('#endDate').val();
        });

        $('#refreshToggle').on('click', function () {
            self._autoRefresh = !self._autoRefresh;
            var $btn = $(this);
            if (self._autoRefresh) {
                $('#refreshLabel').text('ON');
                $btn.addClass('sap-btn-primary').removeClass('sap-btn-secondary');
                toastr.info('Auto-refresh enabled (30s)');
                self._refreshTimer = setInterval(function () {
                    self._ajaxRefresh($('#startDate').val(), $('#endDate').val());
                }, 30000);
            } else {
                $('#refreshLabel').text('Auto');
                $btn.removeClass('sap-btn-primary').addClass('sap-btn-secondary');
                if (self._refreshTimer) {
                    clearInterval(self._refreshTimer);
                    self._refreshTimer = null;
                }
                toastr.info('Auto-refresh disabled');
            }
        });
    },

    // ===========================
    // INITIALIZATION
    // ===========================

    _init: function () {
        this._loadTokens();
        this._chartDefaults();
        var data = window.PageData || {};
        this._renderAllCharts({
            by_status:   data.byStatus,
            by_priority: data.byPriority,
            daily_trend: data.dailyTrend
        });
        this._bindControls();
    },

    // ===========================
    // PUBLIC API
    // ===========================

    refresh: function (startDate, endDate) {
        this._ajaxRefresh(startDate, endDate);
    }
};

// ===========================
// BOOTSTRAP
// ===========================

$(function () {
    Dashboard._init();
});

// ===========================
// WINDOW EXPORT
// ===========================

window.Dashboard = Dashboard;
