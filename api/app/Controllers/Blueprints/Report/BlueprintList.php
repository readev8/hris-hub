<?php

namespace App\Controllers\Blueprints\Report;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class BlueprintList extends BaseApi
{
    public function get_list(): ResponseInterface
    {
        $params = $this->req->getGet();
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($params['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        $status = $params['status'] ?? '';

        $builder = $this->db()->table('blueprints')
            ->select('blueprints.*, p.name as improvement_name, creator.full_name as creator_name')
            ->join('projects as p', 'p.id = blueprints.improvement_id', 'left')
            ->join('users as creator', 'creator.id = blueprints.created_by', 'left');

        if ($status !== '') $builder->where('blueprints.status', (int) $status);

        $total = $builder->countAllResults(false);
        $rows = $builder->orderBy('blueprints.id', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        foreach ($rows as &$r) {
            $r['id'] = $this->api->encryptId($r['id']);
            $r['status_name'] = \App\Config\Enums::projectStatusName($r['status']);
            $moduleCount = $this->db()->table('blueprint_modules')->where('blueprint_id', $r['id'])->countAllResults();
            $r['module_count'] = $moduleCount;
        }

        return $this->JSONResponse('OK', [
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ], 200);
    }

    public function get_pending_approvals(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $role = $this->getCurrentUserRole();
        if (!$role) return $this->JSONResponse('User tidak ditemukan', null, 404);

        $blueprints = [];

        if ($role === \App\Config\Enums::IT_MANAGER || $role === \App\Config\Enums::ADMIN) {
            $draft = $this->db()->table('blueprints')
                ->select('blueprints.*, p.name as improvement_name, creator.full_name as creator_name')
                ->join('projects as p', 'p.id = blueprints.improvement_id', 'left')
                ->join('users as creator', 'creator.id = blueprints.created_by', 'left')
                ->where('blueprints.status', \App\Config\Enums::PROJECT_STATUS_DRAFT)
                ->orderBy('blueprints.id', 'DESC')
                ->get()
                ->getResultArray();
            $blueprints = array_merge($blueprints, $draft);
        }

        if ($role === \App\Config\Enums::DEPT_HEAD || $role === \App\Config\Enums::ADMIN) {
            $pending = $this->db()->table('blueprints')
                ->select('blueprints.*, p.name as improvement_name, creator.full_name as creator_name')
                ->join('projects as p', 'p.id = blueprints.improvement_id', 'left')
                ->join('users as creator', 'creator.id = blueprints.created_by', 'left')
                ->where('blueprints.status', \App\Config\Enums::PROJECT_STATUS_PENDING)
                ->orderBy('blueprints.id', 'DESC')
                ->get()
                ->getResultArray();
            $blueprints = array_merge($blueprints, $pending);
        }

        foreach ($blueprints as &$r) {
            $r['id'] = $this->api->encryptId($r['id']);
            $r['status_name'] = \App\Config\Enums::projectStatusName($r['status']);
        }

        return $this->JSONResponse('OK', $blueprints, 200);
    }
}
