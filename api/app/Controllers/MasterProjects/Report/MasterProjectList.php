<?php

namespace App\Controllers\MasterProjects\Report;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class MasterProjectList extends BaseApi
{
    public function get_list(): ResponseInterface
    {
        $projects = $this->db()->table(Tables::MASTER_PROJECTS)
            ->select(Tables::MASTER_PROJECTS . '.*, ' . Tables::USERS . '.full_name as creator_name')
            ->join(Tables::USERS, Tables::USERS . '.id = ' . Tables::MASTER_PROJECTS . '.created_by', 'left')
            ->where(Tables::MASTER_PROJECTS . '.active', 0)
            ->orderBy(Tables::MASTER_PROJECTS . '.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($projects as $p) {
            $moduleCount = $this->db()->table(Tables::MODULES)
                ->where('master_project_id', $p['id'])
                ->where('active', 0)
                ->countAllResults();

            $bugCount = $this->db()->table(Tables::TICKETS)
                ->join(Tables::PAGES, Tables::PAGES . '.id = ' . Tables::TICKETS . '.page_id')
                ->join(Tables::MODULES, Tables::MODULES . '.id = ' . Tables::PAGES . '.module_id')
                ->where(Tables::MODULES . '.master_project_id', $p['id'])
                ->where(Tables::TICKETS . '.type', Enums::TICKET_TYPE_BUG)
                ->where(Tables::TICKETS . '.active', 0)
                ->where(Tables::PAGES . '.active', 0)
                ->where(Tables::MODULES . '.active', 0)
                ->countAllResults();

            $bugOpen = $this->db()->table(Tables::TICKETS)
                ->join(Tables::PAGES, Tables::PAGES . '.id = ' . Tables::TICKETS . '.page_id')
                ->join(Tables::MODULES, Tables::MODULES . '.id = ' . Tables::PAGES . '.module_id')
                ->where(Tables::MODULES . '.master_project_id', $p['id'])
                ->where(Tables::TICKETS . '.type', Enums::TICKET_TYPE_BUG)
                ->where(Tables::TICKETS . '.active', 0)
                ->where(Tables::PAGES . '.active', 0)
                ->where(Tables::MODULES . '.active', 0)
                ->whereIn(Tables::TICKETS . '.status', [Enums::TICKET_STATUS_OPEN, Enums::TICKET_STATUS_APPROVED, Enums::TICKET_STATUS_IN_PROGRESS])
                ->countAllResults();

            $bugClosed = $this->db()->table(Tables::TICKETS)
                ->join(Tables::PAGES, Tables::PAGES . '.id = ' . Tables::TICKETS . '.page_id')
                ->join(Tables::MODULES, Tables::MODULES . '.id = ' . Tables::PAGES . '.module_id')
                ->where(Tables::MODULES . '.master_project_id', $p['id'])
                ->where(Tables::TICKETS . '.type', Enums::TICKET_TYPE_BUG)
                ->where(Tables::TICKETS . '.active', 0)
                ->where(Tables::PAGES . '.active', 0)
                ->where(Tables::MODULES . '.active', 0)
                ->whereIn(Tables::TICKETS . '.status', [Enums::TICKET_STATUS_RESOLVED, Enums::TICKET_STATUS_CLOSED])
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
        $projects = $this->db()->table(Tables::MASTER_PROJECTS)
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

        $modules = $this->db()->table(Tables::MODULES)
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

        $pages = $this->db()->table(Tables::PAGES)
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

        $builder = $this->db()->table(Tables::TICKETS)
            ->select(Tables::TICKETS . '.*, creator.full_name as creator_name, assignee.full_name as assignee_name, ' . Tables::PAGES . '.name as page_name')
            ->join(Tables::PAGES, Tables::PAGES . '.id = ' . Tables::TICKETS . '.page_id', 'left')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::TICKETS . '.creator_id', 'left')
            ->join(Tables::USERS . ' as assignee', 'assignee.id = ' . Tables::TICKETS . '.assignee_id', 'left')
            ->join(Tables::MODULES, Tables::MODULES . '.id = ' . Tables::PAGES . '.module_id', 'left')
            ->where(Tables::TICKETS . '.status !=', Enums::TICKET_STATUS_REJECTED)
            ->where(Tables::TICKETS . '.active', 0)
            ->where(Tables::PAGES . '.active', 0)
            ->where(Tables::MODULES . '.active', 0);

        if ($pageId) {
            $builder->where(Tables::TICKETS . '.page_id', $pageId);
        } elseif ($moduleId) {
            $builder->where(Tables::MODULES . '.id', $moduleId);
        } else {
            $builder->where(Tables::MODULES . '.master_project_id', $projectId);
        }

        $tickets = $builder->orderBy(Tables::TICKETS . '.priority', 'DESC')
            ->orderBy(Tables::TICKETS . '.created_at', 'ASC')
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

        $bugs = $this->db()->table(Tables::TICKETS)
            ->select(Tables::TICKETS . '.*, creator.full_name as creator_name')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::TICKETS . '.creator_id', 'left')
            ->where(Tables::TICKETS . '.page_id', $pageId)
            ->where(Tables::TICKETS . '.type', Enums::TICKET_TYPE_BUG)
            ->where(Tables::TICKETS . '.active', 0)
            ->orderBy(Tables::TICKETS . '.created_at', 'DESC')
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
        $modules = $this->db()->table(Tables::BLUEPRINT_MODULES)
            ->select(Tables::BLUEPRINT_MODULES . '.*, ' . Tables::BLUEPRINTS . '.name as blueprint_name')
            ->join(Tables::BLUEPRINTS, Tables::BLUEPRINTS . '.id = ' . Tables::BLUEPRINT_MODULES . '.blueprint_id', 'left')
            ->where(Tables::BLUEPRINT_MODULES . '.active', 0)
            ->where(Tables::BLUEPRINTS . '.active', 0)
            ->orderBy(Tables::BLUEPRINTS . '.name', 'ASC')
            ->orderBy(Tables::BLUEPRINT_MODULES . '.sort_order', 'ASC')
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
            $scenarioCount = $this->db()->table(Tables::BLUEPRINT_BUSINESS_SCENARIOS)->where('module_id', $m['id'])->where('active', 0)->countAllResults();
            $designPageCount = $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES)->where('module_id', $m['id'])->where('active', 0)->countAllResults();

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
        $moduleBmIds = [];

        if ($encryptedModuleId) {
            $moduleId = $this->resolveId($encryptedModuleId);
            if ($moduleId) {
                $assignedBmIds = $this->db()->table(Tables::MODULE_BLUEPRINT_MODULES)
                    ->select('blueprint_module_id')
                    ->where('module_id', $moduleId)
                    ->get()
                    ->getResultArray();
                $moduleBmIds = array_column($assignedBmIds, 'blueprint_module_id');
            }
        }

        $builder = $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES . ' as bdp')
            ->select('bdp.*, bm.name as blueprint_module_name, b.name as blueprint_name')
            ->join(Tables::BLUEPRINT_MODULES . ' as bm', 'bm.id = bdp.module_id AND bm.active = 0', 'left')
            ->join(Tables::BLUEPRINTS . ' as b', 'b.id = bm.blueprint_id AND b.active = 0', 'left')
            ->where('bdp.active', 0);

        if (!empty($moduleBmIds)) {
            $builder->whereIn('bdp.module_id', $moduleBmIds);
        } elseif ($encryptedModuleId) {
            return $this->JSONResponse('OK', [], 200);
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
            $specCount = $this->db()->table(Tables::BLUEPRINT_PAGE_SPECIFICATIONS)
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
