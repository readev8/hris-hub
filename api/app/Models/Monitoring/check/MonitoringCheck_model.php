<?php

namespace App\Models\Monitoring\check;

use Config\Tables;

/**
 * Whitelist tabel monitoring + peta kolom filter.
 * date: exact-case kolom tanggal (atau COALESCE(...) NULL-safe, 'bulantahun', null).
 */
class MonitoringCheck_model
{
    private const MAP = [
        'session'                  => ['t' => Tables::MON_SESSION, 'active' => true,  'date' => 'start'],
        'assignment'               => ['t' => Tables::MON_ASSIGNMENT, 'active' => true,  'date' => 'COALESCE(changedon,createdon)'],
        'assignment_approve'       => ['t' => Tables::MON_ASSIGN_APPR, 'active' => true,  'date' => 'COALESCE(changedon,createdon)'],
        'ppanelmt_nilai'           => ['t' => Tables::MON_PPANELMT, 'active' => true,  'date' => 'recdate'],
        'fpkt'                     => ['t' => Tables::MON_FPKT, 'active' => true,  'date' => 'TglPembuatan'],
        'fpkt_jobdesc'             => ['t' => Tables::MON_FPKT_JOBDESC, 'active' => true,  'date' => 'COALESCE(LastUpdate,TglCreate)'],
        'fpkt_pelatihan'           => ['t' => Tables::MON_FPKT_LATIH, 'active' => true,  'date' => 'bulantahun'],
        'fpkt_value'               => ['t' => Tables::MON_FPKT_VALUE, 'active' => false, 'date' => null],
        'ninebox_assessment'       => ['t' => Tables::MON_NB_ASSESS, 'active' => true,  'date' => 'COALESCE(changedon,createdon)'],
        'ninebox_rtc'              => ['t' => Tables::MON_NB_RTC, 'active' => true,  'date' => 'COALESCE(changedon,createdon)'],
        'jobcode'                  => ['t' => Tables::MON_JOBCODE, 'active' => true,  'date' => 'COALESCE(changedon,createdon)'],
        'pengajuan_ijin'           => ['t' => Tables::MON_IJIN, 'active' => true,  'date' => 'createdon'],
        'pengajuan_ijin_approve'   => ['t' => Tables::MON_IJIN_APPR, 'active' => true,  'date' => 'COALESCE(changedon,createdon)'],
        'pengajuan_resign'         => ['t' => Tables::MON_RESIGN, 'active' => true,  'date' => 'TglPengajuan'],
        'pengajuan_resign_approve' => ['t' => Tables::MON_RESIGN_APPR, 'active' => true,  'date' => 'RecDate'],
        'ppanel'                   => ['t' => Tables::MON_PPANEL, 'active' => true,  'date' => 'TglPembuatan'],
        'ss'                       => ['t' => Tables::MON_SS, 'active' => true,  'date' => 'tglpengajuan'],
        'ss_approval'              => ['t' => Tables::MON_SS_APPR, 'active' => true,  'date' => 'recdate'],
        'w_fpk'                    => ['t' => Tables::MON_W_FPK, 'active' => true,  'date' => 'RecDate'],
        'w_fpk_approve'            => ['t' => Tables::MON_W_FPK_APPR, 'active' => true,  'date' => 'RecDate'],
        'w_sk_pengajuan'           => ['t' => Tables::MON_W_SK_PENGAJ, 'active' => true,  'date' => 'RecDate'],
        'w_fpmj_approve'           => ['t' => Tables::MON_W_FPMJ_APPR, 'active' => true,  'date' => 'RecDate'],
        'w_penilaianpanel'         => ['t' => Tables::MON_W_PANEL, 'active' => false, 'date' => 'RecDate'],
        'w_memo_keluar'            => ['t' => Tables::MON_W_MEMO, 'active' => true,  'date' => 'RecDate'],
        'w_sk'                     => ['t' => Tables::MON_W_SK, 'active' => true,  'date' => 'RecDate'],
        'w_pegawai'                => ['t' => Tables::MON_W_PEGAWAI, 'active' => true,  'date' => 'RecDate'],
        'w_pelamar'                => ['t' => Tables::MON_W_PELAMAR, 'active' => true,  'date' => 'RecDate'],
        'w_ppmj_approve'           => ['t' => Tables::MON_W_PPMJ_APPR, 'active' => true,  'date' => 'RecDate'],
        'w_surat_peringatan'       => ['t' => Tables::MON_W_SP, 'active' => true,  'date' => 'RecDate'],
        'w_surat_jamsostek'        => ['t' => Tables::MON_W_SJ, 'active' => true,  'date' => 'RecDate'],
        'w_surat_referensi'        => ['t' => Tables::MON_W_SR, 'active' => true,  'date' => 'RecDate'],
        'w_kontrak'                => ['t' => Tables::MON_W_KONTRAK, 'active' => true,  'date' => 'RecDate'],
    ];

