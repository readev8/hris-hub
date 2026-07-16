<?php

namespace App\Controllers\Roles\Report;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class RoleList extends BaseApi
{
    public function get_list(): ResponseInterface
    {
        $roles = $this->db()->table('roles')
            ->where('active', 0)
            ->orderBy('is_active', 'DESC')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($roles as $r) {
            $userCount = $this->db()->table('users')->where('role_id', $r['id'])->countAllResults();
            $result[] = [
                'raw_id'      => (int) $r['id'],
                'id'          => $this->api->encryptId($r['id']),
                'name'        => $r['name'],
                'slug'        => $r['slug'],
                'description' => $r['description'],
                'is_system'   => (int) $r['is_system'],
                'is_active'   => (int) $r['is_active'],
                'user_count'  => $userCount,
                'created_at'  => $r['created_at'],
            ];
        }

        return $this->JSONResponse('OK', ['data' => $result, 'total' => count($result)], 200);
    }

    public function get_detail(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $role = $this->db()->table('roles')->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$role) return $this->JSONResponse('Role tidak ditemukan', null, 404);

        $permissions = $this->db()->table('role_permissions')
            ->where('role_id', $id)
            ->where('active', 0)
            ->get()
            ->getResultArray();

        return $this->JSONResponse('OK', [
            'id'          => $this->api->encryptId($role['id']),
            'name'        => $role['name'],
            'slug'        => $role['slug'],
            'description' => $role['description'],
            'is_system'   => (int) $role['is_system'],
            'is_active'   => (int) $role['is_active'],
            'permissions' => $permissions,
        ], 200);
    }

    public function get_modules(): ResponseInterface
    {
        $modules = $this->db()->table('access_modules')
            ->where('active', 0)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        return $this->JSONResponse('OK', $modules, 200);
    }

    public function get_user_permissions(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $user = $this->db()->table('users')->where('id', $userId)->get()->getRowArray();
        if (!$user) return $this->JSONResponse('User tidak ditemukan', null, 404);

        $roleId = $user['role_id'] ?? null;
        if (!$roleId) return $this->JSONResponse('OK', [], 200);

        $permissions = $this->db()->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('active', 0)
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($permissions as $p) {
            $result[$p['module_slug']] = [
                'can_view'    => (int) $p['can_view'],
                'can_create'  => (int) $p['can_create'],
                'can_update'  => (int) $p['can_update'],
                'can_delete'  => (int) $p['can_delete'],
                'can_approve' => (int) $p['can_approve'],
            ];
        }

        return $this->JSONResponse('OK', $result, 200);
    }
}
