<?php

namespace App\Controllers\Tickets\Data;

use Config\Tables;
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

        $builder = $this->db()->table(Tables::PAGES)
            ->select(Tables::PAGES . '.id, ' . Tables::PAGES . '.name as page_name, ' . Tables::MODULES . '.name as module_name, ' . Tables::MASTER_PROJECTS . '.name as project_name')
            ->join(Tables::MODULES, Tables::MODULES . '.id = ' . Tables::PAGES . '.module_id', 'left')
            ->join(Tables::MASTER_PROJECTS, Tables::MASTER_PROJECTS . '.id = ' . Tables::MODULES . '.master_project_id', 'left')
            ->where(Tables::PAGES . '.active', 0)
            ->where(Tables::MODULES . '.active', 0)
            ->where(Tables::MASTER_PROJECTS . '.active', 0);

        if (!empty($search)) {
            $builder->groupStart()
                ->like(Tables::PAGES . '.name', $search)
                ->orLike(Tables::MODULES . '.name', $search)
                ->orLike(Tables::MASTER_PROJECTS . '.name', $search)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $rows = $builder->orderBy(Tables::PAGES . '.name', 'ASC')
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
