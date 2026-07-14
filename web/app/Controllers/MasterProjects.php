<?php

namespace App\Controllers;

class MasterProjects extends BaseController
{
    private function guard(string $action = 'can_view'): bool
    {
        return has_permission('master_projects', $action);
    }

    public function index()
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
        return $this->view('master-projects/main_page', ['title' => 'Master Projects']);
    }

    public function ajaxList()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['data' => []]);
        }

        $result = $this->api->get_data('master-projects');

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

        if ($this->request->getMethod() === 'POST') {
            $post = $this->request->getPost();
            $result = $this->api->post_data('master-projects/create', $post);

            if ($result && ($result['status'] ?? false)) {
                return $this->response->setJSON([
                    'status'   => true,
                    'redirect' => site_url('master-projects'),
                    'id'       => $result['data']['result']['id'] ?? null,
                    'name'     => $result['data']['result']['name'] ?? null,
                ]);
            }

            return $this->response->setJSON([
                'status'  => false,
                'message' => $result['data']['message'] ?? 'Failed to create',
            ]);
        }

        return $this->view('master-projects/create', ['title' => 'Create Master Project']);
    }

    public function detail(string $encryptedId)
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }

        $result = $this->api->get_data('master-projects/' . $encryptedId);

        return $this->view('master-projects/detail', [
            'title'   => 'Master Project Detail',
            'project' => $result['data']['result'] ?? null,
        ]);
    }

    public function moduleDetail(string $encryptedProjectId, string $encryptedModuleId)
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }

        // Fetch project for breadcrumb and header
        $projectResult = $this->api->get_data('master-projects/' . $encryptedProjectId);
        $project = $projectResult['data']['result'] ?? null;

        if (!$project) {
            return redirect()->to('/master-projects');
        }

        // Fetch module detail from new API endpoint
        $moduleResult = $this->api->get_data('modules/' . $encryptedModuleId);
        $module = $moduleResult['data']['result'] ?? null;

        if (!$module) {
            return redirect()->to('/master-projects/' . $encryptedProjectId);
        }

        return $this->view('master-projects/module_detail', [
            'title'   => esc($module['name']),
            'project' => $project,
            'module'  => $module,
        ]);
    }

    public function update(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('master-projects/' . $encryptedId . '/update', $post);
        return $this->response->setJSON($result);
    }

    public function edit(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return redirect()->to('/dashboard');
        }

        if ($this->request->getMethod() === 'POST') {
            $post = $this->request->getPost();
            $result = $this->api->post_data('master-projects/' . $encryptedId . '/update', $post);

            if ($result && ($result['status'] ?? false)) {
                return $this->response->setJSON([
                    'status'   => true,
                    'redirect' => site_url('master-projects/' . $encryptedId),
                ]);
            }
            return $this->response->setJSON([
                'status'  => false,
                'message' => $result['data']['message'] ?? 'Failed to update',
            ]);
        }

        $result = $this->api->get_data('master-projects/' . $encryptedId);
        return $this->view('master-projects/edit', [
            'title'   => 'Edit Master Project',
            'project' => $result['data']['result'] ?? null,
        ]);
    }

    public function delete(string $encryptedId)
    {
        if (!$this->guard('can_delete')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $result = $this->api->post_data('master-projects/' . $encryptedId . '/delete');
        return $this->response->setJSON($result);
    }

    public function createModule(string $encryptedProjectId)
    {
        if (!$this->guard('can_create')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('master-projects/' . $encryptedProjectId . '/modules', $post);
        return $this->response->setJSON($result);
    }

    public function updateModule(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('modules/' . $encryptedId . '/update', $post);
        return $this->response->setJSON($result);
    }

    public function deleteModule(string $encryptedId)
    {
        if (!$this->guard('can_delete')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $result = $this->api->post_data('modules/' . $encryptedId . '/delete');
        return $this->response->setJSON($result);
    }

    public function createPage(string $encryptedModuleId)
    {
        if (!$this->guard('can_create')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('modules/' . $encryptedModuleId . '/pages', $post);
        return $this->response->setJSON($result);
    }

    public function updatePage(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('pages/' . $encryptedId . '/update', $post);
        return $this->response->setJSON($result);
    }

    public function deletePage(string $encryptedId)
    {
        if (!$this->guard('can_delete')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $result = $this->api->post_data('pages/' . $encryptedId . '/delete');
        return $this->response->setJSON($result);
    }

    // Cascading dropdown data
    public function getDetail(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(null);
        }

        $result = $this->api->get_data('master-projects/' . $encryptedId);
        return $this->response->setJSON($result['data']['result'] ?? null);
    }

    public function getActive()
    {
        $result = $this->api->get_data('master-projects/active');
        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'getActive failed: ' . json_encode($result));
            return $this->response->setStatusCode(500)->setJSON([]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function getModules(string $encryptedId)
    {
        $result = $this->api->get_data('master-projects/' . $encryptedId . '/modules');
        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'getModules failed: ' . json_encode($result));
            return $this->response->setStatusCode(500)->setJSON([]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function getPages(string $encryptedId)
    {
        $result = $this->api->get_data('modules/' . $encryptedId . '/pages');
        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'getPages failed: ' . json_encode($result));
            return $this->response->setStatusCode(500)->setJSON([]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function getBugList(string $encryptedId)
    {
        $result = $this->api->get_data('pages/' . $encryptedId . '/bugs');
        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'getBugList failed: ' . json_encode($result));
            return $this->response->setStatusCode(500)->setJSON([]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function getKanban(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['open' => [], 'in_progress' => [], 'resolved' => [], 'closed' => []]);
        }

        $result = $this->api->get_data('master-projects/' . $encryptedId . '/kanban');
        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'getKanban API failed: encryptedId=' . $encryptedId . ' result=' . json_encode($result));
            return $this->response->setStatusCode(500)->setJSON(['open' => [], 'in_progress' => [], 'resolved' => [], 'closed' => []]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }
}
