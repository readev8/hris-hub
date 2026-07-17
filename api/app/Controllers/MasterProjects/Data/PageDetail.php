<?php

namespace App\Controllers\MasterProjects\Data;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class PageDetail extends BaseApi
{
    public function get_detail(string $encryptedPageId): ResponseInterface
    {
        $pageId = $this->resolveId($encryptedPageId);
        if (!$pageId) return $this->JSONResponse('ID tidak valid', null, 400);

        $page = $this->db()->table('pages')
            ->select('pages.*, bdp.title as blueprint_design_page_title')
            ->join('blueprint_design_pages as bdp', 'bdp.id = pages.blueprint_design_page_id AND bdp.active = 0', 'left')
            ->where('pages.id', $pageId)
            ->where('pages.active', 0)
            ->get()
            ->getRowArray();

        if (!$page) return $this->JSONResponse('Page tidak ditemukan', null, 404);

        $bugTotal = $this->db()->table('tickets')
            ->where('page_id', $page['id'])
            ->where('type', Enums::TICKET_TYPE_BUG)
            ->where('active', 0)
            ->countAllResults();

        $bugOpen = $this->db()->table('tickets')
            ->where('page_id', $page['id'])
            ->where('type', Enums::TICKET_TYPE_BUG)
            ->whereIn('status', [Enums::TICKET_STATUS_OPEN, Enums::TICKET_STATUS_APPROVED, Enums::TICKET_STATUS_IN_PROGRESS])
            ->where('active', 0)
            ->countAllResults();

        $bugResolved = $this->db()->table('tickets')
            ->where('page_id', $page['id'])
            ->where('type', Enums::TICKET_TYPE_BUG)
            ->whereIn('status', [Enums::TICKET_STATUS_RESOLVED, Enums::TICKET_STATUS_CLOSED])
            ->where('active', 0)
            ->countAllResults();

        $pageSpecs = null;
        if (!empty($page['blueprint_design_page_id'])) {
            $specsRows = $this->db()->table('blueprint_page_specifications')
                ->where('design_page_id', $page['blueprint_design_page_id'])
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

        return $this->JSONResponse('OK', [
            'id'            => $this->api->encryptId($page['id']),
            'name'          => $page['name'],
            'url_path'      => $page['url_path'],
            'description'   => $page['description'],
            'sort_order'    => (int) $page['sort_order'],
            'module_id'     => $this->api->encryptId($page['module_id']),
            'bug_total'     => $bugTotal,
            'bug_open'      => $bugOpen,
            'bug_resolved'  => $bugResolved,
            'blueprint_design_page_id'    => $page['blueprint_design_page_id'] ? $this->api->encryptId($page['blueprint_design_page_id']) : null,
            'blueprint_design_page_title' => $page['blueprint_design_page_title'] ?? null,
            'blueprint_page_specs'        => $pageSpecs,
        ], 200);
    }
}
