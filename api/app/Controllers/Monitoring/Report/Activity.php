<?php

namespace App\Controllers\Monitoring\Report;

use App\Controllers\BaseApi;
use App\Models\Monitoring\check\MonitoringCheck_model;
use App\Models\Monitoring\report\MonitoringRpt_model;

class Activity extends BaseApi
{
    private const TABLE_LABELS = [
        'session' => 'Sesi', 'assignment' => 'Assignment', 'assignment_approve' => 'Assignment Approve',
        'fpkt' => 'FPKT', 'fpkt_jobdesc' => 'FPKT Jobdesc', 'fpkt_pelatihan' => 'FPKT Pelatihan', 'fpkt_value' => 'FPKT Value',
        'ninebox_assessment' => 'Ninebox', 'ninebox_rtc' => 'Ninebox RTC', 'ppanelmt_nilai' => 'Nilai Panel',
        'pengajuan_ijin' => 'Ijin', 'pengajuan_ijin_approve' => 'Ijin Approve',
        'pengajuan_resign' => 'Resign', 'pengajuan_resign_approve' => 'Resign Approve',
        'ppanel' => 'Panel', 'ss' => 'SS', 'ss_approval' => 'SS Approval', 'jobcode' => 'Jobcode',
        'w_fpk' => 'FPK', 'w_fpk_approve' => 'FPK Approve', 'w_pelamar' => 'Pelamar', 'w_fpmj_approve' => 'FPMJ Approve',
        'w_ppmj_approve' => 'PPMJ Approve', 'w_penilaianpanel' => 'Penilaian Panel',
        'w_sk_pengajuan' => 'SK Pengajuan', 'w_sk' => 'SK', 'w_memo_keluar' => 'Memo Keluar',
        'w_surat_peringatan' => 'Surat Peringatan', 'w_surat_jamsostek' => 'Surat Jamsostek', 'w_surat_referensi' => 'Surat Referensi',
        'w_kontrak' => 'Kontrak', 'w_pegawai' => 'Pegawai',
    ];

    private const DOMAIN_KEYS = [
        'assignment' => ['assignment', 'assignment_approve'],
        'fpkt' => ['fpkt', 'fpkt_jobdesc', 'fpkt_pelatihan', 'fpkt_value'],
        'ninebox' => ['ninebox_assessment', 'ninebox_rtc', 'ppanelmt_nilai'],
        'ijin' => ['pengajuan_ijin', 'pengajuan_ijin_approve'],
        'resign' => ['pengajuan_resign', 'pengajuan_resign_approve'],
        'panel' => ['ppanel', 'ss', 'ss_approval', 'jobcode'],
        'rekrutmen' => ['w_fpk', 'w_fpk_approve', 'w_pelamar', 'w_fpmj_approve', 'w_ppmj_approve', 'w_penilaianpanel'],
        'sk' => ['w_sk_pengajuan', 'w_sk', 'w_memo_keluar'],
        'surat' => ['w_surat_peringatan', 'w_surat_jamsostek', 'w_surat_referensi', 'w_kontrak', 'w_pegawai'],
    ];

