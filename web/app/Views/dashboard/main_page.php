<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="dashboard-header">
    <div>
        <h1 class="mb-1">Dashboard</h1>
        <p class="text-secondary mb-0" style="font-size:13px">
            <?php
            $hour = (int) date('H');
            $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            ?>
            <?= $greeting ?>, <?= esc(session('user')['full_name'] ?? 'User') ?>
        </p>
    </div>
    <div class="filter-bar" style="flex-wrap:wrap;gap:8px">
        <input type="date" id="startDate" value="<?= $startDate ?? date('Y-m-01') ?>">
        <span class="text-muted" style="font-size:13px">to</span>
        <input type="date" id="endDate" value="<?= $endDate ?? date('Y-m-d') ?>">
        <button class="sap-btn sap-btn-primary sap-btn-sm" id="filterBtn">
            <i class="fas fa-filter"></i> Apply
        </button>
        <button class="sap-btn sap-btn-secondary sap-btn-sm" id="refreshToggle" title="Auto-refresh every 30s">
            <i class="fas fa-sync-alt"></i> <span id="refreshLabel">Auto</span>
        </button>
    </div>
</div>

<div class="row g-2 mb-4" id="metricCards">
    <div class="col-md-3 col-6">
        <div class="metric-card icon-brand">
            <div class="metric-icon"><i class="fas fa-ticket-alt"></i></div>
            <div class="metric-content">
                <div class="metric-label">Total Tickets</div>
                <div class="metric-value sap-count-up"><?= (int) ($stats['total_tickets'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="metric-card icon-info">
            <div class="metric-icon"><i class="fas fa-rocket"></i></div>
            <div class="metric-content">
                <div class="metric-label">Total Projects</div>
                <div class="metric-value sap-count-up"><?= (int) ($stats['total_projects'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="metric-card icon-warning">
            <div class="metric-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="metric-content">
                <div class="metric-label">Pending Approvals</div>
                <div class="metric-value sap-count-up"><?= (int) ($stats['pending_approvals'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="metric-card icon-success">
            <div class="metric-icon"><i class="fas fa-folder-open"></i></div>
            <div class="metric-content">
                <div class="metric-label">Open Tickets</div>
                <div class="metric-value sap-count-up"><?= (int) (($stats['by_status']['Open'] ?? 0) + ($stats['by_status']['Approved'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-md-6">
        <div class="sap-card">
            <div class="sap-card-header">
                <i class="fas fa-chart-pie" style="color:var(--sap-brand);font-size:18px"></i>
                Ticket by Status
            </div>
            <div class="sap-card-body">
                <canvas id="statusChart" height="140"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sap-card">
            <div class="sap-card-header">
                <i class="fas fa-chart-bar" style="color:var(--sap-brand);font-size:18px"></i>
                Ticket by Type
            </div>
            <div class="sap-card-body">
                <canvas id="typeChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

<?php $trend = $stats['weekly_trend'] ?? []; ?>
<?php if (!empty($trend)): ?>
<div class="sap-card mb-3">
    <div class="sap-card-header">
        <i class="fas fa-chart-line" style="color:var(--sap-brand);font-size:18px"></i>
        Weekly Ticket Trend
    </div>
    <div class="sap-card-body">
        <canvas id="trendChart" height="80"></canvas>
    </div>
</div>
<?php endif; ?>

<?php $bugsByProject = $stats['bugs_by_project'] ?? []; ?>
<?php if (!empty($bugsByProject)): ?>
<div class="sap-card mb-3">
    <div class="sap-card-header">
        <i class="fas fa-bug" style="color:var(--sap-error);font-size:18px"></i>
        Bug Distribution
    </div>
    <div class="sap-card-body">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <canvas id="bugProjectChart" height="80"></canvas>
            </div>
            <div class="col-md-7">
                <table class="sap-table mb-0">
                    <thead>
                        <tr><th>Project</th><th>Total Bugs</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bugsByProject as $bp): ?>
                        <tr>
                            <td class="fw-medium"><?= esc($bp['project_name']) ?></td>
                            <td>
                                <span class="sap-badge rejected"><span class="badge-dot"></span><?= (int) $bp['total'] ?> bugs</span>
                            </td>
                            <td>
                                <a href="<?= site_url('master-projects/' . $bp['project_id']) ?>" class="sap-btn sap-btn-secondary sap-btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($logs)): ?>
<div class="sap-card">
    <div class="sap-card-header">
        <i class="fas fa-chart-line" style="color:var(--sap-text-muted);font-size:18px"></i>
        Recent Activity
    </div>
    <div class="sap-card-body p-0">
        <table class="sap-table mb-0">
            <thead>
                <tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th></tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><span class="text-muted" style="font-size:13px"><?= esc($log['created_at']) ?></span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?= avatar_initials($log['user_name'] ?? '?', 'sm', '#0070F2') ?>
                            <span><?= esc($log['user_name'] ?? '') ?></span>
                        </div>
                    </td>
                    <td><span class="sap-badge info"><span class="badge-dot"></span><?= esc($log['action']) ?></span></td>
                    <td><span class="text-muted"><?= esc($log['entity_type']) ?> #<?= esc($log['entity_id']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="sap-empty mt-3">
    <i class="fas fa-file-alt"></i>
    <h4>No Recent Activity</h4>
    <p>There is no activity log for the selected period.</p>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    var statusData = <?= json_encode($stats['by_status'] ?? []) ?>;
    var typeData = <?= json_encode($stats['by_type'] ?? []) ?>;
    var trendData = <?= json_encode($stats['weekly_trend'] ?? []) ?>;
    var autoRefresh = false;
    var refreshTimer = null;

    var statusColors = {
        'Open': '#E76500',
        'Approved': '#256F3A',
        'In Progress': '#0070F2',
        'Resolved': '#758CA4',
        'Closed': '#556B82',
        'Rejected': '#AA0808'
    };
    var typeColors = ['#0070F2', '#256F3A', '#E76500', '#8B5CF6'];

    if (Object.keys(statusData).length) {
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: Object.keys(statusData).map(function(k) { return statusColors[k] || '#758CA4'; }),
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
    }

    if (Object.keys(typeData).length) {
        new Chart(document.getElementById('typeChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(typeData),
                datasets: [{
                    data: Object.values(typeData),
                    backgroundColor: typeColors.slice(0, Object.keys(typeData).length),
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
    }

    if (trendData.length && document.getElementById('trendChart')) {
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
                    borderWidth: 2,
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
    }

    var bugsByProject = <?= json_encode($stats['bugs_by_project'] ?? []) ?>;
    if (bugsByProject.length && document.getElementById('bugProjectChart')) {
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
                    borderSkipped: false,
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

    $('#filterBtn').on('click', function() {
        location.href = site_url + '/dashboard?start_date=' + $('#startDate').val() + '&end_date=' + $('#endDate').val();
    });

    $('#refreshToggle').on('click', function() {
        autoRefresh = !autoRefresh;
        if (autoRefresh) {
            $('#refreshLabel').text('ON');
            $(this).addClass('sap-btn-primary').removeClass('sap-btn-secondary');
            refreshTimer = setInterval(function() {
                location.reload();
            }, 30000);
            toastr.info('Auto-refresh enabled (30s)');
        } else {
            $('#refreshLabel').text('Auto');
            $(this).removeClass('sap-btn-primary').addClass('sap-btn-secondary');
            if (refreshTimer) {
                clearInterval(refreshTimer);
                refreshTimer = null;
            }
            toastr.info('Auto-refresh disabled');
        }
    });
});
</script>
<?= $this->endSection() ?>
