<?php

namespace App\Libraries;

use Config\Tables;

class AuditLogger
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function log(
        int $userId,
        string $entityType,
        int $entityId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $this->db->table(Tables::AUDIT_LOGS)->insert([
            'user_id'     => $userId,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'action'      => $action,
            'old_values'  => $oldValues ? json_encode($oldValues) : null,
            'new_values'  => $newValues ? json_encode($newValues) : null,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
