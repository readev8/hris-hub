<?php

namespace App\Controllers\Blueprints\Action;

use App\Controllers\BaseApi;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;

class PageSpecifications extends BaseApi
{
    private AuditLogger $audit;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->audit = new AuditLogger();
    }

    public function create($encryptedDesignPageId = null): ResponseInterface
    {
        $designPageId = $this->resolveId($encryptedDesignPageId);
        if (!$designPageId) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $fieldName = trim($input['field_name'] ?? '');
        $datatype = trim($input['datatype'] ?? '');
        $controlType = trim($input['control_type'] ?? '');

        if (empty($fieldName)) return $this->JSONResponse('Field name wajib diisi', null, 400);
        if (empty($datatype)) return $this->JSONResponse('Datatype wajib dipilih', null, 400);
        if (empty($controlType)) return $this->JSONResponse('Control type wajib dipilih', null, 400);

        $validDatatypes = ['text', 'number', 'date', 'datetime', 'time', 'image', 'pdf', 'excel'];
        if (!in_array($datatype, $validDatatypes, true)) {
            return $this->JSONResponse('Datatype tidak valid', null, 400);
        }

        $validControlTypes = ['text', 'password', 'date', 'datetime', 'combobox', 'radiobutton', 'checkbox', 'multipleselect', 'uploadfile'];
        if (!in_array($controlType, $validControlTypes, true)) {
            return $this->JSONResponse('Control type tidak valid', null, 400);
        }

        $designPage = $this->db()->table('blueprint_design_pages')->where('id', $designPageId)->get()->getRowArray();
        if (!$designPage) return $this->JSONResponse('Design page tidak ditemukan', null, 404);

        $maxSort = $this->db()->table('blueprint_page_specifications')->where('design_page_id', $designPageId)->countAllResults();

        $this->db()->transStart();
        $this->db()->table('blueprint_page_specifications')->insert([
            'design_page_id' => $designPageId,
            'module_id'      => $designPage['module_id'],
            'field_name'     => $fieldName,
            'data'           => trim($input['data'] ?? ''),
            'objective'      => trim($input['objective'] ?? ''),
            'initial_data'   => trim($input['initial_data'] ?? ''),
            'condition'      => trim($input['condition'] ?? ''),
            'validation'     => trim($input['validation'] ?? ''),
            'input_display'  => trim($input['input_display'] ?? ''),
            'datatype'       => $datatype,
            'control_type'   => $controlType,
            'ux'             => trim($input['ux'] ?? ''),
            'sort_order'     => $maxSort,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $specId = $this->db()->insertID();

        $this->audit->log($userId, 'blueprint_page_spec', $specId, 'create', null, ['design_page_id' => $designPageId, 'module_id' => $designPage['module_id'], 'field_name' => $fieldName]);
        $this->db()->transComplete();

        return $this->JSONResponse('Page specification berhasil ditambahkan', [
            'id' => $this->api->encryptId($specId),
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

        $spec = $this->db()->table('blueprint_page_specifications')->where('id', $id)->get()->getRowArray();
        if (!$spec) return $this->JSONResponse('Page specification tidak ditemukan', null, 404);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $update = [];
        $fields = ['field_name', 'data', 'objective', 'initial_data', 'condition', 'validation', 'input_display', 'datatype', 'control_type', 'ux'];
        foreach ($fields as $f) {
            if (isset($input[$f])) $update[$f] = trim($input[$f]);
        }
        if (isset($input['sort_order'])) $update['sort_order'] = (int) $input['sort_order'];
        $update['updated_at'] = date('Y-m-d H:i:s');

        if (isset($update['datatype'])) {
            $validDatatypes = ['text', 'number', 'date', 'datetime', 'time', 'image', 'pdf', 'excel'];
            if (!in_array($update['datatype'], $validDatatypes, true)) {
                return $this->JSONResponse('Datatype tidak valid', null, 400);
            }
        }
        if (isset($update['control_type'])) {
            $validControlTypes = ['text', 'password', 'date', 'datetime', 'combobox', 'radiobutton', 'checkbox', 'multipleselect', 'uploadfile'];
            if (!in_array($update['control_type'], $validControlTypes, true)) {
                return $this->JSONResponse('Control type tidak valid', null, 400);
            }
        }

        $this->db()->transStart();
        $this->db()->table('blueprint_page_specifications')->update($update, ['id' => $id]);
        $this->audit->log($userId, 'blueprint_page_spec', $id, 'update', null, $update);
        $this->db()->transComplete();

        return $this->JSONResponse('Page specification berhasil diperbarui', [
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

        $spec = $this->db()->table('blueprint_page_specifications')->where('id', $id)->get()->getRowArray();
        if (!$spec) return $this->JSONResponse('Page specification tidak ditemukan', null, 404);

        $this->db()->transStart();
        $this->db()->table('blueprint_page_specifications')->delete(['id' => $id]);
        $this->audit->log($userId, 'blueprint_page_spec', $id, 'delete', null, ['field_name' => $spec['field_name']]);
        $this->db()->transComplete();

        return $this->JSONResponse('Page specification berhasil dihapus', null, 200);
    }
}
