<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;

class Blueprints extends BaseController
{
    private function guard(string $action = 'can_view'): bool
    {
        return has_permission('blueprints', $action);
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
        return $this->view('blueprints/main_page', [
            'title' => 'Blueprints',
        ]);
    }

    public function ajaxList()
    {
        if (!$this->guard()) {
            return $this->response->setJSON(['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0]);
        }
        $params = $this->request->getGet();
        $result = $this->api->get_data('blueprints', $params);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints ajaxList API failed: ' . json_encode($result));
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
                    'name'        => 'required|min_length[3]|max_length[255]',
                    'description' => 'permit_empty|min_length[10]',
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
                    $error = $this->validateUploadedFiles($files, 'attachments');
                    if ($error) {
                        return $this->response->setJSON(['status' => false, 'message' => $error]);
                    }
                    $savedFiles = $this->saveUploadedFiles($files);
                }

                $result = $this->api->post_data('blueprints/create', $post);

                if (!$result) {
                    log_message('error', 'Blueprints create API unreachable for data: ' . json_encode($post));
                    return $this->response->setJSON([
                        'status'  => false,
                        'message' => 'Server API tidak terjangkau',
                    ]);
                }

                if ($result['status'] ?? false) {
                    $blueprintId = $result['data']['result']['id'] ?? null;

                    if ($blueprintId && !empty($savedFiles)) {
                        foreach ($savedFiles as $sf) {
                            $attResult = $this->api->post_data('blueprints/' . $blueprintId . '/attachments', $sf);
                            if (!$attResult || !($attResult['status'] ?? false)) {
                                $this->deleteUploadedFiles($savedFiles);
                                log_message('error', 'Blueprint attachment save failed for ' . $blueprintId);
                                return $this->response->setJSON([
                                    'status'  => false,
                                    'message' => 'Gagal menyimpan file',
                                ]);
                            }
                        }
                    }

                    return $this->response->setJSON(['status' => true, 'redirect' => site_url('blueprints/' . $blueprintId)]);
                }

                if (!empty($savedFiles)) {
                    $this->deleteUploadedFiles($savedFiles);
                }

                log_message('error', 'Blueprints create API failed: ' . json_encode($result));
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => $result['data']['message'] ?? 'Failed to create blueprint',
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Blueprints create exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
                ]);
            }
        }

        return $this->view('blueprints/create', [
            'title' => 'Create Blueprint',
        ]);
    }

    public function detail(string $encryptedId): string
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
        $result = $this->api->get_data('blueprints/' . $encryptedId);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints detail API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->view('blueprints/detail', [
            'title'     => 'Blueprint Detail',
            'blueprint' => $result['data']['result'] ?? null,
            'token'     => $encryptedId,
            'userPermissions' => session('permissions') ?? [],
        ]);
    }

    public function refreshDetail(string $encryptedId)
    {
        if (!$this->guard()) {
            return $this->denyResponse();
        }

        $result = $this->api->get_data('blueprints/' . $encryptedId);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints refreshDetail API failed for ' . $encryptedId . ': ' . json_encode($result));
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Failed to fetch blueprint data',
            ]);
        }

        $blueprint = $result['data']['result'] ?? [];
        $st = (int) ($blueprint['status'] ?? -1);

        $bpPerms = (session('permissions') ?? [])['blueprints'] ?? [];
        $availableActions = [];
        if (!empty($bpPerms['can_update'])) {
            $availableActions[] = 'edit';
        }
        if (!empty($bpPerms['can_delete'])) {
            $availableActions[] = 'delete';
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => [
                'blueprint' => $blueprint,
                'modules' => $blueprint['modules'] ?? [],
                'comments' => $blueprint['comments'] ?? [],
                'attachments' => $blueprint['attachments'] ?? [],
                'status' => $st,
                'status_name' => $blueprint['status_name'] ?? 'Draft',
                'available_actions' => $availableActions,
            ],
        ]);
    }

    public function edit(string $encryptedId): string
    {
        if (!$this->guard('can_update')) {
            return redirect()->to('/dashboard');
        }
        $result = $this->api->get_data('blueprints/' . $encryptedId);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints edit API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        $improvementsResult = $this->api->get_data('improvements');
        $improvementsList = $improvementsResult['data']['result']['data'] ?? [];

        return $this->view('blueprints/edit', [
            'title'       => 'Edit Blueprint',
            'blueprint'   => $result['data']['result'] ?? null,
            'token'       => $encryptedId,
            'improvements' => $improvementsList,
        ]);
    }

    public function update(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $post = $this->request->getPost();

        $validationRules = [
            'name'        => 'permit_empty|min_length[3]|max_length[255]',
            'description' => 'permit_empty|min_length[10]',
        ];
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $result = $this->api->post_data('blueprints/' . $encryptedId . '/update', $post);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints update API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function delete(string $encryptedId)
    {
        if (!$this->guard('can_delete')) {
            return $this->denyResponse();
        }
        $detail = $this->api->get_data('blueprints/' . $encryptedId);
        $attachments = $detail['data']['result']['attachments'] ?? [];

        $result = $this->api->post_data('blueprints/' . $encryptedId . '/delete');

        if ($result && ($result['status'] ?? false)) {
            $this->deleteUploadedFilesById($attachments);
        }

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints delete API failed for ' . $encryptedId . ': ' . json_encode($result));
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

        $error = $this->validateUploadedFiles($files, 'attachments');
        if ($error) {
            return $this->response->setJSON(['status' => false, 'message' => $error]);
        }

        $savedFiles = $this->saveUploadedFiles($files);
        $attachmentIds = [];

        foreach ($savedFiles as $sf) {
            $attResult = $this->api->post_data('blueprints/' . $encryptedId . '/attachments', $sf);
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

        $filePath = WRITEPATH . 'uploads/blueprints/' . $filename;
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

    public function addComment(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();

        if (empty(trim($data['content'] ?? ''))) {
            return $this->response->setJSON(['status' => false, 'message' => 'Comment content is required']);
        }

        $result = $this->api->post_data('blueprints/' . $encryptedId . '/comments', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints addComment API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function createModule(string $encryptedId)
    {
        if (!$this->guard('can_create')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();
        $result = $this->api->post_data('blueprints/' . $encryptedId . '/modules', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints createModule API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function updateModule(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();
        $result = $this->api->post_data('blueprints/modules/' . $encryptedId . '/update', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints updateModule API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function deleteModule(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('blueprints/modules/' . $encryptedId . '/delete');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints deleteModule API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function createBusinessScenario(string $moduleId)
    {
        if (!$this->guard('can_create')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();
        $blueprintToken = $data['blueprint_token'] ?? null;
        unset($data['blueprint_token']);

        $result = $this->api->post_data('blueprints/modules/' . $moduleId . '/business-scenarios', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints createBusinessScenario API failed for ' . $moduleId . ': ' . json_encode($result));
            return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
        }

        $scenarioId = $result['data']['result']['id'] ?? null;
        if ($scenarioId && $blueprintToken) {
            $files = array_filter($this->request->getFileMultiple('images') ?? [], function ($f) {
                return $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE;
            });
            if (!empty($files)) {
                $error = $this->validateUploadedFiles($files, 'design_pages');
                if ($error) {
                    return $this->response->setJSON(['status' => false, 'message' => $error]);
                }
                $savedFiles = $this->saveUploadedFiles($files);
                foreach ($savedFiles as $sf) {
                    $attData = $sf;
                    $attData['section_type'] = 'business_scenario';
                    $attData['section_id'] = $scenarioId;
                    $attData['module_id'] = $moduleId;
                    $attResult = $this->api->post_data('blueprints/' . $blueprintToken . '/attachments', $attData);
                    if (!$attResult || !($attResult['status'] ?? false)) {
                        $this->deleteUploadedFiles($savedFiles);
                        log_message('error', 'Blueprint scenario attachment save failed for scenario ' . $scenarioId);
                        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan file lampiran']);
                    }
                }
            }
        }

        return $this->response->setJSON($result);
    }

    public function updateBusinessScenario(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();
        $blueprintToken = $data['blueprint_token'] ?? null;
        unset($data['blueprint_token']);

        $deletedAttachments = $data['deleted_attachments'] ?? '';
        unset($data['deleted_attachments']);

        $result = $this->api->post_data('blueprints/business-scenarios/' . $encryptedId . '/update', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints updateBusinessScenario API failed for ' . $encryptedId . ': ' . json_encode($result));
            return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
        }

        if ($blueprintToken) {
            if ($deletedAttachments) {
                $deletedIds = json_decode($deletedAttachments, true);
                if (is_array($deletedIds)) {
                    $uploadPath = WRITEPATH . 'uploads/blueprints/';
                    foreach ($deletedIds as $encAttId) {
                        $attResult = $this->api->delete_data('blueprints/' . $blueprintToken . '/attachments/' . $encAttId);
                        if ($attResult && ($attResult['status'] ?? false)) {
                            $storedName = $attResult['data']['result']['stored_name'] ?? '';
                            if ($storedName) {
                                $filePath = $uploadPath . $storedName;
                                if (is_file($filePath)) { unlink($filePath); }
                            }
                        } else {
                            log_message('error', 'Blueprints: Failed to delete attachment ' . $encAttId . ': ' . json_encode($attResult));
                        }
                    }
                }
            }

            $files = array_filter($this->request->getFileMultiple('images') ?? [], function ($f) {
                return $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE;
            });
            if (!empty($files)) {
                $error = $this->validateUploadedFiles($files, 'design_pages');
                if ($error) {
                    return $this->response->setJSON(['status' => false, 'message' => $error]);
                }
                $savedFiles = $this->saveUploadedFiles($files);
                foreach ($savedFiles as $sf) {
                    $attData = $sf;
                    $attData['section_type'] = 'business_scenario';
                    $attData['section_id'] = $encryptedId;
                    $attResult = $this->api->post_data('blueprints/' . $blueprintToken . '/attachments', $attData);
                    if (!$attResult || !($attResult['status'] ?? false)) {
                        $this->deleteUploadedFiles($savedFiles);
                        log_message('error', 'Blueprint scenario attachment save failed for scenario ' . $encryptedId);
                        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan file lampiran']);
                    }
                }
            }
        }

        return $this->response->setJSON($result);
    }

    public function deleteBusinessScenario(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('blueprints/business-scenarios/' . $encryptedId . '/delete');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints deleteBusinessScenario API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function createDesignPage(string $moduleId)
    {
        if (!$this->guard('can_create')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();
        $blueprintToken = $data['blueprint_token'] ?? null;
        unset($data['blueprint_token']);

        $result = $this->api->post_data('blueprints/modules/' . $moduleId . '/design-pages', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints createDesignPage API failed for ' . $moduleId . ': ' . json_encode($result));
            return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
        }

        $designPageId = $result['data']['result']['id'] ?? null;
        if ($designPageId && $blueprintToken) {
            $files = array_filter($this->request->getFileMultiple('images') ?? [], function ($f) {
                return $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE;
            });
            if (!empty($files)) {
                $error = $this->validateUploadedFiles($files, 'design_pages');
                if ($error) {
                    return $this->response->setJSON(['status' => false, 'message' => $error]);
                }
                $savedFiles = $this->saveUploadedFiles($files);
                foreach ($savedFiles as $sf) {
                    $attData = $sf;
                    $attData['section_type'] = 'design_page';
                    $attData['section_id'] = $designPageId;
                    $attData['module_id'] = $moduleId;
                    $attResult = $this->api->post_data('blueprints/' . $blueprintToken . '/attachments', $attData);
                    if (!$attResult || !($attResult['status'] ?? false)) {
                        $this->deleteUploadedFiles($savedFiles);
                        log_message('error', 'Blueprint design page attachment save failed for page ' . $designPageId);
                        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan file lampiran']);
                    }
                }
            }
        }

        return $this->response->setJSON($result);
    }

    public function updateDesignPage(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();
        $blueprintToken = $data['blueprint_token'] ?? null;
        unset($data['blueprint_token']);

        $deletedAttachments = $data['deleted_attachments'] ?? '';
        unset($data['deleted_attachments']);

        $result = $this->api->post_data('blueprints/design-pages/' . $encryptedId . '/update', $data);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints updateDesignPage API failed for ' . $encryptedId . ': ' . json_encode($result));
            return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
        }

        if ($blueprintToken) {
            if ($deletedAttachments) {
                $deletedIds = json_decode($deletedAttachments, true);
                if (is_array($deletedIds)) {
                    $uploadPath = WRITEPATH . 'uploads/blueprints/';
                    foreach ($deletedIds as $encAttId) {
                        $attResult = $this->api->delete_data('blueprints/' . $blueprintToken . '/attachments/' . $encAttId);
                        if ($attResult && ($attResult['status'] ?? false)) {
                            $storedName = $attResult['data']['result']['stored_name'] ?? '';
                            if ($storedName) {
                                $filePath = $uploadPath . $storedName;
                                if (is_file($filePath)) { unlink($filePath); }
                            }
                        } else {
                            log_message('error', 'Blueprints: Failed to delete attachment ' . $encAttId . ': ' . json_encode($attResult));
                        }
                    }
                }
            }

            $files = array_filter($this->request->getFileMultiple('images') ?? [], function ($f) {
                return $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE;
            });
            if (!empty($files)) {
                $error = $this->validateUploadedFiles($files, 'design_pages');
                if ($error) {
                    return $this->response->setJSON(['status' => false, 'message' => $error]);
                }
                $savedFiles = $this->saveUploadedFiles($files);
                foreach ($savedFiles as $sf) {
                    $attData = $sf;
                    $attData['section_type'] = 'design_page';
                    $attData['section_id'] = $encryptedId;
                    $attResult = $this->api->post_data('blueprints/' . $blueprintToken . '/attachments', $attData);
                    if (!$attResult || !($attResult['status'] ?? false)) {
                        $this->deleteUploadedFiles($savedFiles);
                        log_message('error', 'Blueprint design page attachment save failed for page ' . $encryptedId);
                        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan file lampiran']);
                    }
                }
            }
        }

        return $this->response->setJSON($result);
    }

    public function deleteDesignPage(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('blueprints/design-pages/' . $encryptedId . '/delete');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints deleteDesignPage API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    public function pageSpecifications(string $encryptedDesignPageId): string
    {
        if (!$this->guard()) {
            return redirect()->to('/dashboard');
        }
        $result = $this->api->get_data('blueprints/design-pages/' . $encryptedDesignPageId);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints pageSpecifications API failed for ' . $encryptedDesignPageId . ': ' . json_encode($result));
            return redirect()->to('/blueprints');
        }

        $designPage = $result['data']['result'] ?? null;

        return $this->view('blueprints/page_specifications', [
            'title'      => 'Page Specifications - ' . ($designPage['title'] ?? ''),
            'designPage' => $designPage,
            'token'      => $encryptedDesignPageId,
        ]);
    }

    public function createPageSpecification(string $encryptedDesignPageId)
    {
        if (!$this->guard('can_create')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();
        $blueprintToken = $data['blueprint_token'] ?? null;
        unset($data['blueprint_token']);

        $result = $this->api->post_data('blueprints/design-pages/' . $encryptedDesignPageId . '/page-specifications', $data);
        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints createPageSpecification API failed for ' . $encryptedDesignPageId . ': ' . json_encode($result));
            return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
        }

        $specId = $result['data']['result']['id'] ?? null;
        if ($specId && $blueprintToken) {
            $files = array_filter($this->request->getFileMultiple('ux_image') ?? [], function ($f) {
                return $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE;
            });
            if (!empty($files)) {
                $error = $this->validateUploadedFiles($files, 'page_specifications');
                if ($error) {
                    return $this->response->setJSON(['status' => false, 'message' => $error]);
                }
                $savedFiles = $this->saveUploadedFiles($files);
                foreach ($savedFiles as $sf) {
                    $attData = $sf;
                    $attData['section_type'] = 'page_specification';
                    $attData['section_id'] = $specId;
                    $attResult = $this->api->post_data('blueprints/' . $blueprintToken . '/attachments', $attData);
                    if (!$attResult || !($attResult['status'] ?? false)) {
                        $this->deleteUploadedFiles($savedFiles);
                        log_message('error', 'Blueprints: page spec attachment save failed for spec ' . $specId);
                        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan file UX']);
                    }
                }
            }
        }

        return $this->response->setJSON($result);
    }

    public function updatePageSpecification(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $data = $this->request->getPost();
        $blueprintToken = $data['blueprint_token'] ?? null;
        $removeUx = !empty($data['remove_ux']);
        unset($data['blueprint_token'], $data['remove_ux']);

        $result = $this->api->post_data('blueprints/page-specifications/' . $encryptedId . '/update', $data);
        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints updatePageSpecification API failed for ' . $encryptedId . ': ' . json_encode($result));
            return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
        }

        if ($blueprintToken) {
            if ($removeUx) {
                $existingAtt = $this->request->getPost('existing_ux_att_id');
                if ($existingAtt) {
                    $attResult = $this->api->delete_data('blueprints/' . $blueprintToken . '/attachments/' . $existingAtt);
                    if ($attResult && ($attResult['status'] ?? false)) {
                        $storedName = $attResult['data']['result']['stored_name'] ?? '';
                        if ($storedName) {
                            $filePath = WRITEPATH . 'uploads/blueprints/' . $storedName;
                            if (is_file($filePath)) { unlink($filePath); }
                        }
                    } else {
                        log_message('error', 'Blueprints: Failed to delete UX attachment ' . $existingAtt . ': ' . json_encode($attResult));
                    }
                }
            }

            $files = array_filter($this->request->getFileMultiple('ux_image') ?? [], function ($f) {
                return $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE;
            });
            if (!empty($files)) {
                // Delete existing UX attachment first (single-image enforcement)
                $existingAtt = $this->request->getPost('existing_ux_att_id');
                if ($existingAtt && !$removeUx) {
                    $attResult = $this->api->delete_data('blueprints/' . $blueprintToken . '/attachments/' . $existingAtt);
                    if ($attResult && ($attResult['status'] ?? false)) {
                        $storedName = $attResult['data']['result']['stored_name'] ?? '';
                        if ($storedName) {
                            $filePath = WRITEPATH . 'uploads/blueprints/' . $storedName;
                            if (is_file($filePath)) { unlink($filePath); }
                        }
                    }
                }

                $error = $this->validateUploadedFiles($files, 'page_specifications');
                if ($error) {
                    return $this->response->setJSON(['status' => false, 'message' => $error]);
                }
                $savedFiles = $this->saveUploadedFiles($files);
                foreach ($savedFiles as $sf) {
                    $attData = $sf;
                    $attData['section_type'] = 'page_specification';
                    $attData['section_id'] = $encryptedId;
                    $attResult = $this->api->post_data('blueprints/' . $blueprintToken . '/attachments', $attData);
                    if (!$attResult || !($attResult['status'] ?? false)) {
                        $this->deleteUploadedFiles($savedFiles);
                        log_message('error', 'Blueprints: page spec attachment replace failed for spec ' . $encryptedId);
                        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan file UX']);
                    }
                }
            }
        }

        return $this->response->setJSON($result);
    }

    public function deletePageSpecification(string $encryptedId)
    {
        if (!$this->guard('can_update')) {
            return $this->denyResponse();
        }
        $result = $this->api->post_data('blueprints/page-specifications/' . $encryptedId . '/delete');

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Blueprints deletePageSpecification API failed for ' . $encryptedId . ': ' . json_encode($result));
        }

        return $this->response->setJSON($result ?? ['status' => false, 'message' => 'Failed to connect to server']);
    }

    private function validateUploadedFiles(array $files, string $context = 'attachments'): ?string
    {
        $maxFiles = 5;
        $maxSize  = 500 * 1024;

        $allowedMimes = [
            'attachments' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
            ],
            'design_pages' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            ],
            'page_specifications' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            ],
        ];

        $mimes = $allowedMimes[$context] ?? $allowedMimes['attachments'];

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
            if (!in_array($mime, $mimes, true)) {
                return 'Tipe file tidak diizinkan: ' . $file->getClientName();
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
        $uploadPath = WRITEPATH . 'uploads/blueprints/';

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
        $uploadPath = WRITEPATH . 'uploads/blueprints/';
        foreach ($files as $sf) {
            $path = $uploadPath . ($sf['stored_name'] ?? '');
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function deleteUploadedFilesById(array $attachments): void
    {
        $uploadPath = WRITEPATH . 'uploads/blueprints/';
        foreach ($attachments as $att) {
            $path = $uploadPath . ($att['stored_name'] ?? '');
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
