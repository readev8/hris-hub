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

        if (!$user) {
            return $this->JSONResponse('Email atau password salah', null, 401);
        }

        // Auth is now external (myhr/plus) — this endpoint kept for backward compatibility
        // Password check removed; primary login is via web Auth controller -> myhr/plus API

        $user['token'] = $this->api->encryptId($user['id']);
        $user['role_id'] = $user['role_id'];
        $user['role_name'] = \App\Config\Enums::roleName((int) $user['role_id']);

        // Load permissions for session (graceful if table doesn't exist yet)
        $permissions = [];
        try {
            if ($this->db()->tableExists('role_permissions')) {
                $permissions = $this->db()->table('role_permissions')
                    ->where('role_id', $user['role_id'])
                    ->where('active', 0)
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

    /**
     * Find or create local user by myhr/plus user_id.
     * Called by web Auth controller during login flow.
     * Requires API key auth (X-API-Key) but NOT user auth (X-User-Id).
     */
    public function local_user(): ResponseInterface
    {
        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $myhrUserId = trim($input['user_id'] ?? '');
        $fullName   = trim($input['full_name'] ?? '');
        $email      = trim($input['email'] ?? '');

        if (empty($myhrUserId)) {
            return $this->JSONResponse('user_id wajib diisi', null, 400);
        }

        $db = $this->db();

        // Find existing user
        $user = $db->table('users')
            ->where('user_id', $myhrUserId)
            ->get()
            ->getRowArray();

        if ($user) {
            // User exists — check if active
            if (!$user['is_active']) {
                return $this->JSONResponse('Akun belum aktif. Hubungi administrator.', null, 403);
            }

            // Update name/email from myhr/plus
            $updateData = ['updated_at' => date('Y-m-d H:i:s')];
            if (!empty($fullName)) $updateData['full_name'] = $fullName;
            if (!empty($email))    $updateData['email']      = $email;
            $db->table('users')->where('id', $user['id'])->update($updateData);
            $user['full_name'] = $fullName ?: $user['full_name'];
            $user['email']     = $email    ?: $user['email'];

            return $this->JSONResponse('User ditemukan', $user, 200);
        }

        // Create new user with default role (Requester = 2)
        $now = date('Y-m-d H:i:s');
        $db->table('users')->insert([
            'user_id'    => $myhrUserId,
            'full_name'  => $fullName ?: 'User ' . $myhrUserId,
            'email'      => $email    ?: ($myhrUserId . '@external.local'),
            'role_id'    => 2, // Default: Requester
            'is_active'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $userId = $db->insertID();

        // Reload user
        $user = $db->table('users')->where('id', $userId)->get()->getRowArray();

        return $this->JSONResponse('User baru dibuat', $user, 201);
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

        $user['token'] = $this->api->encryptId($user['id']);
        $user['role_id'] = $user['role_id'];
        $user['role_name'] = \App\Config\Enums::roleName((int) $user['role_id']);

        $permModel = new \App\Models\Roles\PermissionCheck_model();
        $user['permissions'] = $permModel->getUserPermissions($userId);

        return $this->JSONResponse('OK', $user, 200);
    }
}
