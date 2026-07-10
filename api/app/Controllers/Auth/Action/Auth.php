<?php

namespace App\Controllers\Auth\Action;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseApi
{
    protected array $model_map = [];

    public function login(): ResponseInterface
    {
        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->JSONResponse('Email dan password wajib diisi', null, 400);
        }

        $user = $this->db()->table('users')
            ->where('email', $email)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->JSONResponse('Email atau password salah', null, 401);
        }

        unset($user['password']);
        $user['token'] = $this->api->encryptId($user['id']);
        $user['role_name'] = \App\Config\Enums::roleName($user['role']);

        return $this->JSONResponse('Login berhasil', $user, 200);
    }

    public function me(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        $user = $this->db()->table('users')
            ->where('id', $userId)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        if (!$user) {
            return $this->JSONResponse('User not found', null, 404);
        }

        unset($user['password']);
        $user['token'] = $this->api->encryptId($user['id']);
        $user['role_name'] = \App\Config\Enums::roleName($user['role']);

        return $this->JSONResponse('OK', $user, 200);
    }
}
