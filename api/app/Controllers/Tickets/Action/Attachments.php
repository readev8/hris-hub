<?php

namespace App\Controllers\Tickets\Action;

use Config\Tables;
use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class Attachments extends BaseApi
{
    public function add(string $encryptedTicketId): ResponseInterface
    {
        $ticketId = $this->resolveId($encryptedTicketId);
        if (!$ticketId) {
            return $this->JSONResponse('ID tiket tidak valid', null, 400);
        }

        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        $ticket = $this->db()->table(Tables::TICKETS)->where('id', $ticketId)->where('active', 0)->get()->getRowArray();
        if (!$ticket) {
            return $this->JSONResponse('Tiket tidak ditemukan', null, 404);
        }

        if (!$this->checkTicketOwnership($ticketId)) {
            return $this->JSONResponse('Anda tidak memiliki akses ke ticket ini', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $filename = trim($input['filename'] ?? '');
        $storedName = trim($input['stored_name'] ?? '');
        $mimeType = trim($input['mime_type'] ?? '');
        $fileSize = (int) ($input['file_size'] ?? 0);

        if (empty($filename) || empty($storedName) || empty($mimeType) || $fileSize <= 0) {
            return $this->JSONResponse('Data lampiran tidak lengkap', null, 400);
        }

        $commentId = null;
        if (!empty($input['comment_id'])) {
            $commentId = $this->resolveId($input['comment_id']);
            if (!$commentId) {
                return $this->JSONResponse('ID komentar tidak valid', null, 400);
            }
            $comment = $this->db()->table(Tables::TICKET_COMMENTS)
                ->where('id', $commentId)
                ->where('ticket_id', $ticketId)
                ->where('active', 0)
                ->get()
                ->getRowArray();
            if (!$comment) {
                return $this->JSONResponse('Komentar tidak ditemukan', null, 404);
            }
        }

        $this->db()->table(Tables::TICKET_ATTACHMENTS)->insert([
            'ticket_id'   => $ticketId,
            'comment_id'  => $commentId,
            'uploaded_by' => $userId,
            'filename'    => $filename,
            'stored_name' => $storedName,
            'mime_type'   => $mimeType,
            'file_size'   => $fileSize,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $attachmentId = $this->db()->insertID();

        return $this->JSONResponse('Lampiran ditambahkan', [
            'id' => $this->api->encryptId($attachmentId),
        ], 201);
    }

    public function delete($encryptedId = null): ResponseInterface
    {
        if (!$encryptedId) {
            return $this->JSONResponse('ID tidak valid', null, 400);
        }
        $id = $this->resolveId($encryptedId);
        if (!$id) {
            return $this->JSONResponse('ID tidak valid', null, 400);
        }

        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        $attachment = $this->db()->table(Tables::TICKET_ATTACHMENTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$attachment) {
            return $this->JSONResponse('Lampiran tidak ditemukan', null, 404);
        }

        // Check ownership: uploader, ticket creator, or admin
        $isUploader = (int)$attachment['uploaded_by'] === $userId;
        $isTicketCreator = $this->db()->table(Tables::TICKETS)
            ->where('id', $attachment['ticket_id'])
            ->where('creator_id', $userId)
            ->where('active', 0)
            ->countAllResults() > 0;
        $isAdmin = $this->getCurrentUserRole() === Enums::ADMIN;

        if (!$isUploader && !$isTicketCreator && !$isAdmin) {
            return $this->JSONResponse('Anda tidak memiliki akses untuk menghapus lampiran ini', null, 403);
        }

        $this->db()->table(Tables::TICKET_ATTACHMENTS)->update(['active' => 1], ['id' => $id]);

        return $this->JSONResponse('Lampiran dihapus', [
            'stored_name' => $attachment['stored_name'],
            'filename'    => $attachment['filename'],
        ]);
    }
}
