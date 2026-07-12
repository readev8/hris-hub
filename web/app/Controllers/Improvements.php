<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;

class Improvements extends BaseController
{
    public function index(): string
    {
        return $this->view('improvements/main_page', [
            'title' => 'Improvements',
        ]);
    }

    public function ajaxList()
    {
        $params = $this->request->getGet();
        $result = $this->api->get_data('improvements', $params);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements ajaxList API failed: ' . json_encode($result));
            return $this->response->setJSON(['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0]);
        }

        $data = $result['data']['result'];
        return $this->response->setJSON([
            'data'            => $data['data'] ?? [],
            'recordsTotal'    => $data['total'] ?? 0,
            'recordsFiltered' => $data['total'] ?? 0,
        ]);
    }

    public function create()
    {
        helper('form');

        if ($this->request->getMethod() === 'POST') {
            $post = $this->request->getPost();

            $validationRules = [
                'name'          => 'required|min_length[3]|max_length[255]',
                'description'   => 'required|min_length[10]',
                'business_case' => 'required|min_length[10]',
                'priority'      => 'in_list[0,1,2,3]',
            ];
            if (!$this->validate($validationRules)) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Validation failed',
                    'errors'  => $this->validator->getErrors(),
                ]);
            }

            $files = $this->request->getFileMultiple('images') ?? [];

            $savedFiles = [];
            if (!empty($files)) {
                $error = $this->validateUploadedFiles($files);
                if ($error) {
                    return $this->response->setJSON(['status' => false, 'message' => $error]);
                }
                $savedFiles = $this->saveUploadedFiles($files);
            }

            $result = $this->api->post_data('improvements/create', $post);

            if ($result && ($result['status'] ?? false)) {
                $improvementId = $result['data']['result']['id'] ?? null;

                if ($improvementId && !empty($savedFiles)) {
                    foreach ($savedFiles as $sf) {
                        $attResult = $this->api->post_data('improvements/' . $improvementId . '/attachments', $sf);
                        if (!$attResult || !($attResult['status'] ?? false)) {
                            $this->deleteUploadedFiles($savedFiles);
                            log_message('error', 'Improvement attachment save failed for ' . $improvementId);
                            return $this->response->setJSON([
                                'status'  => false,
                                'message' => 'Gagal menyimpan file',
                            ]);
                        }
                    }
                }

                return $this->response->setJSON(['status' => true, 'redirect' => site_url('improvements')]);
            }

            if (!empty($savedFiles)) {
                $this->deleteUploadedFiles($savedFiles);
            }

