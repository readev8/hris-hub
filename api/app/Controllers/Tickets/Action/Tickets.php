<?php

namespace App\Controllers\Tickets\Action;

use App\Controllers\BaseApi;
use App\Config\Enums;
use App\Libraries\AuditLogger;
use App\Models\Tickets\check\TicketsCheck_model;
use CodeIgniter\HTTP\ResponseInterface;

class Tickets extends BaseApi
{
    private AuditLogger $audit;
    private TicketsCheck_model $check;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->audit = new AuditLogger();
        $this->check = new TicketsCheck_model();
    }

    public function create_ticket(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        if (!$this->checkPermission('tickets', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk membuat ticket', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $type = (int) ($input['type'] ?? Enums::TICKET_TYPE_ISSUE);
        $priority = (int) ($input['priority'] ?? Enums::PRIORITY_MEDIUM);
        $dueDate = !empty($input['due_date']) ? $input['due_date'] : null;

        $pageId = null;
        if (!empty($input['page_id'])) {
            $pageId = $this->resolveId($input['page_id']);
        }

        $needsPage = in_array($type, [Enums::TICKET_TYPE_BUG, Enums::TICKET_TYPE_CHANGE_REQUEST, Enums::TICKET_TYPE_DATA_REQUEST], true);
        if ($needsPage && !$pageId) {
            return $this->JSONResponse('Untuk tipe ini, wajib memilih halaman', null, 400);
        }

        if (strlen($title) < 5) {
            return $this->JSONResponse('Judul minimal 5 karakter', null, 400);
        }
        if (empty($description)) {
            return $this->JSONResponse('Deskripsi wajib diisi', null, 400);
        }

        $this->db()->transStart();
        $this->db()->table('tickets')->insert([
            'title'          => $title,
            'description'    => $description,
            'type'           => $type,
            'priority'       => $priority,
            'status'         => Enums::TICKET_STATUS_OPEN,
            'creator_id'     => $userId,
            'page_id'        => $pageId,
            'due_date'       => $dueDate,
            'tracking_code'  => 'TKT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $ticketId = $this->db()->insertID();

        $row = $this->db()->table('tickets')->where('id', $ticketId)->get()->getRowArray();
        $trackingCode = $row['tracking_code'] ?? null;

        $this->audit->log($userId, 'ticket', $ticketId, 'create_ticket', null, [
            'title' => $title, 'type' => $type, 'priority' => $priority, 'page_id' => $pageId,
        ]);
        $this->db()->transComplete();

        return $this->JSONResponse('Ticket berhasil dibuat', [
            'id'            => $this->api->encryptId($ticketId),
            'tracking_code' => $trackingCode,
        ], 201);
    }

    public function take_ticket(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        return $this->transition($id, Enums::TICKET_STATUS_IN_PROGRESS, function ($ticket, $userId) {
            $this->db()->table('tickets')->update([
                'assignee_id' => $userId,
                'status'      => Enums::TICKET_STATUS_IN_PROGRESS,
                'updated_at'  => date('Y-m-d H:i:s'),
            ], ['id' => $ticket['id']]);
            $this->audit->log($userId, 'ticket', $ticket['id'], 'take_ticket', ['assignee_id' => null], ['assignee_id' => $userId]);
            return 'Ticket berhasil diambil';
        });
    }

    public function resolve_ticket(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $note = trim($input['resolution_note'] ?? '');

        if (empty($note)) {
            return $this->JSONResponse('Catatan resolusi wajib diisi', null, 400);
        }

        return $this->transition($id, Enums::TICKET_STATUS_RESOLVED, function ($ticket, $userId) use ($note) {
            $this->db()->table('tickets')->update([
                'status'          => Enums::TICKET_STATUS_RESOLVED,
                'resolution_note' => $note,
                'updated_at'      => date('Y-m-d H:i:s'),
            ], ['id' => $ticket['id']]);
            $this->audit->log($userId, 'ticket', $ticket['id'], 'resolve_ticket', null, ['resolution_note' => $note]);
            return 'Ticket berhasil diresolusi';
        });
    }

    public function close_ticket(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $ticket = $this->db()->table('tickets')->where('id', $id)->get()->getRowArray();
        if (!$ticket) return $this->JSONResponse('Ticket tidak ditemukan', null, 404);

        $creatorId = (int) $ticket['creator_id'];
        if ($creatorId !== $userId && $this->getCurrentUserRole() !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya pembuat ticket yang dapat menutup ticket ini', null, 403);
        }

        return $this->transition($id, Enums::TICKET_STATUS_CLOSED, function ($ticket, $userId) {
            $this->db()->table('tickets')->update([
                'status'     => Enums::TICKET_STATUS_CLOSED,
                'closed_at'  => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $ticket['id']]);
            $this->audit->log($userId, 'ticket', $ticket['id'], 'close_ticket', null, ['closed_at' => date('Y-m-d H:i:s')]);
            return 'Ticket berhasil ditutup';
        });
    }

    public function reopen_ticket(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $note = trim($input['rejection_note'] ?? '');

        if (empty($note)) {
            return $this->JSONResponse('Catatan pembukaan kembali wajib diisi', null, 400);
        }

        return $this->transition($id, Enums::TICKET_STATUS_OPEN, function ($ticket, $userId) use ($note) {
            $this->db()->table('tickets')->update([
                'status'         => Enums::TICKET_STATUS_OPEN,
                'rejection_note' => $note,
                'closed_at'      => null,
                'updated_at'     => date('Y-m-d H:i:s'),
            ], ['id' => $ticket['id']]);
            $this->audit->log($userId, 'ticket', $ticket['id'], 'reopen_ticket', null, ['rejection_note' => $note]);
            return 'Ticket berhasil dibuka kembali';
        });
    }

    public function approve_ticket(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkTicketOwnership($id)) {
            return $this->JSONResponse('Anda tidak memiliki akses ke ticket ini', null, 403);
        }

        return $this->transition($id, Enums::TICKET_STATUS_APPROVED, function ($ticket, $userId) {
            $this->db()->table('tickets')->update([
                'status'     => Enums::TICKET_STATUS_APPROVED,
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $ticket['id']]);
            $this->audit->log($userId, 'ticket', $ticket['id'], 'approve_ticket',
                ['status' => Enums::TICKET_STATUS_OPEN],
                ['status' => Enums::TICKET_STATUS_APPROVED]
            );
            return 'Ticket berhasil di-approve';
        });
    }

    public function reject_ticket(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $note = trim($input['rejection_note'] ?? '');

        if (empty($note)) {
            return $this->JSONResponse('Catatan penolakan wajib diisi', null, 400);
        }

        $ticket = $this->db()->table('tickets')->where('id', $id)->get()->getRowArray();
        if (!$ticket) return $this->JSONResponse('Ticket tidak ditemukan', null, 404);

        if ((int) $ticket['creator_id'] !== $userId && $this->getCurrentUserRole() !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya pembuat ticket yang dapat me-reject', null, 403);
        }

        return $this->transition($id, Enums::TICKET_STATUS_REJECTED, function ($ticket, $userId) use ($note) {
            $this->db()->table('tickets')->update([
                'status'         => Enums::TICKET_STATUS_REJECTED,
                'rejection_note' => $note,
                'updated_at'     => date('Y-m-d H:i:s'),
            ], ['id' => $ticket['id']]);
            $this->audit->log($userId, 'ticket', $ticket['id'], 'reject_ticket',
                ['status' => Enums::TICKET_STATUS_OPEN],
                ['status' => Enums::TICKET_STATUS_REJECTED, 'rejection_note' => $note]
            );
            return 'Ticket berhasil di-reject';
        });
    }

    public function add_comment(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('tickets', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk comment', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $content = trim($input['content'] ?? '');

        if (empty($content)) {
            return $this->JSONResponse('Komentar tidak boleh kosong', null, 400);
        }

        $ticket = $this->db()->table('tickets')->where('id', $id)->get()->getRowArray();
        if (!$ticket) return $this->JSONResponse('Ticket tidak ditemukan', null, 404);

        $this->db()->transStart();
        $this->db()->table('ticket_comments')->insert([
            'ticket_id'  => $id,
            'user_id'    => $userId,
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $commentId = $this->db()->insertID();
        $this->audit->log($userId, 'ticket_comment', $commentId, 'add_comment', null, ['ticket_id' => $id, 'content' => $content]);
        $this->db()->transComplete();

        return $this->JSONResponse('Komentar ditambahkan', [
            'id' => $this->api->encryptId($commentId),
        ], 201);
    }

    public function move_ticket(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkTicketOwnership($id)) {
            return $this->JSONResponse('Anda tidak memiliki akses ke ticket ini', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $newStatus = (int) ($input['new_status'] ?? -1);

        $validStatuses = [
            Enums::TICKET_STATUS_OPEN,
            Enums::TICKET_STATUS_IN_PROGRESS,
            Enums::TICKET_STATUS_RESOLVED,
            Enums::TICKET_STATUS_CLOSED,
        ];
        if (!in_array($newStatus, $validStatuses, true)) {
            return $this->JSONResponse('Status tidak valid', null, 400);
        }

        return $this->transition($id, $newStatus, function ($ticket, $userId) use ($newStatus) {
            $update = [
                'status'     => $newStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($newStatus === Enums::TICKET_STATUS_IN_PROGRESS) {
                $update['assignee_id'] = $userId;
            }

            $this->db()->table('tickets')->update($update, ['id' => $ticket['id']]);

            $this->audit->log($userId, 'ticket', $ticket['id'], 'move_ticket', ['status' => $ticket['status']], ['status' => $newStatus]);

            return 'Ticket dipindahkan ke ' . Enums::ticketStatusName($newStatus);
        });
    }

    public function update_ticket(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $ticket = $this->db()->table('tickets')->where('id', $id)->get()->getRowArray();
        if (!$ticket) return $this->JSONResponse('Ticket tidak ditemukan', null, 404);

        if (!$this->checkTicketOwnership($id)) {
            return $this->JSONResponse('Anda tidak memiliki akses ke ticket ini', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());

        $title = trim($input['title'] ?? $ticket['title']);
        $description = trim($input['description'] ?? $ticket['description']);
        $type = (int) ($input['type'] ?? $ticket['type']);
        $priority = (int) ($input['priority'] ?? $ticket['priority']);
        $dueDate = $input['due_date'] ?? $ticket['due_date'];
        $assigneeId = isset($input['assignee_id']) ? ($input['assignee_id'] !== '' ? $this->resolveId($input['assignee_id']) : null) : $ticket['assignee_id'];

        $needsPage = in_array($type, [Enums::TICKET_TYPE_BUG, Enums::TICKET_TYPE_CHANGE_REQUEST, Enums::TICKET_TYPE_DATA_REQUEST], true);
        if ($needsPage && empty($input['page_id']) && empty($ticket['page_id'])) {
            return $this->JSONResponse('Untuk tipe ini, wajib memilih halaman', null, 400);
        }

        if (strlen($title) < 5) {
            return $this->JSONResponse('Judul minimal 5 karakter', null, 400);
        }
        if (empty($description)) {
            return $this->JSONResponse('Deskripsi wajib diisi', null, 400);
        }

        $pageId = $ticket['page_id'];
        if (isset($input['page_id'])) {
            $pageId = !empty($input['page_id']) ? $this->resolveId($input['page_id']) : null;
        }

        $old = [
            'title' => $ticket['title'], 'description' => $ticket['description'],
            'type' => $ticket['type'], 'priority' => $ticket['priority'],
            'due_date' => $ticket['due_date'], 'assignee_id' => $ticket['assignee_id'],
        ];
        $new = [
            'title' => $title, 'description' => $description,
            'type' => $type, 'priority' => $priority,
            'due_date' => $dueDate, 'assignee_id' => $assigneeId,
            'page_id' => $pageId, 'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db()->transStart();
        $this->db()->table('tickets')->update($new, ['id' => $id]);
        $this->audit->log($userId, 'ticket', $id, 'update_ticket', $old, $new);
        $this->db()->transComplete();

        return $this->JSONResponse('Ticket berhasil diupdate');
    }

    private function transition(int $id, int $targetStatus, callable $onSuccess): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        $ticket = $this->db()->table('tickets')->where('id', $id)->get()->getRowArray();
        if (!$ticket) {
            return $this->JSONResponse('Ticket tidak ditemukan', null, 404);
        }

        $role = $this->getCurrentUserRole();
        if (!$role) {
            return $this->JSONResponse('User tidak ditemukan', null, 404);
        }

        $error = $this->check->validateTransition(
            (int) $ticket['status'],
            $targetStatus,
            $role,
            $userId,
            (int) $ticket['creator_id'],
            $ticket['assignee_id'] ? (int) $ticket['assignee_id'] : null,
        );

        if ($error) {
            return $this->JSONResponse($error, null, 400);
        }

        $this->db()->transStart();
        $message = $onSuccess($ticket, $userId);
        $this->db()->transComplete();

        return $this->JSONResponse($message);
    }
}
