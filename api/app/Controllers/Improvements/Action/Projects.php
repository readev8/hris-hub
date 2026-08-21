<?php

namespace App\Controllers\Improvements\Action;

use App\Controllers\BaseApi;
use App\Config\Enums;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class Projects extends BaseApi
{
    private AuditLogger $audit;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->audit = new AuditLogger();
    }

    public function create_improvement(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('improvements', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk membuat improvement', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $businessCase = trim($input['business_case'] ?? '');
        $priority = (int) ($input['priority'] ?? Enums::PRIORITY_MEDIUM);
        $category = trim($input['category'] ?? '');
        $targetDate = !empty($input['target_date']) ? $input['target_date'] : null;
        $assigneeId = !empty($input['assigned_to']) ? $this->resolveId($input['assigned_to']) : null;
        $approverId = !empty($input['approver_id']) ? $this->resolveId($input['approver_id']) : null;
        $pageId = !empty($input['page_id']) ? $this->resolveId($input['page_id']) : null;

        if (empty($name)) {
            return $this->JSONResponse('Nama proyek wajib diisi', null, 400);
        }
        if (empty($description)) {
            return $this->JSONResponse('Deskripsi wajib diisi', null, 400);
        }

        $this->db()->transStart();
        $this->db()->table(Tables::PROJECTS)->insert([
            'name'                => $name,
            'description'         => $description,
            'business_case'       => $businessCase,
            'priority'            => $priority,
            'category'            => $category ?: null,
            'assignee_id'         => $assigneeId,
            'approver_id'         => $approverId,
            'page_id'             => $pageId,
            'target_date'         => $targetDate,
            'frekuensi_penggunaan' => trim($input['frekuensi_penggunaan'] ?? ''),
            'situasi_terkini'      => trim($input['situasi_terkini'] ?? ''),
            'ada_data_dianalisa'   => !empty($input['ada_data_dianalisa']) ? 1 : 0,
            'jenis_data_analisa'   => trim($input['jenis_data_analisa'] ?? ''),
            'tujuan_analisa'       => trim($input['tujuan_analisa'] ?? ''),
            'dampak_manfaat'       => trim($input['dampak_manfaat'] ?? ''),
            'status'              => Enums::PROJECT_STATUS_DRAFT,
            'approval_workflow'   => json_encode(['stages' => ['it_manager', 'dept_head']]),
            'created_by'          => $userId,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
        $projectId = $this->db()->insertID();

        $userTypeIds = $input['user_type_ids'] ?? [];
        if (!empty($userTypeIds) && is_array($userTypeIds)) {
            $insertBatch = [];
            foreach ($userTypeIds as $utId) {
                $insertBatch[] = [
                    'project_id'   => $projectId,
                    'user_type_id' => (int) $utId,
                    'created_at'   => date('Y-m-d H:i:s'),
                ];
            }
                $this->db()->table(Tables::PROJECT_USER_TYPES)->insertBatch($insertBatch);
        }

        $this->audit->log($userId, 'project', $projectId, 'create_improvement', null, [
            'name' => $name, 'priority' => $priority,
        ]);
        $this->db()->transComplete();

        return $this->JSONResponse('Proyek berhasil dibuat', [
            'id' => $this->api->encryptId($projectId),
        ], 201);
    }

    public function update_improvement(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('improvements', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah improvement', null, 403);
        }

        $project = $this->db()->table(Tables::PROJECTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$project) return $this->JSONResponse('Proyek tidak ditemukan', null, 404);

        $role = $this->getCurrentUserRole();
        if ((int) $project['created_by'] !== $userId && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya pembuat proyek atau admin yang dapat mengubah', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());

        $update = [];
        if (isset($input['name']))          $update['name'] = trim($input['name']);
        if (isset($input['description']))   $update['description'] = trim($input['description']);
        if (isset($input['business_case'])) $update['business_case'] = trim($input['business_case']);
        if (isset($input['priority']))      $update['priority'] = (int) $input['priority'];
        if (isset($input['category']))      $update['category'] = trim($input['category']);
        if (isset($input['target_date']))   $update['target_date'] = $input['target_date'] ?: null;
        if (isset($input['assigned_to']))   $update['assignee_id'] = $this->resolveId($input['assigned_to']);
        if (array_key_exists('approver_id', $input)) {
            $update['approver_id'] = !empty($input['approver_id']) ? $this->resolveId($input['approver_id']) : null;
        }
        if (isset($input['page_id']))        $update['page_id'] = $this->resolveId($input['page_id']);
        if (isset($input['frekuensi_penggunaan'])) $update['frekuensi_penggunaan'] = trim($input['frekuensi_penggunaan']);
        if (isset($input['situasi_terkini']))      $update['situasi_terkini'] = trim($input['situasi_terkini']);
        if (array_key_exists('ada_data_dianalisa', $input)) {
            $update['ada_data_dianalisa'] = !empty($input['ada_data_dianalisa']) ? 1 : 0;
        }
        if (isset($input['jenis_data_analisa']))    $update['jenis_data_analisa'] = trim($input['jenis_data_analisa']);
        if (isset($input['tujuan_analisa']))        $update['tujuan_analisa'] = trim($input['tujuan_analisa']);
        if (isset($input['dampak_manfaat']))        $update['dampak_manfaat'] = trim($input['dampak_manfaat']);
        $update['updated_at'] = date('Y-m-d H:i:s');

        if (empty($update)) {
            return $this->JSONResponse('Tidak ada data yang diubah', null, 400);
        }

        $this->db()->transStart();
        $this->db()->table(Tables::PROJECTS)->update($update, ['id' => $id]);

        if (array_key_exists('user_type_ids', $input)) {
            $this->db()->table(Tables::PROJECT_USER_TYPES)->where('project_id', $id)->delete();
            $userTypeIds = $input['user_type_ids'] ?? [];
            if (!empty($userTypeIds) && is_array($userTypeIds)) {
                $insertBatch = [];
                foreach ($userTypeIds as $utId) {
                    $insertBatch[] = [
                        'project_id'   => $id,
                        'user_type_id' => (int) $utId,
                        'created_at'   => date('Y-m-d H:i:s'),
                    ];
                }
            $this->db()->table(Tables::PROJECT_USER_TYPES)->insertBatch($insertBatch);
            }
        }

        $this->audit->log($userId, 'project', $id, 'update_improvement', null, $update);
        $this->db()->transComplete();

        return $this->JSONResponse('Proyek berhasil diperbarui', [
            'id' => $this->api->encryptId($id),
        ], 200);
    }

    public function delete_improvement(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('improvements', 'can_delete')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menghapus improvement', null, 403);
        }

        $project = $this->db()->table(Tables::PROJECTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$project) return $this->JSONResponse('Proyek tidak ditemukan', null, 404);

        $role = $this->getCurrentUserRole();
        if ((int) $project['created_by'] !== $userId && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya pembuat proyek atau admin yang dapat menghapus', null, 403);
        }

        if (!in_array((int) $project['status'], [Enums::PROJECT_STATUS_DRAFT, Enums::PROJECT_STATUS_REJECTED], true)) {
            return $this->JSONResponse('Proyek hanya dapat dihapus pada status Draft atau Rejected', null, 400);
        }

        $this->db()->transStart();
        $this->db()->table(Tables::PROJECTS)->update(['active' => 1], ['id' => $id]);
        $this->audit->log($userId, 'project', $id, 'delete_improvement', ['status' => $project['status']], null);
        $this->db()->transComplete();

        return $this->JSONResponse('Proyek berhasil dihapus', null, 200);
    }

    public function approve_it(string $encryptedId): ResponseInterface
    {
        return $this->JSONResponse('Approval IT Manager sudah tidak diperlukan. Gunakan approve-dept.', null, 400);
    }

    public function approve_dept(string $encryptedId): ResponseInterface
    {
        if (!$this->checkPermission('improvements', 'can_approve')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk approve improvement', null, 403);
        }
        return $this->approvalAction($encryptedId, Enums::STAGE_PENDING_DEPT, function ($project, $userId) {
            $this->db()->table(Tables::PROJECTS)->update([
                'status'     => Enums::PROJECT_STATUS_APPROVED,
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $project['id']]);

            $this->db()->table(Tables::APPROVAL_REQUESTS)->insert([
                'project_id'      => $project['id'],
                'requester_id'    => $project['created_by'],
                'approver_id'     => $userId,
                'stage_sequence'  => Enums::STAGE_PENDING_DEPT,
                'status'          => Enums::APPROVAL_APPROVED,
                'reviewed_at'     => date('Y-m-d H:i:s'),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            $this->audit->log($userId, 'project', $project['id'], 'approve_dept',
                ['status' => $project['status']],
                ['status' => Enums::PROJECT_STATUS_APPROVED]
            );
            return 'Approval berhasil';
        });
    }

    public function reject(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('improvements', 'can_approve')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk reject improvement', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $notes = trim($input['notes'] ?? '');

        if (empty($notes)) {
            return $this->JSONResponse('Alasan penolakan wajib diisi', null, 400);
        }

        $project = $this->db()->table(Tables::PROJECTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$project) return $this->JSONResponse('Proyek tidak ditemukan', null, 404);

        if (!in_array((int) $project['status'], [Enums::PROJECT_STATUS_DRAFT, Enums::PROJECT_STATUS_PENDING], true)) {
            return $this->JSONResponse('Proyek tidak dapat di-reject pada status ini', null, 400);
        }

        $role = $this->getCurrentUserRole();
        if (!$role) return $this->JSONResponse('User tidak ditemukan', null, 404);

        // Per-user approval check: if approver_id is set, only that user (or admin) can reject
        if (!empty($project['approver_id']) && (int) $project['approver_id'] !== $userId && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya approver yang ditunjuk yang dapat menolak proyek ini', null, 403);
        }

        $projectStatus = (int) $project['status'];

        if ($projectStatus !== Enums::PROJECT_STATUS_DRAFT) {
            return $this->JSONResponse('Proyek tidak dapat di-reject pada status ini', null, 400);
        }

        $stageSeq = Enums::STAGE_PENDING_DEPT;

        $this->db()->transStart();
        $this->db()->table(Tables::PROJECTS)->update([
            'status'     => Enums::PROJECT_STATUS_REJECTED,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        $this->db()->table(Tables::APPROVAL_REQUESTS)->insert([
            'project_id'      => $id,
            'requester_id'    => $project['created_by'],
            'approver_id'     => $userId,
            'stage_sequence'  => $stageSeq,
            'status'          => Enums::APPROVAL_REJECTED,
            'notes'           => $notes,
            'reviewed_at'     => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        $this->audit->log($userId, 'project', $id, 'reject', null, ['notes' => $notes]);
        $this->db()->transComplete();

        return $this->JSONResponse('Proyek berhasil di-reject');
    }

    public function resubmit(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $description = trim($input['description'] ?? '');
        $businessCase = trim($input['business_case'] ?? '');

        $project = $this->db()->table(Tables::PROJECTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$project) return $this->JSONResponse('Proyek tidak ditemukan', null, 404);

        if ((int) $project['status'] !== Enums::PROJECT_STATUS_REJECTED) {
            return $this->JSONResponse('Hanya proyek yang di-reject dapat di-resubmit', null, 400);
        }
        if ((int) $project['created_by'] !== $userId) {
            return $this->JSONResponse('Hanya pembuat proyek yang dapat meresubmit', null, 403);
        }

        $update = [
            'status'     => Enums::PROJECT_STATUS_DRAFT,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (!empty($description)) $update['description'] = $description;
        if (!empty($businessCase)) $update['business_case'] = $businessCase;

        $this->db()->transStart();
        $this->db()->table(Tables::PROJECTS)->update($update, ['id' => $id]);

        $this->audit->log($userId, 'project', $id, 'resubmit', null, $update);
        $this->db()->transComplete();

        return $this->JSONResponse('Proyek berhasil di-resubmit');
    }

    public function add_comment(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('improvements', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menambah komentar', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $content = trim($input['content'] ?? '');

        if (empty($content)) {
            return $this->JSONResponse('Komentar tidak boleh kosong', null, 400);
        }

        $project = $this->db()->table(Tables::PROJECTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$project) return $this->JSONResponse('Proyek tidak ditemukan', null, 404);

        $this->db()->transStart();
        $this->db()->table(Tables::PROJECT_COMMENTS)->insert([
            'project_id' => $id,
            'user_id'    => $userId,
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $commentId = $this->db()->insertID();
        $this->audit->log($userId, 'project_comment', $commentId, 'add_comment', null, ['project_id' => $id]);
        $this->db()->transComplete();

        return $this->JSONResponse('Komentar ditambahkan', [
            'id' => $this->api->encryptId($commentId),
        ], 201);
    }

    private function approvalAction(string $encryptedId, int $expectedStage, callable $onSuccess): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $project = $this->db()->table(Tables::PROJECTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$project) return $this->JSONResponse('Proyek tidak ditemukan', null, 404);

        $role = $this->getCurrentUserRole();
        if (!$role) return $this->JSONResponse('User tidak ditemukan', null, 404);

        // Per-user approval check: if approver_id is set, only that user (or admin) can approve
        if (!empty($project['approver_id']) && (int) $project['approver_id'] !== $userId && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya approver yang ditunjuk yang dapat menyetujui proyek ini', null, 403);
        }

        if ($expectedStage === Enums::STAGE_PENDING_IT && $role !== Enums::IT_MANAGER && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya IT Manager yang dapat approve tahap ini', null, 403);
        }
        if ($expectedStage === Enums::STAGE_PENDING_DEPT && $role !== Enums::DEPT_HEAD && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya Department Head atau Admin yang dapat approve', null, 403);
        }

        $expectedStatus = $expectedStage === Enums::STAGE_PENDING_IT
            ? Enums::PROJECT_STATUS_DRAFT
            : Enums::PROJECT_STATUS_DRAFT;

        if ((int) $project['status'] !== $expectedStatus) {
            return $this->JSONResponse('Status proyek tidak sesuai untuk tahap approval ini', null, 400);
        }

        $this->db()->transStart();
        $message = $onSuccess($project, $userId);
        $this->db()->transComplete();

        return $this->JSONResponse($message);
    }

    public function get_user_types(): ResponseInterface
    {
        $userTypes = $this->db()->table(Tables::MASTER_USER_TYPES)
            ->select('id, name, description')
            ->where('active', 1)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->JSONResponse('OK', $userTypes, 200);
    }
}
