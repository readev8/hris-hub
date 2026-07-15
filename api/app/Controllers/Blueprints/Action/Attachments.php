<?php

namespace App\Controllers\Blueprints\Action;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;

class Attachments extends BaseApi
{
    public function add(string $encryptedBlueprintId): ResponseInterface
    {
        $blueprintId = $this->resolveId($encryptedBlueprintId);
        if (!$blueprintId) return $this->JSONResponse('ID blueprint tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $blueprint = $this->db()->table('blueprints')->where('id', $blueprintId)->get()->getRowArray();
        if (!$blueprint) return $this->JSONResponse('Blueprint tidak ditemukan', null, 404);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $filename = trim($input['filename'] ?? '');
        $storedName = trim($input['stored_name'] ?? '');
        $mimeType = trim($input['mime_type'] ?? '');
        $fileSize = (int) ($input['file_size'] ?? 0);
        $moduleId = !empty($input['module_id']) ? $this->resolveId($input['module_id']) : null;
        $sectionType = trim($input['section_type'] ?? '');
        $sectionId = !empty($input['section_id']) ? $this->resolveId($input['section_id']) : null;

        if (empty($filename) || empty($storedName) || empty($mimeType) || $fileSize <= 0) {
            return $this->JSONResponse('Data lampiran tidak lengkap', null, 400);
        }

        $this->db()->table('blueprint_attachments')->insert([
            'blueprint_id' => $blueprintId,
            'module_id'    => $moduleId,
            'section_type' => $sectionType,
            'section_id'   => $sectionId,
            'uploaded_by'  => $userId,
            'filename'     => $filename,
            'stored_name'  => $storedName,
            'mime_type'    => $mimeType,
            'file_size'    => $fileSize,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $attachmentId = $this->db()->insertID();

        return $this->JSONResponse('Lampiran ditambahkan', [
            'id' => $this->api->encryptId($attachmentId),
        ], 201);
    }

    public function delete($encryptedBlueprintId = null, $encryptedId = null): ResponseInterface
    {
        if (!$encryptedId) return $this->JSONResponse('ID tidak valid', null, 400);

        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $attachment = $this->db()->table('blueprint_attachments')->where('id', $id)->get()->getRowArray();
        if (!$attachment) return $this->JSONResponse('Lampiran tidak ditemukan', null, 404);

        $this->db()->table('blueprint_attachments')->delete(['id' => $id]);

        return $this->JSONResponse('Lampiran dihapus', [
            'stored_name' => $attachment['stored_name'],
            'filename'    => $attachment['filename'],
        ]);
    }
}
