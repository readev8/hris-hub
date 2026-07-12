<?php

namespace App\Controllers\Improvements\Action;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class Attachments extends BaseApi
{
    public function add(string $encryptedProjectId): ResponseInterface
    {
        $projectId = $this->resolveId($encryptedProjectId);
        if (!$projectId) {
            return $this->JSONResponse('ID proyek tidak valid', null, 400);
        }

        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        $project = $this->db()->table('projects')->where('id', $projectId)->get()->getRowArray();
        if (!$project) {
            return $this->JSONResponse('Proyek tidak ditemukan', null, 404);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $filename = trim($input['filename'] ?? '');
        $storedName = trim($input['stored_name'] ?? '');
        $mimeType = trim($input['mime_type'] ?? '');
        $fileSize = (int) ($input['file_size'] ?? 0);

        if (empty($filename) || empty($storedName) || empty($mimeType) || $fileSize <= 0) {
            return $this->JSONResponse('Data lampiran tidak lengkap', null, 400);
        }

        $this->db()->table('project_attachments')->insert([
            'project_id'  => $projectId,
            'uploaded_by' => $userId,
            'filename'    => $filename,
            'stored_name' => $storedName,
            'mime_type'   => $mimeType,
            'file_size'   => $fileSize,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $attachmentId = $this->db()->insertID();

        return $this->JSONResponse('Lampiran ditambahkan', [
            'id' => $this->api->encryptId($attachmentId),
        ], 201);
    }

    public function delete($encryptedProjectId = null, $encryptedId = null): ResponseInterface
    {
        if (!$encryptedId) {
            return $this->JSONResponse('ID tidak valid', null, 400);
        }
        $id = $this->resolveId($encryptedId);
        if (!$id) {
            return $this->JSONResponse('ID tidak valid', null, 400);
        }

        $attachment = $this->db()->table('project_attachments')->where('id', $id)->get()->getRowArray();
        if (!$attachment) {
            return $this->JSONResponse('Lampiran tidak ditemukan', null, 404);
        }

        $this->db()->table('project_attachments')->delete(['id' => $id]);

        return $this->JSONResponse('Lampiran dihapus', [
            'stored_name' => $attachment['stored_name'],
            'filename'    => $attachment['filename'],
        ]);
    }
}
