<?php

namespace App\Models\Tickets\check;

use App\Config\Enums;

class TicketsCheck_model
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function canTransitionStatus(int $current, int $target, int $userRole, bool $isAssignee, bool $isCreator): bool
    {
        if ($userRole === Enums::ADMIN) {
            return true;
        }

        return match ($current) {
            Enums::TICKET_STATUS_OPEN => match ($target) {
                Enums::TICKET_STATUS_IN_PROGRESS => $userRole === Enums::DEVELOPER,
                Enums::TICKET_STATUS_REJECTED    => $isCreator,
                default => false,
            },
            Enums::TICKET_STATUS_IN_PROGRESS => match ($target) {
                Enums::TICKET_STATUS_RESOLVED => $isAssignee && $userRole === Enums::DEVELOPER,
                default => false,
            },
            Enums::TICKET_STATUS_RESOLVED => match ($target) {
                Enums::TICKET_STATUS_CLOSED => $isCreator,
                Enums::TICKET_STATUS_OPEN   => $isCreator || $userRole === Enums::REQUESTER,
                default => false,
            },
            Enums::TICKET_STATUS_CLOSED => match ($target) {
                Enums::TICKET_STATUS_OPEN => $isCreator || $userRole === Enums::REQUESTER || $userRole === Enums::ADMIN,
                default => false,
            },
            Enums::TICKET_STATUS_REJECTED => match ($target) {
                Enums::TICKET_STATUS_OPEN => $isCreator || $userRole === Enums::ADMIN,
                default => false,
            },
            default => false,
        };
    }

    public function validateTransition(int $current, int $target, int $userRole, int $userId, int $creatorId, ?int $assigneeId): ?string
    {
        $isAssignee = $assigneeId === $userId;
        $isCreator  = $creatorId === $userId;

        if (!$this->canTransitionStatus($current, $target, $userRole, $isAssignee, $isCreator)) {
            return 'Transisi status tidak diizinkan untuk role Anda';
        }
        return null;
    }
}
