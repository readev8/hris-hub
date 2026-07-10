<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;

class Tickets extends BaseController
{
    public function index(): string
    {
        return $this->view('tickets/main_page', [
            'title' => 'Tickets',
        ]);
    }

    public function ajaxList()
    {
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
        helper('form');

        if ($this->request->getMethod() === 'POST') {
            $post = $this->request->getPost();
            $files = $this->request->getFileMultiple('images') ?? [];

            $savedFiles = [];
            if (!empty($files)) {
                $error = $this->validateUploadedFiles($files);
                if ($error) {
                    return $this->response->setJSON(['status' => false, 'message' => $error]);
                }
                $savedFiles = $this->saveUploadedFiles($files);
            }

            $result = $this->api->post_data('tickets/create', $post);

            if ($result && ($result['status'] ?? false)) {
                $ticketId = $result['data']['result']['id'] ?? null;

                if ($ticketId && !empty($savedFiles)) {
                    foreach ($savedFiles as $sf) {
                        $attResult = $this->api->post_data('tickets/' . $ticketId . '/attachments', $sf);
                        if (!$attResult || !($attResult['status'] ?? false)) {
                            $this->deleteUploadedFiles($savedFiles);
                            return $this->response->setJSON([
                                'status'  => false,
                                'message' => 'Gagal menyimpan metadata lampiran',
                            ]);
                        }
                    }
                }

                return $this->response->setJSON(['status' => true, 'redirect' => site_url('tickets')]);
            }

            if (!empty($savedFiles)) {
                $this->deleteUploadedFiles($savedFiles);
            }

            return $this->response->setJSON([
                'status'  => false,
                'message' => $result['data']['message'] ?? 'Failed to create ticket',
            ]);
        }

        return $this->view('tickets/create', ['title' => 'Create Ticket']);
    }

    public function detail(string $encryptedId): string
    {
        $result = $this->api->get_data('tickets/' . $encryptedId);

        return $this->view('tickets/detail', [
            'title'  => 'Ticket Detail',
            'ticket' => $result['data']['result'] ?? null,
            'token'  => $encryptedId,
        ]);
    }

    public function take(string $encryptedId)
    {
        $result = $this->api->post_data('tickets/' . $encryptedId . '/take');
        return $this->response->setJSON($result);
    }

    public function resolve(string $encryptedId)
    {
        $data = $this->request->getPost();
        $result = $this->api->post_data('tickets/' . $encryptedId . '/resolve', $data);
        return $this->response->setJSON($result);
    }

    public function close(string $encryptedId)
    {
        $result = $this->api->post_data('tickets/' . $encryptedId . '/close');
        return $this->response->setJSON($result);
    }

    public function reopen(string $encryptedId)
    {
        $data = $this->request->getPost();
        $result = $this->api->post_data('tickets/' . $encryptedId . '/reopen', $data);
        return $this->response->setJSON($result);
    }

    public function approve(string $encryptedId)
    {
        $result = $this->api->post_data('tickets/' . $encryptedId . '/approve');
        return $this->response->setJSON($result);
    }

    public function reject(string $encryptedId)
    {
        $data = $this->request->getPost();
        $result = $this->api->post_data('tickets/' . $encryptedId . '/reject', $data);
        return $this->response->setJSON($result);
    }

    public function addComment(string $encryptedId)
    {
        $content = $this->request->getPost('content');
        $files = $this->request->getFileMultiple('images') ?? [];

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

        return $this->response->setJSON($result);
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
        if (!preg_match('/^[a-zA-Z0-9_]+\.[a-z]{3,4}$/', $filename)) {
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

    private function validateUploadedFiles(array $files): ?string
    {
        if (count($files) > 3) {
            return 'Maksimal 3 file gambar';
        }

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                return 'File tidak valid';
            }

            $mime = $file->getMimeType();
            if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
                return 'Hanya file JPG dan PNG yang diizinkan: ' . $file->getClientName();
            }

            if ($file->getSize() > 2 * 1024 * 1024) {
                return 'File ' . $file->getClientName() . ' melebihi batas 2MB';
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
