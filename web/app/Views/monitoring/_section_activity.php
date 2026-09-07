<?php
/**
 * ============================================================================
 * MONITORING — SECTION 100 LAST ACTIVITY
 * ============================================================================
 *
 * Description: 100 aktivitas terbaru gabungan lintas domain, terurut tanggal.
 *              Klik item untuk detail (modal). Filter per domain.
 */
?>
<div class="mon-section" id="section-activity">
    <div class="mon-section-head">
        <h2><i class="fas fa-history"></i> 100 Last Activity</h2>
        <select id="activity-domain" aria-label="Filter domain aktivitas" style="max-width:190px">
            <option value="">Semua domain</option>
            <option value="assignment">Assignment</option>
            <option value="fpkt">FPKT</option>
            <option value="ninebox">Ninebox</option>
            <option value="ijin">Ijin</option>
            <option value="resign">Resign</option>
            <option value="panel">Panel/SS</option>
            <option value="rekrutmen">Rekrutmen</option>
            <option value="sk">SK</option>
            <option value="surat">Surat</option>
        </select>
    </div>
    <div class="mon-card">
        <div class="table-responsive">
            <table class="sap-table" id="grid-activity">
                <thead><tr><th>Waktu</th><th>Jenis</th><th>User</th><th>Keterangan</th></tr></thead>
                <tbody><tr><td colspan="4" class="text-center text-muted">Memuat…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
