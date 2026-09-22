<?php
/**
 * ============================================================================
 * MONITORING DASHBOARD — MAIN PAGE
 * ============================================================================
 *
 * Description: Dashboard monitoring penggunaan aplikasi (log login) dan log akses aplikasi.
 *              Menampilkan KPI cards, charts, tabs Login Logs dan Access Logs.
 *
 * Required: $stats, $start_date, $end_date
 * Optional: $debug
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>

<?php
$sess    = $stats['sessions'] ?? [];
$asg     = $stats['assignment'] ?? [];
$fpkt    = $stats['fpkt'] ?? [];
$nb      = $stats['ninebox'] ?? [];
$ijin    = $stats['ijin'] ?? [];
$res     = $stats['resign'] ?? [];
$rek     = $stats['rekrutmen'] ?? [];
$sk      = $stats['sk'] ?? [];
$surat   = $stats['surat'] ?? [];
$userName = esc(session('user')['full_name'] ?? 'User');
?>

<!-- ══════════ SECTION 1: Header + Filter ══════════ -->
<div class="dashboard-header">
    <div>
        <h1 class="mb-1">Monitoring Penggunaan Aplikasi</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Halo <?= $userName ?>, pantau penggunaan aplikasi dan akses di sini.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="filter-bar">
            <input type="date" id="filter-start" value="<?= esc($start_date ?? date('Y-m-01'), 'attr') ?>">
            <span class="text-muted" style="font-size:13px">to</span>
            <input type="date" id="filter-end" value="<?= esc($end_date ?? date('Y-m-d'), 'attr') ?>">
            <button class="sap-btn sap-btn-primary sap-btn-sm" id="btn-filter">
                <i class="fas fa-filter"></i> Apply
            </button>
            <button class="sap-btn sap-btn-secondary sap-btn-sm" id="btn-export">
                <i class="fas fa-download"></i> Export CSV
            </button>
            <button class="sap-btn sap-btn-secondary sap-btn-sm" id="btn-export-xlsx">
                <i class="fas fa-file-excel"></i> Export XLSX
            </button>
            <select id="export-table" aria-label="Tabel untuk export" style="max-width:190px">
                <option value="session">Login Logs (Session)</option>
                <option value="assignment">Access Logs (Assignment)</option>
            </select>
            <button class="sap-btn sap-btn-secondary sap-btn-sm" id="refreshToggle" aria-pressed="true" title="Auto-refresh every 60s">
                <i class="fas fa-sync-alt"></i> <span id="refreshLabel">Auto</span>
            </button>
        </div>
    </div>
</div>

<!-- ══════════ SECTION 2: KPI Grid ══════════ -->
<div class="mon-kpi-grid">
    <div class="metric-card">
        <div class="metric-icon-wrap" style="background:rgba(13,148,136,0.1);color:var(--sap-brand)"><i class="fas fa-sign-in-alt"></i></div>
        <div class="metric-value sap-count-up"><?= (int) ($sess['total'] ?? 0) ?></div>
        <div class="metric-label">Total Login</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon-wrap" style="background:rgba(13,148,136,0.1);color:var(--sap-info)"><i class="fas fa-users"></i></div>
        <div class="metric-value sap-count-up"><?= (int) ($sess['total'] ?? 0) ?></div>
        <div class="metric-label">User Aktif</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon-wrap" style="background:rgba(220,38,38,0.1);color:var(--sap-error)"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="metric-value sap-count-up" style="color:var(--sap-error)">0</div>
        <div class="metric-label">Login Gagal</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon-wrap" style="background:rgba(5,150,105,0.1);color:var(--sap-success)"><i class="fas fa-circle"></i></div>
        <div class="metric-value sap-count-up" style="color:var(--sap-success)">12</div>
        <div class="metric-label">Sesi Aktif</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon-wrap" style="background:rgba(234,88,12,0.1);color:var(--sap-accent)"><i class="fas fa-desktop"></i></div>
        <div class="metric-value sap-count-up"><?= (int) ($asg['assignment'] ?? 0) ?></div>
        <div class="metric-label">Total Akses</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon-wrap" style="background:rgba(217,119,6,0.1);color:var(--sap-warning)"><i class="fas fa-chart-bar"></i></div>
        <div class="metric-value sap-count-up" style="font-size:18px">/dashboard</div>
        <div class="metric-label">Top Page</div>
    </div>
</div>

<!-- ══════════ SECTION 3: Charts ══════════ -->
<div class="mon-chart-grid">
    <div class="mon-card">
        <h3><i class="fas fa-chart-line" style="color:var(--sap-brand);margin-right:6px"></i>Login Trend (14 hari)</h3>
        <canvas id="loginTrendChart"></canvas>
    </div>
    <div class="mon-card">
        <h3><i class="fas fa-clock" style="color:var(--sap-accent);margin-right:6px"></i>Login by Hour</h3>
        <canvas id="loginHourChart"></canvas>
    </div>
    <div class="mon-card">
        <h3><i class="fas fa-chart-bar" style="color:var(--sap-success);margin-right:6px"></i>Top Pages (10 teratas)</h3>
        <canvas id="topPagesChart"></canvas>
    </div>
    <div class="mon-card">
        <h3><i class="fas fa-chart-pie" style="color:var(--sap-warning);margin-right:6px"></i>Login Status</h3>
        <canvas id="loginStatusChart"></canvas>
    </div>
</div>

<!-- ══════════ SECTION 4: Tabs ══════════ -->
<div class="mon-section" id="section-logs">
    <div class="mon-section-head">
        <div class="domain-tabs" id="log-tabs">
            <button class="domain-tab active" data-tab="login-logs">
                <i class="fas fa-sign-in-alt"></i> Login Logs
            </button>
            <button class="domain-tab" data-tab="access-logs">
                <i class="fas fa-desktop"></i> Access Logs
            </button>
        </div>
    </div>

    <!-- Login Logs Table -->
    <div class="mon-tab-content" id="tab-login-logs">
        <div class="mon-card">
            <div class="mon-table-toolbar">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="text" id="login-search" class="mon-search-input" placeholder="Search UserID, IP...">
                    <select id="login-status-filter" class="mon-filter-select">
                        <option value="">Semua Status</option>
                        <option value="berhasil">Berhasil</option>
                        <option value="gagal">Gagal</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="sap-table display responsive nowrap" id="grid-login-logs" style="width:100%">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>UserID</th>
                            <th>IP Address</th>
                            <th>Device/Browser</th>
                            <th>Status</th>
                            <th>Aplikasi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Access Logs Table -->
    <div class="mon-tab-content" id="tab-access-logs" style="display:none">
        <div class="mon-card">
            <div class="mon-table-toolbar">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="text" id="access-search" class="mon-search-input" placeholder="Search UserID, Halaman...">
                    <select id="access-method-filter" class="mon-filter-select">
                        <option value="">Semua Method</option>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="sap-table display responsive nowrap" id="grid-access-logs" style="width:100%">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>UserID</th>
                            <th>Halaman</th>
                            <th>Domain</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= view('monitoring-dashboard/_modal_detail') ?>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= asset_url('public/assets/css/page/monitoring-dashboard/main_page.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.PageData = <?= json_encode([
    'stats'     => $stats,
    'startDate' => $start_date,
    'endDate'   => $end_date,
    'debug'     => $debug ?? false,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script defer src="<?= asset_url('public/assets/js/page/monitoring-dashboard/main_page.js') ?>"></script>
<?= $this->endSection() ?>
