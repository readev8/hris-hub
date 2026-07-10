<?php

namespace App\Controllers\Improvements\Report;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class ProjectList extends BaseApi
{
    public function get_list(): ResponseInterface
    {
        $params = $this->req->getGet();
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($params['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        $status = $params['status'] ?? '';
        $priority = $params['priority'] ?? '';

        $builder = $this->db()->table('projects')
            ->select('projects.*, creator.full_name as creator_name')
            ->join('users as creator', 'creator.id = projects.created_by', 'left');

        if ($status !== '') $builder->where('projects.status', (int) $status);
        if ($priority !== '') $builder->where('projects.priority', (int) $priority);

        $total = $builder->countAllResults(false);
        $rows = $builder->orderBy('projects.id', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        foreach ($rows as &$r) {
            $r['id'] = $this->api->encryptId($r['id']);
            $r['status_name'] = \App\Config\Enums::projectStatusName($r['status']);
            $r['priority_name'] = \App\Config\Enums::priorityName($r['priority']);
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

        $user = $this->db()->table('users')->where('id', $userId)->get()->getRowArray();
        if (!$user) return $this->JSONResponse('User tidak ditemukan', null, 404);

        $role = (int) $user['role'];
        $projects = [];

        if ($role === \App\Config\Enums::IT_MANAGER || $role === \App\Config\Enums::ADMIN) {
            $draft = $this->db()->table('projects')
                ->select('projects.*, creator.full_name as creator_name')
                ->join('users as creator', 'creator.id = projects.created_by', 'left')
                ->where('projects.status', \App\Config\Enums::PROJECT_STATUS_DRAFT)
                ->orderBy('projects.id', 'DESC')
                ->get()
                ->getResultArray();
            $projects = array_merge($projects, $draft);
        }

        if ($role === \App\Config\Enums::DEPT_HEAD || $role === \App\Config\Enums::ADMIN) {
            $pending = $this->db()->table('projects')
                ->select('projects.*, creator.full_name as creator_name')
                ->join('users as creator', 'creator.id = projects.created_by', 'left')
                ->where('projects.status', \App\Config\Enums::PROJECT_STATUS_PENDING)
                ->orderBy('projects.id', 'DESC')
                ->get()
                ->getResultArray();
            $projects = array_merge($projects, $pending);
        }

        foreach ($projects as &$r) {
            $r['id'] = $this->api->encryptId($r['id']);
            $r['status_name'] = \App\Config\Enums::projectStatusName($r['status']);
            $r['priority_name'] = \App\Config\Enums::priorityName($r['priority']);
        }

        return $this->JSONResponse('OK', $projects, 200);
    }
}
