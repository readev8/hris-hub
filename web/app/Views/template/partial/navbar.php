<nav class="shell-bar">
    <div class="shell-bar-start">
        <button class="d-md-none shell-btn" data-toggle="sidebar" style="font-size:20px">
            <i class="fas fa-list"></i>
        </button>
        <a href="<?= site_url('dashboard') ?>" class="shell-bar-brand">
            <div class="brand-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <span class="brand-text d-none d-sm-inline">Project Management</span>
        </a>
    </div>

    <div class="shell-bar-center">
    </div>

    <div class="shell-bar-end">
        <button class="shell-btn" title="Notifications">
            <i class="fas fa-bell"></i>
            <span class="badge-dot"></span>
        </button>

        <div class="dropdown">
            <button class="shell-profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <?php $user = session('user'); ?>
                <div class="avatar-circle" style="background:var(--sap-brand);color:#fff">
                    <?= strtoupper(substr(esc($user['full_name'] ?? ''), 0, 1)) ?>
                </div>
                <span class="profile-name d-none d-md-inline"><?= esc($user['full_name'] ?? '') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px;border-radius:8px;border:1px solid var(--sap-border);box-shadow:var(--sap-shadow);padding:6px;margin-top:4px">
                <li>
                    <div class="dropdown-item-text" style="font-size:12px;color:var(--sap-text-muted);padding:4px 12px">
                        <span class="fw-medium" style="color:var(--sap-text)"><?= esc($user['full_name'] ?? '') ?></span><br>
                        <?= esc($user['email'] ?? '') ?>
                    </div>
                </li>
                <li><hr class="dropdown-divider" style="margin:4px 0"></li>
                <li>
                    <a class="dropdown-item" href="<?= site_url('logout') ?>" style="border-radius:6px;font-size:14px;color:var(--sap-error);padding:8px 12px">
                        <i class="fas fa-sign-out-alt me-2"></i>Sign Out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
