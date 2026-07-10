<?php

namespace App\Controllers;

class Users extends BaseController
{
    public function index(): string
    {
        return $this->view('users/main_page', [
            'title' => 'Users',
        ]);
    }

    public function ajaxList()
    {
        $result = $this->api->get_data('users');
        $data = $result['data']['result'] ?? [];
        return $this->response->setJSON(['data' => $data]);
    }
}
