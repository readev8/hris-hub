<nav class="topnavbar">
    <div class="topnavbar-brand">
        <button class="d-md-none nav-icon" data-toggle="sidebar" style="color:#fff;background:transparent;border:none;font-size:24px;padding:4px 8px;border-radius:6px;cursor:pointer">
            <i class="bi bi-list"></i>
        </button>
        <div class="brand-icon">
            <i class="bi bi-kanban"></i>
        </div>
        <a href="<?= site_url('dashboard') ?>" class="brand-text">Project Management</a>
    </div>

    <div class="topnavbar-end">
        <div class="dropdown">
            <button class="user-dropdown dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <?php $user = session('user'); ?>
                <div class="avatar-circle" style="background:rgba(255,255,255,0.2);color:#fff">
                    <?= strtoupper(substr(esc($user['full_name'] ?? ''), 0, 1)) ?>
                </div>
                <span class="d-none d-md-inline" style="font-size:14px;font-weight:500"><?= esc($user['full_name'] ?? '') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:180px;border-radius:8px;border:1px solid var(--border);box-shadow:var(--shadow);padding:6px">
                <li>
                    <div class="dropdown-item-text" style="font-size:12px;color:var(--text-meta);padding:4px 12px">
                        <?= esc($user['email'] ?? '') ?>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= site_url('logout') ?>" style="border-radius:6px;font-size:14px;color:var(--danger)">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a></li>
            </ul>
        </div>
    </div>
</nav>
