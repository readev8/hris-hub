<?php
/**
 * ============================================================================
 * MONITORING — TABEL PER DOMAIN
 * ============================================================================
 *
 * Description: Tab drill-down per domain. Tiap tab memuat 1 tabel utama
 *              via AJAX; tombol export mengunduh CSV tabel aktif.
 *              data-table = kunci whitelist API (MonitoringCheck_model).
 */
$tabs = [
    'assignment' => [['Assignment', 'assignment'], ['Assignment Approve', 'assignment_approve']],
    'fpkt'       => [['FPKT', 'fpkt'], ['FPKT Jobdesc', 'fpkt_jobdesc'], ['FPKT Pelatihan', 'fpkt_pelatihan'], ['FPKT Value', 'fpkt_value']],
    'ninebox'    => [['Assessment', 'ninebox_assessment'], ['RTC', 'ninebox_rtc'], ['Nilai Panel', 'ppanelmt_nilai']],
    'ijin'       => [['Pengajuan Ijin', 'pengajuan_ijin'], ['Ijin Approve', 'pengajuan_ijin_approve']],
    'resign'     => [['Pengajuan Resign', 'pengajuan_resign'], ['Resign Approve', 'pengajuan_resign_approve']],
    'panel'      => [['Panel', 'ppanel'], ['SS', 'ss'], ['SS Approval', 'ss_approval'], ['Jobcode', 'jobcode']],
    'rekrutmen'  => [['FPK', 'w_fpk'], ['FPK Approve', 'w_fpk_approve'], ['Pelamar', 'w_pelamar'], ['FPMJ Approve', 'w_fpmj_approve'], ['PPMJ Approve', 'w_ppmj_approve'], ['Penilaian Panel', 'w_penilaianpanel']],
    'sk'         => [['SK Pengajuan', 'w_sk_pengajuan'], ['SK', 'w_sk'], ['Memo Keluar', 'w_memo_keluar']],
    'surat'      => [['SP', 'w_surat_peringatan'], ['Jamsostek', 'w_surat_jamsostek'], ['Referensi', 'w_surat_referensi'], ['Kontrak', 'w_kontrak'], ['Pegawai', 'w_pegawai']],
];
?>
<div class="mon-section" id="section-domains">
    <div class="mon-section-head"><h2><i class="fas fa-table"></i> Data per Domain</h2></div>
    <div class="domain-tabs" role="tablist">
        <?php $first = true; ?>
        <?php foreach ($tabs as $domain => $tables): ?>
            <button class="domain-tab<?= $first ? ' active' : '' ?>" data-domain="<?= esc($domain, 'attr') ?>" data-table="<?= esc($tables[0][1], 'attr') ?>" role="tab"><?= esc(ucfirst($domain)) ?></button>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </div>
    <div class="domain-subtabs" id="domain-subtabs"></div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="sap-table" id="grid-domain">
                <thead><tr id="grid-domain-head"><th>Data</th></tr></thead>
                <tbody id="grid-domain-body"><tr><td class="text-center text-muted">Pilih tab domain untuk memuat data.</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
<script>window.DomainTabs = <?= json_encode($tabs) ?>;</script>
