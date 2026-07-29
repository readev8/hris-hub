<nav class="shell-bar">
    <div class="shell-bar-start">
        <button class="d-md-none shell-btn" data-toggle="sidebar" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <button class="d-none d-md-flex sidebar-toggle-btn" id="sidebarToggle" title="Toggle sidebar">
            <i class="fas fa-angles-left"></i>
        </button>
        <a href="<?= site_url('dashboard') ?>" class="shell-bar-brand">
            <div class="brand-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <span class="brand-text d-none d-sm-inline">HRIS-Hub</span>
        </a>
    </div>

    <div class="shell-bar-center">
    </div>

    <div class="shell-bar-end">
        <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle dark mode">
            <i class="fas fa-moon"></i>
        </button>
        
        <button class="shell-btn" title="Notifications" aria-label="Notifications">
            <i class="fas fa-bell"></i>
            <span class="badge-dot"></span>
        </button>

        <div class="dropdown">
            <button class="shell-profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <?php $user = session('user'); ?>
                <div class="avatar-circle">
                    <?= strtoupper(substr(esc($user['full_name'] ?? ''), 0, 1)) ?>
                </div>
                <span class="profile-name d-none d-md-inline"><?= esc($user['full_name'] ?? '') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:220px;border-radius:12px;border:1px solid var(--sap-border);box-shadow:var(--sap-shadow-lg);padding:8px;margin-top:8px">
                <li>
                    <div class="dropdown-item-text" style="font-size:13px;color:var(--sap-text-muted);padding:6px 14px">
                        <span class="fw-semibold" style="color:var(--sap-text)"><?= esc($user['full_name'] ?? '') ?></span><br>
                        <span style="font-size:12px"><?= esc($user['email'] ?? '') ?></span>
                    </div>
                </li>
                <li><hr class="dropdown-divider" style="margin:6px 0;border-color:var(--sap-border-light)"></li>
                <li>
                    <a class="dropdown-item" href="<?= site_url('logout') ?>" style="border-radius:8px;font-size:14px;color:var(--sap-error);padding:10px 14px;display:flex;align-items:center;gap:10px">
                        <i class="fas fa-sign-out-alt"></i>Sign Out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
