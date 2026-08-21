<?php

namespace App\Controllers\Roles\Action;

use App\Controllers\BaseApi;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

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

        if (!$this->checkPermission('roles', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk membuat role', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $name = trim($input['name'] ?? '');
        $slug = trim($input['slug'] ?? '');
        $description = trim($input['description'] ?? '');

        if (strlen($name) < 3) return $this->JSONResponse('Nama role minimal 3 karakter', null, 400);
        if (strlen($slug) < 2) return $this->JSONResponse('Slug minimal 2 karakter', null, 400);
        if (!preg_match('/^[a-z0-9_-]+$/', $slug)) return $this->JSONResponse('Slug hanya boleh huruf kecil, angka, dash, underscore', null, 400);

        $exists = $this->db()->table(Tables::ROLES)->where('slug', $slug)->where('active', 0)->countAllResults();
        if ($exists > 0) return $this->JSONResponse('Slug sudah digunakan', null, 400);

        $this->db()->transStart();
        $this->db()->table(Tables::ROLES)->insert([
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

        if (!$this->checkPermission('roles', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah role', null, 403);
        }

        $role = $this->db()->table(Tables::ROLES)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$role) return $this->JSONResponse('Role tidak ditemukan', null, 404);
        if ($role['is_system']) return $this->JSONResponse('Role sistem tidak dapat diubah', null, 400);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $name = trim($input['name'] ?? $role['name']);
        $description = trim($input['description'] ?? $role['description']);
        $isActive = isset($input['is_active']) ? (int) $input['is_active'] : $role['is_active'];

        $this->db()->transStart();
        $this->db()->table(Tables::ROLES)->where('id', $id)->update([
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

        if (!$this->checkPermission('roles', 'can_delete')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menghapus role', null, 403);
        }

        $role = $this->db()->table(Tables::ROLES)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$role) return $this->JSONResponse('Role tidak ditemukan', null, 404);
        if ($role['is_system']) return $this->JSONResponse('Role sistem tidak dapat dihapus', null, 400);

        $userCount = $this->db()->table(Tables::USERS)->where('role_id', $id)->countAllResults();
        if ($userCount > 0) return $this->JSONResponse('Role masih digunakan oleh ' . $userCount . ' user', null, 400);

        $this->db()->transStart();
        $this->db()->table(Tables::ROLES)->where('id', $id)->update(['active' => 1]);
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

        if (!$this->checkPermission('roles', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menyimpan permissions', null, 403);
        }

        $role = $this->db()->table(Tables::ROLES)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$role) return $this->JSONResponse('Role tidak ditemukan', null, 404);

        $input = $this->req->getJSON(true) ?? $this->req->getPost();
        $permissions = $input['permissions'] ?? [];

        if (!is_array($permissions)) return $this->JSONResponse('Format permissions tidak valid', null, 400);

        $this->db()->transStart();
        $this->db()->table(Tables::ROLE_PERMISSIONS)->where('role_id', $id)->update(['active' => 1]);

        $now = date('Y-m-d H:i:s');
        foreach ($permissions as $perm) {
            $moduleSlug = trim($perm['module_slug'] ?? '');
            if (empty($moduleSlug)) continue;

            $this->db()->table(Tables::ROLE_PERMISSIONS)->insert([
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

    /**
     * Get permissions by raw role_id (not encrypted).
     * Called by web Auth controller during login flow.
     * Requires API key auth but NOT user auth.
     */
    public function get_permissions_by_id(string $roleId): ResponseInterface
    {
        $id = (int) $roleId;
        if ($id <= 0) {
            return $this->JSONResponse('ID tidak valid', null, 400);
        }

        $permissions = [];
        try {
            if ($this->db()->tableExists(Tables::ROLE_PERMISSIONS)) {
                $permissions = $this->db()->table(Tables::ROLE_PERMISSIONS)
                    ->where('role_id', $id)
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

        return $this->JSONResponse('OK', $permMap, 200);
    }

    public function toggle_active(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('roles', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah status role', null, 403);
        }

        $role = $this->db()->table(Tables::ROLES)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$role) return $this->JSONResponse('Role tidak ditemukan', null, 404);
        if ($role['is_system']) return $this->JSONResponse('Role sistem tidak dapat diubah', null, 400);

        $newStatus = $role['is_active'] ? 0 : 1;

        $this->db()->transStart();
        $this->db()->table(Tables::ROLES)->where('id', $id)->update([
            'is_active'  => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit->log($userId, 'role', $id, 'toggle_active', ['is_active' => $role['is_active']], ['is_active' => $newStatus]);
        $this->db()->transComplete();

        return $this->JSONResponse('Role berhasil ' . ($newStatus ? 'diaktifkan' : 'dinonaktifkan'));
    }
}
