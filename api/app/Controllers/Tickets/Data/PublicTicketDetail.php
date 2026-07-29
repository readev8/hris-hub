<?php

namespace App\Controllers\Tickets\Data;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class PublicTicketDetail extends BaseApi
{
    public function get_by_code(string $code): ResponseInterface
    {
        $code = trim($code);
        if (empty($code)) {
            return $this->JSONResponse('Kode pelacakan wajib diisi', null, 400);
        }

        $ticket = $this->db()->table('tickets')
            ->where('tracking_code', $code)
            ->where('is_anonymous', 1)
            ->where('active', 0)
            ->get()
            ->getRowArray();

        if (!$ticket) {
            return $this->JSONResponse('Ticket tidak ditemukan', null, 404);
        }

        $ticketId = (int) $ticket['id'];

        $allAttachments = $this->db()->table('ticket_attachments')
            ->select('id, ticket_id, comment_id, filename, stored_name, mime_type, file_size, created_at')
            ->where('ticket_id', $ticketId)
            ->where('active', 0)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        $commentAttachmentMap = [];
        $attachments = [];
        foreach ($allAttachments as $att) {
            $att['id'] = $this->api->encryptId($att['id']);
            if ($att['comment_id']) {
                $commentAttachmentMap[(int) $att['comment_id']][] = $att;
            } else {
                $attachments[] = $att;
            }
        }

        $comments = $this->db()->table('ticket_comments')
            ->select('ticket_comments.id, ticket_comments.content, ticket_comments.created_at, users.full_name')
            ->join('users', 'users.id = ticket_comments.user_id', 'left')
            ->where('ticket_comments.ticket_id', $ticketId)
            ->where('ticket_comments.active', 0)
            ->orderBy('ticket_comments.created_at', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($comments as &$c) {
            $rawId = (int) $c['id'];
            $c['id'] = $this->api->encryptId($rawId);
            $c['attachments'] = $commentAttachmentMap[$rawId] ?? [];
        }
        unset($c);

        $resolvedLog = $this->db()->table('audit_logs al')
            ->select('al.created_at as resolved_at, u.full_name as resolver_name')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.entity_type', 'ticket')
            ->where('al.entity_id', $ticketId)
            ->where('al.action', 'resolve_ticket')
            ->orderBy('al.created_at', 'DESC')
            ->get()
            ->getRowArray();

        $logs = $this->db()->table('audit_logs')
            ->select('action, new_values, created_at')
            ->where('entity_type', 'ticket')
            ->where('entity_id', $ticketId)
            ->whereIn('action', ['public_create', 'create_ticket', 'take_ticket', 'resolve_ticket', 'close_ticket', 'reopen_ticket'])
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        $actionLabels = [
            'public_create' => 'Ticket submitted',
            'create_ticket' => 'Ticket created',
            'take_ticket'   => 'Ticket taken',
            'resolve_ticket'=> 'Ticket resolved',
            'close_ticket'  => 'Ticket closed',
            'reopen_ticket' => 'Ticket reopened',
        ];

        $timeline = [];
        foreach ($logs as $log) {
            $newValues = json_decode($log['new_values'] ?? '{}', true);
            $statusId  = $newValues['status'] ?? null;
            $timeline[] = [
                'action'     => $actionLabels[$log['action']] ?? $log['action'],
                'status'     => $statusId !== null ? Enums::ticketStatusName((int) $statusId) : null,
                'status_id'  => $statusId,
                'created_at' => $log['created_at'],
            ];
        }

        $result = [
            'tracking_code'   => $ticket['tracking_code'],
            'title'           => $ticket['title'],
            'description'     => $ticket['description'],
            'type'            => (int) $ticket['type'],
            'type_name'       => Enums::ticketTypeName((int) $ticket['type']),
            'priority'        => (int) $ticket['priority'],
            'priority_name'   => Enums::priorityName((int) $ticket['priority']),
            'status'          => (int) $ticket['status'],
            'status_name'     => Enums::ticketStatusName((int) $ticket['status']),
            'created_at'      => $ticket['created_at'],
            'closed_at'       => $ticket['closed_at'] ?? null,
            'resolution_note' => $ticket['resolution_note'] ?? null,
            'resolved_at'     => $resolvedLog['resolved_at'] ?? null,
            'resolver_name'   => $resolvedLog['resolver_name'] ?? null,
            'rejection_note'  => $ticket['rejection_note'] ?? null,
            'attachments'     => $attachments,
            'comments'        => $comments,
            'timeline'        => $timeline,
        ];

        return $this->JSONResponse('OK', $result, 200);
    }

    public function close_by_code(string $code): ResponseInterface
    {
        $code = trim($code);
        if (empty($code)) {
            return $this->JSONResponse('Kode pelacakan wajib diisi', null, 400);
        }

        $ticket = $this->db()->table('tickets')
            ->where('tracking_code', $code)
            ->where('is_anonymous', 1)
            ->where('active', 0)
            ->get()
            ->getRowArray();

        if (!$ticket) {
            return $this->JSONResponse('Ticket tidak ditemukan', null, 404);
        }

        if ((int) $ticket['status'] !== Enums::TICKET_STATUS_RESOLVED) {
            return $this->JSONResponse('Hanya tiket dengan status Resolved yang bisa ditutup', null, 400);
        }

        $ticketId = (int) $ticket['id'];
        $now = date('Y-m-d H:i:s');

        $this->db()->table('tickets')->where('id', $ticketId)->update([
            'status'     => Enums::TICKET_STATUS_CLOSED,
            'closed_at'  => $now,
            'updated_at' => $now,
        ]);

        $this->db()->table('audit_logs')->insert([
            'entity_type' => 'ticket',
            'entity_id'   => $ticketId,
            'user_id'     => null,
            'action'      => 'close_ticket',
            'new_values'  => json_encode(['status' => Enums::TICKET_STATUS_CLOSED]),
            'created_at'  => $now,
        ]);

        return $this->JSONResponse('Ticket berhasil ditutup', null, 200);
    }

    public function get_list(): ResponseInterface
    {
        $params = $this->req->getGet();
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($params['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        $search = $params['search'] ?? '';
        $status = $params['status'] ?? '';
        $sort = $params['sort'] ?? 'tickets.id';
        $order = strtoupper($params['order'] ?? 'DESC');

        $allowedSort = ['tickets.id', 'title', 'status', 'priority', 'created_at'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'tickets.id';
        }
        $order = in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';

        $builder = $this->db()->table('tickets')
            ->select('tracking_code, title, status, type, priority, created_at')
            ->where('is_anonymous', 1)
            ->where('active', 0);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('title', $search)
                ->orLike('tracking_code', $search)
                ->groupEnd();
        }
        if ($status !== '') {
            $builder->where('status', (int) $status);
        }

        $total = $builder->countAllResults(false);
        $rows = $builder->orderBy($sort, $order)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($rows as $t) {
            $result[] = [
                'tracking_code' => $t['tracking_code'],
                'title'         => $t['title'],
                'status'        => (int) $t['status'],
                'status_name'   => Enums::ticketStatusName((int) $t['status']),
                'type'          => (int) $t['type'],
                'type_name'     => Enums::ticketTypeName((int) $t['type']),
                'priority'      => (int) $t['priority'],
                'priority_name' => Enums::priorityName((int) $t['priority']),
                'created_at'    => $t['created_at'],
            ];
        }

        return $this->JSONResponse('OK', [
            'data'     => $result,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ], 200);
    }

    public function get_batch_by_codes(): ResponseInterface
    {
        $params = $this->req->getGet();
        $codesParam = $params['codes'] ?? '';
        if (empty($codesParam)) {
            return $this->JSONResponse('Parameter codes wajib diisi', null, 400);
        }

        $codes = array_filter(array_map('trim', explode(',', $codesParam)));
        $codes = array_slice($codes, 0, 50);

        if (empty($codes)) {
            return $this->JSONResponse('OK', [], 200);
        }

        $tickets = $this->db()->table('tickets')
            ->select('tracking_code, title, status, type, priority, created_at')
            ->whereIn('tracking_code', $codes)
            ->where('is_anonymous', 1)
            ->where('active', 0)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($tickets as $t) {
            $result[] = [
                'tracking_code' => $t['tracking_code'],
                'title'         => $t['title'],
                'status'        => (int) $t['status'],
                'status_name'   => Enums::ticketStatusName((int) $t['status']),
                'type'          => (int) $t['type'],
                'type_name'     => Enums::ticketTypeName((int) $t['type']),
                'priority'      => (int) $t['priority'],
                'priority_name' => Enums::priorityName((int) $t['priority']),
                'created_at'    => $t['created_at'],
            ];
        }

        return $this->JSONResponse('OK', $result, 200);
    }
}
