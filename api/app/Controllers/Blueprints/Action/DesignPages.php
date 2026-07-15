<?php

namespace App\Controllers\Blueprints\Action;

use App\Controllers\BaseApi;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;

class DesignPages extends BaseApi
{
    private AuditLogger $audit;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->audit = new AuditLogger();
    }

    public function create($encryptedModuleId = null): ResponseInterface
    {
        $moduleId = $this->resolveId($encryptedModuleId);
        if (!$moduleId) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $title = trim($input['title'] ?? '');
        if (empty($title)) {
            return $this->JSONResponse('Judul wajib diisi', null, 400);
        }

        $module = $this->db()->table('blueprint_modules')->where('id', $moduleId)->get()->getRowArray();
        if (!$module) return $this->JSONResponse('Module tidak ditemukan', null, 404);

        $maxSort = $this->db()->table('blueprint_design_pages')->where('module_id', $moduleId)->countAllResults();

        $this->db()->transStart();
        $this->db()->table('blueprint_design_pages')->insert([
            'module_id'   => $moduleId,
            'title'       => $title,
            'description' => trim($input['description'] ?? ''),
            'sort_order'  => $maxSort,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $designPageId = $this->db()->insertID();

        $this->audit->log($userId, 'blueprint_design_page', $designPageId, 'create', null, ['module_id' => $moduleId, 'title' => $title]);
        $this->db()->transComplete();

        return $this->JSONResponse('Design page berhasil ditambahkan', [
            'id' => $this->api->encryptId($designPageId),
        ], 201);
    }

    public function update($encryptedId = null): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin', null, 403);
        }

        $designPage = $this->db()->table('blueprint_design_pages')->where('id', $id)->get()->getRowArray();
        if (!$designPage) return $this->JSONResponse('Design page tidak ditemukan', null, 404);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $update = [];
        if (isset($input['title']))       $update['title'] = trim($input['title']);
        if (isset($input['description'])) $update['description'] = trim($input['description']);
        $update['updated_at'] = date('Y-m-d H:i:s');

        $this->db()->transStart();
        $this->db()->table('blueprint_design_pages')->update($update, ['id' => $id]);
        $this->audit->log($userId, 'blueprint_design_page', $id, 'update', null, $update);
        $this->db()->transComplete();

        return $this->JSONResponse('Design page berhasil diperbarui', [
            'id' => $this->api->encryptId($id),
        ], 200);
    }

    public function delete($encryptedId = null): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin', null, 403);
        }

        $designPage = $this->db()->table('blueprint_design_pages')->where('id', $id)->get()->getRowArray();
        if (!$designPage) return $this->JSONResponse('Design page tidak ditemukan', null, 404);

        $this->db()->transStart();
        $this->db()->table('blueprint_design_pages')->delete(['id' => $id]);
        $this->audit->log($userId, 'blueprint_design_page', $id, 'delete', null, ['title' => $designPage['title']]);
        $this->db()->transComplete();

        return $this->JSONResponse('Design page berhasil dihapus', null, 200);
    }
}
