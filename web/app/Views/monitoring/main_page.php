<?php
/**
 * ============================================================================
 * MONITORING — MAIN PAGE
 * ============================================================================
 *
 * Description: Halaman monitoring seluruh kegiatan HR (hr_selfservice + wine_hris)
 *              dengan KPI per domain, charts, section Sessions tersendiri,
 *              dan tabel drill-down per domain.
 *
 * Required: $stats, $start_date, $end_date
 * Optional: (none)
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>

<?php
$sess  = $stats['sessions'] ?? [];
$asg   = $stats['assignment'] ?? [];
$fpkt  = $stats['fpkt'] ?? [];
$nb    = $stats['ninebox'] ?? [];
$ijin  = $stats['ijin'] ?? [];
$res   = $stats['resign'] ?? [];
$pss   = $stats['panel_ss'] ?? [];
$rek   = $stats['rekrutmen'] ?? [];
$sk    = $stats['sk'] ?? [];
$surat = $stats['surat'] ?? [];
$userName = esc(session('user')['full_name'] ?? 'User');
?>

<!-- ══════════ SECTION 1: Header + Filter ══════════ -->
<div class="dashboard-header">
    <div>
        <h1 class="mb-1">Monitoring HRIS</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Halo <?= $userName ?>, pantau seluruh kegiatan HR di sini.</p>
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
        </div>
    </div>
</div>

<!-- ══════════ SECTION 2: KPI Grid ══════════ -->
<div class="kpi-grid">
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($sess['total'] ?? 0) ?></div><div class="metric-label">Sesi</div></div>
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($asg['assignment'] ?? 0) ?></div><div class="metric-label">Assignment</div></div>
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($fpkt['fpkt'] ?? 0) ?></div><div class="metric-label">FPKT</div></div>
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($nb['assessment'] ?? 0) ?></div><div class="metric-label">Ninebox</div></div>
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($ijin['pengajuan'] ?? 0) ?></div><div class="metric-label">Ijin</div></div>
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($res['pengajuan'] ?? 0) ?></div><div class="metric-label">Resign</div></div>
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($rek['fpk'] ?? 0) ?></div><div class="metric-label">FPK</div></div>
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($sk['pengajuan'] ?? 0) ?></div><div class="metric-label">SK Pengajuan</div></div>
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($surat['pegawai'] ?? 0) ?></div><div class="metric-label">Pegawai</div></div>
    <div class="metric-card"><div class="metric-value sap-count-up"><?= (int) ($surat['kontrak'] ?? 0) ?></div><div class="metric-label">Kontrak</div></div>
</div>

<!-- ══════════ SECTION 3: Charts ══════════ -->
<div class="chart-grid">
    <div class="chart-card"><h3>Distribusi per Domain</h3><canvas id="monStatusChart"></canvas></div>
    <div class="chart-card"><h3>Tren Sesi (14 hari)</h3><canvas id="monTrendChart"></canvas></div>
</div>

<!-- ══════════ SECTION 4: Sessions (tersendiri) ══════════ -->
<?= view('monitoring/_section_session', ['stats' => $stats]) ?>

<!-- ══════════ SECTION 5: Tabel per Domain ══════════ -->
<?= view('monitoring/_table_domain') ?>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/monitoring/main_page.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.PageData = <?= json_encode(['stats' => $stats, 'startDate' => $start_date, 'endDate' => $end_date]) ?>;
</script>
<script src="<?= base_url('public/assets/js/page/monitoring/main_page.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
