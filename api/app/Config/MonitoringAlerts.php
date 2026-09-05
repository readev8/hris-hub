<?php

namespace Config;

class MonitoringAlerts
{
    /**
     * Aturan threshold alert monitoring.
     * Tipe: 'count_gt' (jumlah > ambang dalam window hari) atau
     *        'drop_pct' (penurunan % vs window sebelumnya).
     *
     * @var array<int, array{table:string,type:string,threshold:float,window_days:int,label:string,level:string}>
     */
    public array $rules = [
        ['table' => 'pengajuan_resign', 'type' => 'count_gt', 'threshold' => 5, 'window_days' => 7, 'label' => 'Pengajuan resign melebihi ambang mingguan', 'level' => 'warning'],
        ['table' => 'pengajuan_ijin', 'type' => 'count_gt', 'threshold' => 50, 'window_days' => 7, 'label' => 'Pengajuan ijin melebihi ambang mingguan', 'level' => 'warning'],
        ['table' => 'session', 'type' => 'drop_pct', 'threshold' => 50, 'window_days' => 7, 'label' => 'Sesi turun drastis dibanding minggu lalu', 'level' => 'critical'],
    ];
}
