/**
 * ============================================================================
 * Dashboard Main Page
 * ============================================================================
 *
 * Description: Dashboard with metrics, charts (Chart.js), and activity log
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// CONSTANTS
// ===========================

var STATUS_COLORS = {
    'Open': '#E76500',
    'Approved': '#256F3A',
    'In Progress': '#0070F2',
    'Resolved': '#758CA4',
    'Closed': '#556B82',
    'Rejected': '#AA0808'
};

var TYPE_COLORS = ['#0070F2', '#256F3A', '#E76500', '#8B5CF6'];

// ===========================
// UI
// ===========================

var DashboardUI = {
    renderStatusChart: function(statusData) {
        if (!Object.keys(statusData).length) return;

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: Object.keys(statusData).map(function(k) { return STATUS_COLORS[k] || '#758CA4'; }),
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 12 }, padding: 16, usePointStyle: true } }
                },
                cutout: '68%',
                animation: { animateRotate: true, duration: 800 }
            }
        });
    },

    renderTypeChart: function(typeData) {
        if (!Object.keys(typeData).length) return;

        new Chart(document.getElementById('typeChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(typeData),
                datasets: [{
                    data: Object.values(typeData),
                    backgroundColor: TYPE_COLORS.slice(0, Object.keys(typeData).length),
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 12 }, padding: 16, usePointStyle: true } }
                },
                cutout: '68%',
                animation: { animateRotate: true, duration: 800 }
            }
        });
    },

    renderTrendChart: function(trendData) {
        if (!trendData.length || !document.getElementById('trendChart')) return;

        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendData.map(function(t) { return t.week; }),
                datasets: [{
                    label: 'Tickets Created',
                    data: trendData.map(function(t) { return t.total; }),
                    borderColor: '#0070F2',
                    backgroundColor: 'rgba(0,112,242,0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: 'Inter' } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 } } }
                },
                animation: { duration: 600 }
            }
        });
    },

    renderBugChart: function(bugsByProject) {
        if (!bugsByProject.length || !document.getElementById('bugProjectChart')) return;

        var bpLabels = bugsByProject.map(function(b) { return b.project_name; });
        var bpData = bugsByProject.map(function(b) { return b.total; });

        new Chart(document.getElementById('bugProjectChart'), {
            type: 'bar',
            data: {
                labels: bpLabels,
                datasets: [{
                    label: 'Bugs',
                    data: bpData,
                    backgroundColor: bpData.map(function(v) {
                        return v > 5 ? '#AA0808' : v > 2 ? '#E76500' : '#256F3A';
                    }),
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: 'Inter' } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter' } } }
                },
                animation: { duration: 600 }
            }
        });
    }
};

// ===========================
// INITIALIZATION
// ===========================

$(function() {
    var data = window.PageData || {};
    var statusData = data.statsByStatus || {};
    var typeData = data.statsByType || {};
    var trendData = data.statsWeeklyTrend || [];
    var bugsByProject = data.statsBugsByProject || [];

    var autoRefresh = false;
    var refreshTimer = null;

    DashboardUI.renderStatusChart(statusData);
    DashboardUI.renderTypeChart(typeData);
    DashboardUI.renderTrendChart(trendData);
    DashboardUI.renderBugChart(bugsByProject);

    $('#filterBtn').on('click', function() {
        location.href = site_url + '/dashboard?start_date=' + $('#startDate').val() + '&end_date=' + $('#endDate').val();
    });

    $('#refreshToggle').on('click', function() {
        autoRefresh = !autoRefresh;
        if (autoRefresh) {
            $('#refreshLabel').text('ON');
            $(this).addClass('sap-btn-primary').removeClass('sap-btn-secondary');
            refreshTimer = setInterval(function() { location.reload(); }, 30000);
            toastr.info('Auto-refresh enabled (30s)');
        } else {
            $('#refreshLabel').text('Auto');
            $(this).removeClass('sap-btn-primary').addClass('sap-btn-secondary');
            if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }
            toastr.info('Auto-refresh disabled');
        }
    });
});
