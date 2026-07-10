<aside class="sidebar">
    <div class="sidebar-group-label">Main Menu</div>
    <ul class="sidebar-nav">
        <li>
            <a href="<?= site_url('dashboard') ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?= site_url('tickets') ?>">
                <i class="fas fa-ticket-alt"></i>
                <span>Tickets</span>
            </a>
        </li>
        <li>
            <a href="<?= site_url('improvements') ?>">
                <i class="fas fa-rocket"></i>
                <span>Improvements</span>
            </a>
        </li>
    </ul>

    <?php $role = (int) session('role'); ?>
    <?php if (in_array($role, [3, 4, 5], true)): ?>
    <div class="sidebar-group-label">Management</div>
    <ul class="sidebar-nav">
        <?php if (in_array($role, [3, 4, 5], true)): ?>
        <li>
            <a href="<?= site_url('approvals') ?>">
                <i class="fas fa-check-circle"></i>
                <span>Approvals</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if ($role === 5): ?>
        <li>
            <a href="<?= site_url('users') ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (in_array($role, [1, 5], true)): ?>
        <li>
            <a href="<?= site_url('master-projects') ?>">
                <i class="fas fa-project-diagram"></i>
                <span>Master Projects</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <?php elseif (in_array($role, [1], true)): ?>
    <div class="sidebar-group-label">Configuration</div>
    <ul class="sidebar-nav">
        <li>
            <a href="<?= site_url('master-projects') ?>">
                <i class="fas fa-project-diagram"></i>
                <span>Master Projects</span>
            </a>
        </li>
    </ul>
    <?php endif; ?>
</aside>
