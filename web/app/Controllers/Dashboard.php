<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');

        $stats = $this->fetchStats($startDate, $endDate);
        $logs  = $this->fetchLogs($startDate, $endDate, 10);

        $user     = session('user') ?? [];
        $roleId   = (int) (session('role_id') ?? ($user['role_id'] ?? 0));
        $roleName = $user['role_name'] ?? \App\Config\Enums::roleName($roleId);

        return $this->view('dashboard/main_page', [
            'title'        => 'Dashboard',
            'stats'        => $stats,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'logs'         => $logs,
            'role_id'      => $roleId,
            'role_name'    => $roleName,
            'is_approver'  => $this->isApprover($roleId),
            'is_manager'   => in_array($roleId, [\App\Config\Enums::IT_MANAGER, \App\Config\Enums::DEPT_HEAD, \App\Config\Enums::ADMIN], true),
            'is_admin'     => $roleId === \App\Config\Enums::ADMIN,
        ]);
    }

    public function ajaxStats()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');

        $stats = $this->fetchStats($startDate, $endDate);
        $logs  = $this->fetchLogs($startDate, $endDate, 10);

        return $this->response->setJSON([
            'status' => true,
            'stats'  => $stats,
            'logs'   => $logs,
        ]);
    }

    private function fetchStats(string $startDate, string $endDate): array
    {
        $result = $this->api->get_data('dashboard/stats', [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Dashboard stats API failed: ' . json_encode($result));
            return [];
        }

        return $result['data']['result'] ?? [];
    }

    private function fetchLogs(string $startDate, string $endDate, int $limit): array
    {
        $result = $this->api->get_data('audit-log/recent', [
            'limit'      => $limit,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Dashboard audit-log API failed: ' . json_encode($result));
            return [];
        }

        return $result['data']['result'] ?? [];
    }

    private function isApprover(int $roleId): bool
    {
        return in_array($roleId, [\App\Config\Enums::IT_MANAGER, \App\Config\Enums::DEPT_HEAD, \App\Config\Enums::ADMIN], true);
    }

    private function guard(): bool
    {
        return session('user_id') !== null;
    }
}
