<?php

namespace App\Controllers;

class Roles extends BaseController
{
    private function guard(): bool
    {
        return has_permission('roles', 'can_view');
    }

    public function index()
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
        return $this->view('roles/main_page', ['title' => 'Roles & Permissions']);
    }

    public function ajaxList()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['data' => []]);
        }

        $result = $this->api->get_data('roles');
        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON(['data' => []]);
        }

        $data = $result['data']['result'];
        return $this->response->setJSON([
            'data'            => $data['data'] ?? [],
            'recordsTotal'    => $data['total'] ?? 0,
            'recordsFiltered' => $data['total'] ?? 0,
        ]);
    }

    public function create()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('roles/create', $post);
        return $this->response->setJSON($result);
    }

    public function update(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('roles/' . $encryptedId . '/update', $post);
        return $this->response->setJSON($result);
    }

    public function delete(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $result = $this->api->post_data('roles/' . $encryptedId . '/delete');
        return $this->response->setJSON($result);
    }

    public function permissions(string $encryptedId)
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }

        $result = $this->api->get_data('roles/' . $encryptedId);
        $role = $result['data']['result'] ?? null;

        $modulesResult = $this->api->get_data('roles/modules/list');
        $modules = $modulesResult['data']['result'] ?? [];

        return $this->view('roles/permissions', [
            'title'    => 'Manage Permissions',
            'role'     => $role,
            'modules'  => $modules,
        ]);
    }

    public function savePermissions(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $permissions = json_decode($post['permissions'] ?? '[]', true);

        $result = $this->api->post_data('roles/' . $encryptedId . '/permissions', [
            'permissions' => $permissions,
        ]);
        return $this->response->setJSON($result);
    }

    public function toggleActive(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $result = $this->api->post_data('roles/' . $encryptedId . '/toggle');
        return $this->response->setJSON($result);
    }
}
