<?php

namespace App\Controllers\MasterProjects\Data;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class ModuleDetail extends BaseApi
{
    public function get_detail(string $encryptedModuleId): ResponseInterface
    {
        $moduleId = $this->resolveId($encryptedModuleId);
        if (!$moduleId) return $this->JSONResponse('ID tidak valid', null, 400);

        $module = $this->db()->table('modules')
            ->where('modules.id', $moduleId)
            ->where('modules.active', 0)
            ->get()
            ->getRowArray();

        if (!$module) return $this->JSONResponse('Module tidak ditemukan', null, 404);

        $blueprintModules = $this->db()->table('module_blueprint_modules as mbm')
            ->select('bm.id as bm_id, bm.name as bm_name, b.id as bp_id, b.name as bp_name')
            ->join('blueprint_modules as bm', 'bm.id = mbm.blueprint_module_id AND bm.active = 0')
            ->join('blueprints as b', 'b.id = bm.blueprint_id AND b.active = 0')
            ->where('mbm.module_id', $moduleId)
            ->get()
            ->getResultArray();
        foreach ($blueprintModules as &$bm) {
            $bm['bm_id'] = $this->api->encryptId($bm['bm_id']);
            $bm['bp_id'] = $this->api->encryptId($bm['bp_id']);
        }
        unset($bm);

        $pages = $this->db()->table('pages')
            ->select('pages.*, bdp.title as blueprint_design_page_title')
            ->join('blueprint_design_pages as bdp', 'bdp.id = pages.blueprint_design_page_id AND bdp.active = 0', 'left')
            ->where('pages.module_id', $moduleId)
            ->where('pages.active', 0)
            ->orderBy('pages.sort_order', 'ASC')
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

            $pageSpecs = null;
            if (!empty($p['blueprint_design_page_id'])) {
                $specsRows = $this->db()->table('blueprint_page_specifications')
                    ->where('design_page_id', $p['blueprint_design_page_id'])
                    ->where('active', 0)
                    ->orderBy('sort_order', 'ASC')
                    ->get()
                    ->getResultArray();

                $pageSpecs = [];
                foreach ($specsRows as $spec) {
                    $pageSpecs[] = [
                        'field_name'   => $spec['field_name'],
                        'data'         => $spec['data'],
                        'objective'    => $spec['objective'],
                        'initial_data' => $spec['initial_data'],
                        'condition'    => $spec['condition'],
                        'validation'   => $spec['validation'],
                        'input_display'=> $spec['input_display'],
                        'datatype'     => $spec['datatype'],
                        'control_type' => $spec['control_type'],
                        'ux'           => $spec['ux'],
                    ];
                }
            }

            $pageList[] = [
                'id'            => $this->api->encryptId($p['id']),
                'name'          => $p['name'],
                'url_path'      => $p['url_path'],
                'description'   => $p['description'],
                'sort_order'    => (int) $p['sort_order'],
                'bug_total'     => $bugTotal,
                'bug_open'      => $bugOpen,
                'bug_resolved'  => $bugResolved,
                'blueprint_design_page_id'    => $p['blueprint_design_page_id'] ? $this->api->encryptId($p['blueprint_design_page_id']) : null,
                'blueprint_design_page_title' => $p['blueprint_design_page_title'] ?? null,
                'blueprint_page_specs'        => $pageSpecs,
            ];
        }

        return $this->JSONResponse('OK', [
            'id'                    => $this->api->encryptId($module['id']),
            'name'                  => $module['name'],
            'description'           => $module['description'],
            'sort_order'            => (int) $module['sort_order'],
            'blueprint_modules'     => $blueprintModules,
            'pages'                 => $pageList,
        ], 200);
    }
}
