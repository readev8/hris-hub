<?php

namespace App\Controllers\Blueprints\Action;

use App\Controllers\BaseApi;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;

class BusinessScenarios extends BaseApi
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

        $module = $this->db()->table('blueprint_modules')->where('id', $moduleId)->where('active', 0)->get()->getRowArray();
        if (!$module) return $this->JSONResponse('Module tidak ditemukan', null, 404);

        $maxSort = $this->db()->table('blueprint_business_scenarios')->where('module_id', $moduleId)->where('active', 0)->countAllResults();

        $this->db()->transStart();
        $this->db()->table('blueprint_business_scenarios')->insert([
            'module_id'   => $moduleId,
            'title'       => $title,
            'description' => $this->sanitizeRichText($this->req->getPost('description')),
            'sort_order'  => $maxSort,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $scenarioId = $this->db()->insertID();

        $this->audit->log($userId, 'blueprint_scenario', $scenarioId, 'create', null, ['module_id' => $moduleId, 'title' => $title]);
        $this->db()->transComplete();

        return $this->JSONResponse('Business scenario berhasil ditambahkan', [
            'id' => $this->api->encryptId($scenarioId),
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

        $scenario = $this->db()->table('blueprint_business_scenarios')->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$scenario) return $this->JSONResponse('Business scenario tidak ditemukan', null, 404);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $update = [];
        if (isset($input['title']))       $update['title'] = trim($input['title']);
        if (isset($input['description'])) $update['description'] = $this->sanitizeRichText($this->req->getPost('description'));
        $update['updated_at'] = date('Y-m-d H:i:s');

        $this->db()->transStart();
        $this->db()->table('blueprint_business_scenarios')->update($update, ['id' => $id]);
        $this->audit->log($userId, 'blueprint_scenario', $id, 'update', null, $update);
        $this->db()->transComplete();

        return $this->JSONResponse('Business scenario berhasil diperbarui', [
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

        $scenario = $this->db()->table('blueprint_business_scenarios')->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$scenario) return $this->JSONResponse('Business scenario tidak ditemukan', null, 404);

        $this->db()->transStart();
        $this->db()->table('blueprint_business_scenarios')->update(['active' => 1], ['id' => $id]);
        $this->audit->log($userId, 'blueprint_scenario', $id, 'delete', null, ['title' => $scenario['title']]);
        $this->db()->transComplete();

        return $this->JSONResponse('Business scenario berhasil dihapus', null, 200);
    }
}
