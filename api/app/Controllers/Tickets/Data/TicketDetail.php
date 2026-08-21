<?php

namespace App\Controllers\Tickets\Data;

use Config\Tables;
use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class TicketDetail extends BaseApi
{
    public function get_detail(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        log_message('debug', 'TicketDetail: encryptedId=' . $encryptedId . ' → decryptedId=' . var_export($id, true));

        if (!$id) {
            return $this->JSONResponse('ID tidak valid', null, 400);
        }

        $rawTicket = $this->db()->table(Tables::TICKETS)
            ->where('id', $id)
            ->get()
            ->getRowArray();
        log_message('debug', 'TicketDetail: raw ticket exists=' . ($rawTicket ? 'YES (id=' . $rawTicket['id'] . ', page_id=' . var_export($rawTicket['page_id'], true) . ')' : 'NO'));

        $ticket = $this->db()->table(Tables::TICKETS)
            ->select(Tables::TICKETS . '.*, creator.full_name as creator_name, assignee.full_name as assignee_name,
                      ' . Tables::TICKETS . '.assignee_id as assignee_raw_id,
                      approver.full_name as approver_name,
                      ' . Tables::PAGES . '.name as page_name, ' . Tables::MODULES . '.name as module_name, ' . Tables::MASTER_PROJECTS . '.name as project_name,
                      ' . Tables::MASTER_PROJECTS . '.id as project_raw_id')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::TICKETS . '.creator_id', 'left')
            ->join(Tables::USERS . ' as assignee', 'assignee.id = ' . Tables::TICKETS . '.assignee_id', 'left')
            ->join(Tables::USERS . ' as approver', 'approver.id = ' . Tables::TICKETS . '.approver_id', 'left')
            ->join(Tables::PAGES, Tables::PAGES . '.id = ' . Tables::TICKETS . '.page_id', 'left')
            ->join(Tables::MODULES, Tables::MODULES . '.id = ' . Tables::PAGES . '.module_id', 'left')
            ->join(Tables::MASTER_PROJECTS, Tables::MASTER_PROJECTS . '.id = ' . Tables::MODULES . '.master_project_id', 'left')
            ->where(Tables::TICKETS . '.id', $id)
            ->get()
            ->getRowArray();
        log_message('debug', 'TicketDetail: joined query result=' . ($ticket ? 'FOUND' : 'NULL'));

        if (!$ticket) {
            return $this->JSONResponse('Ticket tidak ditemukan', null, 404);
        }

        $comments = $this->db()->table(Tables::TICKET_COMMENTS)
            ->select(Tables::TICKET_COMMENTS . '.*, ' . Tables::USERS . '.full_name')
            ->join(Tables::USERS, Tables::USERS . '.id = ' . Tables::TICKET_COMMENTS . '.user_id')
            ->where(Tables::TICKET_COMMENTS . '.ticket_id', $id)
            ->where(Tables::TICKET_COMMENTS . '.active', 0)
            ->orderBy(Tables::TICKET_COMMENTS . '.created_at', 'ASC')
            ->get()
            ->getResultArray();

        $allAttachments = $this->db()->table(Tables::TICKET_ATTACHMENTS)
            ->select('id, ticket_id, comment_id, filename, stored_name, mime_type, file_size, created_at')
            ->where('ticket_id', $id)
            ->where('active', 0)
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

        if (!empty($ticket['approver_id'])) {
            $ticket['approver_id'] = $this->api->encryptId($ticket['approver_id']);
        }

        foreach ($comments as &$c) {
            $rawId = (int) $c['id'];
            $c['id'] = $this->api->encryptId($rawId);
            $c['attachments'] = $commentAttachmentMap[$rawId] ?? [];
        }
        unset($c);
        $ticket['comments'] = $comments;

        // Load approval history if needs_approval is set
        if ((int) ($ticket['needs_approval'] ?? 0) === 1) {
            $approvals = $this->db()->table(Tables::APPROVAL_REQUESTS)
                ->select(Tables::APPROVAL_REQUESTS . '.*, approver.full_name as approver_name')
                ->join(Tables::USERS . ' as approver', 'approver.id = ' . Tables::APPROVAL_REQUESTS . '.approver_id', 'left')
                ->where(Tables::APPROVAL_REQUESTS . '.ticket_id', $id)
                ->where(Tables::APPROVAL_REQUESTS . '.active', 0)
                ->orderBy(Tables::APPROVAL_REQUESTS . '.stage_sequence', 'ASC')
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
