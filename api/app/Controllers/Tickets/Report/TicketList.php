<?php

namespace App\Controllers\Tickets\Report;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class TicketList extends BaseApi
{
    public function get_list(): ResponseInterface
    {
        $params = $this->req->getGet();
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($params['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        $search = $params['search'] ?? '';
        $status = $params['status'] ?? '';
        $type = $params['type'] ?? '';
        $priority = $params['priority'] ?? '';
        $sort = $params['sort'] ?? 'tickets.id';
        $order = strtoupper($params['order'] ?? 'DESC');

        $allowedSort = ['tickets.id', 'title', 'status', 'priority', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'tickets.id';
        }
        $order = in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';

        $builder = $this->db()->table('tickets')
            ->select('tickets.*, creator.full_name as creator_name, assignee.full_name as assignee_name')
            ->join('users as creator', 'creator.id = tickets.creator_id', 'left')
            ->join('users as assignee', 'assignee.id = tickets.assignee_id', 'left');

        if (!empty($search)) {
            $builder->like('tickets.title', $search);
        }
        if ($status !== '') {
            $builder->where('tickets.status', (int) $status);
        }
        if ($type !== '') {
            $builder->where('tickets.type', (int) $type);
        }
        if ($priority !== '') {
            $builder->where('tickets.priority', (int) $priority);
        }

        $total = $builder->countAllResults(false);
        $rows = $builder->orderBy($sort, $order)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        foreach ($rows as &$r) {
            $r['id'] = $this->api->encryptId($r['id']);
            $r['status_name'] = \App\Config\Enums::ticketStatusName($r['status']);
            $r['type_name'] = \App\Config\Enums::ticketTypeName($r['type']);
            $r['priority_name'] = \App\Config\Enums::priorityName($r['priority']);
        }

        return $this->JSONResponse('OK', [
            'data'  => $rows,
            'total' => $total,
            'page'  => $page,
            'per_page' => $perPage,
        ], 200);
    }

    public function get_my_tickets(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        $rows = $this->db()->table('tickets')
            ->select('tickets.*, creator.full_name as creator_name, assignee.full_name as assignee_name')
            ->join('users as creator', 'creator.id = tickets.creator_id', 'left')
            ->join('users as assignee', 'assignee.id = tickets.assignee_id', 'left')
            ->where('tickets.creator_id', $userId)
            ->orWhere('tickets.assignee_id', $userId)
            ->orderBy('tickets.id', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        foreach ($rows as &$r) {
            $r['id'] = $this->api->encryptId($r['id']);
            $r['status_name'] = \App\Config\Enums::ticketStatusName($r['status']);
            $r['type_name'] = \App\Config\Enums::ticketTypeName($r['type']);
            $r['priority_name'] = \App\Config\Enums::priorityName($r['priority']);
        }

        return $this->JSONResponse('OK', $rows, 200);
    }

    public function get_pending_approval(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        $rows = $this->db()->table('tickets')
            ->select('tickets.*, creator.full_name as creator_name')
            ->join('users as creator', 'creator.id = tickets.creator_id', 'left')
            ->where('tickets.status', \App\Config\Enums::TICKET_STATUS_OPEN)
            ->orderBy('tickets.id', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        foreach ($rows as &$r) {
            $r['id'] = $this->api->encryptId($r['id']);
            $r['status_name'] = \App\Config\Enums::ticketStatusName($r['status']);
            $r['type_name'] = \App\Config\Enums::ticketTypeName($r['type']);
            $r['priority_name'] = \App\Config\Enums::priorityName($r['priority']);
        }

        return $this->JSONResponse('OK', $rows, 200);
    }
}
