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
            ->where('id', $moduleId)
            ->get()
            ->getRowArray();

        if (!$module) return $this->JSONResponse('Module tidak ditemukan', null, 404);

        $pages = $this->db()->table('pages')
            ->where('module_id', $moduleId)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $pageList = [];
        foreach ($pages as $p) {
            $bugTotal = $this->db()->table('tickets')
                ->where('page_id', $p['id'])
                ->where('type', Enums::TICKET_TYPE_BUG)
                ->countAllResults();

            $bugOpen = $this->db()->table('tickets')
                ->where('page_id', $p['id'])
                ->where('type', Enums::TICKET_TYPE_BUG)
                ->whereIn('status', [Enums::TICKET_STATUS_OPEN, Enums::TICKET_STATUS_APPROVED, Enums::TICKET_STATUS_IN_PROGRESS])
                ->countAllResults();

            $bugResolved = $this->db()->table('tickets')
                ->where('page_id', $p['id'])
                ->where('type', Enums::TICKET_TYPE_BUG)
                ->whereIn('status', [Enums::TICKET_STATUS_RESOLVED, Enums::TICKET_STATUS_CLOSED])
                ->countAllResults();

            $pageList[] = [
                'id'            => $this->api->encryptId($p['id']),
                'name'          => $p['name'],
                'url_path'      => $p['url_path'],
                'description'   => $p['description'],
                'sort_order'    => (int) $p['sort_order'],
                'bug_total'     => $bugTotal,
                'bug_open'      => $bugOpen,
                'bug_resolved'  => $bugResolved,
            ];
        }

        return $this->JSONResponse('OK', [
            'id'          => $this->api->encryptId($module['id']),
            'name'        => $module['name'],
            'description' => $module['description'],
            'sort_order'  => (int) $module['sort_order'],
            'pages'       => $pageList,
        ], 200);
    }
}
