<?php

namespace App\Controllers\MasterProjects\Report;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class MasterProjectList extends BaseApi
{
    public function get_list(): ResponseInterface
    {
        $projects = $this->db()->table('master_projects')
            ->select('master_projects.*, users.full_name as creator_name')
            ->join('users', 'users.id = master_projects.created_by', 'left')
            ->orderBy('master_projects.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($projects as $p) {
            $moduleCount = $this->db()->table('modules')
                ->where('master_project_id', $p['id'])->countAllResults();

            $bugCount = $this->db()->table('tickets')
                ->join('pages', 'pages.id = tickets.page_id')
                ->join('modules', 'modules.id = pages.module_id')
                ->where('modules.master_project_id', $p['id'])
                ->where('tickets.type', Enums::TICKET_TYPE_BUG)
                ->countAllResults();

            $result[] = [
                'id'           => $this->api->encryptId($p['id']),
                'name'         => $p['name'],
                'description'  => $p['description'],
                'status'       => (int) $p['status'],
                'status_name'  => $p['status'] ? 'Active' : 'Archived',
                'module_count' => $moduleCount,
                'bug_count'    => $bugCount,
                'creator_name' => $p['creator_name'],
                'created_at'   => $p['created_at'],
            ];
        }

        return $this->JSONResponse('OK', ['data' => $result, 'total' => count($result)], 200);
    }

    public function get_active(): ResponseInterface
    {
        $projects = $this->db()->table('master_projects')
            ->select('id, name')
            ->where('status', 1)
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
            ->where('master_project_id', $projectId)
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
            ->where('module_id', $moduleId)
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

    public function get_bugs(string $encryptedPageId): ResponseInterface
    {
        $pageId = $this->resolveId($encryptedPageId);
        if (!$pageId) return $this->JSONResponse('ID tidak valid', null, 400);

        $bugs = $this->db()->table('tickets')
            ->select('tickets.*, creator.full_name as creator_name')
            ->join('users as creator', 'creator.id = tickets.creator_id', 'left')
            ->where('tickets.page_id', $pageId)
            ->where('tickets.type', Enums::TICKET_TYPE_BUG)
            ->orderBy('tickets.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($bugs as $b) {
            $result[] = [
                'id'           => $this->api->encryptId($b['id']),
                'title'        => $b['title'],
                'status'       => (int) $b['status'],
                'status_name'  => Enums::ticketStatusName((int) $b['status']),
                'priority'     => (int) $b['priority'],
                'priority_name'=> Enums::priorityName((int) $b['priority']),
                'creator_name' => $b['creator_name'],
                'created_at'   => $b['created_at'],
            ];
        }

        return $this->JSONResponse('OK', $result, 200);
    }
}
