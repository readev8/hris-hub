<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function login()
    {
        helper('form');

        if ($this->request->getMethod() === 'POST') {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            if (empty($username) || empty($password)) {
                return $this->view('auth/login', [
                    'error' => 'Username dan password wajib diisi',
                ]);
            }

            // Encrypt credentials with AES-128-CTR (same as myhr/plus)
            $encUsername = urlencode($this->api->encrypt($username));
            $encPassword = urlencode($this->api->encrypt($password));

            // POST to myhr/plus auth API (matches myhr/plus flow exactly)
            $authResult = $this->api->postToMyhrAuth('auth-winit/authenticate', [
                'username'   => $encUsername,
                'password'   => $encPassword,
                'apps'       => 'hris_hub',
                'lat'        => 0,
                'lon'        => 0,
                'ipaddress'  => '',
            ]);

            // Null guard — cURL failure or JSON decode error
            if ($authResult === null) {
                log_message('error', 'Auth result is NULL — cURL or JSON failure. Check myhr/plus server at ' . env('myhr.auth_url'));
                return $this->view('auth/login', [
                    'error' => 'Gagal terhubung ke server autentikasi. Pastikan server myhr/plus berjalan.',
                ]);
            }

            log_message('debug', 'Auth result: ' . json_encode($authResult));

            // Detailed debug — log each condition separately
            log_message('debug', 'isSuccess check: data.status=' . var_export($authResult['data']['status'] ?? 'MISSING', true)
                . ' (type=' . gettype($authResult['data']['status'] ?? null) . ')');
            log_message('debug', 'isSuccess check: root.status=' . var_export($authResult['status'] ?? 'MISSING', true)
                . ' (type=' . gettype($authResult['status'] ?? null) . ')');
            log_message('debug', 'isSuccess check: statuscode=' . var_export($authResult['statuscode'] ?? 'MISSING', true)
                . ' (type=' . gettype($authResult['statuscode'] ?? null) . ')');
            log_message('debug', 'isSuccess check: _http_code=' . var_export($authResult['_http_code'] ?? 'MISSING', true));

            // myhr/plus response: {statuscode: 200, data: {id, token, status: true, ...}}
            // Primary: data.status must not be explicitly false (wrong credentials → false)
            $dataStatus = $authResult['data']['status'] ?? null;
            $isSuccess = ($dataStatus !== false && $dataStatus !== 'false');

            // Secondary: must have non-empty id (confirms login actually succeeded)
            if ($isSuccess) {
                $isSuccess = !empty($authResult['data']['id']);
            }

            log_message('debug', 'isSuccess result: ' . var_export($isSuccess, true));

            if (!$isSuccess) {
                $msg = $authResult['message'] ?? $authResult['data']['message'] ?? 'Gagal terhubung ke server autentikasi';
                log_message('error', 'Login failed: ' . $msg . ' | authResult keys: ' . implode(', ', array_keys($authResult)));
                return $this->view('auth/login', ['error' => $msg]);
            }

            // Extract token and userId — may be at root or inside data
            $myhrToken  = $authResult['data']['token'] ?? $authResult['token'] ?? '';
            $myhrUserId = $authResult['data']['id'] ?? $authResult['id'] ?? '';

            if (empty($myhrToken) || $myhrUserId === '' || $myhrUserId === null) {
                log_message('error', 'Login: token or id missing. token=' . substr($myhrToken, 0, 10) . '... id=' . var_export($myhrUserId, true)
                    . ' | data keys: ' . implode(', ', array_keys($authResult['data'] ?? [])));
                return $this->view('auth/login', [
                    'error' => 'Data autentikasi tidak valid dari server',
                ]);
            }

            log_message('debug', 'Login success: userId=' . $myhrUserId . ', token=' . substr($myhrToken, 0, 10) . '...');

            // Fetch user detail from myhr/plus
            $userDetail = $this->api->getFromMyhrApi('authcombine/userdetail', $myhrToken, [
                'token' => $myhrToken,
            ]);

            log_message('debug', 'User detail: ' . json_encode($userDetail));

            $fullName = $userDetail['nama'] ?? $username;
            $email = $userDetail['email'] ?? ($username . '@external.local');

            // Find or create user via internal API
            $localUser = $this->api->post_data('auth/local-user', [
                'user_id'   => $myhrUserId,
                'full_name' => $fullName,
                'email'     => $email,
            ]);

            log_message('debug', 'Local user result: ' . json_encode($localUser));

            if (!$localUser || !($localUser['status'] ?? false)) {
                $msg = $localUser['data']['message'] ?? 'Gagal membuat user lokal';
                log_message('error', 'Login: local-user failed: ' . $msg);
                return $this->view('auth/login', ['error' => $msg]);
            }

            $user = $localUser['data']['result'];

            // Load permissions via internal API
            $permResult = $this->api->get_data('roles/by-id/' . $user['role_id'] . '/permissions');
            $permMap = [];

            if ($permResult && ($permResult['status'] ?? false)) {
                $permMap = $permResult['data']['result'] ?? [];
            } else {
                log_message('error', 'Login: permissions load failed: ' . json_encode($permResult));
            }

            log_message('debug', 'Permissions loaded: ' . count($permMap) . ' modules');

            // Set session
            $session = service('session');
            $user['token'] = $this->api->encrypt($user['id']);
            $user['role_name'] = \App\Config\Enums::roleName((int) $user['role_id']);
            $user['permissions'] = $permMap;

            $session->set('user', $user);
            $session->set('user_id', $user['id']);
            $session->set('role_id', $user['role_id']);
            $session->set('permissions', $permMap);

            return redirect()->to('/dashboard');
        }

        return $this->view('auth/login');
    }

    public function logout()
    {
        service('session')->destroy();
        return redirect()->to('/login');
    }

    public function loginPage()
    {
        helper('form');
        return $this->view('auth/login');
    }

    public function refreshPermissions()
    {
        $session = service('session');
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['status' => false, 'message' => 'Not logged in']);
        }

        $result = $this->api->get_data('auth/me');

        if (!$result || !($result['status'] ?? false)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Failed to refresh']);
        }

        $user = $result['data']['result'];
        $session->set('permissions', $user['permissions'] ?? []);
        $session->set('role_id', $user['role_id']);

        return $this->response->setJSON([
            'status'      => true,
            'message'     => 'Permissions refreshed',
            'permissions' => $user['permissions'] ?? [],
        ]);
    }
}
