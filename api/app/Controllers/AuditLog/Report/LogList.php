<?php

namespace App\Controllers\AuditLog\Report;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class LogList extends BaseApi
{
    public function get_recent_logs(): ResponseInterface
    {
        $params    = $this->req->getGet();
        $limit     = min(100, max(1, (int) ($params['limit'] ?? 50)));
        $dateStart = $params['start_date'] ?? null;
        $dateEnd   = $params['end_date'] ?? null;

        $builder = $this->db()->table(Tables::AUDIT_LOGS)
            ->select(Tables::AUDIT_LOGS . '.*, ' . Tables::USERS . '.full_name as user_name')
            ->join(Tables::USERS, Tables::USERS . '.id = ' . Tables::AUDIT_LOGS . '.user_id', 'left');

        if ($dateStart) {
            $builder->where(Tables::AUDIT_LOGS . '.created_at >=', $dateStart . ' 00:00:00');
        }
        if ($dateEnd) {
            $builder->where(Tables::AUDIT_LOGS . '.created_at <=', $dateEnd . ' 23:59:59');
        }

        $logs = $builder->orderBy(Tables::AUDIT_LOGS . '.created_at', 'DESC')
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

        $logs = $this->db()->table(Tables::AUDIT_LOGS)
            ->select(Tables::AUDIT_LOGS . '.*, ' . Tables::USERS . '.full_name as user_name')
            ->join(Tables::USERS, Tables::USERS . '.id = ' . Tables::AUDIT_LOGS . '.user_id', 'left')
            ->where(Tables::AUDIT_LOGS . '.entity_type', $entityType)
            ->where(Tables::AUDIT_LOGS . '.entity_id', $realId)
            ->orderBy(Tables::AUDIT_LOGS . '.created_at', 'DESC')
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
