<?php

namespace App\Controllers\Blueprints\Data;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class BlueprintDetail extends BaseApi
{
    public function get_detail(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $blueprint = $this->db()->table('blueprints')
            ->select('blueprints.*, p.name as improvement_name, creator.full_name as creator_name, approver.full_name as approver_name')
            ->join('projects as p', 'p.id = blueprints.improvement_id', 'left')
            ->join('users as creator', 'creator.id = blueprints.created_by', 'left')
            ->join('users as approver', 'approver.id = blueprints.approver_id', 'left')
            ->where('blueprints.id', $id)
            ->get()
            ->getRowArray();

        if (!$blueprint) {
            return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);
        }

        $modules = $this->db()->table('blueprint_modules')
            ->where('blueprint_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($modules as &$mod) {
            $mod['id_encrypted'] = $this->api->encryptId($mod['id']);

            $mod['business_scenarios'] = $this->db()->table('blueprint_business_scenarios')
                ->where('module_id', $mod['id'])
                ->orderBy('sort_order', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($mod['business_scenarios'] as &$bs) {
                $bs['id_encrypted'] = $this->api->encryptId($bs['id']);
            }

            $mod['design_pages'] = $this->db()->table('blueprint_design_pages')
                ->where('module_id', $mod['id'])
                ->orderBy('sort_order', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($mod['design_pages'] as &$dp) {
                $dp['id_encrypted'] = $this->api->encryptId($dp['id']);

                $dp['page_specifications'] = $this->db()->table('blueprint_page_specifications')
                    ->where('design_page_id', $dp['id'])
                    ->orderBy('sort_order', 'ASC')
                    ->get()
                    ->getResultArray();
                foreach ($dp['page_specifications'] as &$ps) {
                    $ps['id_encrypted'] = $this->api->encryptId($ps['id']);
                }
            }
        }
        unset($mod, $bs, $dp, $ps);

        $allAttachments = $this->db()->table('blueprint_attachments')
            ->where('blueprint_id', $id)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();
        foreach ($allAttachments as &$att) {
            $att['id'] = $this->api->encryptId($att['id']);
        }
        unset($att);

        $blueprintAttachments = [];
        $attachmentsBySection = [];
        foreach ($allAttachments as $att) {
            $sectionType = trim($att['section_type'] ?? '');
            $sectionId = $att['section_id'] ?? 0;
            if ($sectionType === '' || $sectionId == 0) {
                $blueprintAttachments[] = $att;
            } else {
                $key = $sectionType . ':' . $sectionId;
                if (!isset($attachmentsBySection[$key])) {
                    $attachmentsBySection[$key] = [];
                }
                $attachmentsBySection[$key][] = $att;
            }
        }
        $blueprint['attachments'] = $blueprintAttachments;

        foreach ($modules as &$mod) {
            foreach ($mod['business_scenarios'] as &$bs) {
                $bs['attachments'] = $attachmentsBySection['business_scenario:' . $bs['id']] ?? [];
            }
            foreach ($mod['design_pages'] as &$dp) {
                $dp['attachments'] = $attachmentsBySection['design_page:' . $dp['id']] ?? [];
            }
        }
        unset($mod, $bs, $dp);
        $blueprint['modules'] = $modules;

        $approvals = $this->db()->table('blueprint_approval_requests')
            ->select('blueprint_approval_requests.*, approver.full_name as approver_name')
            ->join('users as approver', 'approver.id = blueprint_approval_requests.approver_id', 'left')
            ->where('blueprint_approval_requests.blueprint_id', $id)
            ->orderBy('blueprint_approval_requests.stage_sequence', 'ASC')
            ->get()
            ->getResultArray();

        $comments = $this->db()->table('blueprint_comments')
            ->select('blueprint_comments.*, users.full_name')
            ->join('users', 'users.id = blueprint_comments.user_id')
            ->where('blueprint_comments.blueprint_id', $id)
            ->orderBy('blueprint_comments.created_at', 'ASC')
            ->get()
            ->getResultArray();

        $blueprint['id'] = $this->api->encryptId($blueprint['id']);
        $blueprint['status_name'] = \App\Config\Enums::projectStatusName($blueprint['status']);

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

        $blueprint['approval_history'] = $approvals;
        $blueprint['comments'] = $comments;

        return $this->JSONResponse('OK', $blueprint, 200);
    }
}
