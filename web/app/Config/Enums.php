<?php

namespace App\Config;

abstract class Enums
{
    // Roles
    const DEVELOPER  = 1;
    const REQUESTER  = 2;
    const DEPT_HEAD  = 3;
    const IT_MANAGER = 4;
    const ADMIN      = 5;

    // Ticket types
    const TICKET_TYPE_BUG            = 0;
    const TICKET_TYPE_ISSUE          = 1;
    const TICKET_TYPE_TASK           = 2;
    const TICKET_TYPE_CHANGE_REQUEST      = 3;
    const TICKET_TYPE_DATA_REQUEST        = 4;
    const TICKET_TYPE_CHANGE_DATA_REQUEST = 5;

    // Priorities
    const PRIORITY_LOW      = 0;
    const PRIORITY_MEDIUM   = 1;
    const PRIORITY_HIGH     = 2;
    const PRIORITY_CRITICAL = 3;

    // Ticket statuses
    const TICKET_STATUS_OPEN       = 0;
    const TICKET_STATUS_APPROVED   = 1;
    const TICKET_STATUS_IN_PROGRESS = 2;
    const TICKET_STATUS_RESOLVED   = 3;
    const TICKET_STATUS_CLOSED     = 4;
    const TICKET_STATUS_REJECTED   = 5;

    // Project statuses
    const PROJECT_STATUS_DRAFT     = 0;
    const PROJECT_STATUS_PENDING   = 1;
    const PROJECT_STATUS_APPROVED  = 2;
    const PROJECT_STATUS_REJECTED  = 3;
    const PROJECT_STATUS_ON_HOLD   = 4;
    const PROJECT_STATUS_COMPLETED = 5;

    // Approval request statuses
    const APPROVAL_PENDING  = 0;
    const APPROVAL_APPROVED = 1;
    const APPROVAL_REJECTED = 2;
    const APPROVAL_SKIPPED  = 3;

    // Approval workflow stages
    const STAGE_PENDING_IT    = 1;
    const STAGE_PENDING_DEPT  = 2;

    public static function roleName(int $role): string
    {
        return match ($role) {
            self::DEVELOPER  => 'Developer',
            self::REQUESTER  => 'Requester',
            self::DEPT_HEAD  => 'Dept Head',
            self::IT_MANAGER => 'IT Manager',
            self::ADMIN      => 'Admin',
            default          => 'Unknown',
        };
    }

    public static function ticketStatusName(int $status): string
    {
        return match ($status) {
            self::TICKET_STATUS_OPEN        => 'Open',
            self::TICKET_STATUS_APPROVED    => 'Approved',
            self::TICKET_STATUS_IN_PROGRESS => 'In Progress',
            self::TICKET_STATUS_RESOLVED    => 'Resolved',
            self::TICKET_STATUS_CLOSED      => 'Closed',
            self::TICKET_STATUS_REJECTED    => 'Rejected',
            default                         => 'Unknown',
        };
    }

    public static function ticketTypeName(int $type): string
    {
        return match ($type) {
            self::TICKET_TYPE_BUG            => 'Bug',
            self::TICKET_TYPE_ISSUE          => 'Issue',
            self::TICKET_TYPE_TASK           => 'Task',
            self::TICKET_TYPE_CHANGE_REQUEST => 'Change Request',
            self::TICKET_TYPE_DATA_REQUEST   => 'Data Request',
            self::TICKET_TYPE_CHANGE_DATA_REQUEST => 'Change Data Request',
            default                          => 'Unknown',
        };
    }

    public static function priorityName(int $priority): string
    {
        return match ($priority) {
            self::PRIORITY_LOW      => 'Low',
            self::PRIORITY_MEDIUM   => 'Medium',
            self::PRIORITY_HIGH     => 'High',
            self::PRIORITY_CRITICAL => 'Critical',
            default                 => 'Unknown',
        };
    }

    public static function projectStatusName(int $status): string
    {
        return match ($status) {
            self::PROJECT_STATUS_DRAFT     => 'Draft',
            self::PROJECT_STATUS_PENDING   => 'Pending',
            self::PROJECT_STATUS_APPROVED  => 'Approved',
            self::PROJECT_STATUS_REJECTED  => 'Rejected',
            self::PROJECT_STATUS_ON_HOLD   => 'On Hold',
            self::PROJECT_STATUS_COMPLETED => 'Completed',
            default                        => 'Unknown',
        };
    }

    public static function blueprintStatusName(int $status): string
    {
        return self::projectStatusName($status);
    }
}
