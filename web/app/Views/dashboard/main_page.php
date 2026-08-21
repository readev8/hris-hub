<?php
/**
 * ============================================================================
 * DASHBOARD — MAIN PAGE
 * ============================================================================
 *
 * Description: Halaman dashboard utama dengan KPI cards, charts, approval queue,
 *              workload, project health, dan recent activity.
 *
 * Required: $stats, $logs, $start_date, $end_date, $role_id, $is_approver,
 *           $is_manager, $is_admin, $role_name
 * Optional: (none)
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>

<?php
$hour = (int) date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$userName = esc(session('user')['full_name'] ?? 'User');
$totals   = $stats['totals'] ?? [];
$pending  = $stats['pending_approvals'] ?? [];
$byStatus = $stats['by_status'] ?? [];
$byType   = $stats['by_type'] ?? [];
$byPriority = $stats['by_priority'] ?? [];
$dailyTrend  = $stats['daily_trend'] ?? [];
$bugsByProject = $stats['bugs_by_project'] ?? [];
$workload = $stats['assignee_workload'] ?? [];
$myQueue  = $stats['my_queue'] ?? [];
$projectsByStatus = $stats['projects_by_status'] ?? [];
$blueprintsByStatus = $stats['blueprints_by_status'] ?? [];
$overdue  = (int) ($stats['overdue_tickets'] ?? 0);
$unassigned = (int) ($stats['unassigned_open'] ?? 0);
$openTicketsCount = (int) (($byStatus['Open'] ?? 0) + ($byStatus['Approved'] ?? 0));

// Role flags
$roleId     = (int) ($role_id ?? 0);
$isApprover = $is_approver ?? false;
$isManager  = $is_manager ?? false;
$isAdmin    = $is_admin ?? false;
$isRequester = $roleId === \App\Config\Enums::REQUESTER;
$isDeveloper = $roleId === \App\Config\Enums::DEVELOPER;
?>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- SECTION 1: Header + Quick Actions                            -->
<!-- ════════════════════════════════════════════════════════════ -->
<div class="dashboard-header">
    <div>
        <h1 class="mb-1"><?= $greeting ?>, <?= $userName ?></h1>
        <p class="text-secondary mb-0" style="font-size:13px">
            <span class="sap-badge <?= strtolower(str_replace(' ', '', $role_name ?? 'user')) ?> me-1"><span class="badge-dot"></span><?= esc($role_name ?? 'User') ?></span>
            Here's what's happening in your workspace.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <?php if (has_permission('tickets', 'can_create')): ?>
        <a href="<?= site_url('tickets/create') ?>" class="sap-btn sap-btn-primary sap-btn-sm">
            <i class="fas fa-plus"></i> Create Ticket
        </a>
        <?php endif; ?>
        <?php if (has_permission('improvements', 'can_create')): ?>
        <a href="<?= site_url('improvements/create') ?>" class="sap-btn sap-btn-accent sap-btn-sm">
            <i class="fas fa-lightbulb"></i> Submit Improvement
        </a>
        <?php endif; ?>
        <div class="filter-bar">
            <input type="date" id="startDate" value="<?= esc($start_date ?? date('Y-m-01'), 'attr') ?>">
            <span class="text-muted" style="font-size:13px">to</span>
            <input type="date" id="endDate" value="<?= esc($end_date ?? date('Y-m-d'), 'attr') ?>">
            <button class="sap-btn sap-btn-primary sap-btn-sm" id="filterBtn">
                <i class="fas fa-filter"></i> Apply
            </button>
            <button class="sap-btn sap-btn-secondary sap-btn-sm" id="refreshToggle" title="Auto-refresh every 30s">
                <i class="fas fa-sync-alt"></i> <span id="refreshLabel">Auto</span>
            </button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- SECTION 2: KPI Metric Cards (role-aware)                     -->
<!-- ════════════════════════════════════════════════════════════ -->
<div class="kpi-grid mb-4" id="metricCards">
    <?php if ($isRequester || $isDeveloper): ?>
    <a href="<?= site_url('tickets') ?>?status=0" class="metric-card metric-clickable icon-brand">
        <div class="metric-icon"><i class="fas fa-inbox"></i></div>
        <div class="metric-content">
            <div class="metric-label">My Open Tickets</div>
            <div class="metric-value sap-count-up"><?= (int) ($myQueue['my_open_tickets'] ?? 0) ?></div>
        </div>
    </a>
    <?php else: ?>
    <a href="<?= site_url('tickets') ?>" class="metric-card metric-clickable icon-brand">
        <div class="metric-icon"><i class="fas fa-ticket-alt"></i></div>
        <div class="metric-content">
            <div class="metric-label">Total Tickets</div>
            <div class="metric-value sap-count-up"><?= (int) ($totals['tickets'] ?? 0) ?></div>
        </div>
    </a>
    <?php endif; ?>

    <?php if ($isApprover): ?>
    <a href="<?= site_url('approvals') ?>" class="metric-card metric-clickable icon-warning <?= (($pending['total'] ?? 0) > 0) ? 'metric-pulse' : '' ?>">
        <div class="metric-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="metric-content">
            <div class="metric-label">Pending My Approval</div>
            <div class="metric-value sap-count-up"><?= (int) ($pending['total'] ?? 0) ?></div>
        </div>
    </a>
    <?php endif; ?>

    <a href="<?= site_url('improvements') ?>" class="metric-card metric-clickable icon-info">
        <div class="metric-icon"><i class="fas fa-rocket"></i></div>
        <div class="metric-content">
            <div class="metric-label"><?= ($isRequester || $isDeveloper) ? 'My Improvements' : 'Total Projects' ?></div>
            <div class="metric-value sap-count-up"><?= (int) ($totals['projects'] ?? 0) ?></div>
        </div>
    </a>

    <?php if ($isManager): ?>
    <a href="<?= site_url('blueprints') ?>" class="metric-card metric-clickable icon-success">
        <div class="metric-icon"><i class="fas fa-drafting-compass"></i></div>
        <div class="metric-content">
            <div class="metric-label">Total Blueprints</div>
            <div class="metric-value sap-count-up"><?= (int) ($totals['blueprints'] ?? 0) ?></div>
        </div>
    </a>
    <?php endif; ?>

    <?php if ($overdue > 0): ?>
    <a href="<?= site_url('tickets') ?>?overdue=1" class="metric-card metric-clickable icon-danger metric-pulse">
        <div class="metric-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="metric-content">
            <div class="metric-label">Overdue Tickets</div>
            <div class="metric-value sap-count-up"><?= $overdue ?></div>
        </div>
    </a>
    <?php endif; ?>

    <a href="<?= site_url('tickets') ?>?status=0" class="metric-card metric-clickable icon-success">
        <div class="metric-icon"><i class="fas fa-folder-open"></i></div>
        <div class="metric-content">
            <div class="metric-label">Open Tickets</div>
            <div class="metric-value sap-count-up"><?= (int) ($byStatus['Open'] ?? 0) ?></div>
        </div>
    </a>
</div>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- SECTION 3: Approval Queue (approvers only)                   -->
<!-- ════════════════════════════════════════════════════════════ -->
<?php if ($isApprover && (($pending['total'] ?? 0) > 0 || !empty($myQueue['my_pending_approvals']['tickets']) || !empty($myQueue['my_pending_approvals']['improvements']) || !empty($myQueue['my_pending_approvals']['blueprints']))): ?>
<div class="sap-card mb-3" id="approvalQueueCard">
    <div class="sap-card-header">
        <i class="fas fa-tasks" style="color:var(--sap-warning);font-size:18px"></i>
        Approval Queue
        <span class="sap-badge warning ms-2"><span class="badge-dot"></span><?= (int) ($pending['total'] ?? 0) ?> pending</span>
    </div>
    <div class="sap-card-body p-0">
        <div class="approval-queue-grid">
            <?php if (!empty($myQueue['my_pending_approvals']['tickets'])): ?>
            <div class="approval-queue-section">
                <h6 class="approval-queue-title"><i class="fas fa-ticket-alt"></i> Tickets (<?= count($myQueue['my_pending_approvals']['tickets']) ?>)</h6>
                <?php foreach ($myQueue['my_pending_approvals']['tickets'] as $item): ?>
                <div class="approval-queue-item">
                    <div class="aq-item-main">
                        <a href="<?= site_url('tickets/' . $item['id']) ?>" class="aq-item-title"><?= esc($item['title']) ?></a>
                        <span class="aq-item-meta">by <?= esc($item['creator_name'] ?? '?') ?> · <?= esc($item['priority'] ?? '') ?> priority</span>
                    </div>
                    <span class="aq-item-age text-muted" style="font-size:12px"><?= esc($item['created_at'] ?? '') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($myQueue['my_pending_approvals']['improvements'])): ?>
            <div class="approval-queue-section">
                <h6 class="approval-queue-title"><i class="fas fa-rocket"></i> Improvements (<?= count($myQueue['my_pending_approvals']['improvements']) ?>)</h6>
                <?php foreach ($myQueue['my_pending_approvals']['improvements'] as $item): ?>
                <div class="approval-queue-item">
                    <div class="aq-item-main">
                        <a href="<?= site_url('improvements/' . $item['id']) ?>" class="aq-item-title"><?= esc($item['title']) ?></a>
                        <span class="aq-item-meta">by <?= esc($item['creator_name'] ?? '?') ?> · <?= esc($item['priority'] ?? '') ?> priority</span>
                    </div>
                    <span class="aq-item-age text-muted" style="font-size:12px"><?= esc($item['created_at'] ?? '') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($myQueue['my_pending_approvals']['blueprints'])): ?>
            <div class="approval-queue-section">
                <h6 class="approval-queue-title"><i class="fas fa-drafting-compass"></i> Blueprints (<?= count($myQueue['my_pending_approvals']['blueprints']) ?>)</h6>
                <?php foreach ($myQueue['my_pending_approvals']['blueprints'] as $item): ?>
                <div class="approval-queue-item">
                    <div class="aq-item-main">
                        <a href="<?= site_url('blueprints/' . $item['id']) ?>" class="aq-item-title"><?= esc($item['title']) ?></a>
                        <span class="aq-item-meta">by <?= esc($item['creator_name'] ?? '?') ?></span>
                    </div>
                    <span class="aq-item-age text-muted" style="font-size:12px"><?= esc($item['created_at'] ?? '') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- SECTION 4: Charts Grid (2x2)                                 -->
<!-- ════════════════════════════════════════════════════════════ -->
<div class="chart-grid mb-3">
    <div class="sap-card">
        <div class="sap-card-header">
            <i class="fas fa-chart-pie" style="color:var(--sap-brand);font-size:18px"></i>
            Tickets by Status
        </div>
        <div class="sap-card-body">
            <canvas id="statusChart" height="180"></canvas>
        </div>
    </div>
    <div class="sap-card">
        <div class="sap-card-header">
            <i class="fas fa-exclamation-circle" style="color:var(--sap-high);font-size:18px"></i>
            Tickets by Priority
        </div>
        <div class="sap-card-body">
            <canvas id="priorityChart" height="180"></canvas>
        </div>
    </div>
</div>

<div class="sap-card mb-3">
    <div class="sap-card-header">
        <i class="fas fa-chart-line" style="color:var(--sap-brand);font-size:18px"></i>
        Daily Trend (Last 14 Days)
        <span class="ms-auto text-muted" style="font-size:12px;font-weight:400">
            <span style="color:var(--sap-brand)">●</span> Created &nbsp;
            <span style="color:var(--sap-success)">●</span> Resolved
        </span>
    </div>
    <div class="sap-card-body">
        <canvas id="trendChart" height="80"></canvas>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- SECTION 5: Assignee Workload (managers/admin only)           -->
<!-- ════════════════════════════════════════════════════════════ -->
<?php if ($isManager && !empty($workload)): ?>
<div class="sap-card mb-3">
    <div class="sap-card-header">
        <i class="fas fa-users" style="color:var(--sap-info);font-size:18px"></i>
        Assignee Workload (Top 5)
    </div>
    <div class="sap-card-body">
        <div id="workloadContainer">
            <?php foreach ($workload as $w): ?>
            <div class="workload-row">
                <span class="workload-name"><?= esc($w['assignee_name'] ?? '?') ?></span>
                <div class="workload-bar-wrap">
                    <div class="workload-bar" style="width:<?= min(100, ((int) $w['total']) * 10) ?>%">
                        <span class="workload-bar-open" style="width:<?= ((int) ($w['open'] + $w['in_progress']) > 0) ? round(($w['open'] / max(1, $w['open'] + $w['in_progress'])) * 100) : 0 ?>%"></span>
                    </div>
                </div>
                <span class="workload-count">
                    <span class="sap-badge rejected sap-badge-sm"><span class="badge-dot"></span><?= (int) $w['open'] ?> open</span>
                    <span class="sap-badge info sap-badge-sm"><span class="badge-dot"></span><?= (int) $w['in_progress'] ?> in progress</span>
                    <strong><?= (int) $w['total'] ?> total</strong>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- SECTION 6: Master Project Health                             -->
<!-- ════════════════════════════════════════════════════════════ -->
<?php if (!empty($bugsByProject)): ?>
<div class="sap-card mb-3">
    <div class="sap-card-header">
        <i class="fas fa-bug" style="color:var(--sap-error);font-size:18px"></i>
        Master Project Bug Health
    </div>
    <div class="sap-card-body p-0">
        <div style="overflow-x:auto">
            <table class="sap-table mb-0">
                <thead>
                    <tr><th>Project</th><th>Total</th><th>Open</th><th>Closed</th><th>Health</th><th style="width:80px">Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($bugsByProject as $bp):
                        $health = (int) ($bp['health_score'] ?? 100);
                        $healthCls = $health >= 70 ? 'approved' : ($health >= 40 ? 'open' : 'rejected');
                    ?>
                    <tr>
                        <td class="fw-medium"><?= esc($bp['project_name']) ?></td>
                        <td><span class="sap-badge info sap-badge-sm"><span class="badge-dot"></span><?= (int) $bp['total'] ?></span></td>
                        <td><span class="sap-badge rejected sap-badge-sm"><span class="badge-dot"></span><?= (int) $bp['open'] ?></span></td>
                        <td><span class="sap-badge approved sap-badge-sm"><span class="badge-dot"></span><?= (int) $bp['closed'] ?></span></td>
                        <td><span class="health-badge health-<?= $health >= 70 ? 'good' : ($health >= 40 ? 'warn' : 'bad') ?>"><?= $health ?>%</span></td>
                        <td>
                            <a href="<?= site_url('master-projects/' . $bp['project_id']) ?>" class="sap-btn sap-btn-secondary sap-btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- SECTION 7: Project & Blueprint Pipeline (managers only)      -->
<!-- ════════════════════════════════════════════════════════════ -->
<?php if ($isManager && (!empty($projectsByStatus) || !empty($blueprintsByStatus))): ?>
<div class="row g-2 mb-3">
    <?php if (!empty($projectsByStatus)): ?>
    <div class="col-md-6">
        <div class="sap-card h-100">
            <div class="sap-card-header">
                <i class="fas fa-rocket" style="color:var(--sap-brand);font-size:18px"></i>
                Improvement Pipeline
            </div>
            <div class="sap-card-body">
                <div class="pipeline-stats">
                    <?php foreach (['Draft' => 'draft', 'Pending' => 'pending', 'Approved' => 'approved', 'Rejected' => 'rejected', 'On Hold' => 'open', 'Completed' => 'closed'] as $label => $cls):
                        $count = (int) ($projectsByStatus[$label] ?? 0);
                        if ($count > 0):
                    ?>
                    <span class="sap-badge <?= $cls ?>"><span class="badge-dot"></span><?= $label ?>: <?= $count ?></span>
                    <?php endif;
                    endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($blueprintsByStatus)): ?>
    <div class="col-md-6">
        <div class="sap-card h-100">
            <div class="sap-card-header">
                <i class="fas fa-drafting-compass" style="color:var(--sap-accent);font-size:18px"></i>
                Blueprint Pipeline
            </div>
            <div class="sap-card-body">
                <div class="pipeline-stats">
                    <?php foreach (['Draft' => 'draft', 'Pending' => 'pending', 'Approved' => 'approved', 'Rejected' => 'rejected', 'On Hold' => 'open', 'Completed' => 'closed'] as $label => $cls):
                        $count = (int) ($blueprintsByStatus[$label] ?? 0);
                        if ($count > 0):
                    ?>
                    <span class="sap-badge <?= $cls ?>"><span class="badge-dot"></span><?= $label ?>: <?= $count ?></span>
                    <?php endif;
                    endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════ -->
<!-- SECTION 8: Recent Activity                                   -->
<!-- ════════════════════════════════════════════════════════════ -->
<div class="sap-card">
    <div class="sap-card-header">
        <i class="fas fa-history" style="color:var(--sap-text-muted);font-size:18px"></i>
        Recent Activity
        <span class="ms-auto text-muted" style="font-size:12px;font-weight:400"><?= esc($start_date ?? '') ?> → <?= esc($end_date ?? '') ?></span>
    </div>
    <div class="sap-card-body p-0">
        <?php if (!empty($logs)): ?>
        <div style="overflow-x:auto">
            <table class="sap-table mb-0">
                <thead>
                    <tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log):
                        $action = $log['action'] ?? '';
                        $actionCls = 'info';
                        if (strpos($action, 'create') !== false) $actionCls = 'in-progress';
                        elseif (strpos($action, 'approve') !== false) $actionCls = 'approved';
                        elseif (strpos($action, 'reject') !== false) $actionCls = 'rejected';
                        elseif (strpos($action, 'resolve') !== false || strpos($action, 'close') !== false) $actionCls = 'resolved';
                        elseif (strpos($action, 'delete') !== false) $actionCls = 'rejected';
                    ?>
                    <tr>
                        <td><span class="text-muted" style="font-size:13px"><?= esc($log['created_at'] ?? '') ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?= avatar_initials($log['user_name'] ?? '?', 'sm', 'var(--sap-brand)') ?>
                                <span><?= esc($log['user_name'] ?? '') ?></span>
                            </div>
                        </td>
                        <td><span class="sap-badge <?= $actionCls ?>"><span class="badge-dot"></span><?= esc(str_replace('_', ' ', $action)) ?></span></td>
                        <td><span class="text-muted"><?= esc($log['entity_type'] ?? '') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="sap-empty" style="padding:40px">
            <i class="fas fa-file-alt" style="font-size:36px"></i>
            <h4>No Recent Activity</h4>
            <p>There is no activity log for the selected period.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/dashboard/main_page.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.PageData = <?= json_encode([
    'byStatus'      => $byStatus,
    'byType'        => $byType,
    'byPriority'    => $byPriority,
    'dailyTrend'    => $dailyTrend,
    'bugsByProject' => $bugsByProject,
    'startDate'     => $start_date ?? date('Y-m-01'),
    'endDate'       => $end_date ?? date('Y-m-d'),
    'isAdmin'       => $isAdmin,
    'isManager'     => $isManager,
    'isApprover'    => $isApprover,
]) ?>;
</script>
<script src="<?= base_url('public/assets/js/page/dashboard/main_page.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
