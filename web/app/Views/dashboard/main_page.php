<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="dashboard-header">
        <h1 class="mb-0">Dashboard</h1>
        <div class="filter-date-range">
            <input type="date" id="startDate" value="<?= date('Y-m-01') ?>">
            <span class="text-meta">to</span>
            <input type="date" id="endDate" value="<?= date('Y-m-d') ?>">
            <button class="btn btn-sm btn-primary" id="filterBtn">
                <i class="bi bi-filter"></i> Filter
            </button>
        </div>
    </div>

    <div class="row g-2 mb-3" id="metricCards">
        <div class="col-md-3 col-6">
            <div class="metric-card icon-primary">
                <div class="metric-icon"><i class="bi bi-ticket-perforated"></i></div>
                <div class="metric-content">
                    <div class="metric-label">Total Tickets</div>
                    <div class="metric-value"><?= (int) ($stats['total_tickets'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="metric-card icon-info">
                <div class="metric-icon"><i class="bi bi-rocket-takeoff"></i></div>
                <div class="metric-content">
                    <div class="metric-label">Total Projects</div>
                    <div class="metric-value"><?= (int) ($stats['total_projects'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="metric-card icon-warning">
                <div class="metric-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="metric-content">
                    <div class="metric-label">Pending Approvals</div>
                    <div class="metric-value"><?= (int) ($stats['pending_approvals'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="metric-card icon-success">
                <div class="metric-icon"><i class="bi bi-folder2-open"></i></div>
                <div class="metric-content">
                    <div class="metric-label">Open Tickets</div>
                    <div class="metric-value tabular"><?= (int) (($stats['by_status']['Open'] ?? 0) + ($stats['by_status']['Approved'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart" style="color:var(--primary);font-size:18px"></i>
                    Ticket Status
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="140"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart" style="color:var(--primary);font-size:18px"></i>
                    Ticket Type
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php $bugsByProject = $stats['bugs_by_project'] ?? []; ?>
    <?php if (!empty($bugsByProject)): ?>
    <div class="card mt-2">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-bug" style="color:#E11D48;font-size:18px"></i>
            Bug Distribution
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <canvas id="bugProjectChart" height="80"></canvas>
                </div>
                <div class="col-md-7">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>Project</th><th>Total Bugs</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bugsByProject as $bp): ?>
                            <tr>
                                <td class="fw-medium"><?= esc($bp['project_name']) ?></td>
                                <td>
                                    <span class="status-badge danger"><span class="badge-dot"></span><?= (int) $bp['total'] ?> bugs</span>
                                </td>
                                <td>
                                    <a href="<?= site_url('master-projects/' . $bp['project_id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
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
    <div class="card mt-3">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-activity" style="color:var(--text-meta);font-size:18px"></i>
            Recent Activity
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><span class="text-meta" style="font-size:13px"><?= esc($log['created_at']) ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?= avatar_initials($log['user_name'] ?? '?', 'sm', '#0F4C81') ?>
                                <span><?= esc($log['user_name'] ?? '') ?></span>
                            </div>
                        </td>
                        <td><span class="status-badge info"><span class="badge-dot"></span><?= esc($log['action']) ?></span></td>
                        <td><span class="text-meta"><?= esc($log['entity_type']) ?> #<?= esc($log['entity_id']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-state mt-3">
        <i class="bi bi-journal-text"></i>
        <h4>No Recent Activity</h4>
        <p>There is no activity log for the selected period.</p>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    var statusData = <?= json_encode($stats['by_status'] ?? []) ?>;
    var typeData = <?= json_encode($stats['by_type'] ?? []) ?>;

    var statusColors = {
        'Open': '#F59E0B',
        'Approved': '#10B981',
        'In Progress': '#0EA5E9',
        'Resolved': '#64748B',
        'Closed': '#94A3B8',
        'Rejected': '#E11D48'
    };
    var typeColors = ['#0F4C81', '#10B981', '#F59E0B'];

    if (Object.keys(statusData).length) {
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: Object.keys(statusData).map(function(k) { return statusColors[k] || '#94A3B8'; }),
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 12 }, padding: 16 } }
                },
                cutout: '65%'
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
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 12 }, padding: 16 } }
                },
                cutout: '65%'
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
                        return v > 5 ? '#E11D48' : v > 2 ? '#F59E0B' : '#10B981';
                    }),
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    $('#filterBtn').on('click', function() {
        location.href = site_url + '/dashboard?start_date=' + $('#startDate').val() + '&end_date=' + $('#endDate').val();
    });
});
</script>
<?= $this->endSection() ?>
