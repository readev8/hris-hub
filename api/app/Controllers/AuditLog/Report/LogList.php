<?php

namespace App\Controllers\AuditLog\Report;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class LogList extends BaseApi
{
    public function get_recent_logs(): ResponseInterface
    {
        $params    = $this->req->getGet();
        $limit     = min(100, max(1, (int) ($params['limit'] ?? 50)));
        $dateStart = $params['start_date'] ?? null;
        $dateEnd   = $params['end_date'] ?? null;

        $builder = $this->db()->table('audit_logs')
            ->select('audit_logs.*, users.full_name as user_name')
            ->join('users', 'users.id = audit_logs.user_id', 'left');

        if ($dateStart) {
            $builder->where('audit_logs.created_at >=', $dateStart . ' 00:00:00');
        }
        if ($dateEnd) {
            $builder->where('audit_logs.created_at <=', $dateEnd . ' 23:59:59');
        }

        $logs = $builder->orderBy('audit_logs.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($logs as &$row) {
            $row['id'] = $this->api->encryptId($row['id']);
            $row['old_values'] = $row['old_values'] ? json_decode($row['old_values'], true) : null;
            $row['new_values'] = $row['new_values'] ? json_decode($row['new_values'], true) : null;
        }

        return $this->JSONResponse('OK', $logs, 200);
    }

    public function get_entity_logs(): ResponseInterface
    {
        $entityType = $this->req->getGet('entity_type');
        $entityId = $this->req->getGet('entity_id');

        if (empty($entityType) || empty($entityId)) {
            return $this->JSONResponse('Parameter entity_type dan entity_id diperlukan', null, 400);
        }

        $realId = $this->api->decryptId($entityId);
        if (!$realId) {
            return $this->JSONResponse('Entity ID tidak valid', null, 400);
        }

        $logs = $this->db()->table('audit_logs')
            ->select('audit_logs.*, users.full_name as user_name')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.entity_type', $entityType)
            ->where('audit_logs.entity_id', $realId)
            ->orderBy('audit_logs.created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        foreach ($logs as &$row) {
            $row['id'] = $this->api->encryptId($row['id']);
            $row['old_values'] = $row['old_values'] ? json_decode($row['old_values'], true) : null;
            $row['new_values'] = $row['new_values'] ? json_decode($row['new_values'], true) : null;
        }

        return $this->JSONResponse('OK', $logs, 200);
    }
}
