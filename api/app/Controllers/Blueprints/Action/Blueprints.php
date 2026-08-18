<?php

namespace App\Controllers\Blueprints\Action;

use App\Controllers\BaseApi;
use App\Config\Enums;
use App\Libraries\AuditLogger;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class Blueprints extends BaseApi
{
    private AuditLogger $audit;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->audit = new AuditLogger();
    }

    public function create(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk membuat blueprint', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $improvementId = !empty($input['improvement_id']) ? $this->resolveId($input['improvement_id']) : null;
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');

        if (empty($name)) {
            return $this->JSONResponse('Nama blueprint wajib diisi', null, 400);
        }

        if ($improvementId) {
            $project = $this->db()->table(Tables::PROJECTS)->where('id', $improvementId)->where('active', 0)->get()->getRowArray();
            if (!$project) {
                return $this->JSONResponse('Improvement tidak ditemukan', null, 404);
            }
        }

        $this->db()->transStart();
        $this->db()->table(Tables::BLUEPRINTS)->insert([
            'improvement_id' => $improvementId,
            'name'           => $name,
            'description'    => $description,
            'status'         => Enums::PROJECT_STATUS_DRAFT,
            'created_by'     => $userId,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $blueprintId = $this->db()->insertID();

        $this->audit->log($userId, 'blueprint', $blueprintId, 'create', null, [
            'name' => $name, 'improvement_id' => $improvementId,
        ]);
        $this->db()->transComplete();

        return $this->JSONResponse('Blueprint berhasil dibuat', [
            'id' => $this->api->encryptId($blueprintId),
        ], 201);
    }

    public function update($encryptedId = null): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah blueprint', null, 403);
        }

        $blueprint = $this->db()->table(Tables::BLUEPRINTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$blueprint) return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $update = [];
        if (isset($input['name']))        $update['name'] = trim($input['name']);
        if (isset($input['description'])) $update['description'] = trim($input['description']);
        if (array_key_exists('improvement_id', $input)) {
            $newImprovementId = !empty($input['improvement_id']) ? $this->resolveId($input['improvement_id']) : null;
            if ($newImprovementId) {
                $project = $this->db()->table(Tables::PROJECTS)->where('id', $newImprovementId)->where('active', 0)->get()->getRowArray();
                if (!$project) {
                    return $this->JSONResponse('Improvement tidak ditemukan', null, 404);
                }
            }
            $update['improvement_id'] = $newImprovementId;
        }
        $update['updated_at'] = date('Y-m-d H:i:s');

        if (empty($update) || count($update) === 1) {
            return $this->JSONResponse('Tidak ada data yang diubah', null, 400);
        }

        $this->db()->transStart();
        $this->db()->table(Tables::BLUEPRINTS)->update($update, ['id' => $id]);
        $this->audit->log($userId, 'blueprint', $id, 'update', null, $update);
        $this->db()->transComplete();

        return $this->JSONResponse('Blueprint berhasil diperbarui', [
            'id' => $this->api->encryptId($id),
        ], 200);
    }

    public function delete($encryptedId = null): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_delete')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menghapus blueprint', null, 403);
        }

        $blueprint = $this->db()->table(Tables::BLUEPRINTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$blueprint) return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);

        if (!in_array((int) $blueprint['status'], [Enums::PROJECT_STATUS_DRAFT, Enums::PROJECT_STATUS_REJECTED], true)) {
            return $this->JSONResponse('Blueprint hanya dapat dihapus pada status Draft atau Rejected', null, 400);
        }

        $this->db()->transStart();
        $this->db()->table(Tables::BLUEPRINTS)->update(['active' => 1], ['id' => $id]);
        $this->audit->log($userId, 'blueprint', $id, 'delete', ['status' => $blueprint['status']], null);
        $this->db()->transComplete();

        return $this->JSONResponse('Blueprint berhasil dihapus', null, 200);
    }

    public function approve_it(string $encryptedId): ResponseInterface
    {
        if (!$this->checkPermission('blueprints', 'can_approve')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk approve blueprint', null, 403);
        }
        return $this->approvalAction($encryptedId, Enums::STAGE_PENDING_IT, function ($blueprint, $userId) {
            $this->db()->table(Tables::BLUEPRINTS)->update([
                'status'     => Enums::PROJECT_STATUS_PENDING,
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $blueprint['id']]);

            $this->db()->table(Tables::BLUEPRINT_APPROVAL_REQUESTS)->insert([
                'blueprint_id'   => $blueprint['id'],
                'requester_id'   => $blueprint['created_by'],
                'approver_id'    => $userId,
                'stage_sequence' => Enums::STAGE_PENDING_IT,
                'status'         => Enums::APPROVAL_APPROVED,
                'reviewed_at'    => date('Y-m-d H:i:s'),
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            $this->audit->log($userId, 'blueprint', $blueprint['id'], 'approve_it',
                ['status' => Enums::PROJECT_STATUS_DRAFT],
                ['status' => Enums::PROJECT_STATUS_PENDING]
            );
            return 'IT Manager approval berhasil';
        });
    }

    public function approve_dept(string $encryptedId): ResponseInterface
    {
        if (!$this->checkPermission('blueprints', 'can_approve')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk approve blueprint', null, 403);
        }
        return $this->approvalAction($encryptedId, Enums::STAGE_PENDING_DEPT, function ($blueprint, $userId) {
            $this->db()->table(Tables::BLUEPRINTS)->update([
                'status'     => Enums::PROJECT_STATUS_APPROVED,
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $blueprint['id']]);

            $this->db()->table(Tables::BLUEPRINT_APPROVAL_REQUESTS)->insert([
                'blueprint_id'   => $blueprint['id'],
                'requester_id'   => $blueprint['created_by'],
                'approver_id'    => $userId,
                'stage_sequence' => Enums::STAGE_PENDING_DEPT,
                'status'         => Enums::APPROVAL_APPROVED,
                'reviewed_at'    => date('Y-m-d H:i:s'),
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            $this->audit->log($userId, 'blueprint', $blueprint['id'], 'approve_dept',
                ['status' => Enums::PROJECT_STATUS_PENDING],
                ['status' => Enums::PROJECT_STATUS_APPROVED]
            );
            return 'Department Head approval berhasil';
        });
    }

    public function reject(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_approve')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk reject blueprint', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $notes = trim($input['notes'] ?? '');
        if (empty($notes)) {
            return $this->JSONResponse('Alasan penolakan wajib diisi', null, 400);
        }

        $blueprint = $this->db()->table(Tables::BLUEPRINTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$blueprint) return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);

        if (!in_array((int) $blueprint['status'], [Enums::PROJECT_STATUS_DRAFT, Enums::PROJECT_STATUS_PENDING], true)) {
            return $this->JSONResponse('Blueprint tidak dapat di-reject pada status ini', null, 400);
        }

        $role = $this->getCurrentUserRole();
        if (!$role) return $this->JSONResponse('User tidak ditemukan', null, 404);

        if (!empty($blueprint['approver_id']) && (int) $blueprint['approver_id'] !== $userId && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya approver yang ditunjuk yang dapat menolak blueprint ini', null, 403);
        }

        $projectStatus = (int) $blueprint['status'];
        if ($projectStatus === Enums::PROJECT_STATUS_DRAFT) {
            if ($role !== Enums::IT_MANAGER && $role !== Enums::ADMIN) {
                return $this->JSONResponse('Hanya IT Manager yang dapat me-reject pada tahap ini', null, 403);
            }
            $stageSeq = Enums::STAGE_PENDING_IT;
        } else {
            if ($role !== Enums::DEPT_HEAD && $role !== Enums::ADMIN) {
                return $this->JSONResponse('Hanya Department Head yang dapat me-reject pada tahap ini', null, 403);
            }
            $stageSeq = Enums::STAGE_PENDING_DEPT;
        }

        $this->db()->transStart();
        $this->db()->table(Tables::BLUEPRINTS)->update([
            'status'     => Enums::PROJECT_STATUS_REJECTED,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        $this->db()->table(Tables::BLUEPRINT_APPROVAL_REQUESTS)->insert([
            'blueprint_id'   => $id,
            'requester_id'   => $blueprint['created_by'],
            'approver_id'    => $userId,
            'stage_sequence' => $stageSeq,
            'status'         => Enums::APPROVAL_REJECTED,
            'notes'          => $notes,
            'reviewed_at'    => date('Y-m-d H:i:s'),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->audit->log($userId, 'blueprint', $id, 'reject', null, ['notes' => $notes]);
        $this->db()->transComplete();

        return $this->JSONResponse('Blueprint berhasil di-reject');
    }

    public function resubmit(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $blueprint = $this->db()->table(Tables::BLUEPRINTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$blueprint) return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);

        if ((int) $blueprint['status'] !== Enums::PROJECT_STATUS_REJECTED) {
            return $this->JSONResponse('Hanya blueprint yang di-reject dapat di-resubmit', null, 400);
        }
        if ((int) $blueprint['created_by'] !== $userId) {
            return $this->JSONResponse('Hanya pembuat blueprint yang dapat meresubmit', null, 403);
        }

        $this->db()->transStart();
        $this->db()->table(Tables::BLUEPRINTS)->update([
            'status'     => Enums::PROJECT_STATUS_PENDING,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        $this->db()->table(Tables::BLUEPRINT_APPROVAL_REQUESTS)->insert([
            'blueprint_id'   => $id,
            'requester_id'   => $userId,
            'approver_id'    => null,
            'stage_sequence' => Enums::STAGE_PENDING_IT,
            'status'         => Enums::APPROVAL_PENDING,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->audit->log($userId, 'blueprint', $id, 'resubmit', null, ['status' => Enums::PROJECT_STATUS_PENDING]);
        $this->db()->transComplete();

        return $this->JSONResponse('Blueprint berhasil di-resubmit');
    }

    public function add_comment(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('blueprints', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menambah komentar', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $content = trim($input['content'] ?? '');
        if (empty($content)) {
            return $this->JSONResponse('Komentar tidak boleh kosong', null, 400);
        }

        $blueprint = $this->db()->table(Tables::BLUEPRINTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$blueprint) return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);

        $this->db()->transStart();
        $this->db()->table(Tables::BLUEPRINT_COMMENTS)->insert([
            'blueprint_id' => $id,
            'user_id'      => $userId,
            'content'      => $content,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $commentId = $this->db()->insertID();
        $this->audit->log($userId, 'blueprint_comment', $commentId, 'add_comment', null, ['blueprint_id' => $id]);
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

        $blueprint = $this->db()->table(Tables::BLUEPRINTS)->where('id', $id)->where('active', 0)->get()->getRowArray();
        if (!$blueprint) return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);

        $role = $this->getCurrentUserRole();
        if (!$role) return $this->JSONResponse('User tidak ditemukan', null, 404);

        if (!empty($blueprint['approver_id']) && (int) $blueprint['approver_id'] !== $userId && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya approver yang ditunjuk yang dapat menyetujui blueprint ini', null, 403);
        }

        if ($expectedStage === Enums::STAGE_PENDING_IT && $role !== Enums::IT_MANAGER && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya IT Manager yang dapat approve tahap ini', null, 403);
        }
        if ($expectedStage === Enums::STAGE_PENDING_DEPT && $role !== Enums::DEPT_HEAD && $role !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya Department Head yang dapat approve tahap ini', null, 403);
        }

        $expectedStatus = $expectedStage === Enums::STAGE_PENDING_IT
            ? Enums::PROJECT_STATUS_DRAFT
            : Enums::PROJECT_STATUS_PENDING;

        if ((int) $blueprint['status'] !== $expectedStatus) {
            return $this->JSONResponse('Status blueprint tidak sesuai untuk tahap approval ini', null, 400);
        }

        $this->db()->transStart();
        $message = $onSuccess($blueprint, $userId);
        $this->db()->transComplete();

        return $this->JSONResponse($message);
    }
}
