<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function login()
    {
        helper('form');

        if ($this->request->getMethod() === 'POST') {
            $email    = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            if (empty($email) || empty($password)) {
                return $this->view('auth/login', [
                    'error' => 'Email dan password wajib diisi',
                ]);
            }

            $result = $this->api->post_data('auth/login', [
                'email'    => $email,
                'password' => $password,
            ]);

            if ($result === null || !($result['status'] ?? false)) {
                $msg = $result['data']['message'] ?? 'Gagal terhubung ke server';
                return $this->view('auth/login', ['error' => $msg]);
            }

            $user = $result['data']['result'];
            $session = service('session');
            $session->set('user', $user);
            $session->set('user_id', $user['id']);
            $session->set('role_id', $user['role_id'] ?? $user['role']);
            $session->set('permissions', $user['permissions'] ?? []);

            return redirect()->to('/dashboard');
        }

        return $this->view('auth/login');
    }

    public function logout()
    {
        service('session')->destroy();
        return redirect()->to('/login');
    }

    public function loginPage()
    {
        helper('form');
        return $this->view('auth/login');
    }

    public function refreshPermissions()
    {
        $session = service('session');
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['status' => false, 'message' => 'Not logged in']);
        }

        $result = $this->api->get_data('auth/me');

        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Failed to refresh']);
        }

        $user = $result['data']['result'];
        $session->set('permissions', $user['permissions'] ?? []);
        $session->set('role_id', $user['role_id'] ?? $user['role']);

        return $this->response->setJSON([
            'status'      => true,
            'message'     => 'Permissions refreshed',
            'permissions' => $user['permissions'] ?? [],
        ]);
    }
}
