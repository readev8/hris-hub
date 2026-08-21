<?php

namespace App\Controllers\Improvements\Data;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class ProjectDetail extends BaseApi
{
    public function get_detail(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $project = $this->db()->table(Tables::PROJECTS)
            ->select(Tables::PROJECTS . '.*, creator.full_name as creator_name, assignee.full_name as assignee_name, approver.full_name as approver_name')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::PROJECTS . '.created_by', 'left')
            ->join(Tables::USERS . ' as assignee', 'assignee.id = ' . Tables::PROJECTS . '.assignee_id', 'left')
            ->join(Tables::USERS . ' as approver', 'approver.id = ' . Tables::PROJECTS . '.approver_id', 'left')
            ->where(Tables::PROJECTS . '.id', $id)
            ->where(Tables::PROJECTS . '.active', 0)
            ->get()
            ->getRowArray();

        if (!$project) {
            return $this->JSONResponse('Proyek tidak ditemukan', null, 404);
        }

        // Scope (page -> module -> project)
        if (!empty($project['page_id'])) {
            $page = $this->db()->table(Tables::PAGES)
                ->select(Tables::PAGES . '.name as page_name, ' . Tables::MODULES . '.name as module_name, mp.name as project_name')
                ->join(Tables::MODULES, Tables::MODULES . '.id = ' . Tables::PAGES . '.module_id', 'left')
                ->join(Tables::MASTER_PROJECTS . ' as mp', 'mp.id = ' . Tables::MODULES . '.master_project_id', 'left')
                ->where(Tables::PAGES . '.id', $project['page_id'])
                ->where(Tables::PAGES . '.active', 0)
                ->where(Tables::MODULES . '.active', 0)
                ->where('mp.active', 0)
                ->get()
                ->getRowArray();
            if ($page) {
                $project['page_name'] = $page['page_name'] ?? null;
                $project['module_name'] = $page['module_name'] ?? null;
                $project['project_name'] = $page['project_name'] ?? null;
            }
        }

        // Attachments
        $attachments = $this->db()->table(Tables::PROJECT_ATTACHMENTS)
            ->where('project_id', $id)
            ->where('active', 0)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();
        foreach ($attachments as &$att) {
            $att['id'] = $this->api->encryptId($att['id']);
        }
        $project['attachments'] = $attachments;

        // Encrypted keys for form pre-population
        if (!empty($project['assignee_id'])) {
            $project['assigned_to'] = $this->api->encryptId($project['assignee_id']);
        }
        if (!empty($project['approver_id'])) {
            $project['approver_id'] = $this->api->encryptId($project['approver_id']);
        }
        if (!empty($project['page_id'])) {
            $project['page_id'] = $this->api->encryptId($project['page_id']);
        }

        $approvals = $this->db()->table(Tables::APPROVAL_REQUESTS)
            ->select(Tables::APPROVAL_REQUESTS . '.*, approver.full_name as approver_name')
            ->join(Tables::USERS . ' as approver', 'approver.id = ' . Tables::APPROVAL_REQUESTS . '.approver_id', 'left')
            ->where(Tables::APPROVAL_REQUESTS . '.project_id', $id)
            ->where(Tables::APPROVAL_REQUESTS . '.active', 0)
            ->orderBy(Tables::APPROVAL_REQUESTS . '.stage_sequence', 'ASC')
            ->get()
            ->getResultArray();

        $comments = $this->db()->table(Tables::PROJECT_COMMENTS)
            ->select(Tables::PROJECT_COMMENTS . '.*, ' . Tables::USERS . '.full_name')
            ->join(Tables::USERS, Tables::USERS . '.id = ' . Tables::PROJECT_COMMENTS . '.user_id')
            ->where(Tables::PROJECT_COMMENTS . '.project_id', $id)
            ->where(Tables::PROJECT_COMMENTS . '.active', 0)
            ->orderBy(Tables::PROJECT_COMMENTS . '.created_at', 'ASC')
            ->get()
            ->getResultArray();

        $blueprints = $this->db()->table(Tables::BLUEPRINTS)
            ->select(Tables::BLUEPRINTS . '.id, ' . Tables::BLUEPRINTS . '.name, ' . Tables::BLUEPRINTS . '.status, ' . Tables::BLUEPRINTS . '.created_at, creator.full_name as creator_name')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::BLUEPRINTS . '.created_by', 'left')
            ->where(Tables::BLUEPRINTS . '.improvement_id', $id)
            ->where(Tables::BLUEPRINTS . '.active', 0)
            ->orderBy(Tables::BLUEPRINTS . '.created_at', 'DESC')
            ->get()
            ->getResultArray();
        foreach ($blueprints as &$bp) {
            $bp['id'] = $this->api->encryptId($bp['id']);
            $bp['status_name'] = \App\Config\Enums::projectStatusName($bp['status']);
        }
        unset($bp);
        $project['blueprints'] = $blueprints;

        $userTypes = $this->db()->table(Tables::MASTER_USER_TYPES)
            ->select(Tables::MASTER_USER_TYPES . '.id, ' . Tables::MASTER_USER_TYPES . '.name')
            ->join(Tables::PROJECT_USER_TYPES, Tables::PROJECT_USER_TYPES . '.user_type_id = ' . Tables::MASTER_USER_TYPES . '.id')
            ->where(Tables::PROJECT_USER_TYPES . '.project_id', $id)
            ->where(Tables::MASTER_USER_TYPES . '.active', 1)
            ->get()
            ->getResultArray();
        $project['user_types'] = $userTypes;

        $project['id'] = $this->api->encryptId($project['id']);
        $project['status_name'] = \App\Config\Enums::projectStatusName($project['status']);
        $project['priority_name'] = \App\Config\Enums::priorityName($project['priority']);
        $project['approval_workflow'] = json_decode($project['approval_workflow'] ?? '{}', true);

        foreach ($approvals as &$a) {
            $a['id'] = $this->api->encryptId($a['id']);
            $a['status_name'] = match ((int) $a['status']) {
                0 => 'Pending',
                1 => 'Approved',
                2 => 'Rejected',
                3 => 'Skipped',
                default => 'Unknown',
            };
        }
        foreach ($comments as &$c) {
            $c['id'] = $this->api->encryptId($c['id']);
        }

        $project['approval_history'] = $approvals;
        $project['comments'] = $comments;

        return $this->JSONResponse('OK', $project, 200);
    }
}
