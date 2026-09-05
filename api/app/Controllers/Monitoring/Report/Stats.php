<?php

namespace App\Controllers\Monitoring\Report;

use App\Controllers\BaseApi;
use App\Models\Monitoring\report\MonitoringRpt_model;

class Stats extends BaseApi
{
    public function get_stats()
    {
        try {
            $t0 = microtime(true);
            $start = $this->request->getGet('start_date');
            $end = $this->request->getGet('end_date');
            log_message('debug', '[Monitoring][Stats] start=' . ($start ?? '-') . ' end=' . ($end ?? '-'));
            $m = new MonitoringRpt_model();
            $c = fn(string $k) => $m->countTable($k, $start, $end);
            $domainKeys = [
                'sessions' => ['session'], 'assignment' => ['assignment', 'assignment_approve'],
                'fpkt' => ['fpkt', 'fpkt_jobdesc', 'fpkt_pelatihan', 'fpkt_value'],
                'ninebox' => ['ninebox_assessment', 'ninebox_rtc', 'ppanelmt_nilai'],
                'ijin' => ['pengajuan_ijin', 'pengajuan_ijin_approve'],
                'resign' => ['pengajuan_resign', 'pengajuan_resign_approve'],
                'panel_ss' => ['ppanel', 'ss', 'ss_approval', 'jobcode'],
                'rekrutmen' => ['w_fpk', 'w_fpk_approve', 'w_pelamar', 'w_fpmj_approve', 'w_ppmj_approve', 'w_penilaianpanel'],
                'sk' => ['w_sk_pengajuan', 'w_sk', 'w_memo_keluar'],
                'surat' => ['w_surat_peringatan', 'w_surat_jamsostek', 'w_surat_referensi', 'w_kontrak', 'w_pegawai'],
            ];
            [$prevStart, $prevEnd] = $this->previousRange($start, $end);
            $previous = [];
            foreach ($domainKeys as $domain => $keys) {
                $total = 0;
                foreach ($keys as $k) {
                    $total += $m->countTable($k, $prevStart, $prevEnd);
                }
                $previous[$domain] = $total;
            }
            $result = [
                'sessions'   => ['total' => $c('session')],
                'assignment' => ['assignment' => $c('assignment'), 'approve' => $c('assignment_approve')],
                'fpkt'       => ['fpkt' => $c('fpkt'), 'jobdesc' => $c('fpkt_jobdesc'), 'pelatihan' => $c('fpkt_pelatihan'), 'value' => $c('fpkt_value')],
                'ninebox'    => ['assessment' => $c('ninebox_assessment'), 'rtc' => $c('ninebox_rtc'), 'nilai' => $c('ppanelmt_nilai')],
                'ijin'       => ['pengajuan' => $c('pengajuan_ijin'), 'approve' => $c('pengajuan_ijin_approve')],
                'resign'     => ['pengajuan' => $c('pengajuan_resign'), 'approve' => $c('pengajuan_resign_approve')],
                'panel_ss'   => ['ppanel' => $c('ppanel'), 'ss' => $c('ss'), 'ss_approval' => $c('ss_approval'), 'jobcode' => $c('jobcode')],
                'rekrutmen'  => ['fpk' => $c('w_fpk'), 'fpk_approve' => $c('w_fpk_approve'), 'pelamar' => $c('w_pelamar'), 'fpmj_approve' => $c('w_fpmj_approve'), 'ppmj_approve' => $c('w_ppmj_approve'), 'panel' => $c('w_penilaianpanel')],
                'sk'         => ['pengajuan' => $c('w_sk_pengajuan'), 'sk' => $c('w_sk'), 'memo' => $c('w_memo_keluar')],
                'surat'      => ['peringatan' => $c('w_surat_peringatan'), 'jamsostek' => $c('w_surat_jamsostek'), 'referensi' => $c('w_surat_referensi'), 'kontrak' => $c('w_kontrak'), 'pegawai' => $c('w_pegawai')],
            ];
            $result['by_status'] = [
                'assignment_approve' => $m->statusBreakdown('assignment_approve', $start, $end),
                'pengajuan_ijin_approve' => $m->statusBreakdown('pengajuan_ijin_approve', $start, $end),
                'pengajuan_resign_approve' => $m->statusBreakdown('pengajuan_resign_approve', $start, $end),
                'w_fpk_approve' => $m->statusBreakdown('w_fpk_approve', $start, $end),
                'w_ppmj_approve' => $m->statusBreakdown('w_ppmj_approve', $start, $end),
            ];
            $result['previous'] = $previous;
            log_message('debug', '[Monitoring][Stats] done tables=32 ms=' . (int) ((microtime(true) - $t0) * 1000));
            return $this->JSONResponse('OK', $result, 200);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->JSONResponse('Gagal memuat statistik monitoring', null, 500);
        }
    }

    /** Geser rentang mundur sepanjang durasinya untuk perbandingan periode lalu. */
    private function previousRange(?string $start, ?string $end): array
    {
        if (!$start || !$end) {
            return [null, null];
        }
        try {
            $s = new \DateTime($start);
            $e = new \DateTime($end);
            $len = (int) $s->diff($e)->days + 1;
            $ps = (clone $s)->modify("-{$len} days")->format('Y-m-d');
            $pe = (clone $s)->modify('-1 day')->format('Y-m-d');
            return [$ps, $pe];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}
