<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;

class Improvements extends BaseController
{
    private function guard(string $action = 'can_view'): bool
    {
        return has_permission('improvements', $action);
    }

    private function denyResponse()
    {
        return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki izin']);
    }

    public function index(): string
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
        return $this->view('improvements/main_page', [
            'title' => 'Improvements',
        ]);
    }

    public function ajaxList()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0]);
        }
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
        if (!$this->guard('can_create')) {
            return $this->denyResponse();
        }
        helper('form');

        if ($this->request->getMethod() === 'POST') {
            try {
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

                $files = array_filter($this->request->getFileMultiple('images') ?? [], function ($f) {
                    return $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE;
                });

                $savedFiles = [];
                if (!empty($files)) {
                    $error = $this->validateUploadedFiles($files);
                    if ($error) {
                        return $this->response->setJSON(['status' => false, 'message' => $error]);
                    }
                    $savedFiles = $this->saveUploadedFiles($files);
                }

                $result = $this->api->post_data('improvements/create', $post);

                if (!$result) {
                    log_message('error', 'Improvements create API unreachable for data: ' . json_encode($post));
                    return $this->response->setJSON([
                        'status'  => false,
                        'message' => 'Server API tidak terjangkau',
                    ]);
                }

                if ($result['status'] ?? false) {
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
            } catch (\Throwable $e) {
                log_message('error', 'Improvements create exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
                ]);
            }
        }

        $usersResult = $this->api->get_data('users');
        $usersList = $usersResult['data']['result'] ?? [];

        return $this->view('improvements/create', [
            'title' => 'Create Improvement',
            'users' => $usersList,
        ]);
    }

    public function detail(string $encryptedId): string
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
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
        if (!$this->guard('can_update')) {
            return redirect()->to('/dashboard');
        }
        $result = $this->api->get_data('improvements/' . $encryptedId);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements edit API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        $usersResult = $this->api->get_data('users');
        $usersList = $usersResult['data']['result'] ?? [];

        return $this->view('improvements/edit', [
            'title'       => 'Edit Improvement',
            'improvement' => $result['data']['result'] ?? null,
            'token'       => $encryptedId,
            'users'       => $usersList,
        ]);
    }

    public function update(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
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
        if (!$this->guard('can_delete')) {
            return $this->denyResponse();
        }
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
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $files = array_filter($this->request->getFileMultiple('images') ?? [], function ($f) {
            return $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE;
        });
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
        if (!$this->guard('can_approve')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('improvements/' . $encryptedId . '/approve-it');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements approveIt API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function approveDept(string $encryptedId)
    {
        if (!$this->guard('can_approve')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('improvements/' . $encryptedId . '/approve-dept');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements approveDept API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function reject(string $encryptedId)
    {
        if (!$this->guard('can_approve')) {
            return $this->denyResponse();
        }
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
        if (!$this->guard('can_create')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();
        $result = $this->api->post_data('improvements/' . $encryptedId . '/resubmit', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Improvements resubmit API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function addComment(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
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
        $maxFiles = 5;
        $maxSize  = 500 * 1024;
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (count($files) > $maxFiles) {
            return 'Maksimal ' . $maxFiles . ' file';
        }

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (!$file->isValid()) {
                return 'File tidak valid';
            }

            $mime = $file->getMimeType();
            if (!in_array($mime, $allowedMimes, true)) {
                return 'Hanya file JPG, PNG, GIF, WebP, dan PDF yang diizinkan: ' . $file->getClientName();
            }

            if ($file->getSize() > $maxSize) {
                return 'File ' . $file->getClientName() . ' melebihi batas ' . ($maxSize / 1024) . 'KB';
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
