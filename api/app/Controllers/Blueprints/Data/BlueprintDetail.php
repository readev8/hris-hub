<?php

namespace App\Controllers\Blueprints\Data;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class BlueprintDetail extends BaseApi
{
    public function get_detail(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $blueprint = $this->db()->table(Tables::BLUEPRINTS)
            ->select(Tables::BLUEPRINTS . '.*, p.id as improvement_id_raw, p.name as improvement_name, creator.full_name as creator_name, approver.full_name as approver_name')
            ->join(Tables::PROJECTS . ' as p', 'p.id = ' . Tables::BLUEPRINTS . '.improvement_id AND p.active = 0', 'left')
            ->join(Tables::USERS . ' as creator', 'creator.id = ' . Tables::BLUEPRINTS . '.created_by', 'left')
            ->join(Tables::USERS . ' as approver', 'approver.id = ' . Tables::BLUEPRINTS . '.approver_id', 'left')
            ->where(Tables::BLUEPRINTS . '.id', $id)
            ->where(Tables::BLUEPRINTS . '.active', 0)
            ->get()
            ->getRowArray();

        if (!$blueprint) {
            return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);
        }

        $modules = $this->db()->table(Tables::BLUEPRINT_MODULES)
            ->where('blueprint_id', $id)
            ->where('active', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($modules as &$mod) {
            $mod['id_encrypted'] = $this->api->encryptId($mod['id']);

            $mod['business_scenarios'] = $this->db()->table(Tables::BLUEPRINT_BUSINESS_SCENARIOS)
                ->where('module_id', $mod['id'])
                ->where('active', 0)
                ->orderBy('sort_order', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($mod['business_scenarios'] as &$bs) {
                $bs['id_encrypted'] = $this->api->encryptId($bs['id']);
            }

            $mod['design_pages'] = $this->db()->table(Tables::BLUEPRINT_DESIGN_PAGES)
                ->where('module_id', $mod['id'])
                ->where('active', 0)
                ->orderBy('sort_order', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($mod['design_pages'] as &$dp) {
                $dp['id_encrypted'] = $this->api->encryptId($dp['id']);

                $dp['page_specifications'] = $this->db()->table(Tables::BLUEPRINT_PAGE_SPECIFICATIONS)
                    ->where('design_page_id', $dp['id'])
                    ->where('active', 0)
                    ->orderBy('sort_order', 'ASC')
                    ->get()
                    ->getResultArray();
                foreach ($dp['page_specifications'] as &$ps) {
                    $ps['id_encrypted'] = $this->api->encryptId($ps['id']);
                }
            }
        }
        unset($mod, $bs, $dp, $ps);

        $allAttachments = $this->db()->table(Tables::BLUEPRINT_ATTACHMENTS)
            ->where('blueprint_id', $id)
            ->where('active', 0)
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
                foreach ($dp['page_specifications'] as &$ps) {
                    $psUx = $attachmentsBySection['page_specification:' . $ps['id']] ?? [];
                    $ps['ux_attachment'] = !empty($psUx) ? $psUx[0] : null;
                }
            }
        }
        unset($mod, $bs, $dp);
        $blueprint['modules'] = $modules;

        $approvals = $this->db()->table(Tables::BLUEPRINT_APPROVAL_REQUESTS)
            ->select(Tables::BLUEPRINT_APPROVAL_REQUESTS . '.*, approver.full_name as approver_name')
            ->join(Tables::USERS . ' as approver', 'approver.id = ' . Tables::BLUEPRINT_APPROVAL_REQUESTS . '.approver_id', 'left')
            ->where(Tables::BLUEPRINT_APPROVAL_REQUESTS . '.blueprint_id', $id)
            ->where(Tables::BLUEPRINT_APPROVAL_REQUESTS . '.active', 0)
            ->orderBy(Tables::BLUEPRINT_APPROVAL_REQUESTS . '.stage_sequence', 'ASC')
            ->get()
            ->getResultArray();

        $comments = $this->db()->table(Tables::BLUEPRINT_COMMENTS)
            ->select(Tables::BLUEPRINT_COMMENTS . '.*, ' . Tables::USERS . '.full_name')
            ->join(Tables::USERS, Tables::USERS . '.id = ' . Tables::BLUEPRINT_COMMENTS . '.user_id')
            ->where(Tables::BLUEPRINT_COMMENTS . '.blueprint_id', $id)
            ->where(Tables::BLUEPRINT_COMMENTS . '.active', 0)
            ->orderBy(Tables::BLUEPRINT_COMMENTS . '.created_at', 'ASC')
            ->get()
            ->getResultArray();

        $blueprint['id'] = $this->api->encryptId($blueprint['id']);
        $blueprint['status_name'] = \App\Config\Enums::projectStatusName($blueprint['status']);
        if (!empty($blueprint['improvement_id_raw'])) {
            $blueprint['improvement_token'] = $this->api->encryptId($blueprint['improvement_id_raw']);
        }
        unset($blueprint['improvement_id_raw']);

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
