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
            ->where('master_projects.active', 0)
            ->orderBy('master_projects.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($projects as $p) {
            $moduleCount = $this->db()->table('modules')
                ->where('master_project_id', $p['id'])
                ->where('active', 0)
                ->countAllResults();

            $bugCount = $this->db()->table('tickets')
                ->join('pages', 'pages.id = tickets.page_id')
                ->join('modules', 'modules.id = pages.module_id')
                ->where('modules.master_project_id', $p['id'])
                ->where('tickets.type', Enums::TICKET_TYPE_BUG)
                ->where('tickets.active', 0)
                ->where('pages.active', 0)
                ->where('modules.active', 0)
                ->countAllResults();

            $bugOpen = $this->db()->table('tickets')
                ->join('pages', 'pages.id = tickets.page_id')
                ->join('modules', 'modules.id = pages.module_id')
                ->where('modules.master_project_id', $p['id'])
                ->where('tickets.type', Enums::TICKET_TYPE_BUG)
                ->where('tickets.active', 0)
                ->where('pages.active', 0)
                ->where('modules.active', 0)
                ->whereIn('tickets.status', [Enums::TICKET_STATUS_OPEN, Enums::TICKET_STATUS_APPROVED, Enums::TICKET_STATUS_IN_PROGRESS])
                ->countAllResults();

            $bugClosed = $this->db()->table('tickets')
                ->join('pages', 'pages.id = tickets.page_id')
                ->join('modules', 'modules.id = pages.module_id')
                ->where('modules.master_project_id', $p['id'])
                ->where('tickets.type', Enums::TICKET_TYPE_BUG)
                ->where('tickets.active', 0)
                ->where('pages.active', 0)
                ->where('modules.active', 0)
                ->whereIn('tickets.status', [Enums::TICKET_STATUS_RESOLVED, Enums::TICKET_STATUS_CLOSED])
                ->countAllResults();

            $result[] = [
                'id'           => $this->api->encryptId($p['id']),
                'name'         => $p['name'],
                'description'  => $p['description'],
                'status'       => (int) $p['status'],
                'status_name'  => $p['status'] ? 'Active' : 'Archived',
                'module_count' => $moduleCount,
                'bug_count'    => $bugCount,
                'bug_open'     => $bugOpen,
                'bug_closed'   => $bugClosed,
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

    public function get_kanban(string $encryptedProjectId): ResponseInterface
    {
        $projectId = $this->resolveId($encryptedProjectId);
        if (!$projectId) return $this->JSONResponse('ID tidak valid', null, 400);

        $params = $this->req->getGet();
        $moduleId = !empty($params['moduleId']) ? $this->resolveId($params['moduleId']) : null;
        $pageId   = !empty($params['pageId'])   ? $this->resolveId($params['pageId'])   : null;

        $builder = $this->db()->table('tickets')
            ->select('tickets.*, creator.full_name as creator_name, assignee.full_name as assignee_name, pages.name as page_name')
            ->join('pages', 'pages.id = tickets.page_id', 'left')
            ->join('users as creator', 'creator.id = tickets.creator_id', 'left')
            ->join('users as assignee', 'assignee.id = tickets.assignee_id', 'left')
            ->join('modules', 'modules.id = pages.module_id', 'left')
            ->where('tickets.status !=', Enums::TICKET_STATUS_REJECTED)
            ->where('tickets.active', 0)
            ->where('pages.active', 0)
            ->where('modules.active', 0);

        if ($pageId) {
            $builder->where('tickets.page_id', $pageId);
        } elseif ($moduleId) {
            $builder->where('modules.id', $moduleId);
        } else {
            $builder->where('modules.master_project_id', $projectId);
        }

        $tickets = $builder->orderBy('tickets.priority', 'DESC')
            ->orderBy('tickets.created_at', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [
            'open'        => [],
            'in_progress' => [],
            'resolved'    => [],
            'closed'      => [],
        ];

        foreach ($tickets as $t) {
            $item = [
                'id'            => $this->api->encryptId($t['id']),
                'title'         => $t['title'],
                'status'        => (int) $t['status'],
                'status_name'   => Enums::ticketStatusName((int) $t['status']),
                'type'          => (int) $t['type'],
                'type_name'     => Enums::ticketTypeName((int) $t['type']),
                'priority'      => (int) $t['priority'],
                'priority_name' => Enums::priorityName((int) $t['priority']),
                'creator_name'  => $t['creator_name'],
                'assignee_name' => $t['assignee_name'],
                'page_name'     => $t['page_name'] ?? null,
                'created_at'    => $t['created_at'],
            ];

            $status = (int) $t['status'];
            if ($status === Enums::TICKET_STATUS_OPEN) {
                $grouped['open'][] = $item;
            } elseif ($status === Enums::TICKET_STATUS_APPROVED || $status === Enums::TICKET_STATUS_IN_PROGRESS) {
                $grouped['in_progress'][] = $item;
            } elseif ($status === Enums::TICKET_STATUS_RESOLVED) {
                $grouped['resolved'][] = $item;
            } elseif ($status === Enums::TICKET_STATUS_CLOSED) {
                $grouped['closed'][] = $item;
            }
        }

        return $this->JSONResponse('OK', $grouped, 200);
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
            ->where('tickets.active', 0)
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

    public function get_available_blueprint_modules(): ResponseInterface
    {
        $modules = $this->db()->table('blueprint_modules')
            ->select('blueprint_modules.*, blueprints.name as blueprint_name')
            ->join('blueprints', 'blueprints.id = blueprint_modules.blueprint_id', 'left')
            ->where('blueprint_modules.active', 0)
            ->where('blueprints.active', 0)
            ->orderBy('blueprints.name', 'ASC')
            ->orderBy('blueprint_modules.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($modules as $m) {
            $bpId = $m['blueprint_id'];
            if (!isset($grouped[$bpId])) {
                $grouped[$bpId] = [
                    'blueprint_id'   => $this->api->encryptId($bpId),
                    'blueprint_name' => $m['blueprint_name'] ?? 'Untitled Blueprint',
                    'modules'        => [],
                ];
            }
            $scenarioCount = $this->db()->table('blueprint_business_scenarios')->where('module_id', $m['id'])->where('active', 0)->countAllResults();
            $designPageCount = $this->db()->table('blueprint_design_pages')->where('module_id', $m['id'])->where('active', 0)->countAllResults();

            $grouped[$bpId]['modules'][] = [
                'id'              => $this->api->encryptId($m['id']),
                'name'            => $m['name'],
                'scenario_count'  => $scenarioCount,
                'design_page_count' => $designPageCount,
            ];
        }

        return $this->JSONResponse('OK', array_values($grouped), 200);
    }

    public function get_available_design_pages(): ResponseInterface
    {
        $params = $this->req->getGet();
        $encryptedModuleId = $params['module_id'] ?? null;
        $moduleBlueprintModuleId = null;

        if ($encryptedModuleId) {
            $moduleId = $this->resolveId($encryptedModuleId);
            if ($moduleId) {
                $module = $this->db()->table('modules')
                    ->select('blueprint_module_id')
                    ->where('id', $moduleId)
                    ->where('active', 0)
                    ->get()
                    ->getRowArray();
                $moduleBlueprintModuleId = $module['blueprint_module_id'] ?? null;
            }
        }

        $builder = $this->db()->table('blueprint_design_pages as bdp')
            ->select('bdp.*, bm.name as blueprint_module_name, b.name as blueprint_name')
            ->join('blueprint_modules as bm', 'bm.id = bdp.module_id AND bm.active = 0', 'left')
            ->join('blueprints as b', 'b.id = bm.blueprint_id AND b.active = 0', 'left')
            ->where('bdp.active', 0);

        if ($moduleBlueprintModuleId) {
            $builder->where('bdp.module_id', $moduleBlueprintModuleId);
        }

        $designPages = $builder->orderBy('b.name', 'ASC')
            ->orderBy('bm.sort_order', 'ASC')
            ->orderBy('bdp.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($designPages as $dp) {
            $bmId = $dp['module_id'];
            if (!isset($grouped[$bmId])) {
                $grouped[$bmId] = [
                    'blueprint_module_id'  => $this->api->encryptId($bmId),
                    'blueprint_module_name' => $dp['blueprint_module_name'] ?? 'Untitled Module',
                    'blueprint_name'       => $dp['blueprint_name'] ?? 'Untitled Blueprint',
                    'design_pages'         => [],
                ];
            }
            $specCount = $this->db()->table('blueprint_page_specifications')
                ->where('design_page_id', $dp['id'])
                ->where('active', 0)
                ->countAllResults();

            $grouped[$bmId]['design_pages'][] = [
                'id'          => $this->api->encryptId($dp['id']),
                'title'       => $dp['title'],
                'description' => $dp['description'],
                'spec_count'  => $specCount,
            ];
        }

        return $this->JSONResponse('OK', array_values($grouped), 200);
    }
}
