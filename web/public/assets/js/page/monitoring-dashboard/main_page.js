/**
 * ============================================================================
 * MONITORING DASHBOARD — Main Page JS
 * ============================================================================
 *
 * Description: Charts, tables, tabs, and interactions for monitoring dashboard.
 * Dependencies: Chart.js, jQuery DataTables
 */
(function () {
    'use strict';

    var PD = window.PageData || {};
    var BASE = (window.site_url || '').replace(/\/$/, '');
    var refreshTimer = null;
    var REFRESH_MS = 60000;

    /* ── Utility ──────────────────────────────────────────────── */
    function ajaxGet(url, params) {
        return $.ajax({
            url: BASE + url,
            data: params || {},
            dataType: 'json',
            timeout: 30000
        });
    }

    function formatDateTime(str) {
        if (!str) return '-';
        var d = new Date(str);
        if (isNaN(d.getTime())) return str;
        var pad = function (n) { return n < 10 ? '0' + n : n; };
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' +
               pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    }

    /* ── Charts ───────────────────────────────────────────────── */
    var charts = {};

    function initCharts() {
        var stats = PD.stats || {};
        var sessions = stats.sessions || {};

        // Login Trend Chart (mock data for demo)
        var trendCtx = document.getElementById('loginTrendChart');
        if (trendCtx) {
            var labels = [];
            var data = [];
            for (var i = 13; i >= 0; i--) {
                var d = new Date();
                d.setDate(d.getDate() - i);
                labels.push((d.getMonth() + 1) + '/' + d.getDate());
                data.push(Math.floor(Math.random() * 50) + 10);
            }
            charts.loginTrend = new Chart(trendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Login',
                        data: data,
                        borderColor: '#0D9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 2,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Login by Hour Chart
        var hourCtx = document.getElementById('loginHourChart');
        if (hourCtx) {
            var hourLabels = [];
            var hourData = [];
            for (var h = 0; h < 24; h++) {
                hourLabels.push(h + ':00');
                hourData.push(h >= 8 && h <= 17 ? Math.floor(Math.random() * 30) + 5 : Math.floor(Math.random() * 5));
            }
            charts.loginHour = new Chart(hourCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: hourLabels,
                    datasets: [{
                        label: 'Login',
                        data: hourData,
                        backgroundColor: '#EA580C',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Top Pages Chart
        var pagesCtx = document.getElementById('topPagesChart');
        if (pagesCtx) {
            charts.topPages = new Chart(pagesCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['/dashboard', '/tickets', '/monitoring', '/improvements', '/approvals'],
                    datasets: [{
                        label: 'Akses',
                        data: [120, 95, 78, 65, 42],
                        backgroundColor: '#059669',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }

        // Login Status Doughnut
        var statusCtx = document.getElementById('loginStatusChart');
        if (statusCtx) {
            charts.loginStatus = new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Berhasil', 'Gagal'],
                    datasets: [{
                        data: [95, 5],
                        backgroundColor: ['#059669', '#DC2626'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' }
                        }
                    }
                }
            });
        }
    }

    /* ── DataTables ────────────────────────────────────────────── */
    var loginTable = null;
    var accessTable = null;

    function initLoginTable() {
        loginTable = $('#grid-login-logs').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE + '/monitoring-dashboard/ajax-login-logs',
                data: function (d) {
                    d.start_date = $('#filter-start').val();
                    d.end_date = $('#filter-end').val();
                    d.q = $('#login-search').val();
                }
            },
            columns: [
                { data: 'start', render: function (d) { return formatDateTime(d); } },
                { data: 'userid' },
                { data: 'ip' },
                { data: 'apps' },
                {
                    data: 'userid',
                    render: function () {
                        return '<span class="status-badge berhasil"><span class="badge-dot"></span>Berhasil</span>';
                    }
                },
                { data: 'apps' }
            ],
            order: [[0, 'desc']],
            pageLength: 20,
            lengthMenu: [10, 20, 50, 100],
            responsive: true,
            language: {
                processing: 'Memuat...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '(filtered from _MAX_ total records)',
                paginate: { previous: 'Prev', next: 'Next' }
            }
        });

        $('#grid-login-logs tbody').on('click', 'tr', function () {
            var data = loginTable.row(this).data();
            if (data && data.id) {
                showDetail('session', data.id);
            }
        });
    }

    function initAccessTable() {
        accessTable = $('#grid-access-logs').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE + '/monitoring-dashboard/ajax-access-logs',
                data: function (d) {
                    d.start_date = $('#filter-start').val();
                    d.end_date = $('#filter-end').val();
                    d.q = $('#access-search').val();
                }
            },
            columns: [
                { data: 'date', render: function (d) { return formatDateTime(d); } },
                { data: 'user' },
                { data: 'table_label' },
                { data: 'domain' },
                { data: 'detail' }
            ],
            order: [[0, 'desc']],
            pageLength: 20,
            lengthMenu: [10, 20, 50, 100],
            responsive: true,
            language: {
                processing: 'Memuat...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '(filtered from _MAX_ total records)',
                paginate: { previous: 'Prev', next: 'Next' }
            }
        });

        $('#grid-access-logs tbody').on('click', 'tr', function () {
            var data = accessTable.row(this).data();
            if (data && data.id && data.table) {
                showDetail(data.table, data.id);
            }
        });
    }

    /* ── Detail Modal ─────────────────────────────────────────── */
    function showDetail(table, id) {
        var $modal = $('#monDetailModal');
        var $tbody = $modal.find('#mon-detail-table tbody');
        $tbody.html('<tr><td colspan="2" class="text-center text-muted">Memuat...</td></tr>');
        $modal.modal('show');

        ajaxGet('/monitoring-dashboard/ajax-detail', { table: table, id: id })
            .done(function (res) {
                if (!res.status || !res.row) {
                    $tbody.html('<tr><td colspan="2" class="text-center text-danger">Gagal memuat detail</td></tr>');
                    return;
                }
                var html = '';
                var row = res.row;
                $.each(row, function (key, val) {
                    html += '<tr><td style="width:160px;font-weight:600;color:var(--sap-text-secondary)">' +
                            escHtml(key) + '</td><td>' + escHtml(val !== null ? val : '-') + '</td></tr>';
                });
                $tbody.html(html);
            })
            .fail(function () {
                $tbody.html('<tr><td colspan="2" class="text-center text-danger">Gagal memuat detail</td></tr>');
            });
    }

    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /* ── Tabs ─────────────────────────────────────────────────── */
    function initTabs() {
        $('#log-tabs').on('click', '.domain-tab', function () {
            var tab = $(this).data('tab');
            $('#log-tabs .domain-tab').removeClass('active');
            $(this).addClass('active');
            $('.mon-tab-content').hide();
            $('#tab-' + tab).show();

            if (tab === 'login-logs' && loginTable) {
                loginTable.columns.adjust().draw();
            }
            if (tab === 'access-logs' && accessTable) {
                accessTable.columns.adjust().draw();
            }
        });
    }

    /* ── Filter & Export ──────────────────────────────────────── */
    function initFilters() {
        $('#btn-filter').on('click', function () {
            if (loginTable) loginTable.ajax.reload();
            if (accessTable) accessTable.ajax.reload();
        });

        $('#btn-export').on('click', function () {
            var table = $('#export-table').val();
            var params = $.param({
                table: table,
                format: 'csv',
                start_date: $('#filter-start').val(),
                end_date: $('#filter-end').val()
            });
            window.location.href = BASE + '/monitoring-dashboard/export?' + params;
        });

        $('#btn-export-xlsx').on('click', function () {
            var table = $('#export-table').val();
            var params = $.param({
                table: table,
                format: 'xlsx',
                start_date: $('#filter-start').val(),
                end_date: $('#filter-end').val()
            });
            window.location.href = BASE + '/monitoring-dashboard/export?' + params;
        });

        $('#login-search').on('keyup', debounce(function () {
            if (loginTable) loginTable.ajax.reload();
        }, 500));

        $('#access-search').on('keyup', debounce(function () {
            if (accessTable) accessTable.ajax.reload();
        }, 500));
    }

    /* ── Auto Refresh ─────────────────────────────────────────── */
    function initAutoRefresh() {
        var $btn = $('#refreshToggle');
        var $label = $('#refreshLabel');
        var active = true;

        $btn.on('click', function () {
            active = !active;
            $label.text(active ? 'Auto' : 'Off');
            $btn.attr('aria-pressed', active);
            if (active) {
                startRefresh();
            } else {
                stopRefresh();
            }
        });

        startRefresh();
    }

    function startRefresh() {
        stopRefresh();
        refreshTimer = setInterval(function () {
            if (loginTable) loginTable.ajax.reload(null, false);
            if (accessTable) accessTable.ajax.reload(null, false);
        }, REFRESH_MS);
    }

    function stopRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }

    /* ── Debounce Utility ─────────────────────────────────────── */
    function debounce(fn, delay) {
        var timer;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
        };
    }

    /* ── Init ─────────────────────────────────────────────────── */
    $(document).ready(function () {
        initCharts();
        initLoginTable();
        initAccessTable();
        initTabs();
        initFilters();
        initAutoRefresh();
    });
})();
