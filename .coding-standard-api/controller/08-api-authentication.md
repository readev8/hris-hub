# Standar Autentikasi API

Dokumen ini mengatur standar autentikasi untuk REST API HRIS Self-Service yang diakses oleh web server.

## 1. Standar Autentikasi Saat Ini (Basic Auth + API Key)
Sistem saat ini menggunakan tiga lapis autentikasi:
1. **HTTP Basic Auth** (username + password)
2. **API Key** (header `Key`)
3. **Session Token** (header `Token`)

### A. Masalah yang Harus Diperbaiki

#### Masalah 1: Error Suppression
```php
// SALAH — Menggunakan @ untuk suppress error
@$username = $_SERVER['PHP_AUTH_USER'];
@$password = $_SERVER['PHP_AUTH_PW'];
@$key = $_SERVER['HTTP_KEY'];
@$token = $_SERVER['HTTP_TOKEN'];

// BENAR — Proper null check
$username = $_SERVER['PHP_AUTH_USER'] ?? null;
$password = $_SERVER['PHP_AUTH_PW'] ?? null;
$key = $_SERVER['HTTP_KEY'] ?? null;
$token = $_SERVER['HTTP_TOKEN'] ?? null;
```

#### Masalah 2: Error Message yang Terlalu Spesifik
```php
// SALAH — Membantu attacker mengenumerasi
if ($username != API_AUTH_USERNAME || $password != API_AUTH_PASSWORD)
    return 'Username atau Password API Salah';
if (!$data) return 'API key tidak valids';
if (!$this->checkToken($token)) return 'Token tidak valid';

// BENAR — Pesan generik yang tidak membocorkan informasi
if (!$this->validateCredentials($username, $password, $key))
    return 'Kredensial tidak valid';
if (!$this->validateToken($token))
    return 'Sesi telah berakhir, silakan login kembali';
```

#### Masalah 3: Token Disimpan Plain-Text
Token saat ini disimpan langsung di database tanpa hashing. Jika database bocor, semua token bisa digunakan langsung.

```php
// SALAH — Token plain-text di database
// Tabel session: id, userid, token (plain-text), start, active, apps
$data = $model->where('key', $key)->first();

// BENAR — Token di-hash sebelum disimpan
$tokenHash = hash('sha256', $token);
$data = $model->where('token_hash', $tokenHash)->first();
```

#### Masalah 4: Token Expiry Hardcoded
```php
// SALAH — Expiry 15 jam di-hardcode
$hourDifference > 15;

// BENAR — Dari konfigurasi
$hourDifference > (int) env('auth.tokenExpiryHours', 4);
```

## 2. Standar Autentikasi yang Direkomendasikan (JWT)

Untuk pengembangan ke depan, sangat direkomendasikan melakukan migrasi ke **JSON Web Token (JWT)**.

### A. Arsitektur JWT

```
Client                          Server
  |                               |
  |--- POST /auth/login --------->|
  |                               |--- Verifikasi credentials
  |<-- Access Token + Refresh ----|
  |                               |
  |--- GET /api/data ------------>|
  |    Header: Authorization:     |
  |    Bearer <access_token>      |
  |                               |--- Verifikasi JWT signature
  |<-- 200 OK + Data -------------|
```

### B. Konfigurasi JWT yang Direkomendasikan

| Parameter | Nilai | Keterangan |
| :--- | :--- | :--- |
| Algorithm | `HS256` atau `RS256` | RS256 lebih aman untuk production |
| Access Token TTL | 15-30 menit | Token berumur pendek |
| Refresh Token TTL | 7 hari | Untuk mendapatkan access token baru |
| Key Storage | `.env` | Tidak boleh hardcoded |
| Token Payload | `sub`, `iat`, `exp`, `userid` | Minimal, jangan simpan data sensitif |

### C. Contoh Implementasi JWT (Menggunakan Library `firebase/php-jwt`)

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    public function generateToken(int $userId, int $pegawaiId): array
    {
        $now = time();

        $accessToken = JWT::encode([
            'sub' => $userId,
            'pegawaiid' => $pegawaiId,
            'iat' => $now,
            'exp' => $now + (int) env('auth.accessTokenTTL', 900), // 15 menit
        ], env('jwt.secret'), 'HS256');

        $refreshToken = bin2hex(random_bytes(32));
        // Simpan refresh token ke database (hashed)

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => (int) env('auth.accessTokenTTL', 900),
        ];
    }

    public function validateToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key(env('jwt.secret'), 'HS256'));
        } catch (\Throwable $th) {
            log_message('warning', "JWT validation failed: " . $th->getMessage());
            return null;
        }
    }
}
```

## 3. Standar API Key per Client (Jika Tetap Menggunakan API Key)

Jika migrasi ke JWT belum memungkinkan, perbaiki sistem API key yang ada:

### A. API Key Unik per Client
Setiap aplikasi client (web, mobile, partner) harus memiliki API key yang **berbeda**.

```php
// SALAH — Satu API key untuk semua client
$key = $_SERVER['HTTP_KEY'];
if ($key !== KEY) return 'API key tidak valid';

// BENAR — API key per client
$key = $_SERVER['HTTP_KEY'] ?? null;
$client = $model->where('api_key', $key)->where('active', 1)->first();
if (!$client) return 'API key tidak valid';
// Log client identity untuk audit trail
log_message('info', "API access from client: {$client['name']}");
```

### B. Rate Limiting per API Key
Selain per IP, rate limiting juga harus diterapkan per API key untuk mencegah penyalahgunaan.

## 4. Standar Session Token (Jika Tetap Menggunakan Token DB)

### A. Token Generation
Token harus dibuat menggunakan cryptographically secure random generator:

```php
// SALAH — Token tidak cukup random
$token = md5($userId . time());

// BENAR — Cryptographically secure
$token = bin2hex(random_bytes(32));
```

### B. Token Storage
Token wajib di-hash sebelum disimpan di database:

```php
// Sebelum disimpan
$tokenHash = hash('sha256', $token);

// Saat validasi
$tokenHash = hash('sha256', $receivedToken);
$data = $model->where('token_hash', $tokenHash)
              ->where('active', 1)
              ->first();
```

### C. Token Lifecycle

| Event | Aksi |
| :--- | :--- |
| Login | Generate token, simpan hash ke DB, return plain token ke client |
| Setiap request | Validasi hash, cek expiry |
| Logout | Set `active = 0` di DB |
| Token expired | Set `active = 0`, client harus login ulang |
| Password change | Revoke semua token user tersebut |

## 5. Checklist Autentikasi per Endpoint

Setiap endpoint harus melalui checklist berikut:

- [ ] **Authentication**: Apakah user terautentikasi? (Filter global)
- [ ] **Authorization**: Apakah user memiliki akses ke endpoint ini? (RBAC)
- [ ] **Ownership/IDOR**: Apakah user memiliki akses ke **data spesifik** yang diminta? (Object-level)
- [ ] **Rate Limiting**: Apakah request tidak melebihi batas?
- [ ] **Input Validation**: Apakah semua input tervalidasi?
