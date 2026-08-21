<?php

namespace App\Models\Roles;

use Config\Tables;

class PermissionCheck_model
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getUserPermissions(int $userId): array
    {
        $user = $this->db->table(Tables::USERS)->where('id', $userId)->get()->getRowArray();
        if (!$user || empty($user['role_id'])) return [];

        $permissions = $this->db->table(Tables::ROLE_PERMISSIONS)
            ->where('active', 0)
            ->where('role_id', $user['role_id'])
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

        return $result;
    }

    public function hasPermission(int $userId, string $module, string $action = 'can_view'): bool
    {
        $permissions = $this->getUserPermissions($userId);
        return ($permissions[$module][$action] ?? 0) === 1;
    }
}
