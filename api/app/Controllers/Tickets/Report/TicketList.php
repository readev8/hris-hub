<?php

namespace App\Controllers\Tickets\Report;

use Config\Tables;
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
        $overdue = $params['overdue'] ?? '';
        $sort = $params['sort'] ?? Tables::TICKETS . '.id';
        $order = strtoupper($params['order'] ?? 'DESC');

        $allowedSort = [Tables::TICKETS . '.id', 'title', 'status', 'priority', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSort)) {
            $sort = Tables::TICKETS . '.id';
        }
        $order = in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';

        $builder = $this->db()->table(Tables::TICKETS)
            ->select(Tables::TICKETS . '.*, creator.full_name as creator_name, assignee.full_name as assignee_name')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::TICKETS . '.creator_id', 'left')
            ->join(Tables::USERS . ' as assignee', 'assignee.id = ' . Tables::TICKETS . '.assignee_id', 'left')
            ->where(Tables::TICKETS . '.active', 0);

        if (!empty($search)) {
            $builder->groupStart()
                ->like(Tables::TICKETS . '.title', $search)
                ->orLike(Tables::TICKETS . '.tracking_code', $search)
                ->groupEnd();
        }
        if ($status !== '') {
            $builder->where(Tables::TICKETS . '.status', (int) $status);
        }
        if ($type !== '') {
            $builder->where(Tables::TICKETS . '.type', (int) $type);
        }
        if ($priority !== '') {
            $builder->where(Tables::TICKETS . '.priority', (int) $priority);
        }
        if ($overdue === '1') {
            $builder->where(Tables::TICKETS . '.due_date IS NOT NULL', null, false)
                   ->where(Tables::TICKETS . '.due_date <', date('Y-m-d'))
                   ->whereNotIn(Tables::TICKETS . '.status', [
                       \App\Config\Enums::TICKET_STATUS_RESOLVED,
                       \App\Config\Enums::TICKET_STATUS_CLOSED,
                       \App\Config\Enums::TICKET_STATUS_REJECTED,
                   ]);
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

        $rows = $this->db()->table(Tables::TICKETS)
            ->select(Tables::TICKETS . '.*, creator.full_name as creator_name, assignee.full_name as assignee_name')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::TICKETS . '.creator_id', 'left')
            ->join(Tables::USERS . ' as assignee', 'assignee.id = ' . Tables::TICKETS . '.assignee_id', 'left')
            ->where(Tables::TICKETS . '.active', 0)
            ->where(Tables::TICKETS . '.creator_id', $userId)
            ->orWhere(Tables::TICKETS . '.assignee_id', $userId)
            ->orderBy(Tables::TICKETS . '.id', 'DESC')
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

        $rows = $this->db()->table(Tables::TICKETS)
            ->select(Tables::TICKETS . '.*, creator.full_name as creator_name')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::TICKETS . '.creator_id', 'left')
            ->where(Tables::TICKETS . '.active', 0)
            ->where(Tables::TICKETS . '.status', \App\Config\Enums::TICKET_STATUS_OPEN)
            ->orderBy(Tables::TICKETS . '.id', 'DESC')
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

    public function get_my_taken_tickets(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        $params = $this->req->getGet();
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($params['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        $search = $params['search'] ?? '';
        $status = $params['status'] ?? '';
        $overdue = $params['overdue'] ?? '';

        $builder = $this->db()->table(Tables::TICKETS)
            ->select(Tables::TICKETS . '.*, creator.full_name as creator_name, assignee.full_name as assignee_name')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::TICKETS . '.creator_id', 'left')
            ->join(Tables::USERS . ' as assignee', 'assignee.id = ' . Tables::TICKETS . '.assignee_id', 'left')
            ->where(Tables::TICKETS . '.active', 0)
            ->where(Tables::TICKETS . '.assignee_id', $userId);

        if (!empty($search)) {
            $builder->groupStart()
                ->like(Tables::TICKETS . '.title', $search)
                ->orLike(Tables::TICKETS . '.tracking_code', $search)
                ->groupEnd();
        }
        if ($status !== '') {
            $builder->where(Tables::TICKETS . '.status', (int) $status);
        }
        if ($overdue === '1') {
            $builder->where(Tables::TICKETS . '.due_date IS NOT NULL', null, false)
                   ->where(Tables::TICKETS . '.due_date <', date('Y-m-d'))
                   ->whereNotIn(Tables::TICKETS . '.status', [
                       \App\Config\Enums::TICKET_STATUS_RESOLVED,
                       \App\Config\Enums::TICKET_STATUS_CLOSED,
                       \App\Config\Enums::TICKET_STATUS_REJECTED,
                   ]);
        }

        $total = $builder->countAllResults(false);
        $rows = $builder->orderBy(Tables::TICKETS . '.id', 'DESC')
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
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ], 200);
    }
}
