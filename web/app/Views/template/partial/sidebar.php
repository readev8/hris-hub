<?php
/**
 * SIDEBAR — Navigasi samping dengan menu berbasis permission user.
 */
$perms = session('permissions') ?? [];
$canDashboard      = !empty($perms['dashboard']['can_view']);
$canTickets        = !empty($perms['tickets']['can_view']);
$canImprovements   = !empty($perms['improvements']['can_view']);
$canApprove        = !empty($perms['approvals']['can_view']);
$canUsers          = !empty($perms['users']['can_view']);
$canMasterProjects = !empty($perms['master_projects']['can_view']);
$canRoles          = !empty($perms['roles']['can_view']);
$canBlueprints     = !empty($perms['blueprints']['can_view']);
$canModuleFlows   = !empty($perms['module_flows']['can_view']);
?>
<aside class="sidebar">
    <?php if ($canDashboard || $canTickets || $canImprovements || $canBlueprints): ?>
        <div class="sidebar-group-label">Main Menu</div>
        <ul class="sidebar-nav">
            <?php if ($canDashboard): ?>
                <li>
                    <a href="<?= site_url('dashboard') ?>">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>
    <ul class="sidebar-nav">
        <?php if ($canApprove || $canTickets): ?>
            <?php if ($canTickets): ?>
                <li>
                    <a href="<?= site_url('tickets') ?>">
                        <i class="fas fa-ticket"></i>
                        <span>Tickets</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('my-tickets') ?>">
                        <i class="fas fa-user-check"></i>
                        <span>My Tickets</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('tickets/chain') ?>">
                        <i class="fas fa-project-diagram"></i>
                        <span>Ticket Chain</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($canApprove): ?>
                <li>
                    <a href="<?= site_url('approvals') ?>">
                        <i class="fas fa-check-circle"></i>
                        <span>Need Approvals</span>
                    </a>
                </li>
            <?php endif; ?>

    </ul>
<?php endif; ?>
<div class="sidebar-group-label">Project & Improvements</div>
<ul class="sidebar-nav">
    <?php if ($canImprovements): ?>
        <li>
            <a href="<?= site_url('improvements') ?>">
                <i class="fas fa-rocket"></i>
                <span>Improvements</span>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($canBlueprints): ?>
        <li>
            <a href="<?= site_url('blueprints') ?>">
                <i class="fas fa-drafting-compass"></i>
                <span>Blueprints</span>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($canMasterProjects): ?>
        <li>
            <a href="<?= site_url('master-projects') ?>">
                <i class="fas fa-folder-tree"></i>
                <span>Projects</span>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($canModuleFlows): ?>
        <li>
            <a href="<?= site_url('module-flows') ?>">
                <i class="fas fa-diagram-project"></i>
                <span>Module Flows</span>
            </a>
        </li>
    <?php endif; ?>
</ul>

<?php if ($canApprove || $canUsers || $canMasterProjects || $canRoles): ?>
    <div class="sidebar-group-label">Management Users</div>
    <ul class="sidebar-nav">

        <?php if ($canUsers): ?>
            <li>
                <a href="<?= site_url('users') ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($canRoles): ?>
            <li>
                <a href="<?= site_url('roles') ?>">
                    <i class="fas fa-user-shield"></i>
                    <span>Roles</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
<?php endif; ?>

<div class="sidebar-footer">
    <span class="sidebar-footer-text">v<?= config('App')->assetVersion ?></span>
</div>
</aside>