    public function isAllowedTable(string $key): bool
    {
        return isset(self::MAP[$key]);
    }

    public function tableName(string $key): string
    {
        return self::MAP[$key]['t'];
    }

    public function hasActive(string $key): bool
    {
        return self::MAP[$key]['active'];
    }

    public function dateExpr(string $key): ?string
    {
        return self::MAP[$key]['date'];
    }

    /** Kolom tanggal/status/ref untuk last-doc tracking per approval table. */
    public function docColumns(string $key): ?array
    {
        return match ($key) {
            'assignment_approve' => ['date' => 'createdon', 'status' => 'status', 'ref' => 'assignmentid'],
            'pengajuan_ijin_approve' => ['date' => 'createdon', 'status' => 'status', 'ref' => 'pengajuanijinid'],
            'pengajuan_resign_approve' => ['date' => 'RecDate', 'status' => 'Status', 'ref' => 'PengajuanId'],
            'w_fpk_approve' => ['date' => 'RecDate', 'status' => 'Status', 'ref' => 'FpkID'],
            'w_ppmj_approve' => ['date' => 'RecDate', 'status' => 'Status', 'ref' => 'PpmjID'],
            default => null,
        };
    }

    /** Kolom status untuk breakdown (GROUP BY), null = tidak didukung. Terverifikasi via SHOW COLUMNS. */
    public function statusColumn(string $key): ?string
    {
        return match ($key) {
            'assignment_approve' => 'status',
            'pengajuan_ijin_approve' => 'status',
            'pengajuan_resign_approve' => 'Status',
            'w_fpk_approve' => 'Status',
            'w_ppmj_approve' => 'Status',
            default => null,
        };
    }
    /** Kolom yang boleh di-sort (selain PK & kolom tanggal sederhana). */
    public function sortable(string $key): array
    {
        return match ($key) {
            'session' => ['start', 'userid'],
            'w_pegawai' => ['Nama', 'TglMasuk'],
            'w_pelamar' => ['Nama'],
            'w_fpk' => ['Tgl', 'No'],
            'w_sk' => ['Tanggal', 'No'],
            'w_kontrak' => ['AwalKontrak', 'No'],
            'w_memo_keluar' => ['Tanggal', 'No'],
            'w_sk_pengajuan' => ['Tanggal', 'No'],
            default => [],
        };
    }

    /** Kolom teks untuk pencarian (LIKE), null = tanpa search. Terverifikasi via SHOW COLUMNS. */
    public function searchable(string $key): ?string
    {
        return match ($key) {
            'session' => 'userid',
            'w_pegawai' => 'Nama',
            'w_pelamar' => 'Nama',
            'w_fpk', 'w_sk', 'w_sk_pengajuan', 'w_memo_keluar', 'w_kontrak',
            'w_surat_peringatan', 'w_surat_jamsostek', 'w_surat_referensi' => 'No',
            'pengajuan_ijin' => 'keterangan',
            'ss' => 'judul',
            default => null,
        };
    }
}
