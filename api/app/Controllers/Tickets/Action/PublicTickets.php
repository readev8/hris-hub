<?php

namespace App\Controllers\Tickets\Action;

use Config\Tables;
use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class PublicTickets extends BaseApi
{
    public function create(): ResponseInterface
    {
        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());

        if (!empty($input['website'])) {
            return $this->JSONResponse('Ticket berhasil dibuat', ['id' => null, 'tracking_code' => 'TKT-PENDING'], 201);
        }

        $ip = $this->request->getIPAddress();
        $cacheKey = 'anon_create_' . md5($ip);
        $cache = cache();
        $count = (int) ($cache->get($cacheKey) ?? 0);
        if ($count >= 5) {
            return $this->JSONResponse('Terlalu banyak pengajuan. Coba lagi dalam satu jam.', null, 429);
        }

        $title       = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $type        = (int) ($input['type'] ?? Enums::TICKET_TYPE_ISSUE);
        $priority    = (int) ($input['priority'] ?? Enums::PRIORITY_MEDIUM);
        $pageId      = !empty($input['page_id']) ? $this->resolveId($input['page_id']) : null;

        $needsPage = in_array($type, [Enums::TICKET_TYPE_BUG, Enums::TICKET_TYPE_CHANGE_REQUEST, Enums::TICKET_TYPE_DATA_REQUEST, Enums::TICKET_TYPE_CHANGE_DATA_REQUEST], true);
        if ($needsPage && !$pageId) {
            return $this->JSONResponse('Untuk tipe ini, wajib memilih halaman', null, 400);
        }
        if (strlen($title) < 5) {
            return $this->JSONResponse('Judul minimal 5 karakter', null, 400);
        }
        if (empty($description)) {
            return $this->JSONResponse('Deskripsi wajib diisi', null, 400);
        }

        $trackingCode = 'TKT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

        $this->db()->transStart();
        $this->db()->table(Tables::TICKETS)->insert([
            'title'          => $title,
            'description'    => $description,
            'type'           => $type,
            'priority'       => $priority,
            'status'         => Enums::TICKET_STATUS_OPEN,
            'creator_id'     => null,
            'is_anonymous'   => 1,
            'approver_id'    => null,
            'page_id'        => $pageId,
            'due_date'       => null,
            'needs_approval' => 0,
            'tracking_code'  => $trackingCode,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $ticketId = $this->db()->insertID();
        $this->db()->transComplete();

        if (!$this->db()->transStatus()) {
            return $this->JSONResponse('Gagal membuat ticket', null, 500);
        }

        try {
            $this->db()->table(Tables::AUDIT_LOGS)->insert([
                'entity_type' => 'ticket',
                'entity_id'   => $ticketId,
                'user_id'     => null,
                'action'      => 'public_create',
                'new_values'  => json_encode(['tracking_code' => $trackingCode, 'title' => $title, 'type' => $type]),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Audit log insert failed for ticket ' . $ticketId . ': ' . $e->getMessage());
        }

        $cache->save($cacheKey, $count + 1, 3600);

        return $this->JSONResponse('Ticket berhasil dibuat', [
            'id'            => $this->api->encryptId($ticketId),
            'tracking_code' => $trackingCode,
        ], 201);
    }
}
