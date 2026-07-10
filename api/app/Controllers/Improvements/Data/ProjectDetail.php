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
            ->select('projects.*, creator.full_name as creator_name')
            ->join('users as creator', 'creator.id = projects.created_by', 'left')
            ->where('projects.id', $id)
            ->get()
            ->getRowArray();

        if (!$project) {
            return $this->JSONResponse('Proyek tidak ditemukan', null, 404);
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
