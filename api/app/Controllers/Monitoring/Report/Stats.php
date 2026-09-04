<?php

namespace App\Controllers\Monitoring\Report;

use App\Controllers\BaseApi;
use App\Models\Monitoring\report\MonitoringRpt_model;

class Stats extends BaseApi
{
    public function get_stats()
    {
        try {
            $start = $this->request->getGet('start_date');
            $end = $this->request->getGet('end_date');
            $m = new MonitoringRpt_model();
            $c = fn(string $k) => $m->countTable($k, $start, $end);
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
            return $this->JSONResponse('OK', $result, 200);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->JSONResponse('Gagal memuat statistik monitoring', null, 500);
        }
    }
}
