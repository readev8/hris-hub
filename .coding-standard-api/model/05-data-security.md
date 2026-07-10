# Standar Keamanan & Privasi Data di Model

Model bertanggung jawab untuk memastikan data yang dikirim ke layer atas sudah aman dan sesuai dengan kebijakan privasi perusahaan.

## 1. Data Masking
Data sensitif (seperti Gaji, NIK, Nomor HP Pribadi) wajib disamarkan atau dihilangkan jika tidak dibutuhkan oleh UI.

```php
public function getPegawaiDetail($id) {
    $data = $this->db->table('pegawai')->where('id', $id)->get()->getRowArray();
    
    if ($data) {
        // Masking NIK
        $data['nik'] = substr($data['nik'], 0, 4) . '******';
        // Hapus field Gaji jika bukan otoritasnya
        unset($data['salary']);
    }
    return $data;
}
```

## 2. Kebijakan Soft-Delete
Sistem HRIS dilarang menghapus baris data secara fisik (`DELETE FROM`). Gunakan kolom penanda.
- **Kolom Standar**: `is_deleted` (TINYINT: 0/1).
- **Aturan**: Setiap query `SELECT` wajib menyertakan filter `WHERE is_deleted = 0`.

## 3. Log Audit Otomatis
Setiap operasi mutasi (`Act_model`) wajib menyertakan informasi siapa yang melakukan perubahan dan kapan.
- `created_by` / `updated_by`
- `created_at` / `updated_at`
- `client_ip` / `user_agent` (Opsional)

## 4. Larangan Debug Code di Model
**Dilarang keras** meninggalkan kode debug di dalam model, baik di blok `catch` maupun di bagian lainnya.

```php
// SALAH — Debug code ditemukan di model
catch (\Throwable $th) {
    var_dump($th->getMessage());
}

// SALAH — var_dump + die di model
public function get_data_non_token_authentication($url, $param) {
    $response = file_get_contents(trim($link), ...);
    var_dump($response);
    die;
}

// SALAH — echo di model
function test() {
    echo "lalala";
}

// BENAR — Log error, jangan output langsung
catch (\Throwable $th) {
    log_message('error', "[" . __METHOD__ . "] " . $th->getMessage());
    return ['success' => false, 'message' => 'Terjadi kesalahan'];
}
```

## 5. Larangan Log Data Sensitif
Pencatatan log (`log_message()`) **dilarang** mencatat data sensitif berikut:
- Password (meskipun sudah di-hash)
- API Key / Secret Key
- Token (auth token, session token)
- NIK / Nomor KTP
- Nomor rekening bank
- Data gaji / salary

```php
// SALAH — Mencatat data sensitif ke log
log_message('info', "User login: NIK={$nik}, password={$password}");
log_message('error', "Token invalid: " . $token);

// BENAR — Masking atau hilangkan data sensitif
log_message('info', "User login attempt: user_id={$userId}");
log_message('error', "Token validation failed for user_id={$userId}");
```

## 6. Larangan Comment-Out kode yang Menyebarkan Informasi
Dilarang meninggalkan komentar yang berisi konfigurasi sensitif (password, SMTP credentials, dll).

```php
// SALAH — Credential di-comment
// $config["SMTPUser"]  = "hr-helpdesk@wismilak.com";
// $config["SMTPPass"]  = "@H31pd35k";

// BENAR — Hapus credential dari kode, gunakan .env
$config["SMTPUser"] = env('email.smtpUser');
$config["SMTPPass"] = env('email.smtpPass');
```
