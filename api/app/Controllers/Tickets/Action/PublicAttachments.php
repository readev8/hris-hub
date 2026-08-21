<?php

namespace App\Controllers\Tickets\Action;

use Config\Tables;
use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class PublicAttachments extends BaseApi
{
    public function add(string $encryptedTicketId): ResponseInterface
    {
        $ticketId = $this->resolveId($encryptedTicketId);
        if (!$ticketId) {
            return $this->JSONResponse('ID tidak valid', null, 400);
        }

        $ticket = $this->db()->table(Tables::TICKETS)
            ->where('id', $ticketId)
            ->where('is_anonymous', 1)
            ->where('active', 0)
            ->get()
            ->getRowArray();

        if (!$ticket) {
            return $this->JSONResponse('Ticket tidak ditemukan', null, 404);
        }

        $input = $this->req->getJSON(true);
        if (!$input || empty($input['filename']) || empty($input['stored_name']) || empty($input['mime_type'])) {
            return $this->JSONResponse('Data lampiran tidak valid', null, 400);
        }

        $this->db()->table(Tables::TICKET_ATTACHMENTS)->insert([
            'ticket_id'  => $ticketId,
            'comment_id' => null,
            'filename'   => $this->cleanInput($input['filename']),
            'stored_name'=> $this->cleanInput($input['stored_name']),
            'mime_type'  => $this->cleanInput($input['mime_type']),
            'file_size'  => (int) ($input['file_size'] ?? 0),
            'active'     => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->JSONResponse('Lampiran berhasil ditambahkan');
    }
}
