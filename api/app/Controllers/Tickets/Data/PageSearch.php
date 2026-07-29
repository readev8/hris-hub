<?php

namespace App\Controllers\Tickets\Data;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class PageSearch extends BaseApi
{
    public function search(): ResponseInterface
    {
        $params = $this->req->getGet();
        $search = trim($params['search'] ?? '');
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, min(50, (int) ($params['per_page'] ?? 15)));
        $offset = ($page - 1) * $perPage;

        $builder = $this->db()->table('pages')
            ->select('pages.id, pages.name as page_name, modules.name as module_name, master_projects.name as project_name')
            ->join('modules', 'modules.id = pages.module_id', 'left')
            ->join('master_projects', 'master_projects.id = modules.master_project_id', 'left')
            ->where('pages.active', 0)
            ->where('modules.active', 0)
            ->where('master_projects.active', 0);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('pages.name', $search)
                ->orLike('modules.name', $search)
                ->orLike('master_projects.name', $search)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $rows = $builder->orderBy('pages.name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['page_id'] = $this->api->encryptId($row['id']);
            unset($row['id']);
        }

        return $this->JSONResponse('OK', [
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ], 200);
    }
}
