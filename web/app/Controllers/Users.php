<?php

namespace App\Controllers;

class Users extends BaseController
{
    private function guard(string $action = 'can_view'): bool
    {
        return has_permission('users', $action);
    }

    public function index(): string
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
        return $this->view('users/main_page', [
            'title' => 'Users',
        ]);
    }

    public function ajaxList()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['data' => []]);
        }
        $result = $this->api->get_data('users');
        $data = $result['data']['result'] ?? [];
        return $this->response->setJSON(['data' => $data]);
    }
}
