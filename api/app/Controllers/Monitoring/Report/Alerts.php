<?php

namespace App\Controllers\Monitoring\Report;

use App\Controllers\BaseApi;
use App\Models\Monitoring\report\MonitoringRpt_model;
use Config\MonitoringAlerts;

class Alerts extends BaseApi
{
    public function get_alerts()
    {
        try {
            $t0 = microtime(true);
            $cfg = new MonitoringAlerts();
            $m = new MonitoringRpt_model();
            $today = date('Y-m-d');
            $alerts = [];
            foreach ($cfg->rules as $rule) {
                $start = date('Y-m-d', strtotime('-' . (int) $rule['window_days'] . ' days', strtotime($today)));
                if ($rule['type'] === 'count_gt') {
                    $n = $m->countTable($rule['table'], $start, $today);
                    if ($n > (float) $rule['threshold']) {
                        $alerts[] = ['table' => $rule['table'], 'level' => $rule['level'], 'message' => $rule['label'] . " ({$n} dalam {$rule['window_days']} hari)", 'count' => $n];
                    }
                } elseif ($rule['type'] === 'drop_pct') {
                    $days = (int) $rule['window_days'];
                    $prevEnd = date('Y-m-d', strtotime('-' . ($days + 1) . ' days', strtotime($today)));
                    $prevStart = date('Y-m-d', strtotime('-' . ($days * 2 + 1) . ' days', strtotime($today)));
                    $cur = $m->countTable($rule['table'], $start, $today);
                    $prev = $m->countTable($rule['table'], $prevStart, $prevEnd);
                    if ($prev > 0 && (($prev - $cur) / $prev * 100) >= (float) $rule['threshold']) {
                        $alerts[] = ['table' => $rule['table'], 'level' => $rule['level'], 'message' => $rule['label'] . " ({$cur} vs {$prev})", 'count' => $cur];
                    }
                }
            }
            log_message('debug', '[Monitoring][Alerts] rules=' . count($cfg->rules) . ' active=' . count($alerts) . ' ms=' . (int) ((microtime(true) - $t0) * 1000));
            return $this->JSONResponse('OK', ['items' => $alerts, 'total' => count($alerts)], 200);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->JSONResponse('Gagal memuat alert monitoring', null, 500);
        }
    }
}
