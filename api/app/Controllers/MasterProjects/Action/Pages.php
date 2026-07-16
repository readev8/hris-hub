<?php

namespace App\Controllers\MasterProjects\Action;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class Pages extends BaseApi
{
    public function create_page(string $encryptedModuleId): ResponseInterface
    {
        $moduleId = $this->resolveId($encryptedModuleId);
        if (!$moduleId) return $this->JSONResponse('ID modul tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk membuat halaman', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $name = trim($input['name'] ?? '');

        if (empty($name)) {
            return $this->JSONResponse('Nama halaman wajib diisi', null, 400);
        }

        $module = $this->db()->table('modules')->where('id', $moduleId)->where('active', 0)->get()->getRowArray();
        if (!$module) return $this->JSONResponse('Modul tidak ditemukan', null, 404);

        $maxSort = $this->db()->table('pages')
            ->selectMax('sort_order')
            ->where('module_id', $moduleId)
            ->where('active', 0)
            ->get()
            ->getRowArray();

        $this->db()->table('pages')->insert([
            'module_id'   => $moduleId,
            'name'        => $name,
            'url_path'    => trim($input['url_path'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'sort_order'  => ((int) ($maxSort['sort_order'] ?? 0)) + 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db()->insertID();

        return $this->JSONResponse('Halaman berhasil dibuat', [
            'id' => $this->api->encryptId($id),
        ], 201);
    }

    public function update_page(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah halaman', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());

        $this->db()->table('pages')->update([
            'name'        => trim($input['name'] ?? ''),
            'url_path'    => trim($input['url_path'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'updated_at'  => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return $this->JSONResponse('Halaman berhasil diupdate');
    }

    public function delete_page(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_delete')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menghapus halaman', null, 403);
        }

        $this->db()->table('pages')->update(['active' => 1], ['id' => $id]);
        return $this->JSONResponse('Halaman berhasil dihapus');
    }
}
