<?php
/**
 * ============================================================================
 * MONITORING — SECTION SESSIONS (tersendiri)
 * ============================================================================
 *
 * Description: Section khusus tabel hr_selfservice.session — KPI sesi,
 *              tren login, distribusi apps, dan tabel sesi terbaru.
 *
 * Required: $stats
 * Optional: (none)
 */
$sess = $stats['sessions'] ?? [];
?>
<div class="mon-section" id="section-session">
    <div class="mon-section-head">
        <h2><i class="fas fa-sign-in-alt"></i> Sesi Pengguna</h2>
        <span class="text-muted" style="font-size:12px">Sumber: hr_selfservice.session (active=0)</span>
    </div>
    <div class="mon-kpi-grid">
        <div class="metric-card"><div class="metric-value sap-count-up" id="sess-total"><?= (int) ($sess['total'] ?? 0) ?></div><div class="metric-label">Total Sesi</div></div>
        <div class="metric-card"><div class="metric-value sap-count-up" id="sess-today">0</div><div class="metric-label">Sesi Hari Ini</div></div>
        <div class="metric-card"><div class="metric-value sap-count-up" id="sess-users">0</div><div class="metric-label">User Unik</div></div>
    </div>
    <div class="mon-chart-grid">
        <div class="mon-card"><h3>Tren Sesi per Hari</h3><canvas id="sessTrendChart"></canvas></div>
        <div class="mon-card"><h3>Distribusi Apps</h3><canvas id="sessAppsChart"></canvas></div>
    </div>
    <div class="mon-card">
        <h3>Sesi Terbaru (100 terakhir)</h3>
        <div class="table-responsive">
            <table class="sap-table display responsive nowrap" id="grid-session" style="width:100%">
                <thead><tr><th>UserID</th><th>IP</th><th>Apps</th><th>Start</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
