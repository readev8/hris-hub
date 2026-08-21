<?php

namespace App\Controllers\Blueprints\Action;

use App\Controllers\BaseApi;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

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

        $rawInput = $this->req->getJSON(true) ?? $this->req->getPost();
        $input = $this->cleanInput($rawInput);
        $title = trim($input['title'] ?? '');
        if (empty($title)) {
            return $this->JSONResponse('Judul wajib diisi', null, 400);
        }

        $module = $this->db()->table(Tables::BLUEPRINT_MODULES)->where('id', $moduleId)->where('active', 0)->get()->getRowArray();
        if (!$module) return $this->JSONResponse('Module tidak ditemukan', null, 404);

        $maxSort = $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES)->where('module_id', $moduleId)->where('active', 0)->countAllResults();

        $this->db()->transStart();
        $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES)->insert([
            'module_id'   => $moduleId,
            'title'       => $title,
            'description' => $this->sanitizeRichText($rawInput['description'] ?? ''),
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

        $designPage = $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$designPage) return $this->JSONResponse('Design page tidak ditemukan', null, 404);

        $rawInput = $this->req->getJSON(true) ?? $this->req->getPost();
        $input = $this->cleanInput($rawInput);
        $update = [];
        if (isset($input['title']))       $update['title'] = trim($input['title']);
        if (isset($rawInput['description'])) $update['description'] = $this->sanitizeRichText($rawInput['description']);
        $update['updated_at'] = date('Y-m-d H:i:s');

        $this->db()->transStart();
        $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES)->update($update, ['id' => $id]);
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

        $designPage = $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$designPage) return $this->JSONResponse('Design page tidak ditemukan', null, 404);

        $this->db()->transStart();
        $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES)->update(['active' => 1], ['id' => $id]);
        $this->audit->log($userId, 'blueprint_design_page', $id, 'delete', null, ['title' => $designPage['title']]);
        $this->db()->transComplete();

        return $this->JSONResponse('Design page berhasil dihapus', null, 200);
    }
}
