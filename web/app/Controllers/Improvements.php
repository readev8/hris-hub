<?php

namespace App\Controllers;

class Improvements extends BaseController
{
    public function index(): string
    {
        return $this->view('improvements/main_page', [
            'title' => 'Improvements',
        ]);
    }

    public function ajaxList()
    {
        $params = $this->request->getGet();
        $result = $this->api->get_data('improvements', $params);

        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON(['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0]);
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
        helper('form');

        if ($this->request->getMethod() === 'POST') {
            $post = $this->request->getPost();
            $result = $this->api->post_data('improvements/create', $post);

            if ($result && ($result['status'] ?? false)) {
                return $this->response->setJSON(['status' => true, 'redirect' => site_url('improvements')]);
            }
            return $this->response->setJSON([
                'status'  => false,
                'message' => $result['data']['message'] ?? 'Failed to create improvement',
            ]);
        }

        return $this->view('improvements/create', ['title' => 'Create Improvement']);
    }

    public function detail(string $encryptedId): string
    {
        $result = $this->api->get_data('improvements/' . $encryptedId);
        return $this->view('improvements/detail', [
            'title'       => 'Improvement Detail',
            'improvement' => $result['data']['result'] ?? null,
            'token'       => $encryptedId,
        ]);
    }

    public function approveIt(string $encryptedId)
    {
        $result = $this->api->post_data('improvements/' . $encryptedId . '/approve-it');
        return $this->response->setJSON($result);
    }

    public function approveDept(string $encryptedId)
    {
        $result = $this->api->post_data('improvements/' . $encryptedId . '/approve-dept');
        return $this->response->setJSON($result);
    }

    public function reject(string $encryptedId)
    {
        $data = $this->request->getPost();
        $result = $this->api->post_data('improvements/' . $encryptedId . '/reject', $data);
        return $this->response->setJSON($result);
    }

    public function resubmit(string $encryptedId)
    {
        $data = $this->request->getPost();
        $result = $this->api->post_data('improvements/' . $encryptedId . '/resubmit', $data);
        return $this->response->setJSON($result);
    }

    public function addComment(string $encryptedId)
    {
        $data = $this->request->getPost();
        $result = $this->api->post_data('improvements/' . $encryptedId . '/comments', $data);
        return $this->response->setJSON($result);
    }
}
