<?php

namespace App\Controllers\Monitoring\Data;

use App\Controllers\BaseApi;
use App\Models\Monitoring\check\MonitoringCheck_model;

class Detail extends BaseApi
{
    public function get_detail()
    {
        try {
            $t0 = microtime(true);
            $table = (string) $this->request->getGet('table');
            $id = (string) $this->request->getGet('id');
            $check = new MonitoringCheck_model();
            if (!$check->isAllowedTable($table) || $id === '') {
                return $this->JSONResponse('Parameter tidak valid', null, 400);
            }
            $pk = 'id';
            if (in_array($table, ['fpkt', 'fpkt_jobdesc', 'fpkt_value'], true)) {
                $pk = 'Id';
            } elseif (str_starts_with($table, 'w_')) {
                $pk = 'ID';
            }
            $db = \Config\Database::connect();
            $b = $db->table($check->tableName($table))->where($pk, $id);
            if ($check->hasActive($table)) {
                $b->where('active', 0);
            }
            $row = $b->get(1)->getRowArray();
            if (!$row) {
                return $this->JSONResponse('Data tidak ditemukan', null, 404);
            }
            unset($row['Foto']);
            log_message('debug', '[Monitoring][Detail] table=' . $table . ' found=y ms=' . (int) ((microtime(true) - $t0) * 1000));
            return $this->JSONResponse('OK', $row, 200);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->JSONResponse('Gagal memuat detail monitoring', null, 500);
        }
    }
}
