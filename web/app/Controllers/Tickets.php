<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;

class Tickets extends BaseController
{
    private function guard(string $action = 'can_view'): bool
    {
        return has_permission('tickets', $action);
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
        return $this->view('tickets/main_page', [
            'title' => 'Tickets',
        ]);
    }

    public function ajaxList()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0]);
        }
        $params = $this->request->getGet();
        $result = $this->api->get_data('tickets', $params);

        if (!$result || !($result['status'] ?? false)) {
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

                $post['type'] = (string) ($post['type'] ?? '');
                $post['priority'] = (string) ($post['priority'] ?? '');

                $rules = [
                    'title'       => 'required|min_length[5]|max_length[255]',
                    'description' => 'required|min_length[10]',
                    'type'        => 'required|in_list[0,1,2,3]',
                    'priority'    => 'required|in_list[0,1,2,3]',
                ];
                if (($post['type'] ?? '') === '0') {
                    $rules['page_id'] = 'required';
                }
                if (!$this->validate($rules)) {
                    log_message('error', 'Ticket create validation failed: ' . json_encode($this->validator->getErrors()) . ' POST: ' . json_encode($post));
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

                $result = $this->api->post_data('tickets/create', $post);

                if (!$result) {
                    log_message('error', 'Ticket create API unreachable for data: ' . json_encode($post));
                    return $this->response->setJSON([
                        'status'  => false,
                        'message' => 'Server API tidak terjangkau',
                    ]);
                }

                if ($result['status'] ?? false) {
                    $ticketId = $result['data']['result']['id'] ?? null;
                    $trackingCode = $result['data']['result']['tracking_code'] ?? null;

                    if ($ticketId && !empty($savedFiles)) {
                        foreach ($savedFiles as $sf) {
                            $attResult = $this->api->post_data('tickets/' . $ticketId . '/attachments', $sf);
                            if (!$attResult || !($attResult['status'] ?? false)) {
                                $this->deleteUploadedFiles($savedFiles);
                                log_message('error', 'Ticket attachment metadata save failed for ' . $ticketId);
                                return $this->response->setJSON([
                                    'status'  => false,
                                    'message' => 'Gagal menyimpan metadata lampiran',
                                ]);
                            }
                        }
                    }

                    return $this->response->setJSON([
                        'status'        => true,
                        'redirect'      => site_url('tickets'),
                        'tracking_code' => $trackingCode,
                    ]);
                }

                if (!empty($savedFiles)) {
                    $this->deleteUploadedFiles($savedFiles);
                }

                log_message('error', 'Tickets create API failed: ' . json_encode($result));
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => $result['data']['message'] ?? 'Failed to create ticket',
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Ticket create exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
                ]);
            }
        }

        return $this->view('tickets/create', ['title' => 'Create Ticket']);
    }

    public function update(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $post = $this->request->getPost();
        if (empty($post)) {
            $rawBody = $this->request->getBody();
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $post = $decoded;
            }
        }

        $rules = [
            'title'       => 'permit_empty|min_length[5]|max_length[255]',
            'description' => 'permit_empty|min_length[10]',
            'type'        => 'permit_empty|in_list[0,1,2,3]',
            'priority'    => 'permit_empty|in_list[0,1,2,3]',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $result = $this->api->post_data('tickets/' . $encryptedId . '/update', $post);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets update API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function delete(string $encryptedId)
    {
        if (!$this->guard('can_delete')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('tickets/' . $encryptedId . '/delete');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets delete API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function assign(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $post = $this->request->getPost();

        if (empty($post['assignee_id'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Assignee is required']);
        }

        $result = $this->api->post_data('tickets/' . $encryptedId . '/assign', $post);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets assign API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function detail(string $encryptedId): string
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
        $result = $this->api->get_data('tickets/' . $encryptedId);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets detail API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->view('tickets/detail', [
            'title'  => 'Ticket Detail',
            'ticket' => $result['data']['result'] ?? null,
            'token'  => $encryptedId,
        ]);
    }

    public function edit(string $encryptedId): string
    {
        if (!$this->guard('can_update')) {
            return redirect()->to('/dashboard');
        }
        $result = $this->api->get_data('tickets/' . $encryptedId);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets edit API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->view('tickets/edit', [
            'title'  => 'Edit Ticket',
            'ticket' => $result['data']['result'] ?? null,
            'token'  => $encryptedId,
        ]);
    }

    public function take(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('tickets/' . $encryptedId . '/take');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets take API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function resolve(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();

        if (empty(trim($data['resolution_note'] ?? ''))) {
            return $this->response->setJSON(['status' => false, 'message' => 'Resolution note is required']);
        }

        $result = $this->api->post_data('tickets/' . $encryptedId . '/resolve', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets resolve API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function close(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('tickets/' . $encryptedId . '/close');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets close API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function reopen(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();

        if (empty(trim($data['rejection_note'] ?? ''))) {
            return $this->response->setJSON(['status' => false, 'message' => 'Reopen reason is required']);
        }

        $result = $this->api->post_data('tickets/' . $encryptedId . '/reopen', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets reopen API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function approve(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('tickets/' . $encryptedId . '/approve');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets approve API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function reject(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();

        if (empty(trim($data['rejection_note'] ?? ''))) {
            return $this->response->setJSON(['status' => false, 'message' => 'Rejection note is required']);
        }

        $result = $this->api->post_data('tickets/' . $encryptedId . '/reject', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets reject API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function addComment(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $content = $this->request->getPost('content');

        if (empty(trim($content ?? ''))) {
            return $this->response->setJSON(['status' => false, 'message' => 'Comment content is required']);
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

        $data = ['content' => $content];
        $result = $this->api->post_data('tickets/' . $encryptedId . '/comments', $data);

        if ($result && ($result['status'] ?? false)) {
            $commentId = $result['data']['result']['id'] ?? null;

            if ($commentId && !empty($savedFiles)) {
                foreach ($savedFiles as $sf) {
                    $sf['comment_id'] = $commentId;
                    $attResult = $this->api->post_data('tickets/' . $encryptedId . '/attachments', $sf);
                    if (!$attResult || !($attResult['status'] ?? false)) {
                        $this->deleteUploadedFiles($savedFiles);
                        log_message('error', 'Ticket comment attachment save failed for comment ' . $commentId);
                        return $this->response->setJSON([
                            'status'  => false,
                            'message' => 'Gagal menyimpan metadata lampiran',
                        ]);
                    }
                }
            }

            return $this->response->setJSON($result);
        }

        if (!empty($savedFiles)) {
            $this->deleteUploadedFiles($savedFiles);
        }

        log_message('error', 'Tickets addComment API failed for ' . $encryptedId . ': ' . json_encode($result));
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
            $attResult = $this->api->post_data('tickets/' . $encryptedId . '/attachments', $sf);
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

        $filePath = WRITEPATH . 'uploads/tickets/' . $filename;
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

    public function deleteAttachment(string $encryptedId)
    {
        if (!$this->guard('can_delete')) {
            return $this->denyResponse();
        }
        $result = $this->api->delete_data('attachments/' . $encryptedId);
        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets deleteAttachment API failed for ' . $encryptedId . ': ' . json_encode($result));
        }
        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function move(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $post = $this->request->getPost();

        if (!isset($post['new_status'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Status is required']);
        }

        $result = $this->api->post_data('tickets/' . $encryptedId . '/move', $post);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Tickets move API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    private function validateUploadedFiles(array $files): ?string
    {
        $maxFiles = 5;
        $maxSize  = 5 * 1024 * 1024;
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];

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
                return 'File ' . $file->getClientName() . ' melebihi batas ' . ($maxSize / 1024 / 1024) . 'MB';
            }
        }

        return null;
    }

    private function saveUploadedFiles(array $files): array
    {
        $saved = [];
        $uploadPath = WRITEPATH . 'uploads/tickets/';

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
        $uploadPath = WRITEPATH . 'uploads/tickets/';
        foreach ($files as $sf) {
            $path = $uploadPath . ($sf['stored_name'] ?? '');
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
