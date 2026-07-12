<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');

        $statsResult = $this->api->get_data('dashboard/stats', [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);
        $logsResult = $this->api->get_data('audit-log/recent', [
            'limit'      => 10,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        if (!$statsResult || !($statsResult['status'] ?? false)) {
            log_message('error', 'Dashboard stats API failed: ' . json_encode($statsResult));
        }
        if (!$logsResult || !($logsResult['status'] ?? false)) {
            log_message('error', 'Dashboard audit-log API failed: ' . json_encode($logsResult));
        }

        $stats = $statsResult['data']['result'] ?? [];
        $logs  = $logsResult['data']['result'] ?? [];

        return $this->view('dashboard/main_page', [
            'title'      => 'Dashboard',
            'stats'      => $stats,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'logs'       => $logs,
        ]);
    }
}
