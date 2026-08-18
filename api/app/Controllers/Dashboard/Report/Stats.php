<?php

namespace App\Controllers\Dashboard\Report;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class Stats extends BaseApi
{
    public function get_stats(): ResponseInterface
    {
        $params     = $this->req->getGet();
        $dateStart  = $params['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateEnd    = $params['end_date'] ?? date('Y-m-d');
        $rangeStart = $dateStart . ' 00:00:00';
        $rangeEnd   = $dateEnd . ' 23:59:59';

        $userId = $this->getCurrentUserId();
        $role   = $this->getCurrentUserRole();
        $db     = $this->db();

        // ── Ticket counts by status (in date range) ────────────────
        $ticketStatusRows = $db->table(Tables::TICKETS)
            ->select('status, COUNT(*) as count')
            ->where('active', 0)
            ->where('created_at >=', $rangeStart)
            ->where('created_at <=', $rangeEnd)
            ->groupBy('status')
            ->get()->getResultArray();

        $byStatus = [];
        foreach ($ticketStatusRows as $row) {
            $byStatus[Enums::ticketStatusName((int) $row['status'])] = (int) $row['count'];
        }

        // ── Ticket counts by type (in date range) ──────────────────
        $ticketTypeRows = $db->table(Tables::TICKETS)
            ->select('type, COUNT(*) as count')
            ->where('active', 0)
            ->where('created_at >=', $rangeStart)
            ->where('created_at <=', $rangeEnd)
            ->groupBy('type')
            ->get()->getResultArray();

        $byType = [];
        foreach ($ticketTypeRows as $row) {
            $byType[Enums::ticketTypeName((int) $row['type'])] = (int) $row['count'];
        }

        // ── Ticket counts by priority (in date range) ──────────────
        $ticketPriorityRows = $db->table(Tables::TICKETS)
            ->select('priority, COUNT(*) as count')
            ->where('active', 0)
            ->where('created_at >=', $rangeStart)
            ->where('created_at <=', $rangeEnd)
            ->groupBy('priority')
            ->get()->getResultArray();

        $byPriority = [];
        foreach ($ticketPriorityRows as $row) {
            $byPriority[Enums::priorityName((int) $row['priority'])] = (int) $row['count'];
        }

        // ── Totals (date-range scoped) ──────────────────────────────
        $totalTickets   = $db->table(Tables::TICKETS)->where('active', 0)->where('created_at >=', $rangeStart)->where('created_at <=', $rangeEnd)->countAllResults();
        $totalProjects  = $db->table(Tables::PROJECTS)->where('active', 0)->where('created_at >=', $rangeStart)->where('created_at <=', $rangeEnd)->countAllResults();
        $totalBlueprints = $db->table(Tables::BLUEPRINTS)->where('active', 0)->where('created_at >=', $rangeStart)->where('created_at <=', $rangeEnd)->countAllResults();

        // ── Entity totals (system-wide, no date filter) ────────────
        $totalMasterProjects = $db->table(Tables::MASTER_PROJECTS)->where('active', 0)->countAllResults();
        $totalModules        = $db->table(Tables::MODULES)->where('active', 0)->countAllResults();
        $totalPages          = $db->table(Tables::PAGES)->where('active', 0)->countAllResults();
        $totalUsers          = $db->table(Tables::USERS)->where('is_active', 1)->countAllResults();

        // ── Pending approvals (split by source) ────────────────────
        $pendingTickets     = $db->table(Tables::TICKETS)->where('active', 0)->where('status', Enums::TICKET_STATUS_OPEN)->groupStart()->where('needs_approval', 1)->orGroupStart()->where('needs_approval', null)->where('approver_id IS NOT NULL')->groupEnd()->groupEnd()->countAllResults();
        $pendingImprovements = $db->table(Tables::PROJECTS)->where('active', 0)->whereIn('status', [Enums::PROJECT_STATUS_DRAFT, Enums::PROJECT_STATUS_PENDING])->countAllResults();
        $pendingBlueprints  = $db->table(Tables::BLUEPRINTS)->where('active', 0)->whereIn('status', [Enums::PROJECT_STATUS_DRAFT, Enums::PROJECT_STATUS_PENDING])->countAllResults();

        // ── Overdue & unassigned ───────────────────────────────────
        $overdueTickets = $db->table(Tables::TICKETS)
            ->where('active', 0)
            ->where('due_date IS NOT NULL')
            ->where('due_date <', date('Y-m-d'))
            ->whereNotIn('status', [Enums::TICKET_STATUS_RESOLVED, Enums::TICKET_STATUS_CLOSED, Enums::TICKET_STATUS_REJECTED])
            ->countAllResults();

        $unassignedOpen = $db->table(Tables::TICKETS)
            ->where('active', 0)
            ->whereIn('status', [Enums::TICKET_STATUS_OPEN, Enums::TICKET_STATUS_APPROVED])
            ->groupStart()->where('assignee_id IS NULL')->orWhere('assignee_id', 0)->groupEnd()
            ->countAllResults();

        // ── Assignee workload (top 5 by open+in-progress) ─────────
        $assigneeWorkload = [];
        if (in_array($role, [Enums::IT_MANAGER, Enums::DEPT_HEAD, Enums::ADMIN], true)) {
            $workloadRows = $db->table(Tables::TICKETS)
                ->select(Tables::USERS . '.full_name as assignee_name, COUNT(*) as total,
                    SUM(CASE WHEN ' . Tables::TICKETS . '.status = 0 THEN 1 ELSE 0 END) as open_count,
                    SUM(CASE WHEN ' . Tables::TICKETS . '.status = 2 THEN 1 ELSE 0 END) as in_progress_count')
                ->join(Tables::USERS, Tables::USERS . '.id = ' . Tables::TICKETS . '.assignee_id', 'inner')
                ->where(Tables::TICKETS . '.active', 0)
                ->whereIn(Tables::TICKETS . '.status', [Enums::TICKET_STATUS_OPEN, Enums::TICKET_STATUS_APPROVED, Enums::TICKET_STATUS_IN_PROGRESS])
                ->groupBy(Tables::TICKETS . '.assignee_id')
                ->orderBy('total', 'DESC')
                ->limit(5)
                ->get()->getResultArray();

            foreach ($workloadRows as $w) {
                $assigneeWorkload[] = [
                    'assignee_name' => $w['assignee_name'],
                    'open'          => (int) $w['open_count'],
                    'in_progress'   => (int) $w['in_progress_count'],
                    'total'         => (int) $w['total'],
                ];
            }
        }

        // ── Daily trend (last 14 days) ─────────────────────────────
        $dailyTrend = $this->buildTrend($db, 'day', 14, 'Y-m-d');

        // ── Weekly trend (last 8 weeks) ────────────────────────────
        $weeklyTrend = $this->buildTrend($db, 'week', 8, '\WW');

        // ── Bugs by project (with open/closed split + health) ──────
        $bugsByProject = $db->table(Tables::TICKETS)
            ->select(Tables::MASTER_PROJECTS . '.id, ' . Tables::MASTER_PROJECTS . '.name,
                COUNT(*) as total,
                SUM(CASE WHEN ' . Tables::TICKETS . '.status IN (0,1,2) THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN ' . Tables::TICKETS . '.status IN (3,4) THEN 1 ELSE 0 END) as closed_count')
            ->join(Tables::PAGES, Tables::PAGES . '.id = ' . Tables::TICKETS . '.page_id')
            ->join(Tables::MODULES, Tables::MODULES . '.id = ' . Tables::PAGES . '.module_id')
            ->join(Tables::MASTER_PROJECTS, Tables::MASTER_PROJECTS . '.id = ' . Tables::MODULES . '.master_project_id')
            ->where(Tables::TICKETS . '.active', 0)
            ->where(Tables::PAGES . '.active', 0)
            ->where(Tables::MODULES . '.active', 0)
            ->where(Tables::MASTER_PROJECTS . '.active', 0)
            ->where(Tables::TICKETS . '.type', Enums::TICKET_TYPE_BUG)
            ->groupBy(Tables::MASTER_PROJECTS . '.id')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        $bugProjectList = [];
        foreach ($bugsByProject as $bp) {
            $total  = (int) $bp['total'];
            $closed = (int) $bp['closed_count'];
            $health = $total > 0 ? (int) round(($closed / $total) * 100) : 100;
            $bugProjectList[] = [
                'project_id'   => $this->api->encryptId($bp['id']),
                'project_name' => $bp['name'],
                'total'        => $total,
                'open'         => (int) $bp['open_count'],
                'closed'       => $closed,
                'health_score' => $health,
            ];
        }

        // ── Projects by status ─────────────────────────────────────
        $projectStatusRows = $db->table(Tables::PROJECTS)
            ->select('status, COUNT(*) as count')
            ->where('active', 0)
            ->groupBy('status')
            ->get()->getResultArray();
        $projectsByStatus = [];
        foreach ($projectStatusRows as $row) {
            $projectsByStatus[Enums::projectStatusName((int) $row['status'])] = (int) $row['count'];
        }

        // ── Blueprints by status ───────────────────────────────────
        $blueprintStatusRows = $db->table(Tables::BLUEPRINTS)
            ->select('status, COUNT(*) as count')
            ->where('active', 0)
            ->groupBy('status')
            ->get()->getResultArray();
        $blueprintsByStatus = [];
        foreach ($blueprintStatusRows as $row) {
            $blueprintsByStatus[Enums::blueprintStatusName((int) $row['status'])] = (int) $row['count'];
        }

        // ── My queue (role-aware) ──────────────────────────────────
        $myQueue = $this->buildMyQueue($db, $userId, $role);

        // ── Assemble response ──────────────────────────────────────
        return $this->JSONResponse('OK', [
            'role'                  => $role,
            'totals' => [
                'tickets'         => $totalTickets,
                'projects'        => $totalProjects,
                'blueprints'      => $totalBlueprints,
                'master_projects' => $totalMasterProjects,
                'modules'         => $totalModules,
                'pages'           => $totalPages,
                'users'           => $totalUsers,
            ],
            'pending_approvals' => [
                'tickets'     => $pendingTickets,
                'improvements' => $pendingImprovements,
                'blueprints'  => $pendingBlueprints,
                'total'       => $pendingTickets + $pendingImprovements + $pendingBlueprints,
            ],
            'by_status'             => $byStatus,
            'by_type'               => $byType,
            'by_priority'           => $byPriority,
            'overdue_tickets'       => $overdueTickets,
            'unassigned_open'       => $unassignedOpen,
            'assignee_workload'     => $assigneeWorkload,
            'daily_trend'           => $dailyTrend,
            'weekly_trend'          => $weeklyTrend,
            'bugs_by_project'       => $bugProjectList,
            'projects_by_status'    => $projectsByStatus,
            'blueprints_by_status'  => $blueprintsByStatus,
            'my_queue'              => $myQueue,
        ], 200);
    }

    /**
     * Build a trend series (created vs resolved) for the last N periods.
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     * @param string                               $unit  'day' or 'week'
     * @param int                                  $count Number of periods
     * @param string                               $fmt   PHP date format for label
     */
    private function buildTrend($db, string $unit, int $count, string $fmt): array
    {
        $created = $db->table(Tables::TICKETS)
            ->select("DATE_FORMAT(created_at, '" . ($unit === 'week' ? '%x-%v' : '%Y-%m-%d') . "') as period, COUNT(*) as cnt")
            ->where('active', 0)
            ->where('created_at >=', date('Y-m-d 00:00:00', strtotime('-' . $count . ' ' . $unit . 's')))
            ->groupBy('period')
            ->get()->getResultArray();

        $resolved = $db->table(Tables::TICKETS)
            ->select("DATE_FORMAT(updated_at, '" . ($unit === 'week' ? '%x-%v' : '%Y-%m-%d') . "') as period, COUNT(*) as cnt")
            ->where('active', 0)
            ->whereIn('status', [Enums::TICKET_STATUS_RESOLVED, Enums::TICKET_STATUS_CLOSED])
            ->where('updated_at >=', date('Y-m-d 00:00:00', strtotime('-' . $count . ' ' . $unit . 's')))
            ->groupBy('period')
            ->get()->getResultArray();

        $createdMap = [];
        foreach ($created as $c) { $createdMap[$c['period']] = (int) $c['cnt']; }
        $resolvedMap = [];
        foreach ($resolved as $r) { $resolvedMap[$r['period']] = (int) $r['cnt']; }

        $series = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            $ts    = strtotime('-' . $i . ' ' . $unit . 's');
            $key   = $unit === 'week' ? date('o-W', $ts) : date('Y-m-d', $ts);
            $label = $unit === 'week' ? 'W' . date('W', $ts) : date('M j', $ts);
            $series[] = [
                $unit === 'week' ? 'week' : 'date' => $label,
                'created'  => $createdMap[$key] ?? 0,
                'resolved' => $resolvedMap[$key] ?? 0,
            ];
        }
        return $series;
    }

    /**
     * Build role-aware "my queue" data.
     */
    private function buildMyQueue($db, ?int $userId, ?int $role): array
    {
        if (!$userId) {
            return ['my_open_tickets' => 0, 'my_pending_approvals' => ['tickets' => [], 'improvements' => [], 'blueprints' => []]];
        }

        // My open tickets (created by me OR assigned to me)
        $myOpenTickets = 0;
        if (in_array($role, [Enums::REQUESTER, Enums::DEVELOPER, Enums::ADMIN], true)) {
            $myOpenTickets = $db->table(Tables::TICKETS)
                ->where('active', 0)
                ->whereIn('status', [Enums::TICKET_STATUS_OPEN, Enums::TICKET_STATUS_APPROVED, Enums::TICKET_STATUS_IN_PROGRESS])
                ->groupStart()->where('creator_id', $userId)->orWhere('assignee_id', $userId)->groupEnd()
                ->countAllResults();
        }

        // Pending approvals (role-aware)
        $pendingTicketsList     = [];
        $pendingImprovementsList = [];
        $pendingBlueprintsList  = [];

        if (in_array($role, [Enums::IT_MANAGER, Enums::DEPT_HEAD, Enums::ADMIN], true)) {
            // Tickets awaiting approval
            $ticketRows = $db->table(Tables::TICKETS)
                ->select(Tables::TICKETS . '.id, ' . Tables::TICKETS . '.title, ' . Tables::TICKETS . '.priority, ' . Tables::TICKETS . '.created_at, creator.full_name as creator_name')
                ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::TICKETS . '.creator_id', 'left')
                ->where(Tables::TICKETS . '.active', 0)
                ->where(Tables::TICKETS . '.status', Enums::TICKET_STATUS_OPEN)
                ->where(Tables::TICKETS . '.needs_approval', 1)
                ->orderBy(Tables::TICKETS . '.created_at', 'ASC')
                ->limit(10)
                ->get()->getResultArray();
            foreach ($ticketRows as $t) {
                $pendingTicketsList[] = [
                    'id'           => $this->api->encryptId($t['id']),
                    'title'        => $t['title'],
                    'priority'     => Enums::priorityName((int) $t['priority']),
                    'creator_name' => $t['creator_name'],
                    'created_at'   => $t['created_at'],
                ];
            }

            // Improvements pending (role-aware)
            $impStatuses = $role === Enums::IT_MANAGER ? [Enums::PROJECT_STATUS_DRAFT] : [Enums::PROJECT_STATUS_PENDING];
            if ($role === Enums::ADMIN) { $impStatuses = [Enums::PROJECT_STATUS_DRAFT, Enums::PROJECT_STATUS_PENDING]; }
            $impRows = $db->table(Tables::PROJECTS)
                ->select(Tables::PROJECTS . '.id, ' . Tables::PROJECTS . '.name, ' . Tables::PROJECTS . '.priority, ' . Tables::PROJECTS . '.created_at, creator.full_name as creator_name')
                ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::PROJECTS . '.created_by', 'left')
                ->where(Tables::PROJECTS . '.active', 0)
                ->whereIn(Tables::PROJECTS . '.status', $impStatuses)
                ->orderBy(Tables::PROJECTS . '.created_at', 'ASC')
                ->limit(10)
                ->get()->getResultArray();
            foreach ($impRows as $p) {
                $pendingImprovementsList[] = [
                    'id'           => $this->api->encryptId($p['id']),
                    'title'        => $p['name'],
                    'priority'     => Enums::priorityName((int) $p['priority']),
                    'creator_name' => $p['creator_name'],
                    'created_at'   => $p['created_at'],
                ];
            }

            // Blueprints pending (role-aware)
            $bpStatuses = $role === Enums::IT_MANAGER ? [Enums::PROJECT_STATUS_DRAFT] : [Enums::PROJECT_STATUS_PENDING];
            if ($role === Enums::ADMIN) { $bpStatuses = [Enums::PROJECT_STATUS_DRAFT, Enums::PROJECT_STATUS_PENDING]; }
            $bpRows = $db->table(Tables::BLUEPRINTS)
                ->select(Tables::BLUEPRINTS . '.id, ' . Tables::BLUEPRINTS . '.name, ' . Tables::BLUEPRINTS . '.created_at, creator.full_name as creator_name')
                ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::BLUEPRINTS . '.created_by', 'left')
                ->where(Tables::BLUEPRINTS . '.active', 0)
                ->whereIn(Tables::BLUEPRINTS . '.status', $bpStatuses)
                ->orderBy(Tables::BLUEPRINTS . '.created_at', 'ASC')
                ->limit(10)
                ->get()->getResultArray();
            foreach ($bpRows as $b) {
                $pendingBlueprintsList[] = [
                    'id'           => $this->api->encryptId($b['id']),
                    'title'        => $b['name'],
                    'creator_name' => $b['creator_name'],
                    'created_at'   => $b['created_at'],
                ];
            }
        }

        return [
            'my_open_tickets'       => $myOpenTickets,
            'my_pending_approvals'  => [
                'tickets'      => $pendingTicketsList,
                'improvements' => $pendingImprovementsList,
                'blueprints'   => $pendingBlueprintsList,
            ],
        ];
    }
}
