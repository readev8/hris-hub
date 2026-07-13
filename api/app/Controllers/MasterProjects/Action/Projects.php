<?php

namespace App\Controllers\MasterProjects\Action;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class Projects extends BaseApi
{
    public function create_project(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk membuat project', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $name = trim($input['name'] ?? '');

        if (empty($name)) {
            return $this->JSONResponse('Nama project wajib diisi', null, 400);
        }

        $this->db()->table('master_projects')->insert([
            'name'        => $name,
            'description' => trim($input['description'] ?? ''),
            'status'      => 1,
            'created_by'  => $userId,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db()->insertID();

        return $this->JSONResponse('Project berhasil dibuat', [
            'id' => $this->api->encryptId($id),
        ], 201);
    }

    public function update_project(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah project', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());

        $this->db()->table('master_projects')->update([
            'name'        => trim($input['name'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'status'      => (int) ($input['status'] ?? 1),
            'updated_at'  => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return $this->JSONResponse('Project berhasil diupdate');
    }

    public function delete_project(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_delete')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menghapus project', null, 403);
        }

        $this->db()->table('master_projects')->delete(['id' => $id]);
        return $this->JSONResponse('Project berhasil dihapus');
    }
}
