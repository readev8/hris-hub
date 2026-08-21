<?php

namespace App\Controllers;

/**
 * ============================================================================
 * ROLES CONTROLLER
 * ============================================================================
 *
 * Description: Manages roles and their per-module permission matrix.
 *
 * Responsibilities:
 * - List roles for the management page and AJAX DataTables
 * - Create, update, delete, and toggle role active status via API
 * - Display and save role permission assignments per module
 */

class Roles extends BaseController
{
    private function guard(string $action = 'can_view'): bool
    {
        return has_permission('roles', $action);
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
        if (!$this->guard('can_create')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();

        if (empty(trim($post['name'] ?? ''))) {
            return $this->response->setJSON(['status' => false, 'message' => 'Field name wajib diisi']);
        }

        $result = $this->api->post_data('roles/create', $post);
        return $this->response->setJSON($result);
    }

    public function update(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('roles/' . $encryptedId . '/update', $post);
        return $this->response->setJSON($result);
    }

    public function delete(string $encryptedId)
    {
        if (!$this->guard('can_delete')) {
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
        if (!$this->guard('can_update')) {
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
        if (!$this->guard('can_update')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $result = $this->api->post_data('roles/' . $encryptedId . '/toggle');
        return $this->response->setJSON($result);
    }
}
