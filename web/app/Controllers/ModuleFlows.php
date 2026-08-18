<?php

namespace App\Controllers;

/**
 * ============================================================================
 * MODULE FLOWS CONTROLLER
 * ============================================================================
 *
 * Description: Web proxy untuk visualisasi flow antar modul.
 *   Menyediakan halaman kanvas interaktif dan endpoint AJAX
 *   untuk CRUD node (modul), koneksi, dan posisi.
 *
 * Permissions required: module_flows (can_view, can_create, can_update, can_delete)
 */
class ModuleFlows extends BaseController
{
    private function guard(string $action = 'can_view'): bool
    {
        return has_permission('module_flows', $action);
    }

    public function index(): string
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
        return $this->view('module-flows/canvas', [
            'title' => 'Module Flows',
        ]);
    }

    public function ajaxGetCanvas()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }
        try {
            $result = $this->api->get_data('module-flows/canvas');
            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            log_message('error', 'ModuleFlows ajaxGetCanvas: ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memuat data']);
        }
    }

    public function ajaxAddModule()
    {
        if (!$this->guard('can_create')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }
        try {
            $post = $this->request->getPost();
            $result = $this->api->post_data('module-flows/canvas/modules', $post);
            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            log_message('error', 'ModuleFlows ajaxAddModule: ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal menambahkan modul']);
        }
    }

    public function ajaxUpdatePosition(string $id)
    {
        if (!$this->guard('can_update')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }
        try {
            $post = $this->request->getPost();
            $result = $this->api->post_data('module-flows/canvas/modules/' . $id . '/position', $post);
            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            log_message('error', 'ModuleFlows ajaxUpdatePosition: ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memperbarui posisi']);
        }
    }

    public function ajaxRemoveModule(string $id)
    {
        if (!$this->guard('can_delete')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }
        try {
            $result = $this->api->post_data('module-flows/canvas/modules/' . $id . '/remove');
            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            log_message('error', 'ModuleFlows ajaxRemoveModule: ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus modul']);
        }
    }

    public function ajaxAddConnection()
    {
        if (!$this->guard('can_create')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }
        try {
            $post = $this->request->getPost();
            $result = $this->api->post_data('module-flows/connections', $post);
            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            log_message('error', 'ModuleFlows ajaxAddConnection: ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal menambahkan koneksi']);
        }
    }

    public function ajaxDeleteConnection(string $id)
    {
        if (!$this->guard('can_delete')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }
        try {
            $result = $this->api->post_data('module-flows/connections/' . $id . '/delete');
            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            log_message('error', 'ModuleFlows ajaxDeleteConnection: ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus koneksi']);
        }
    }
}
