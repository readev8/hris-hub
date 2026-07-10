<?php

namespace App\Controllers;

class MasterProjects extends BaseController
{
    private function guard(): bool
    {
        $role = (int) session('role');
        if (!in_array($role, [1, 5], true)) {
            return false;
        }
        return true;
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
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }

        if ($this->request->getMethod() === 'POST') {
            $post = $this->request->getPost();
            $result = $this->api->post_data('master-projects/create', $post);

            if ($result && ($result['status'] ?? false)) {
                return $this->response->setJSON(['status' => true, 'redirect' => site_url('master-projects')]);
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

    public function update(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('master-projects/' . $encryptedId . '/update', $post);
        return $this->response->setJSON($result);
    }

    public function delete(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $result = $this->api->post_data('master-projects/' . $encryptedId . '/delete');
        return $this->response->setJSON($result);
    }

    public function createModule(string $encryptedProjectId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('master-projects/' . $encryptedProjectId . '/modules', $post);
        return $this->response->setJSON($result);
    }

    public function updateModule(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('modules/' . $encryptedId . '/update', $post);
        return $this->response->setJSON($result);
    }

    public function deleteModule(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $result = $this->api->post_data('modules/' . $encryptedId . '/delete');
        return $this->response->setJSON($result);
    }

    public function createPage(string $encryptedModuleId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('modules/' . $encryptedModuleId . '/pages', $post);
        return $this->response->setJSON($result);
    }

    public function updatePage(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $post = $this->request->getPost();
        $result = $this->api->post_data('pages/' . $encryptedId . '/update', $post);
        return $this->response->setJSON($result);
    }

    public function deletePage(string $encryptedId)
    {
        if (!$this->guard()) {
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
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function getModules(string $encryptedId)
    {
        $result = $this->api->get_data('master-projects/' . $encryptedId . '/modules');
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function getPages(string $encryptedId)
    {
        $result = $this->api->get_data('modules/' . $encryptedId . '/pages');
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function getBugList(string $encryptedId)
    {
        $result = $this->api->get_data('pages/' . $encryptedId . '/bugs');
        return $this->response->setJSON($result['data']['result'] ?? []);
    }
}
