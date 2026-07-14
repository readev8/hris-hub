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

        $roles = $this->api->get_data('roles');
        $rolesList = $roles['data']['result']['data'] ?? [];

        return $this->view('users/main_page', [
            'title' => 'Users',
            'roles' => $rolesList,
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

    public function addUserPage(): string
    {
        if (!$this->guard('can_create')) {
            return redirect()->to('/users');
        }

        $roles = $this->api->get_data('roles');
        $rolesList = $roles['data']['result']['data'] ?? [];

        return $this->view('users/add_user', [
            'title' => 'Add User',
            'roles' => $rolesList,
        ]);
    }

    public function ajaxLookupUser()
    {
        if (!$this->guard('can_create')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $result = $this->api->post_data('users/lookup', [
            'user_id' => $this->request->getPost('user_id'),
        ]);

        return $this->response->setJSON($result);
    }

    public function ajaxSearchHris()
    {
        if (!$this->guard('can_create')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $q = $this->request->getGet('q');
        $result = $this->api->get_data('users/search-hris', ['q' => $q]);
        return $this->response->setJSON($result);
    }

    public function ajaxAddByUserid()
    {
        if (!$this->guard('can_create')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $result = $this->api->post_data('users/add-by-userid', [
            'user_id'   => $this->request->getPost('user_id'),
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email'),
            'role_id'   => $this->request->getPost('role_id'),
        ]);

        return $this->response->setJSON($result);
    }

    public function ajaxDetail()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $encryptedId = $this->request->getGet('id');
        $result = $this->api->get_data('users/' . $encryptedId);
        return $this->response->setJSON($result);
    }

    public function ajaxUpdate()
    {
        if (!$this->guard('can_update')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $rawBody = $this->request->getBody();
        $data = json_decode($rawBody, true);
        if (!$data) {
            $data = [
                'id'        => $this->request->getPost('id'),
                'full_name' => $this->request->getPost('full_name'),
                'email'     => $this->request->getPost('email'),
                'role_id'   => $this->request->getPost('role_id'),
            ];
        }

        $encryptedId = $data['id'] ?? '';
        unset($data['id']);

        $result = $this->api->post_data('users/' . $encryptedId . '/update', $data);
        return $this->response->setJSON($result);
    }

    public function ajaxToggle()
    {
        if (!$this->guard('can_update')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $rawBody = $this->request->getBody();
        $data = json_decode($rawBody, true);
        $encryptedId = $data['id'] ?? $this->request->getPost('id');

        $result = $this->api->post_data('users/' . $encryptedId . '/toggle', []);
        return $this->response->setJSON($result);
    }

    public function ajaxDelete()
    {
        if (!$this->guard('can_delete')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $rawBody = $this->request->getBody();
        $data = json_decode($rawBody, true);
        $encryptedId = $data['id'] ?? $this->request->getPost('id');

        $result = $this->api->post_data('users/' . $encryptedId . '/delete', []);
        return $this->response->setJSON($result);
    }
}
