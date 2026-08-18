<?php

namespace App\Controllers\MasterProjects\Data;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class MasterProjectDetail extends BaseApi
{
    public function get_detail(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $project = $this->db()->table(Tables::MASTER_PROJECTS)
            ->select(Tables::MASTER_PROJECTS . '.*, ' . Tables::USERS . '.full_name as creator_name')
            ->join(Tables::USERS, Tables::USERS . '.id = ' . Tables::MASTER_PROJECTS . '.created_by', 'left')
            ->where(Tables::MASTER_PROJECTS . '.id', $id)
            ->where(Tables::MASTER_PROJECTS . '.active', 0)
            ->get()
            ->getRowArray();

        if (!$project) return $this->JSONResponse('Project tidak ditemukan', null, 404);

        $modules = $this->db()->table(Tables::MODULES)
            ->where(Tables::MODULES . '.master_project_id', $id)
            ->where(Tables::MODULES . '.active', 0)
            ->orderBy(Tables::MODULES . '.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $moduleIds = array_column($modules, 'id');
        $blueprintModulesByModule = [];
        if (!empty($moduleIds)) {
            $junctionRows = $this->db()->table(Tables::MODULE_BLUEPRINT_MODULES . ' as mbm')
                ->select('mbm.module_id, bm.id as bm_id, bm.name as bm_name, b.id as bp_id, b.name as bp_name')
                ->join(Tables::BLUEPRINT_MODULES . ' as bm', 'bm.id = mbm.blueprint_module_id AND bm.active = 0')
                ->join(Tables::BLUEPRINTS . ' as b', 'b.id = bm.blueprint_id AND b.active = 0')
                ->whereIn('mbm.module_id', $moduleIds)
                ->get()
                ->getResultArray();
            foreach ($junctionRows as $jr) {
                $blueprintModulesByModule[$jr['module_id']][] = [
                    'bm_id'          => $this->api->encryptId($jr['bm_id']),
                    'bm_name'        => $jr['bm_name'],
                    'bp_id'          => $this->api->encryptId($jr['bp_id']),
                    'bp_name'        => $jr['bp_name'],
                ];
            }
        }

        $moduleList = [];
        foreach ($modules as $m) {
            $pages = $this->db()->table(Tables::PAGES)
                ->where('module_id', $m['id'])
                ->where('active', 0)
                ->orderBy('sort_order', 'ASC')
                ->get()
                ->getResultArray();

            $pageList = [];
            foreach ($pages as $p) {
                $bugTotal = $this->db()->table(Tables::TICKETS)
                    ->where('page_id', $p['id'])
                    ->where('type', Enums::TICKET_TYPE_BUG)
                    ->where('active', 0)
                    ->countAllResults();

                $bugOpen = $this->db()->table(Tables::TICKETS)
                    ->where('page_id', $p['id'])
                    ->where('type', Enums::TICKET_TYPE_BUG)
                    ->whereIn('status', [Enums::TICKET_STATUS_OPEN, Enums::TICKET_STATUS_APPROVED, Enums::TICKET_STATUS_IN_PROGRESS])
                    ->where('active', 0)
                    ->countAllResults();

                $bugResolved = $this->db()->table(Tables::TICKETS)
                    ->where('page_id', $p['id'])
                    ->where('type', Enums::TICKET_TYPE_BUG)
                    ->whereIn('status', [Enums::TICKET_STATUS_RESOLVED, Enums::TICKET_STATUS_CLOSED])
                    ->where('active', 0)
                    ->countAllResults();

                $pageList[] = [
                    'id'              => $this->api->encryptId($p['id']),
                    'name'            => $p['name'],
                    'url_path'        => $p['url_path'],
                    'description'     => $p['description'],
                    'sort_order'      => (int) $p['sort_order'],
                    'bug_total'       => $bugTotal,
                    'bug_open'        => $bugOpen,
                    'bug_resolved'    => $bugResolved,
                ];
            }

            $moduleList[] = [
                'id'                => $this->api->encryptId($m['id']),
                'name'              => $m['name'],
                'description'       => $m['description'],
                'sort_order'        => (int) $m['sort_order'],
                'blueprint_modules' => $blueprintModulesByModule[$m['id']] ?? [],
                'pages'             => $pageList,
            ];
        }

        return $this->JSONResponse('OK', [
            'id'          => $encryptedId,
            'name'        => $project['name'],
            'description' => $project['description'],
            'status'      => (int) $project['status'],
            'status_name' => $project['status'] ? 'Active' : 'Archived',
            'creator_name'=> $project['creator_name'],
            'created_at'  => $project['created_at'],
            'modules'     => $moduleList,
        ], 200);
    }
}
