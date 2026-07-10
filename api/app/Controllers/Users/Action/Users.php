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

        $user = $this->db()->table('users')->where('id', $userId)->get()->getRowArray();
        if (!$user || (int) $user['role'] !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya admin yang dapat mengakses data users', null, 403);
        }

        $rows = $this->db()->table('users')
            ->select('id, full_name, email, role, is_active, created_at, updated_at')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$r) {
            $r['id'] = $this->api->encryptId($r['id']);
            $r['role_name'] = Enums::roleName((int) $r['role']);
        }

        return $this->JSONResponse('OK', $rows, 200);
    }

    public function create(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->JSONResponse('Unauthorized', null, 401);
        }

        $user = $this->db()->table('users')->where('id', $userId)->get()->getRowArray();
        if (!$user || (int) $user['role'] !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya admin yang dapat membuat user', null, 403);
        }

        $input = $this->cleanInput($this->req->getJSON(true) ?? $this->req->getPost());
        $fullName = trim($input['full_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $role = (int) ($input['role'] ?? Enums::DEVELOPER);

        if (empty($fullName) || empty($email) || empty($password)) {
            return $this->JSONResponse('Nama, email, dan password wajib diisi', null, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->JSONResponse('Format email tidak valid', null, 400);
        }

        $existing = $this->db()->table('users')->where('email', $email)->get()->getRowArray();
        if ($existing) {
            return $this->JSONResponse('Email sudah digunakan', null, 400);
        }

        $this->db()->table('users')->insert([
            'full_name' => $fullName,
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'role'      => $role,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->JSONResponse('User berhasil dibuat', [
            'id' => $this->api->encryptId($this->db()->insertID()),
        ], 201);
    }

    public function toggle_active(string $encryptedId): ResponseInterface
    {
        $id = $this->resolveId($encryptedId);
        if (!$id) return $this->JSONResponse('ID tidak valid', null, 400);

        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->JSONResponse('Unauthorized', null, 401);

        $admin = $this->db()->table('users')->where('id', $userId)->get()->getRowArray();
        if (!$admin || (int) $admin['role'] !== Enums::ADMIN) {
            return $this->JSONResponse('Hanya admin yang dapat mengubah status user', null, 403);
        }

        $target = $this->db()->table('users')->where('id', $id)->get()->getRowArray();
        if (!$target) return $this->JSONResponse('User tidak ditemukan', null, 404);

        $newActive = $target['is_active'] ? 0 : 1;
        $this->db()->table('users')->update([
            'is_active'  => $newActive,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return $this->JSONResponse($newActive ? 'User diaktifkan' : 'User dinonaktifkan');
    }
}
