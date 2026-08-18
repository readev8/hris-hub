<?php

namespace App\Controllers\ModuleFlows\Action;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class FlowActions extends BaseApi
{
    public function add_module(): ResponseInterface
    {
        if (!$this->checkPermission('module_flows', 'can_create')) {
            return $this->JSONResponse('Forbidden', null, 403);
        }

        $post = $this->req->getJSON(true) ?? $this->req->getPost();
        $moduleId = $this->resolveId($post['module_id'] ?? '');
        if (!$moduleId) return $this->JSONResponse('ID modul tidak valid', null, 400);

        $x = isset($post['pos_x']) ? (int) $post['pos_x'] : 0;
        $y = isset($post['pos_y']) ? (int) $post['pos_y'] : 0;

        $db = $this->db();

        $module = $db->table(Tables::MODULES)
            ->where('id', $moduleId)
            ->where('active', 0)
            ->get()
            ->getRowArray();
        if (!$module) return $this->JSONResponse('Modul tidak ditemukan', null, 404);

        $existing = $db->table(Tables::FLOW_NODE_POSITIONS)
            ->where('module_id', $moduleId)
            ->where('active', 0)
            ->get()
            ->getRowArray();
        if ($existing) return $this->JSONResponse('Modul sudah ada di kanvas', null, 409);

        $now = date('Y-m-d H:i:s');
        $db->table(Tables::FLOW_NODE_POSITIONS)->insert([
            'module_id'  => $moduleId,
            'pos_x'      => $x,
            'pos_y'      => $y,
            'created_by' => $this->getCurrentUserId(),
            'created_at' => $now,
            'updated_at' => $now,
            'active'     => 0,
        ]);

        $projectName = null;
        if ($module['master_project_id']) {
            $projectName = $db->table(Tables::MASTER_PROJECTS)
                ->where('id', $module['master_project_id'])
                ->where('active', 0)
                ->get()
                ->getRowArray()['name'] ?? null;
        }

        return $this->JSONResponse('OK', [
            'module_id'    => $this->api->encryptId($moduleId),
            'name'         => $module['name'],
            'project_id'   => $module['master_project_id'] ? $this->api->encryptId($module['master_project_id']) : null,
            'project_name' => $projectName,
            'pos_x'        => $x,
            'pos_y'        => $y,
        ], 200);
    }

    public function update_position(string $encryptedModuleId): ResponseInterface
    {
        if (!$this->checkPermission('module_flows', 'can_update')) {
            return $this->JSONResponse('Forbidden', null, 403);
        }

        $moduleId = $this->resolveId($encryptedModuleId);
        if (!$moduleId) return $this->JSONResponse('ID modul tidak valid', null, 400);

        $post = $this->req->getJSON(true) ?? $this->req->getPost();
        $x = isset($post['pos_x']) ? (int) $post['pos_x'] : 0;
        $y = isset($post['pos_y']) ? (int) $post['pos_y'] : 0;

        $db = $this->db();

        $row = $db->table(Tables::FLOW_NODE_POSITIONS)
            ->where('module_id', $moduleId)
            ->where('active', 0)
            ->get()
            ->getRowArray();
        if (!$row) return $this->JSONResponse('Modul tidak ada di kanvas', null, 404);

        $db->table(Tables::FLOW_NODE_POSITIONS)
            ->where('id', $row['id'])
            ->update([
                'pos_x'      => $x,
                'pos_y'      => $y,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->JSONResponse('OK', null, 200);
    }

    public function remove_module(string $encryptedModuleId): ResponseInterface
    {
        if (!$this->checkPermission('module_flows', 'can_delete')) {
            return $this->JSONResponse('Forbidden', null, 403);
        }

        $moduleId = $this->resolveId($encryptedModuleId);
        if (!$moduleId) return $this->JSONResponse('ID modul tidak valid', null, 400);

        $db = $this->db();

        $row = $db->table(Tables::FLOW_NODE_POSITIONS)
            ->where('module_id', $moduleId)
            ->where('active', 0)
            ->get()
            ->getRowArray();
        if (!$row) return $this->JSONResponse('Modul tidak ada di kanvas', null, 404);

        $now = date('Y-m-d H:i:s');

        // Soft-delete position
        $db->table(Tables::FLOW_NODE_POSITIONS)
            ->where('id', $row['id'])
            ->update(['active' => 1, 'updated_at' => $now]);

        // Soft-delete connections touching this module
        $db->table(Tables::FLOW_CONNECTIONS)
            ->groupStart()
            ->where('from_module_id', $moduleId)
            ->orWhere('to_module_id', $moduleId)
            ->groupEnd()
            ->where('active', 0)
            ->update(['active' => 1, 'updated_at' => $now]);

        return $this->JSONResponse('OK', null, 200);
    }

    public function add_connection(): ResponseInterface
    {
        if (!$this->checkPermission('module_flows', 'can_create')) {
            return $this->JSONResponse('Forbidden', null, 403);
        }

        $post = $this->req->getJSON(true) ?? $this->req->getPost();
        $fromId = $this->resolveId($post['from'] ?? '');
        $toId   = $this->resolveId($post['to'] ?? '');
        if (!$fromId || !$toId) return $this->JSONResponse('ID modul tidak valid', null, 400);
        if ($fromId === $toId) return $this->JSONResponse('Tidak dapat menghubungkan modul ke dirinya sendiri', null, 400);

        $db = $this->db();

        $dup = $db->table(Tables::FLOW_CONNECTIONS)
            ->where('from_module_id', $fromId)
            ->where('to_module_id', $toId)
            ->where('active', 0)
            ->get()
            ->getRowArray();
        if ($dup) return $this->JSONResponse('Koneksi sudah ada', null, 409);

        // Both modules must be on canvas
        $onCanvas = $db->table(Tables::FLOW_NODE_POSITIONS)
            ->whereIn('module_id', [$fromId, $toId])
            ->where('active', 0)
            ->countAllResults();
        if ($onCanvas !== 2) return $this->JSONResponse('Kedua modul harus ada di kanvas', null, 400);

        $now = date('Y-m-d H:i:s');
        $db->table(Tables::FLOW_CONNECTIONS)->insert([
            'from_module_id' => $fromId,
            'to_module_id'   => $toId,
            'created_by'     => $this->getCurrentUserId(),
            'created_at'     => $now,
            'updated_at'     => $now,
            'active'         => 0,
        ]);

        return $this->JSONResponse('OK', [
            'id'   => $this->api->encryptId($db->insertID()),
            'from' => $this->api->encryptId($fromId),
            'to'   => $this->api->encryptId($toId),
        ], 200);
    }

    public function delete_connection(string $encryptedConnectionId): ResponseInterface
    {
        if (!$this->checkPermission('module_flows', 'can_delete')) {
            return $this->JSONResponse('Forbidden', null, 403);
        }

        $connId = $this->resolveId($encryptedConnectionId);
        if (!$connId) return $this->JSONResponse('ID koneksi tidak valid', null, 400);

        $db = $this->db();

        $conn = $db->table(Tables::FLOW_CONNECTIONS)
            ->where('id', $connId)
            ->where('active', 0)
            ->get()
            ->getRowArray();
        if (!$conn) return $this->JSONResponse('Koneksi tidak ditemukan', null, 404);

        $db->table(Tables::FLOW_CONNECTIONS)
            ->where('id', $connId)
            ->update(['active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->JSONResponse('OK', null, 200);
    }
}
