<?php

namespace App\Controllers\Users\Action;

use App\Controllers\BaseApi;
use App\Config\Enums;
use CodeIgniter\HTTP\ResponseInterface;

class Users extends BaseApi
{
    public function get_list(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        if (!$this->checkPermission('users', 'can_view')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk melihat data users', null, 403);
        }

        $rows = $this->db()->table('users')
            ->select('id, user_id, full_name, email, role_id, is_active, created_at, updated_at')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$r) {
            $r['id'] = $this->api->encryptId($r['id']);
            $r['role_name'] = Enums::roleName((int) $r['role_id']);
        }

        return $this->JSONResponse('OK', $rows, 200);
    }

    public function get_detail(string $encryptedId): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('users', 'can_view')) {
            return $this->JSONResponse('Anda tidak memiliki izin', null, 403);
        }

        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $row = $this->db()->table('users')
            ->select('id, user_id, full_name, email, role_id, is_active, created_at, updated_at')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$row) return $this->JSONResponse('User tidak ditemukan', null, 404);

        $row['id'] = $this->api->encryptId($row['id']);
        $row['role_name'] = Enums::roleName((int) $row['role_id']);

        return $this->JSONResponse('OK', $row, 200);
    }

    public function create(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        if (!$this->checkPermission('users', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk membuat user', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $fullName = trim($input['full_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $roleId = (int) ($input['role_id'] ?? Enums::DEVELOPER);

        if (empty($fullName) || empty($email)) {
            return $this->JSONResponse('Nama dan email wajib diisi', null, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->JSONResponse('Format email tidak valid', null, 400);
        }

        $existing = $this->db()->table('users')->where('email', $email)->get()->getRowArray();
        if ($existing) {
            return $this->JSONResponse('Email sudah digunakan', null, 400);
        }

        $this->db()->table('users')->insert([
            'full_name'  => $fullName,
            'email'      => $email,
            'role_id'    => $roleId,
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->JSONResponse('User berhasil dibuat', [
            'id' => $this->api->encryptId($this->db()->insertID()),
        ], 201);
    }

    /**
     * Search HRIS users from wine_winit.user by name, username, or department
     */
    public function search_hris(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('users', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin', null, 403);
        }

        $q = trim($this->req->getGet('q') ?? '');
        if (mb_strlen($q) < 2) {
            return $this->JSONResponse('OK', [], 200);
        }

        $like = '%' . $q . '%';
        $rows = $this->db()->query(
            "SELECT id as pegawaiid, Username, Nama, Dept 
             FROM wine_winit.user 
             WHERE (nama LIKE ? OR Username LIKE ? OR Dept LIKE ?)
             ORDER BY Nama ASC 
             LIMIT 20",
            [$like, $like, $like]
        )->getResultArray();
        log_message('debug', $this->db()->getLastQuery());
        return $this->JSONResponse('OK', $rows, 200);
    }

    public function lookup_user(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        if (!$this->checkPermission('users', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mencari user', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $targetUserId = trim($input['user_id'] ?? '');

        if (empty($targetUserId)) {
            return $this->JSONResponse('User ID wajib diisi', null, 400);
        }

        $existing = $this->db()->table('users')
            ->where('user_id', $targetUserId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $existing['role_name'] = Enums::roleName((int) $existing['role_id']);
            $existing['already_added'] = true;
            return $this->JSONResponse('User sudah terdaftar', $existing, 200);
        }

        // Direct query to wine_winit.user table — use ID (PK), not PegawaiID
        $hrUser = $this->db()->query(
            "SELECT ID, Nama, Dept, Email, JabatanID, PerusahaanID 
             FROM wine_winit.user 
             WHERE ID = ?",
            [$targetUserId]
        )->getRowArray();

        if (!$hrUser) {
            return $this->JSONResponse('User tidak ditemukan di sistem HRIS', null, 404);
        }

        $result = [
            'user_id'    => $hrUser['ID'],
            'full_name'  => $hrUser['Nama'] ?? '',
            'jabatan'    => $hrUser['JabatanID'] ?? '',
            'departemen' => $hrUser['Dept'] ?? '',
            'perusahaan' => $hrUser['PerusahaanID'] ?? '',
            'already_added' => false,
        ];

        return $this->JSONResponse('OK', $result, 200);
    }

    public function add_by_userid(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        if (!$this->checkPermission('users', 'can_create')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menambah user', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $targetUserId = trim($input['user_id'] ?? '');
        $roleId = (int) ($input['role_id'] ?? 2);
        $fullName = trim($input['full_name'] ?? '');
        $email = trim($input['email'] ?? '');

        if (empty($targetUserId)) {
            return $this->JSONResponse('User ID wajib diisi', null, 400);
        }

        $existing = $this->db()->table('users')
            ->where('user_id', $targetUserId)
            ->get()
            ->getRowArray();

        if ($existing) {
            return $this->JSONResponse('User sudah terdaftar di sistem', null, 400);
        }

        $this->db()->table('users')->insert([
            'user_id'    => $targetUserId,
            'full_name'  => $fullName ?: 'User ' . $targetUserId,
            'email'      => $email ?: ($targetUserId . '@external.local'),
            'role_id'    => $roleId,
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $newId = $this->db()->insertID();

        log_message('info', "User added from myhr/plus: user_id=$targetUserId, local_id=$newId, role_id=$roleId");

        return $this->JSONResponse('User berhasil ditambahkan', [
            'id'     => $this->api->encryptId($newId),
            'user_id' => $targetUserId,
        ], 201);
    }

    public function update_user(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('users', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah user', null, 403);
        }

        $target = $this->db()->table('users')->where('id', $id)->get()->getRowArray();
        if (!$target) return $this->JSONResponse('User tidak ditemukan', null, 404);

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());

        $updateData = ['updated_at' => date('Y-m-d H:i:s')];

        if (isset($input['full_name'])) {
            $fullName = trim($input['full_name']);
            if (empty($fullName)) return $this->JSONResponse('Nama tidak boleh kosong', null, 400);
            $updateData['full_name'] = $fullName;
        }

        if (isset($input['email'])) {
            $email = trim($input['email']);
            if (empty($email)) return $this->JSONResponse('Email tidak boleh kosong', null, 400);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->JSONResponse('Format email tidak valid', null, 400);
            }
            $existing = $this->db()->table('users')->where('email', $email)->where('id !=', $id)->get()->getRowArray();
            if ($existing) return $this->JSONResponse('Email sudah digunakan user lain', null, 400);
            $updateData['email'] = $email;
        }

        if (isset($input['role_id'])) {
            $roleId = (int) $input['role_id'];
            if ($roleId < 1 || $roleId > 5) return $this->JSONResponse('Role tidak valid', null, 400);
            $updateData['role_id'] = $roleId;
        }

        $this->db()->table('users')->update($updateData, ['id' => $id]);

        log_message('info', "User updated: id=$id by user=$userId");

        return $this->JSONResponse('User berhasil diperbarui');
    }

    public function toggle_active(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $currentUserId = $this->getCurrentUserId();
        if (!$currentUserId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('users', 'can_update')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk mengubah status user', null, 403);
        }

        $currentRecord = $this->db()->table('users')->where('user_id', $currentUserId)->get()->getRowArray();
        if ($currentRecord && (int) $currentRecord['id'] === $id) {
            return $this->JSONResponse('Anda tidak dapat menonaktifkan akun sendiri', null, 400);
        }

        $target = $this->db()->table('users')->where('id', $id)->get()->getRowArray();
        if (!$target) return $this->JSONResponse('User tidak ditemukan', null, 404);

        $newActive = $target['is_active'] ? 0 : 1;
        $this->db()->table('users')->update([
            'is_active'  => $newActive,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        log_message('info', "User toggled: id=$id, new_active=$newActive, by user=$currentUserId");

        return $this->JSONResponse($newActive ? 'User diaktifkan' : 'User dinonaktifkan');
    }

    public function delete_user(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $currentUserId = $this->getCurrentUserId();
        if (!$currentUserId) return $this->JSONResponse('Unauthorized', null, 401);

        if (!$this->checkPermission('users', 'can_delete')) {
            return $this->JSONResponse('Anda tidak memiliki izin untuk menghapus user', null, 403);
        }

        $currentRecord = $this->db()->table('users')->where('user_id', $currentUserId)->get()->getRowArray();
        if ($currentRecord && (int) $currentRecord['id'] === $id) {
            return $this->JSONResponse('Anda tidak dapat menghapus akun sendiri', null, 400);
        }

        $target = $this->db()->table('users')->where('id', $id)->get()->getRowArray();
        if (!$target) return $this->JSONResponse('User tidak ditemukan', null, 404);

        $this->db()->table('users')->where('id', $id)->delete();

        log_message('info', "User deleted: id=$id, user_id={$target['user_id']}, by user=$currentUserId");

        return $this->JSONResponse('User berhasil dihapus');
    }
}
