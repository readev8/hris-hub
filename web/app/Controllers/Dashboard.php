<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $stats = $this->api->get_data('dashboard/stats')['data']['result'] ?? [];
        $logs  = $this->api->get_data('audit-log/recent', ['limit' => 10])['data']['result'] ?? [];

        return $this->view('dashboard/main_page', [
            'title'  => 'Dashboard',
            'stats'  => $stats,
            'logs'   => $logs,
        ]);
    }
}
