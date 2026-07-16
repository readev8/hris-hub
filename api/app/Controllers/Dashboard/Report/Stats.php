<?php

namespace App\Controllers\Dashboard\Report;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class Stats extends BaseApi
{
    public function get_stats(): ResponseInterface
    {
        $params = $this->req->getGet();
        $dateStart = $params['date_start'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateEnd = $params['date_end'] ?? date('Y-m-d');

        $db = $this->db();

        $ticketCounts = $db->table('tickets')
            ->select('status, COUNT(*) as count')
            ->where('active', 0)
            ->where('created_at >=', $dateStart . ' 00:00:00')
            ->where('created_at <=', $dateEnd . ' 23:59:59')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $typeCounts = $db->table('tickets')
            ->select('type, COUNT(*) as count')
            ->where('active', 0)
            ->where('created_at >=', $dateStart . ' 00:00:00')
            ->where('created_at <=', $dateEnd . ' 23:59:59')
            ->groupBy('type')
            ->get()
            ->getResultArray();

        $totalTickets = $db->table('tickets')
            ->where('active', 0)
            ->where('created_at >=', $dateStart . ' 00:00:00')
            ->where('created_at <=', $dateEnd . ' 23:59:59')
            ->countAllResults();

        $totalProjects = $db->table('projects')
            ->where('active', 0)
            ->where('created_at >=', $dateStart . ' 00:00:00')
            ->where('created_at <=', $dateEnd . ' 23:59:59')
            ->countAllResults();

        $statusSummary = [];
        foreach ($ticketCounts as $row) {
            $statusName = Enums::ticketStatusName((int) $row['status']);
            $statusSummary[$statusName] = (int) $row['count'];
        }

        $typeSummary = [];
        foreach ($typeCounts as $row) {
            $typeName = Enums::ticketTypeName((int) $row['type']);
            $typeSummary[$typeName] = (int) $row['count'];
        }

        $pendingApprovals = $db->table('projects')
            ->where('active', 0)
            ->whereIn('status', [Enums::PROJECT_STATUS_DRAFT, Enums::PROJECT_STATUS_PENDING])
            ->countAllResults();

        $bugsByProject = $db->table('tickets')
            ->select('master_projects.id, master_projects.name, COUNT(*) as total')
            ->join('pages', 'pages.id = tickets.page_id')
            ->join('modules', 'modules.id = pages.module_id')
            ->join('master_projects', 'master_projects.id = modules.master_project_id')
            ->where('tickets.active', 0)
            ->where('pages.active', 0)
            ->where('modules.active', 0)
            ->where('master_projects.active', 0)
            ->where('tickets.type', Enums::TICKET_TYPE_BUG)
            ->where('tickets.created_at >=', $dateStart . ' 00:00:00')
            ->where('tickets.created_at <=', $dateEnd . ' 23:59:59')
            ->groupBy('master_projects.id')
            ->get()
            ->getResultArray();

        $bugProjectList = [];
        foreach ($bugsByProject as $bp) {
            $bugProjectList[] = [
                'project_id'   => $this->api->encryptId($bp['id']),
                'project_name' => $bp['name'],
                'total'        => (int) $bp['total'],
            ];
        }

        return $this->JSONResponse('OK', [
            'total_tickets'      => $totalTickets,
            'total_projects'     => $totalProjects,
            'pending_approvals'  => $pendingApprovals,
            'by_status'          => $statusSummary,
            'by_type'            => $typeSummary,
            'bugs_by_project'    => $bugProjectList,
        ], 200);
    }
}
