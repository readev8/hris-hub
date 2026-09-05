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
            $sortRaw = $this->request->getGet('sort');
            $sort = (is_string($sortRaw) && $sortRaw !== '') ? $sortRaw : null;
            $dir = strtoupper((string) ($this->request->getGet('dir') ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
            $qRaw = $this->request->getGet('q');
            $q = is_string($qRaw) && $qRaw !== '' ? $this->cleanInput($qRaw) : null;
            $m = new MonitoringRpt_model();
            $data = $m->getList($table, $this->request->getGet('start_date'), $this->request->getGet('end_date'), $take, $skip, $sort, $dir, is_string($q) ? $q : null);
            return $this->JSONResponse('OK', ['items' => $data['items'], 'pagination' => ['take' => $take, 'skip' => $skip, 'total' => $data['total']]], 200);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->JSONResponse('Gagal memuat daftar monitoring', null, 500);
        }
    }
}
