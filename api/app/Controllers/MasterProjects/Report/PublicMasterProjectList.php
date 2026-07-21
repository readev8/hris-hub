<?php

namespace App\Controllers\MasterProjects\Report;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class PublicMasterProjectList extends BaseApi
{
    public function get_active(): ResponseInterface
    {
        $projects = $this->db()->table('master_projects')
            ->select('id, name')
            ->where('status', 1)
            ->where('active', 0)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($projects as $p) {
            $result[] = [
                'id'   => $this->api->encryptId($p['id']),
                'name' => $p['name'],
            ];
        }

        return $this->JSONResponse('OK', $result, 200);
    }

    public function get_modules(string $encryptedProjectId): ResponseInterface
    {
        $projectId = $this->resolveId($encryptedProjectId);
        if (!$projectId) return $this->JSONResponse('ID tidak valid', null, 400);

        $modules = $this->db()->table('modules')
            ->select('id, name')
            ->where('master_project_id', $projectId)
            ->where('active', 0)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($modules as $m) {
            $result[] = [
                'id'   => $this->api->encryptId($m['id']),
                'name' => $m['name'],
            ];
        }

        return $this->JSONResponse('OK', $result, 200);
    }

    public function get_pages(string $encryptedModuleId): ResponseInterface
    {
        $moduleId = $this->resolveId($encryptedModuleId);
        if (!$moduleId) return $this->JSONResponse('ID tidak valid', null, 400);

        $pages = $this->db()->table('pages')
            ->select('id, name')
            ->where('module_id', $moduleId)
            ->where('active', 0)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($pages as $p) {
            $result[] = [
                'id'   => $this->api->encryptId($p['id']),
                'name' => $p['name'],
            ];
        }

        return $this->JSONResponse('OK', $result, 200);
    }
}
