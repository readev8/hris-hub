<?php

namespace App\Controllers\MasterProjects\Action;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class Modules extends BaseApi
{
    public function create_module(string $encryptedProjectId): ResponseInterface
    {
        $projectId = $this->resolveId($encryptedProjectId);
        if (!$projectId) return $this->JSONResponse('ID project tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk membuat modul', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $name = trim($input['name'] ?? '');

        if (empty($name)) {
            return $this->JSONResponse('Nama modul wajib diisi', null, 400);
        }

        $project = $this->db()->table('master_projects')->where('id', $projectId)->where('active', 0)->get()->getRowArray();
        if (!$project) return $this->JSONResponse('Project tidak ditemukan', null, 404);

        $maxSort = $this->db()->table('modules')
            ->selectMax('sort_order')
            ->where('master_project_id', $projectId)
            ->where('active', 0)
            ->get()
            ->getRowArray();

        $this->db()->table('modules')->insert([
            'master_project_id' => $projectId,
            'name'              => $name,
            'description'       => trim($input['description'] ?? ''),
            'sort_order'        => ((int) ($maxSort['sort_order'] ?? 0)) + 1,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db()->insertID();

        return $this->JSONResponse('Modul berhasil dibuat', [
            'id' => $this->api->encryptId($id),
        ], 201);
    }

    public function update_module(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah modul', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());

        $this->db()->table('modules')->update([
            'name'        => trim($input['name'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'updated_at'  => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return $this->JSONResponse('Modul berhasil diupdate');
    }

    public function delete_module(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_delete')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menghapus modul', null, 403);
        }

        $this->db()->table('modules')->update(['active' => 1], ['id' => $id]);
        return $this->JSONResponse('Modul berhasil dihapus');
    }
}
