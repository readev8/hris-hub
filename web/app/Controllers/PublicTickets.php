<?php

namespace App\Controllers;

use CodeIgniter\HTTP\UploadedFile;

/**
 * ============================================================================
 * PUBLIC TICKETS CONTROLLER
 * ============================================================================
 *
 * Description: Guest-facing ticket portal to submit and track tickets without
 * authentication.
 *
 * Responsibilities:
 * - Render public ticket pages (list, create, detail)
 * - Submit public tickets with optional file attachments
 * - Look up tickets by tracking code (single and batch)
 * - Close public tickets by tracking code
 * - Serve public master data (projects, modules, pages) and attachment files
 */
class PublicTickets extends BaseController
{
    public function list(): string
    {
        return $this->view('public/tickets_list', [
            'title' => 'My Tickets',
        ]);
    }

    public function ajaxList()
    {
        $params = $this->request->getGet();
        $result = $this->api->get_data('tickets/public/list', $params);
        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON([
                'data'     => [],
                'total'    => 0,
                'page'     => 1,
                'per_page' => 20,
            ]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function create()
    {
        helper('form');

        if ($this->request->getMethod() === 'POST') {
            return $this->ajaxCreate();
        }

        return $this->view('public/tickets_create', [
            'title' => 'Submit a Ticket',
        ]);
    }

    public function detail(string $code)
    {
        return $this->view('public/tickets_detail', [
            'title'         => 'Ticket Detail',
            'tracking_code' => $code,
        ]);
    }

    public function ajaxCreate()
    {
        try {
            $post = $this->request->getPost();

            $post['type']     = (string) ($post['type'] ?? '');
            $post['priority'] = (string) ($post['priority'] ?? '');

            $rules = [
                'title'       => 'required|min_length[5]|max_length[255]',
                'description' => 'required|min_length[10]',
                'type'        => 'required|in_list[0,1,2,3,4,5]',
                'priority'    => 'required|in_list[0,1,2,3]',
            ];
            if (in_array($post['type'] ?? '', ['0', '3', '4', '5'], true)) {
                $rules['page_id'] = 'required';
            }
            if (!$this->validate($rules)) {
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
                $error = $this->validateUploadedFiles($files, 3, 2 * 1024 * 1024);
                if ($error) {
                    return $this->response->setJSON(['status' => false, 'message' => $error]);
                }
                $savedFiles = $this->saveUploadedFiles($files);
            }

            $result = $this->api->post_data('tickets/public/create', $post);

            if (!$result) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Server API tidak terjangkau',
                ]);
            }

            if ($result['status'] ?? false) {
                $ticketId     = $result['data']['result']['id'] ?? null;
                $trackingCode = $result['data']['result']['tracking_code'] ?? null;

                if ($ticketId && !empty($savedFiles)) {
                    foreach ($savedFiles as $sf) {
                        $attResult = $this->api->post_data('tickets/public/' . $ticketId . '/attachments', $sf);
                        if (!$attResult || !($attResult['status'] ?? false)) {
                            $this->deleteUploadedFiles($savedFiles);
                            return $this->response->setJSON([
                                'status'  => false,
                                'message' => 'Gagal menyimpan metadata lampiran',
                            ]);
                        }
                    }
                }

                return $this->response->setJSON([
                    'status'        => true,
                    'redirect'      => site_url('public/tickets'),
                    'tracking_code' => $trackingCode,
                ]);
            }

            if (!empty($savedFiles)) {
                $this->deleteUploadedFiles($savedFiles);
            }

            return $this->response->setJSON([
                'status'  => false,
                'message' => $result['data']['message'] ?? 'Gagal membuat ticket',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'PublicTickets ajaxCreate exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan server']);
        }
    }

    public function ajaxLookup(string $code)
    {
        $result = $this->api->get_data('tickets/public/by-code/' . $code);
        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => $result['data']['message'] ?? 'Ticket tidak ditemukan',
            ]);
        }
        return $this->response->setJSON($result['data']['result']);
    }

    public function ajaxBatchLookup()
    {
        $codes = $this->request->getGet('codes') ?? '';
        $result = $this->api->get_data('tickets/public/batch-by-codes', ['codes' => $codes]);
        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON([]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function ajaxClose(string $code)
    {
        $result = $this->api->post_data('tickets/public/' . $code . '/close');
        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => $result['data']['message'] ?? 'Gagal menutup ticket',
            ]);
        }
        return $this->response->setJSON([
            'status'  => true,
            'message' => $result['data']['message'] ?? 'Ticket berhasil ditutup',
        ]);
    }

    public function ajaxProjects()
    {
        $result = $this->api->get_data('master-projects/public/active');
        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON([]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function ajaxModules(string $encryptedProjectId)
    {
        $result = $this->api->get_data('master-projects/public/' . $encryptedProjectId . '/modules');
        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON([]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function ajaxPages(string $encryptedModuleId)
    {
        $result = $this->api->get_data('modules/public/' . $encryptedModuleId . '/pages');
        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON([]);
        }
        return $this->response->setJSON($result['data']['result'] ?? []);
    }

    public function serveAttachment(string $filename)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+\.[a-z0-9]{3,5}$/', $filename)) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $path = WRITEPATH . 'uploads/tickets/' . $filename;
        if (!is_file($path)) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($path));
    }

    private function validateUploadedFiles(array $files, int $maxFiles, int $maxSize): ?string
    {
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint',
            'text/csv',
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
                return 'Tipe file tidak diizinkan: ' . $file->getClientName();
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