    public function get_activity()
    {
        try {
            $t0 = microtime(true);
            $limit = max(1, min((int) ($this->request->getGet('limit') ?? 100), 100));
            $domain = (string) ($this->request->getGet('domain') ?? '');
            $start = $this->request->getGet('start_date');
            $end = $this->request->getGet('end_date');
            $check = new MonitoringCheck_model();
            $m = new MonitoringRpt_model();
            $groups = $domain !== '' && isset(self::DOMAIN_KEYS[$domain]) ? [$domain => self::DOMAIN_KEYS[$domain]] : self::DOMAIN_KEYS;
            $bucket = [];
            $skipped = [];
            foreach ($groups as $dom => $keys) {
                foreach ($keys as $k) {
                    $dateExpr = $check->dateExpr($k);
                    if ($dateExpr === null || $dateExpr === 'bulantahun') {
                        $skipped[] = $k;
                        continue;
                    }
                    $dateCol = $this->dateColumn($k, $dateExpr);
                    $actorCol = $this->actorColumn($k);
                    foreach ($m->getList($k, $start, $end, 6, 0)['items'] as $r) {
                        $f = $this->fields($k, $r);
                        $actorRaw = $actorCol !== null ? ($r[$actorCol] ?? null) : null;
                        $bucket[] = [
                            'domain' => $dom,
                            'table' => $k,
                            'table_label' => self::TABLE_LABELS[$k] ?? $k,
                            'id' => (string) ($r['id'] ?? $r['Id'] ?? $r['ID'] ?? ''),
                            'date' => (string) ($r[$dateCol] ?? ''),
                            'title' => $f['title'],
                            'detail' => $f['detail'],
                            'actor_raw' => $actorRaw !== null ? (string) $actorRaw : null,
                        ];
                    }
                }
            }
            // Resolve actor names batch (wine_hris.pegawai)
            $actorIds = array_values(array_unique(array_filter(array_map(fn($b) => is_numeric($b['actor_raw'] ?? null) && (int) $b['actor_raw'] > 0 ? (int) $b['actor_raw'] : null, $bucket))));
            $names = [];
            if ($actorIds) {
                $rows = $this->db()->table('wine_hris.pegawai')->select('ID, Nama')->whereIn('ID', $actorIds)->get()->getResultArray();
                $names = array_column($rows, 'Nama', 'ID');
            }
            foreach ($bucket as &$b) {
                $raw = $b['actor_raw'] ?? null;
                unset($b['actor_raw']);
                if ($raw === null || $raw === '') {
                    $b['user'] = '-';
                } elseif (isset($names[(int) $raw])) {
                    $b['user'] = $names[(int) $raw] . ' #' . (int) $raw;
                } elseif (!is_numeric($raw)) {
                    $b['user'] = (string) $raw;
                } else {
                    $b['user'] = 'User #' . (int) $raw;
                }
            }
            unset($b);
            usort($bucket, fn($a, $b) => strcmp($b['date'], $a['date']));
            $items = array_values(array_filter(array_slice($bucket, 0, $limit), fn($a) => $a['date'] !== ''));
            log_message('debug', '[Monitoring][Activity] limit=' . $limit . ' items=' . count($items) . ' skipped=' . count($skipped) . ' ms=' . (int) ((microtime(true) - $t0) * 1000));
            return $this->JSONResponse('OK', ['items' => $items, 'skipped' => array_values(array_unique($skipped))], 200);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->JSONResponse('Gagal memuat aktivitas monitoring', null, 500);
        }
    }

    /** Kolom tanggal sederhana untuk membaca nilai per baris (COALESCE → kolom buatnya). */
    private function dateColumn(string $key, string $dateExpr): string
    {
        return match ($key) {
            'assignment', 'assignment_approve', 'ninebox_assessment', 'ninebox_rtc', 'jobcode', 'pengajuan_ijin_approve' => 'createdon',
            'fpkt_jobdesc' => 'TglCreate',
            default => $dateExpr,
        };
    }

    private function actorColumn(string $key): ?string
    {
        return match ($key) {
            'assignment' => 'pegawaiid',
            'assignment_approve' => 'userid',
            'fpkt' => 'UserId',
            'ninebox_assessment' => 'useridpenilai',
            'ninebox_rtc' => 'createdby',
            'ppanelmt_nilai' => 'userid',
            'pengajuan_ijin' => 'pegawaiid',
            'pengajuan_ijin_approve' => 'userid',
            'pengajuan_resign' => 'UserId',
            'pengajuan_resign_approve' => 'UserId',
            'ppanel' => 'PegawaiId',
            'ss' => 'pegawaiid',
            'ss_approval' => 'atasanid',
            'jobcode' => 'createdby',
            'w_fpk' => 'Peminta',
            'w_fpk_approve' => 'UserID',
            'w_fpmj_approve' => 'UserID',
            'w_ppmj_approve' => 'UserID',
            'w_penilaianpanel' => 'UserPenilai',
            'w_sk_pengajuan' => 'Peminta',
            'w_sk' => 'PegawaiID',
            'w_memo_keluar' => 'PegawaiID',
            'w_surat_peringatan' => 'PegawaiID',
            'w_kontrak' => 'PegawaiID',
            default => null,
        };
    }

