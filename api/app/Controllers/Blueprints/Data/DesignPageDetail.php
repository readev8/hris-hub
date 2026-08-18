<?php

namespace App\Controllers\Blueprints\Data;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class DesignPageDetail extends BaseApi
{
    public function get_detail(string $encryptedDesignPageId): ResponseInterface
    {
        $designPageId = $this->resolveId($encryptedDesignPageId);
        if (!$designPageId) return $this->JSONResponse('ID tidak valid', null, 400);

        $designPage = $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES)
            ->select(Tables::BLUEPRINT_DESIGN_PAGES . '.*, m.name as module_name, m.blueprint_id, b.name as blueprint_name')
            ->join(Tables::BLUEPRINT_MODULES . ' as m', 'm.id = ' . Tables::BLUEPRINT_DESIGN_PAGES . '.module_id', 'left')
            ->join(Tables::BLUEPRINTS . ' as b', 'b.id = m.blueprint_id', 'left')
            ->where(Tables::BLUEPRINT_DESIGN_PAGES . '.id', $designPageId)
            ->where(Tables::BLUEPRINT_DESIGN_PAGES . '.active', 0)
            ->where('m.active', 0)
            ->where('b.active', 0)
            ->get()
            ->getRowArray();

        if (!$designPage) {
            return $this->JSONResponse('Design page tidak ditemukan', null, 404);
        }

        $designPage['id_encrypted'] = $this->api->encryptId($designPage['id']);
        $designPage['blueprint_id_encrypted'] = $this->api->encryptId($designPage['blueprint_id']);

        $specs = $this->db()->table(Tables::BLUEPRINT_PAGE_SPECIFICATIONS)
            ->where('design_page_id', $designPageId)
            ->where('active', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $specIds = [];
        foreach ($specs as $s) { $specIds[] = $s['id']; }

        $uxAttachments = [];
        if (!empty($specIds)) {
            $atts = $this->db()->table(Tables::BLUEPRINT_ATTACHMENTS)
                ->whereIn('section_id', $specIds)
                ->where('section_type', 'page_specification')
                ->where('active', 0)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
            foreach ($atts as $a) {
                $sid = $a['section_id'];
                if (!isset($uxAttachments[$sid])) {
                    $a['id'] = $this->api->encryptId($a['id']);
                    $uxAttachments[$sid] = $a;
                }
            }
        }

        foreach ($specs as &$s) {
            $s['id_encrypted'] = $this->api->encryptId($s['id']);
            $s['ux_attachment'] = $uxAttachments[$s['id']] ?? null;
        }
        $designPage['page_specifications'] = $specs;

        return $this->JSONResponse('OK', $designPage, 200);
    }
}
