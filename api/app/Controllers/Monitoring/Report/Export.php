<?php

namespace App\Controllers\Monitoring\Report;

use App\Controllers\BaseApi;
use App\Models\Monitoring\check\MonitoringCheck_model;
use App\Models\Monitoring\report\MonitoringRpt_model;
use CodeIgniter\HTTP\ResponseInterface;

class Export extends BaseApi
{
    public function get_export(): ResponseInterface
    {
        try {
            $table = (string) $this->request->getGet('table');
            $check = new MonitoringCheck_model();
            if (!$check->isAllowedTable($table)) {
                return $this->JSONResponse('Tabel tidak dikenal', null, 400);
            }
            $m = new MonitoringRpt_model();
            $page = $m->getList($table, $this->request->getGet('start_date'), $this->request->getGet('end_date'), 100, 0);
            $rows = $page['items'];
            // Batch berikutnya hingga maks 5000 baris
            $all = $rows;
            $skip = 100;
            while (count($rows) === 100 && count($all) < 5000) {
                $page = $m->getList($table, $this->request->getGet('start_date'), $this->request->getGet('end_date'), 100, $skip);
                $rows = $page['items'];
                $all = array_merge($all, $rows);
                $skip += 100;
            }
            foreach ($all as &$r) {
                unset($r['Foto']);
            }
            unset($r);
            $filename = 'monitoring-' . $table . '-' . date('Ymd') . '.csv';
            $out = fopen('php://temp', 'r+');
            if (!empty($all)) {
                fputcsv($out, array_keys($all[0]));
                foreach ($all as $r) {
                    fputcsv($out, array_values(array_map(fn($v) => is_scalar($v) ? (string) $v : '', $r)));
                }
            }
            rewind($out);
            $csv = stream_get_contents($out);
            fclose($out);
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/csv; charset=utf-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($csv);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->JSONResponse('Gagal mengekspor data monitoring', null, 500);
        }
    }
}