            log_message('error', 'Improvements create API failed: ' . json_encode($result));
            return $this->response->setJSON([
                'status'  => false,
                'message' => $result['data']['message'] ?? 'Failed to create improvement',
            ]);
        }

        return $this->view('improvements/create', ['title' => 'Create Improvement']);
    }

    public function detail(string $encryptedId): string
    {
        $result = $this->api->get_data('improvements/' . $encryptedId);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements detail API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->view('improvements/detail', [
            'title'       => 'Improvement Detail',
            'improvement' => $result['data']['result'] ?? null,
            'token'       => $encryptedId,
        ]);
    }

    public function edit(string $encryptedId): string
    {
        $result = $this->api->get_data('improvements/' . $encryptedId);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements edit API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->view('improvements/edit', [
            'title'       => 'Edit Improvement',
            'improvement' => $result['data']['result'] ?? null,
            'token'       => $encryptedId,
        ]);
    }

    public function update(string $encryptedId)
    {
        $post = $this->request->getPost();

        $validationRules = [
            'name'          => 'permit_empty|min_length[3]|max_length[255]',
            'description'   => 'permit_empty|min_length[10]',
            'business_case' => 'permit_empty|min_length[10]',
            'priority'      => 'permit_empty|in_list[0,1,2,3]',
        ];
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $result = $this->api->post_data('improvements/' . $encryptedId . '/update', $post);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements update API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function delete(string $encryptedId)
    {
        $detail = $this->api->get_data('improvements/' . $encryptedId);
        $attachments = $detail['data']['result']['attachments'] ?? [];

        $result = $this->api->post_data('improvements/' . $encryptedId . '/delete');

        if ($result && ($result['status'] ?? false)) {
            $this->deleteUploadedFilesById($attachments);
        }

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements delete API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function uploadAttachment(string $encryptedId)
    {
        $files = $this->request->getFileMultiple('images') ?? [];
        if (empty($files)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada file yang diunggah']);
        }

        $error = $this->validateUploadedFiles($files);
        if ($error) {
            return $this->response->setJSON(['status' => false, 'message' => $error]);
        }

        $savedFiles = $this->saveUploadedFiles($files);
        $attachmentIds = [];

        foreach ($savedFiles as $sf) {
            $attResult = $this->api->post_data('improvements/' . $encryptedId . '/attachments', $sf);
            if ($attResult && ($attResult['status'] ?? false)) {
                $attachmentIds[] = $attResult['data']['result']['id'] ?? null;
            } else {
                $this->deleteUploadedFiles($savedFiles);
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Gagal menyimpan metadata lampiran',
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'data'   => ['ids' => $attachmentIds, 'files' => $savedFiles],
        ]);
    }

    public function serveFile(string $filename)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+\.[a-z0-9]{3,5}$/', $filename)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $filePath = WRITEPATH . 'uploads/improvements/' . $filename;
        if (!is_file($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($filePath);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Length', (string) filesize($filePath))
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($filePath));
    }

    public function approveIt(string $encryptedId)
    {
        $result = $this->api->post_data('improvements/' . $encryptedId . '/approve-it');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements approveIt API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function approveDept(string $encryptedId)
    {
        $result = $this->api->post_data('improvements/' . $encryptedId . '/approve-dept');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements approveDept API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function reject(string $encryptedId)
    {
        $data = $this->request->getPost();

        if (empty($data['notes'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Rejection notes are required']);
        }

        $result = $this->api->post_data('improvements/' . $encryptedId . '/reject', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements reject API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function resubmit(string $encryptedId)
    {
        $data = $this->request->getPost();
        $result = $this->api->post_data('improvements/' . $encryptedId . '/resubmit', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements resubmit API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function addComment(string $encryptedId)
    {
        $data = $this->request->getPost();

        if (empty(trim($data['content'] ?? ''))) {
            return $this->response->setJSON(['status' => false, 'message' => 'Comment content is required']);
        }

        $result = $this->api->post_data('improvements/' . $encryptedId . '/comments', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements addComment API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    private function validateUploadedFiles(array $files): ?string
    {
        $maxFiles = 3;
        $maxSize  = 5 * 1024 * 1024;
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];

        if (count($files) > $maxFiles) {
            return 'Maksimal ' . $maxFiles . ' file';
        }

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                return 'File tidak valid';
            }

            $mime = $file->getMimeType();
            if (!in_array($mime, $allowedMimes, true)) {
                return 'Hanya file JPG, PNG, GIF, WebP, dan PDF yang diizinkan: ' . $file->getClientName();
            }

            if ($file->getSize() > $maxSize) {
                return 'File ' . $file->getClientName() . ' melebihi batas ' . ($maxSize / 1024 / 1024) . 'MB';
            }
        }

        return null;
    }

    private function saveUploadedFiles(array $files): array
    {
        $saved = [];
        $uploadPath = WRITEPATH . 'uploads/improvements/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $ext = $file->getExtension();
            $storedName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

            $saved[] = [
                'filename'    => $file->getClientName(),
                'stored_name' => $storedName,
                'mime_type'   => $file->getMimeType(),
                'file_size'   => $file->getSize(),
            ];

            $file->move($uploadPath, $storedName);
        }

        return $saved;
    }

    private function deleteUploadedFiles(array $files): void
    {
        $uploadPath = WRITEPATH . 'uploads/improvements/';
        foreach ($files as $sf) {
            $path = $uploadPath . ($sf['stored_name'] ?? '');
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function deleteUploadedFilesById(array $attachments): void
    {
        $uploadPath = WRITEPATH . 'uploads/improvements/';
        foreach ($attachments as $att) {
            $path = $uploadPath . ($att['stored_name'] ?? '');
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
