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
            $session->set('role', $user['role']);

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
}
