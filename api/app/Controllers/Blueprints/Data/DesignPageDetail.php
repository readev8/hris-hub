<?php

namespace App\Controllers\Blueprints\Data;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class DesignPageDetail extends BaseApi
{
    public function get_detail(string $encryptedDesignPageId): ResponseInterface
    {
        $designPageId = $this->resolveId($encryptedDesignPageId);
        if (!$designPageId) return $this->JSONResponse('ID tidak valid', null, 400);

        $designPage = $this->db()->table('blueprint_design_pages')
            ->select('blueprint_design_pages.*, m.name as module_name, m.blueprint_id, b.name as blueprint_name')
            ->join('blueprint_modules as m', 'm.id = blueprint_design_pages.module_id', 'left')
            ->join('blueprints as b', 'b.id = m.blueprint_id', 'left')
            ->where('blueprint_design_pages.id', $designPageId)
            ->where('blueprint_design_pages.active', 0)
            ->where('m.active', 0)
            ->where('b.active', 0)
            ->get()
            ->getRowArray();

        if (!$designPage) {
            return $this->JSONResponse('Design page tidak ditemukan', null, 404);
        }

        $designPage['id_encrypted'] = $this->api->encryptId($designPage['id']);
        $designPage['blueprint_id_encrypted'] = $this->api->encryptId($designPage['blueprint_id']);

        $specs = $this->db()->table('blueprint_page_specifications')
            ->where('design_page_id', $designPageId)
            ->where('active', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        foreach ($specs as &$s) {
            $s['id_encrypted'] = $this->api->encryptId($s['id']);
        }
        $designPage['page_specifications'] = $specs;

        return $this->JSONResponse('OK', $designPage, 200);
    }
}
