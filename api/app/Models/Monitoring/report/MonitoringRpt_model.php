<?php

namespace App\Models\Monitoring\report;

use App\Models\Monitoring\check\MonitoringCheck_model;

class MonitoringRpt_model
{
    protected \CodeIgniter\Database\BaseConnection $db;
    protected MonitoringCheck_model $check;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->check = new MonitoringCheck_model();
    }

    private function filtered(string $key, ?string $start, ?string $end): \CodeIgniter\Database\BaseBuilder
    {
        $b = $this->db->table($this->check->tableName($key));
        if ($this->check->hasActive($key)) {
            $b->where('active', 0);
        }
        $d = $this->check->dateExpr($key);
        if ($d === 'bulantahun') {
            if ($start) {
                $b->where('Tahun >=', (int) substr($start, 0, 4));
            }
            if ($end) {
                $b->where('Tahun <=', (int) substr($end, 0, 4));
            }
        } else {
            if ($d && $start) {
                $b->where($d . ' >=', $start . ' 00:00:00');
            }
            if ($d && $end) {
                $b->where($d . ' <=', $end . ' 23:59:59');
            }
        }
        return $b;
    }

    public function countTable(string $key, ?string $start = null, ?string $end = null): int
    {
        return (int) $this->filtered($key, $start, $end)->countAllResults();
    }

    public function getList(string $key, ?string $start, ?string $end, int $take = 20, int $skip = 0): array
    {
        $take = max(1, min($take, 100));
        $skip = max(0, $skip);
        return $this->filtered($key, $start, $end)
            ->orderBy($this->orderCol($key), 'DESC')
            ->get($take, $skip)
            ->getResultArray();
    }

    private function orderCol(string $key): string
    {
        $d = $this->check->dateExpr($key);
        if ($d === null || $d === 'bulantahun') {
            return $this->pk($key);
        }
        return $d;
    }

    private function pk(string $key): string
    {
        if (in_array($key, ['fpkt', 'fpkt_jobdesc', 'fpkt_value'], true)) {
            return 'Id';
        }
        if (str_starts_with($key, 'w_')) {
            return 'ID';
        }
        return 'id';
    }
}
