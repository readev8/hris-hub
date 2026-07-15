<?php

namespace App\Controllers\Tickets\Data;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class TicketDetail extends BaseApi
{
    public function get_detail(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) {
            return $this->JSONResponse('ID tidak valid', null, 400);
        }

        $ticket = $this->db()->table('tickets')
            ->select('tickets.*, creator.full_name as creator_name, assignee.full_name as assignee_name,
                      tickets.assignee_id as assignee_raw_id,
                      pages.name as page_name, modules.name as module_name, master_projects.name as project_name,
                      master_projects.id as project_raw_id')
            ->join('users as creator', 'creator.id = tickets.creator_id', 'left')
            ->join('users as assignee', 'assignee.id = tickets.assignee_id', 'left')
            ->join('pages', 'pages.id = tickets.page_id', 'left')
            ->join('modules', 'modules.id = pages.module_id', 'left')
            ->join('master_projects', 'master_projects.id = modules.master_project_id', 'left')
            ->where('tickets.id', $id)
            ->get()
            ->getRowArray();

        if (!$ticket) {
            return $this->JSONResponse('Ticket tidak ditemukan', null, 404);
        }

        $comments = $this->db()->table('ticket_comments')
            ->select('ticket_comments.*, users.full_name')
            ->join('users', 'users.id = ticket_comments.user_id')
            ->where('ticket_comments.ticket_id', $id)
            ->orderBy('ticket_comments.created_at', 'ASC')
            ->get()
            ->getResultArray();

        $allAttachments = $this->db()->table('ticket_attachments')
            ->select('id, ticket_id, comment_id, filename, stored_name, mime_type, file_size, created_at')
            ->where('ticket_id', $id)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        $commentAttachmentMap = [];
        $ticketAttachments = [];
        foreach ($allAttachments as $att) {
            $att['id'] = $this->api->encryptId($att['id']);
            $att['ticket_id'] = $encryptedId;
            if ($att['comment_id']) {
                $commentAttachmentMap[(int) $att['comment_id']][] = $att;
            } else {
                $ticketAttachments[] = $att;
            }
        }

        $ticket['id'] = $encryptedId;
        $ticket['status_name'] = \App\Config\Enums::ticketStatusName($ticket['status']);
        $ticket['type_name'] = \App\Config\Enums::ticketTypeName($ticket['type']);
        $ticket['priority_name'] = \App\Config\Enums::priorityName($ticket['priority']);
        $ticket['attachments'] = $ticketAttachments;
        if ($ticket['project_raw_id']) {
            $ticket['project_id'] = $this->api->encryptId($ticket['project_raw_id']);
        }
        unset($ticket['project_raw_id']);

        foreach ($comments as &$c) {
            $rawId = (int) $c['id'];
            $c['id'] = $this->api->encryptId($rawId);
            $c['attachments'] = $commentAttachmentMap[$rawId] ?? [];
        }
        unset($c);
        $ticket['comments'] = $comments;

        // Load approval history if needs_approval is set
        if ((int) ($ticket['needs_approval'] ?? 0) === 1) {
            $approvals = $this->db()->table('approval_requests')
                ->select('approval_requests.*, approver.full_name as approver_name')
                ->join('users as approver', 'approver.id = approval_requests.approver_id', 'left')
                ->where('approval_requests.ticket_id', $id)
                ->orderBy('approval_requests.stage_sequence', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($approvals as &$a) {
                $a['id'] = $this->api->encryptId($a['id']);
                $a['status_name'] = match ((int) $a['status']) {
                    0 => 'Pending',
                    1 => 'Approved',
                    2 => 'Rejected',
                    3 => 'Skipped',
                    default => 'Unknown',
                };
            }
            unset($a);
            $ticket['approval_history'] = $approvals;
        } else {
            $ticket['approval_history'] = [];
        }

        return $this->JSONResponse('OK', $ticket, 200);
    }
}
