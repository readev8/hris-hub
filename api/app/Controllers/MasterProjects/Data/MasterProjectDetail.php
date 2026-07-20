<?php

namespace App\Controllers\MasterProjects\Data;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class MasterProjectDetail extends BaseApi
{
    public function get_detail(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $project = $this->db()->table('master_projects')
            ->select('master_projects.*, users.full_name as creator_name')
            ->join('users', 'users.id = master_projects.created_by', 'left')
            ->where('master_projects.id', $id)
            ->where('master_projects.active', 0)
            ->get()
            ->getRowArray();

        if (!$project) return $this->JSONResponse('Project tidak ditemukan', null, 404);

        $modules = $this->db()->table('modules')
            ->where('modules.master_project_id', $id)
            ->where('modules.active', 0)
            ->orderBy('modules.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $moduleIds = array_column($modules, 'id');
        $blueprintModulesByModule = [];
        if (!empty($moduleIds)) {
            $junctionRows = $this->db()->table('module_blueprint_modules as mbm')
                ->select('mbm.module_id, bm.id as bm_id, bm.name as bm_name, b.id as bp_id, b.name as bp_name')
                ->join('blueprint_modules as bm', 'bm.id = mbm.blueprint_module_id AND bm.active = 0')
                ->join('blueprints as b', 'b.id = bm.blueprint_id AND b.active = 0')
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
            $pages = $this->db()->table('pages')
                ->where('module_id', $m['id'])
                ->where('active', 0)
                ->orderBy('sort_order', 'ASC')
                ->get()
                ->getResultArray();

            $pageList = [];
            foreach ($pages as $p) {
                $bugTotal = $this->db()->table('tickets')
                    ->where('page_id', $p['id'])
                    ->where('type', Enums::TICKET_TYPE_BUG)
                    ->where('active', 0)
                    ->countAllResults();

                $bugOpen = $this->db()->table('tickets')
                    ->where('page_id', $p['id'])
                    ->where('type', Enums::TICKET_TYPE_BUG)
                    ->whereIn('status', [Enums::TICKET_STATUS_OPEN, Enums::TICKET_STATUS_APPROVED, Enums::TICKET_STATUS_IN_PROGRESS])
                    ->where('active', 0)
                    ->countAllResults();

                $bugResolved = $this->db()->table('tickets')
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
