<?php

namespace App\Controllers;

/**
 * ============================================================================
 * APPROVALS CONTROLLER
 * ============================================================================
 *
 * Description: Halaman pusat approval — menampilkan daftar item
 *   yang menunggu persetujuan dari user yang login.
 *
 * Responsibilities:
 * - Render halaman approval center
 * - Proxy data pending approval dari API (tickets & improvements)
 */
class Approvals extends BaseController
{
    private function guard(string $action = 'can_view'): bool
    {
        return has_permission('approvals', $action);
    }

    public function index(): string
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
        return $this->view('approvals/main_page', [
            'title' => 'Approval Center',
        ]);
    }

    public function ajaxList()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['data' => []]);
        }

        $type = $this->request->getGet('type');

        if ($type === 'tickets') {
            $result = $this->api->get_data('tickets/pending-approval');
        } else {
            $result = $this->api->get_data('improvements/pending-approvals');
        }

        $data = $result['data']['result'] ?? [];
        return $this->response->setJSON(['data' => $data]);
    }
}
