<aside class="sidebar">
    <div class="sidebar-section">Main Menu</div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?= site_url('dashboard') ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?= site_url('tickets') ?>">
                <i class="bi bi-ticket-perforated-fill"></i>
                <span>Tickets</span>
            </a>
        </li>
        <li>
            <a href="<?= site_url('improvements') ?>">
                <i class="bi bi-rocket-takeoff-fill"></i>
                <span>Improvements</span>
            </a>
        </li>
    </ul>

    <?php $role = (int) session('role'); ?>
    <?php if (in_array($role, [3, 4, 5], true)): ?>
    <div class="sidebar-section">Management</div>
    <ul class="sidebar-menu">
        <?php if (in_array($role, [3, 4, 5], true)): ?>
        <li>
            <a href="<?= site_url('approvals') ?>">
                <i class="bi bi-check2-circle"></i>
                <span>Approvals</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if ($role === 5): ?>
        <li>
            <a href="<?= site_url('users') ?>">
                <i class="bi bi-people-fill"></i>
                <span>Users</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (in_array($role, [1, 5], true)): ?>
        <li>
            <a href="<?= site_url('master-projects') ?>">
                <i class="bi bi-diagram-3-fill"></i>
                <span>Master Projects</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <?php elseif (in_array($role, [1], true)): ?>
    <div class="sidebar-section">Configuration</div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?= site_url('master-projects') ?>">
                <i class="bi bi-diagram-3-fill"></i>
                <span>Master Projects</span>
            </a>
        </li>
    </ul>
    <?php endif; ?>
</aside>
