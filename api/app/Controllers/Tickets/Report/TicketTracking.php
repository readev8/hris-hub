<?php

namespace App\Controllers\Tickets\Report;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class TicketTracking extends BaseApi
{
    private array $actionLabels = [
        'create_ticket' => 'Ticket created',
        'approve_ticket' => 'Approved',
        'reject_ticket' => 'Rejected',
        'take_ticket' => 'Taken by staff',
        'resolve_ticket' => 'Resolved',
        'close_ticket' => 'Closed',
        'reopen_ticket' => 'Reopened',
        'move_ticket' => 'Status changed',
    ];

    public function get_by_code(string $code): ResponseInterface
    {
        $code = trim($code);
        if (empty($code)) {
            return $this->JSONResponse('Tracking code wajib diisi', null, 400);
        }

        $ticket = $this->db()->table('tickets')
            ->where('tracking_code', $code)
            ->get()
            ->getRowArray();

        if (!$ticket) {
            return $this->JSONResponse('Ticket tidak ditemukan', null, 404);
        }

        $logs = $this->db()->table('audit_logs')
            ->where('entity_type', 'ticket')
            ->where('entity_id', $ticket['id'])
            ->whereIn('action', array_keys($this->actionLabels))
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        $timeline = [];
        foreach ($logs as $log) {
            $newValues = json_decode($log['new_values'] ?? '{}', true);
            $statusId = $newValues['status'] ?? null;
            $timeline[] = [
                'action'      => $this->actionLabels[$log['action']] ?? $log['action'],
                'status'      => $statusId !== null ? Enums::ticketStatusName((int) $statusId) : null,
                'status_id'   => $statusId,
                'created_at'  => $log['created_at'],
            ];
        }

        $result = [
            'tracking_code'  => $ticket['tracking_code'],
            'title'          => $ticket['title'],
            'type'           => (int) $ticket['type'],
            'type_name'      => Enums::ticketTypeName((int) $ticket['type']),
            'priority'       => (int) $ticket['priority'],
            'priority_name'  => Enums::priorityName((int) $ticket['priority']),
            'status'         => (int) $ticket['status'],
            'status_name'    => Enums::ticketStatusName((int) $ticket['status']),
            'created_at'     => $ticket['created_at'],
            'due_date'       => $ticket['due_date'],
            'timeline'       => $timeline,
        ];

        return $this->JSONResponse('OK', $result, 200);
    }
}
