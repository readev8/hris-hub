<?php

namespace App\Controllers\Monitoring\Report;

use App\Controllers\BaseApi;
use App\Models\Monitoring\check\MonitoringCheck_model;
use App\Models\Monitoring\report\MonitoringRpt_model;

class Trend extends BaseApi
{
    public function get_trend()
    {
        try {
            $table = (string) $this->request->getGet('table');
            $check = new MonitoringCheck_model();
            if (!$check->isAllowedTable($table)) {
                return $this->JSONResponse('Tabel tidak dikenal', null, 400);
            }
            $dateExpr = $check->dateExpr($table);
            if ($dateExpr === null || $dateExpr === 'bulantahun') {
                return $this->JSONResponse('Tabel tidak mendukung tren harian', ['items' => []], 200);
            }
            $days = max(1, min((int) ($this->request->getGet('days') ?? 14), 90));
            $end = (string) ($this->request->getGet('end_date') ?? date('Y-m-d'));
            $start = (string) ($this->request->getGet('start_date') ?? date('Y-m-d', strtotime("-{$days} days", strtotime($end))));
            $m = new MonitoringRpt_model();
            $items = $m->countByDay($table, $start, $end);
            if (count($items) > $days + 1) {
                $items = array_slice($items, -($days + 1));
            }
            return $this->JSONResponse('OK', ['items' => $items], 200);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->JSONResponse('Gagal memuat tren monitoring', null, 500);
        }
    }
}
