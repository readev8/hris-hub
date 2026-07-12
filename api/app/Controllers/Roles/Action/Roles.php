<?php

namespace App\Controllers\Roles\Action;

use App\Controllers\BaseApi;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;

class Roles extends BaseApi
{
    private AuditLogger $audit;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->audit = new AuditLogger();
    }

    public function create_role(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $name = trim($input['name'] ?? '');
        $slug = trim($input['slug'] ?? '');
        $description = trim($input['description'] ?? '');

        if (strlen($name) < 3) return $this->JSONResponse('Nama role minimal 3 karakter', null, 400);
        if (strlen($slug) < 2) return $this->JSONResponse('Slug minimal 2 karakter', null, 400);
        if (!preg_match('/^[a-z0-9_-]+$/', $slug)) return $this->JSONResponse('Slug hanya boleh huruf kecil, angka, dash, underscore', null, 400);

        $exists = $this->db()->table('roles')->where('slug', $slug)->countAllResults();
        if ($exists > 0) return $this->JSONResponse('Slug sudah digunakan', null, 400);

        $this->db()->transStart();
        $this->db()->table('roles')->insert([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $roleId = $this->db()->insertID();

        $this->audit->log($userId, 'role', $roleId, 'create_role', null, ['name' => $name, 'slug' => $slug]);
        $this->db()->transComplete();

        return $this->JSONResponse('Role berhasil dibuat', [
            'id' => $this->api->encryptId($roleId),
        ], 201);
    }

    public function update_role(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $role = $this->db()->table('roles')->where('id', $id)->get()->getRowArray();
        if (!$role) return $this->JSONResponse('Role tidak ditemukan', null, 404);
        if ($role['is_system']) return $this->JSONResponse('Role sistem tidak dapat diubah', null, 400);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $name = trim($input['name'] ?? $role['name']);
        $description = trim($input['description'] ?? $role['description']);
        $isActive = isset($input['is_active']) ? (int) $input['is_active'] : $role['is_active'];

        $this->db()->transStart();
        $this->db()->table('roles')->where('id', $id)->update([
            'name'        => $name,
            'description' => $description,
            'is_active'   => $isActive,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->audit->log($userId, 'role', $id, 'update_role', ['name' => $role['name']], ['name' => $name]);
        $this->db()->transComplete();

        return $this->JSONResponse('Role berhasil diupdate');
    }

    public function delete_role(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $role = $this->db()->table('roles')->where('id', $id)->get()->getRowArray();
        if (!$role) return $this->JSONResponse('Role tidak ditemukan', null, 404);
        if ($role['is_system']) return $this->JSONResponse('Role sistem tidak dapat dihapus', null, 400);

        $userCount = $this->db()->table('users')->where('role_id', $id)->countAllResults();
        if ($userCount > 0) return $this->JSONResponse('Role masih digunakan oleh ' . $userCount . ' user', null, 400);

        $this->db()->transStart();
        $this->db()->table('roles')->where('id', $id)->delete();
        $this->audit->log($userId, 'role', $id, 'delete_role', ['name' => $role['name']], null);
        $this->db()->transComplete();

        return $this->JSONResponse('Role berhasil dihapus');
    }

    public function save_permissions(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $role = $this->db()->table('roles')->where('id', $id)->get()->getRowArray();
        if (!$role) return $this->JSONResponse('Role tidak ditemukan', null, 404);

        $input = $this->req->getJSON(true) ?? $this->req->getPost();
        $permissions = $input['permissions'] ?? [];

        if (!is_array($permissions)) return $this->JSONResponse('Format permissions tidak valid', null, 400);

        $this->db()->transStart();
        $this->db()->table('role_permissions')->where('role_id', $id)->delete();

        $now = date('Y-m-d H:i:s');
        foreach ($permissions as $perm) {
            $moduleSlug = trim($perm['module_slug'] ?? '');
            if (empty($moduleSlug)) continue;

            $this->db()->table('role_permissions')->insert([
                'role_id'      => $id,
                'module_slug'  => $moduleSlug,
                'can_view'     => (int) ($perm['can_view'] ?? 0),
                'can_create'   => (int) ($perm['can_create'] ?? 0),
                'can_update'   => (int) ($perm['can_update'] ?? 0),
                'can_delete'   => (int) ($perm['can_delete'] ?? 0),
                'can_approve'  => (int) ($perm['can_approve'] ?? 0),
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        $this->audit->log($userId, 'role', $id, 'save_permissions', null, ['permission_count' => count($permissions)]);
        $this->db()->transComplete();

        return $this->JSONResponse('Permissions berhasil disimpan');
    }

    public function toggle_active(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $role = $this->db()->table('roles')->where('id', $id)->get()->getRowArray();
        if (!$role) return $this->JSONResponse('Role tidak ditemukan', null, 404);
        if ($role['is_system']) return $this->JSONResponse('Role sistem tidak dapat diubah', null, 400);

        $newStatus = $role['is_active'] ? 0 : 1;

        $this->db()->transStart();
        $this->db()->table('roles')->where('id', $id)->update([
            'is_active'  => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit->log($userId, 'role', $id, 'toggle_active', ['is_active' => $role['is_active']], ['is_active' => $newStatus]);
        $this->db()->transComplete();

        return $this->JSONResponse('Role berhasil ' . ($newStatus ? 'diaktifkan' : 'dinonaktifkan'));
    }
}
