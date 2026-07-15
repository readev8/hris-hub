<?php

namespace App\Controllers\Blueprints\Action;

use App\Controllers\BaseApi;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;

class Modules extends BaseApi
{
    private AuditLogger $audit;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->audit = new AuditLogger();
    }

    public function create($encryptedBlueprintId = null): ResponseInterface
    {
        $blueprintId = $this->resolveId($encryptedBlueprintId);
        if (!$blueprintId) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            return $this->JSONResponse('Nama module wajib diisi', null, 400);
        }

        $blueprint = $this->db()->table('blueprints')->where('id', $blueprintId)->get()->getRowArray();
        if (!$blueprint) return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);

        $maxSort = $this->db()->table('blueprint_modules')->where('blueprint_id', $blueprintId)->countAllResults();

        $this->db()->transStart();
        $this->db()->table('blueprint_modules')->insert([
            'blueprint_id' => $blueprintId,
            'name'         => $name,
            'description'  => trim($input['description'] ?? ''),
            'sort_order'   => $maxSort,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        $moduleId = $this->db()->insertID();

        $this->audit->log($userId, 'blueprint_module', $moduleId, 'create', null, ['blueprint_id' => $blueprintId, 'name' => $name]);
        $this->db()->transComplete();

        return $this->JSONResponse('Module berhasil ditambahkan', [
            'id' => $this->api->encryptId($moduleId),
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

        $module = $this->db()->table('blueprint_modules')->where('id', $id)->get()->getRowArray();
        if (!$module) return $this->JSONResponse('Module tidak ditemukan', null, 404);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $update = [];
        if (isset($input['name']))        $update['name'] = trim($input['name']);
        if (isset($input['description'])) $update['description'] = trim($input['description']);
        if (isset($input['sort_order']))  $update['sort_order'] = (int) $input['sort_order'];
        $update['updated_at'] = date('Y-m-d H:i:s');

        if (empty($update) || count($update) === 1) {
            return $this->JSONResponse('Tidak ada data yang diubah', null, 400);
        }

        $this->db()->transStart();
        $this->db()->table('blueprint_modules')->update($update, ['id' => $id]);
        $this->audit->log($userId, 'blueprint_module', $id, 'update', null, $update);
        $this->db()->transComplete();

        return $this->JSONResponse('Module berhasil diperbarui', [
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

        $module = $this->db()->table('blueprint_modules')->where('id', $id)->get()->getRowArray();
        if (!$module) return $this->JSONResponse('Module tidak ditemukan', null, 404);

        $this->db()->transStart();
        $this->db()->table('blueprint_modules')->delete(['id' => $id]);
        $this->audit->log($userId, 'blueprint_module', $id, 'delete', null, ['name' => $module['name']]);
        $this->db()->transComplete();

        return $this->JSONResponse('Module berhasil dihapus', null, 200);
    }
}
