<?php

namespace App\Controllers\Improvements\Data;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class ProjectDetail extends BaseApi
{
    public function get_detail(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $project = $this->db()->table('projects')
            ->select('projects.*, creator.full_name as creator_name, assignee.full_name as assignee_name, approver.full_name as approver_name')
            ->join('users as creator', 'creator.id = projects.created_by', 'left')
            ->join('users as assignee', 'assignee.id = projects.assignee_id', 'left')
            ->join('users as approver', 'approver.id = projects.approver_id', 'left')
            ->where('projects.id', $id)
            ->get()
            ->getRowArray();

        if (!$project) {
            return $this->JSONResponse('Proyek tidak ditemukan', null, 404);
        }

        // Scope (page -> module -> project)
        if (!empty($project['page_id'])) {
            $page = $this->db()->table('pages')
                ->select('pages.name as page_name, modules.name as module_name, mp.name as project_name')
                ->join('modules', 'modules.id = pages.module_id', 'left')
                ->join('master_projects as mp', 'mp.id = modules.master_project_id', 'left')
                ->where('pages.id', $project['page_id'])
                ->get()
                ->getRowArray();
            if ($page) {
                $project['page_name'] = $page['page_name'] ?? null;
                $project['module_name'] = $page['module_name'] ?? null;
                $project['project_name'] = $page['project_name'] ?? null;
            }
        }

        // Attachments
        $attachments = $this->db()->table('project_attachments')
            ->where('project_id', $id)
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

        $approvals = $this->db()->table('approval_requests')
            ->select('approval_requests.*, approver.full_name as approver_name')
            ->join('users as approver', 'approver.id = approval_requests.approver_id', 'left')
            ->where('approval_requests.project_id', $id)
            ->orderBy('approval_requests.stage_sequence', 'ASC')
            ->get()
            ->getResultArray();

        $comments = $this->db()->table('project_comments')
            ->select('project_comments.*, users.full_name')
            ->join('users', 'users.id = project_comments.user_id')
            ->where('project_comments.project_id', $id)
            ->orderBy('project_comments.created_at', 'ASC')
            ->get()
            ->getResultArray();

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
