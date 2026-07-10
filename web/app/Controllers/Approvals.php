<?php

namespace App\Controllers;

class Approvals extends BaseController
{
    public function index(): string
    {
        return $this->view('approvals/main_page', [
            'title' => 'Approval Center',
        ]);
    }

    public function ajaxList()
    {
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
