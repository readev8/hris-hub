<?php

namespace App\Controllers\Monitoring\Report;

use App\Controllers\BaseApi;
use App\Models\Monitoring\check\MonitoringCheck_model;
use App\Models\Monitoring\report\MonitoringRpt_model;

class ListReport extends BaseApi
{
    public function get_list()
    {
        try {
            $table = (string) $this->request->getGet('table');
            $check = new MonitoringCheck_model();
            if (!$check->isAllowedTable($table)) {
                return $this->JSONResponse('Tabel tidak dikenal', null, 400);
            }
            $take = max(1, min((int) ($this->request->getGet('take') ?? 20), 100));
            $skip = max(0, (int) ($this->request->getGet('skip') ?? 0));
            $m = new MonitoringRpt_model();
            $items = $m->getList($table, $this->request->getGet('start_date'), $this->request->getGet('end_date'), $take, $skip);
            return $this->JSONResponse('OK', ['items' => $items, 'pagination' => ['take' => $take, 'skip' => $skip]], 200);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->JSONResponse('Gagal memuat daftar monitoring', null, 500);
        }
    }
}
