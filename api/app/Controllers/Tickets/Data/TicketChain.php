<?php

namespace App\Controllers\Tickets\Data;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class TicketChain extends BaseApi
{
    public function get_chain(): ResponseInterface
    {
        $params = $this->req->getGet();
        $trackingCode = trim($params['code'] ?? '');

        if (empty($trackingCode)) {
            return $this->JSONResponse('Tracking code wajib diisi', null, 400);
        }

        // Build chain from current ticket up to root
        $chain = [];
        $currentCode = $trackingCode;
        $maxDepth = 20;

        while ($currentCode && $maxDepth > 0) {
            $ticket = $this->db()->table('tickets')
                ->select('id, tracking_code, title, status, type, priority, referral, created_at')
                ->where('tracking_code', $currentCode)
                ->where('active', 0)
                ->get()
                ->getRowArray();

            if (!$ticket) break;

            $ticket['status_name'] = Enums::ticketStatusName($ticket['status']);
            $ticket['type_name'] = Enums::ticketTypeName($ticket['type']);
            $ticket['priority_name'] = Enums::priorityName($ticket['priority']);
            $ticket['is_current'] = ($currentCode === $trackingCode);

            array_unshift($chain, $ticket);
            $currentCode = $ticket['referral'];
            $maxDepth--;
        }

        // Reverse lookup: find all children that refer to the current ticket
        $children = $this->db()->table('tickets')
            ->select('id, tracking_code, title, status, type, priority, referral, created_at')
            ->where('referral', $trackingCode)
            ->where('active', 0)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($children as &$child) {
            $child['status_name'] = Enums::ticketStatusName($child['status']);
            $child['type_name'] = Enums::ticketTypeName($child['type']);
            $child['priority_name'] = Enums::priorityName($child['priority']);
            $child['is_current'] = false;
        }

        $root = $chain[0] ?? null;
        $current = end($chain) ?: null;

        return $this->JSONResponse('OK', [
            'chain'    => $chain,
            'children' => $children,
            'root'     => $root ? [
                'tracking_code' => $root['tracking_code'],
                'title'         => $root['title'],
            ] : null,
            'current' => $current ? [
                'tracking_code' => $current['tracking_code'],
                'title'         => $current['title'],
            ] : null,
            'depth'       => count($chain) - 1,
            'chain_count' => count($chain),
            'children_count' => count($children),
        ], 200);
    }
}