    private function fields(string $key, array $row): array
    {
        $title = '';
        $detail = '';
        switch ($key) {
            case 'session':
                $title = (string) ($row['userid'] ?? '');
                $detail = (string) ($row['apps'] ?? '');
                break;
            case 'assignment':
                $title = (string) ($row['no'] ?? '');
                $detail = mb_substr((string) ($row['keterangan'] ?? ''), 0, 60);
                break;
            case 'assignment_approve':
                $title = '#' . ($row['assignmentid'] ?? '');
                $detail = mb_substr((string) ($row['keterangan'] ?? ''), 0, 60);
                break;
            case 'fpkt':
                $title = 'Pegawai ' . ($row['PegawaiId'] ?? '');
                $detail = '';
                break;
            case 'fpkt_jobdesc':
                $title = mb_substr((string) ($row['Description'] ?? ''), 0, 50);
                $detail = 'Tipe ' . ($row['Tipe'] ?? '');
                break;
            case 'ninebox_assessment':
                $title = 'Pegawai ' . ($row['pegawaiid'] ?? '');
                $detail = ($row['periode'] ?? '') . ' • ' . ($row['potensi'] ?? '') . '/' . ($row['performa'] ?? '') . ' • Box ' . ($row['box'] ?? '');
                break;
            case 'ninebox_rtc':
                $title = 'Pegawai ' . ($row['pegawaiid'] ?? '');
                $detail = 'Periode ' . ($row['periode'] ?? '');
                break;
            case 'ppanelmt_nilai':
                $title = 'User ' . ($row['userid'] ?? '');
                $detail = mb_substr((string) ($row['catatan'] ?? ''), 0, 50);
                break;
            case 'pengajuan_ijin':
                $title = 'Pegawai ' . ($row['pegawaiid'] ?? '');
                $detail = mb_substr((string) ($row['keterangan'] ?? ''), 0, 50) . ' • ' . substr((string) ($row['datestart'] ?? ''), 0, 10);
                break;
            case 'pengajuan_ijin_approve':
                $title = 'Ijin #' . ($row['pengajuanijinid'] ?? '');
                $detail = mb_substr((string) ($row['keterangan'] ?? ''), 0, 60);
                break;
            case 'pengajuan_resign':
                $title = 'Pegawai ' . ($row['PegawaiId'] ?? '');
                $detail = 'Resign ' . ($row['TglResign'] ?? '');
                break;
            case 'pengajuan_resign_approve':
                $title = 'Resign #' . ($row['PengajuanId'] ?? '');
                $detail = mb_substr((string) ($row['Keterangan'] ?? ''), 0, 60);
                break;
            case 'ppanel':
                $title = 'Pegawai ' . ($row['PegawaiId'] ?? '');
                $detail = 'Panel FPKT';
                break;
            case 'ss':
                $title = mb_substr((string) ($row['judul'] ?? ''), 0, 50);
                $detail = $row['noreg'] ?? '';
                break;
            case 'ss_approval':
                $title = 'SS #' . ($row['ssid'] ?? '');
                $detail = mb_substr((string) ($row['comment'] ?? ''), 0, 60);
                break;
            case 'jobcode':
                $title = mb_substr((string) ($row['title'] ?? ''), 0, 50);
                $detail = ($row['jobcode'] ?? '') . ' • Lvl ' . ($row['level'] ?? '');
                break;
            case 'w_fpk':
                $title = $row['No'] ?? '';
                $detail = mb_substr((string) ($row['Keterangan'] ?? ''), 0, 60);
                break;
            case 'w_fpk_approve':
                $title = 'FPK #' . ($row['FpkID'] ?? '');
                $detail = mb_substr((string) ($row['Keterangan'] ?? ''), 0, 60);
                break;
            case 'w_pelamar':
                $title = $row['Nama'] ?? '';
                $detail = 'Pelamar';
                break;
            case 'w_fpmj_approve':
                $title = 'FPMJ #' . ($row['FpmjID'] ?? '');
                $detail = mb_substr((string) ($row['Keterangan'] ?? ''), 0, 60);
                break;
            case 'w_ppmj_approve':
                $title = 'PPMJ #' . ($row['PpmjID'] ?? '');
                $detail = mb_substr((string) ($row['Keterangan'] ?? ''), 0, 60);
                break;
            case 'w_penilaianpanel':
                $title = 'Panel #' . ($row['FpmjID'] ?? '');
                $detail = 'Nilai ' . ($row['NilaiAkhir'] ?? '');
                break;
            case 'w_sk_pengajuan':
                $title = $row['No'] ?? '';
                $detail = 'SK Pengajuan';
                break;
            case 'w_sk':
                $title = $row['No'] ?? '';
                $detail = 'SK';
                break;
            case 'w_memo_keluar':
                $title = $row['No'] ?? '';
                $detail = 'Memo Keluar';
                break;
            case 'w_surat_peringatan':
                $title = $row['No'] ?? '';
                $detail = 'Jenis ' . ($row['Jenis'] ?? '');
                break;
            case 'w_surat_jamsostek':
            case 'w_surat_referensi':
                $title = $row['No'] ?? '';
                $detail = '';
                break;
            case 'w_kontrak':
                $title = $row['No'] ?? '';
                $detail = ($row['AwalKontrak'] ?? '') . ' s/d ' . ($row['AkhirKontrak'] ?? '');
                break;
            case 'w_pegawai':
                $title = $row['Nama'] ?? '';
                $detail = 'NIP ' . ($row['NIP'] ?? '');
                break;
            default:
                $title = 'ID ' . ($row['id'] ?? $row['Id'] ?? $row['ID'] ?? '');
                $detail = '';
                break;
        }
        return ['title' => mb_substr($title, 0, 80), 'detail' => mb_substr($detail, 0, 120)];
    }
}
