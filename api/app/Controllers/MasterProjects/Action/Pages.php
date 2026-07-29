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

    public function assign_blueprint_design_page(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah halaman', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $designPageId = !empty($input['blueprint_design_page_id']) ? $this->resolveId($input['blueprint_design_page_id']) : null;

        $page = $this->db()->table('pages')->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$page) return $this->JSONResponse('Halaman tidak ditemukan', null, 404);

        if ($designPageId) {
            $designPage = $this->db()->table('blueprint_design_pages')->where('id', $designPageId)->where('active', 0)->get()->getRowArray();
            if (!$designPage) return $this->JSONResponse('Blueprint design page tidak ditemukan', null, 404);
        }

        $this->db()->table('pages')->update([
            'blueprint_design_page_id' => $designPageId,
            'updated_at'               => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return $this->JSONResponse('Blueprint design page berhasil diassign');
    }

    public function unassign_blueprint_design_page(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah halaman', null, 403);
        }

        $page = $this->db()->table('pages')->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$page) return $this->JSONResponse('Halaman tidak ditemukan', null, 404);

        $this->db()->table('pages')->update([
            'blueprint_design_page_id' => null,
            'updated_at'               => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return $this->JSONResponse('Blueprint design page berhasil diunassign');
    }

    public function import_design_pages(string $encryptedModuleId): ResponseInterface
    {
        $moduleId = $this->resolveId($encryptedModuleId);
        if (!$moduleId) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('master_projects', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $designPageIds = $input['design_page_ids'] ?? [];

        if (empty($designPageIds)) {
            return $this->JSONResponse('Pilih minimal satu design page', null, 400);
        }

        $module = $this->db()->table('modules')->where('id', $moduleId)->where('active', 0)->get()->getRowArray();
        if (!$module) return $this->JSONResponse('Module tidak ditemukan', null, 404);

        $imported = 0;
        $skipped = 0;
        $this->db()->transStart();

        foreach ($designPageIds as $encryptedDesignPageId) {
            $designPageId = $this->resolveId($encryptedDesignPageId);
            if (!$designPageId) { $skipped++; continue; }

            $designPage = $this->db()->table('blueprint_design_pages')
                ->where('id', $designPageId)->where('active', 0)->get()->getRowArray();
            if (!$designPage) { $skipped++; continue; }

            // Skip if already imported
            $existing = $this->db()->table('pages')
                ->where('module_id', $moduleId)
                ->where('blueprint_design_page_id', $designPageId)
                ->where('active', 0)->get()->getRowArray();
            if ($existing) { $skipped++; continue; }

            $this->db()->table('pages')->insert([
                'module_id'                => $moduleId,
                'blueprint_design_page_id' => $designPageId,
                'name'                     => $designPage['title'],
                'description'              => $designPage['description'] ?? '',
                'sort_order'               => $designPage['sort_order'] ?? 0,
                'created_at'              => date('Y-m-d H:i:s'),
                'updated_at'              => date('Y-m-d H:i:s'),
            ]);
            $imported++;
        }

        $this->db()->transComplete();

        return $this->JSONResponse("Berhasil import {$imported} design page(s)", [
            'imported' => $imported,
            'skipped'  => $skipped,
        ], 201);
    }
}
