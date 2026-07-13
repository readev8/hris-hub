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
        $user['role_id'] = $user['role_id'] ?? $user['role'];
        $user['role_name'] = \App\Config\Enums::roleName((int) $user['role_id']);

        // Load permissions for session (graceful if table doesn't exist yet)
        $permissions = [];
        try {
            if ($this->db()->tableExists('role_permissions')) {
                $permissions = $this->db()->table('role_permissions')
                    ->where('role_id', $user['role_id'])
                    ->get()
                    ->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('error', 'Permissions load failed: ' . $e->getMessage());
        }

        $permMap = [];
        foreach ($permissions as $p) {
            $permMap[$p['module_slug']] = [
                'can_view'    => (int) $p['can_view'],
                'can_create'  => (int) $p['can_create'],
                'can_update'  => (int) $p['can_update'],
                'can_delete'  => (int) $p['can_delete'],
                'can_approve' => (int) $p['can_approve'],
            ];
        }
        $user['permissions'] = $permMap;

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
        $user['role_id'] = $user['role_id'] ?? $user['role'];
        $user['role_name'] = \App\Config\Enums::roleName((int) $user['role_id']);

        $permModel = new \App\Models\Roles\PermissionCheck_model();
        $user['permissions'] = $permModel->getUserPermissions($userId);

        return $this->JSONResponse('OK', $user, 200);
    }
}
