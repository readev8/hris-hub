# Standar Clean Code & Maintainability

Dokumen ini mengatur standar penulisan kode agar lebih efisien, mudah dibaca, dan mudah dikelola dalam jangka panjang.

## 1. Lazy-Loading Pattern (Performance)
Dilarang melakukan inisialisasi semua model secara massal di `initController` atau `__construct`. Hal ini memboroskan memori dan memperlambat API.
- **Standar**: Model akan dipanggil secara otomatis (on-demand) melalui magic method `__get()`.
- **Implementasi**: Cukup gunakan `$this->nama_model->method()` tanpa perlu `new Nama_model()`.

## 2. Lean Controller Policy
Controller dilarang berisi logika bisnis yang kompleks atau query SQL mentah.
- **Tugas Controller**: Menerima input, validasi parameter, panggil model, kirim response.
- **Batas Baris**: Jika satu method controller melebihi 50 baris, pindahkan logika bisnis ke Model.

## 3. Strict Typing & PHP 8.1+ Features
Wajib menggunakan *Type Hinting* untuk parameter dan *Return Types* untuk setiap method.

```php
// CONTOH
public function get_list(int $page = 1): ResponseInterface 
{
    // ...
}
```

## 4. Naming Consistency
- **Endpoints (Controller)**: `snake_case` (contoh: `get_active_users`).
- **Logic Methods (Model)**: `camelCase` (contoh: `getActiveUsers`).
- **Variables**: `camelCase` (contoh: `userData`).
- **DB Columns**: `lowercase_snake_case`.

## 5. Mandatory PHPDoc for Magic Properties
Karena menggunakan lazy-loading, setiap Class Controller wajib memiliki anotasi `@property` di bagian header agar fitur Autocomplete pada IDE (VSCode/PHPStorm) tetap berjalan.

```php
/**
 * @property AdmLeaveAct_model $adm_leave_act
 * @property MasterDataCheck_model $master_data_check
 */
class Leave extends BaseApi { ... }
```

## 6. Centralized Constants
Dilarang menggunakan "Magic Numbers" atau "Magic Strings".
- **Salah**: `if ($status == 1)`
- **Benar**: `if ($status == LeaveStatus::APPROVED)`

## 7. Larangan Dead Code (Kode Mati)
**Dilarang keras** meninggalkan kode yang sudah tidak digunakan dalam bentuk komentar. Kode yang sudah tidak dipakai wajib **dihapus**, bukan di-comment out.

```php
// SALAH — Commented-out code yang ditinggalkan
public function getNeedApproval($userid) {
    /*
    $q = "select f.ID id, f.No no, ...from wine_hris.fpk f ...";
    $data_fpk = $this->db->query($q, [$userid])->getResultArray();
    foreach ($data_fpk as $key => $value) {
        // ... 50 baris kode yang di-comment
    }
    */

    // kode yang sebenarnya dipakai
    $nineboxAssessment = $this->getValuation(...);
}

// SALAH — Commented-out import yang ditinggalkan
// use App\Models\Hiring\Career\Questioner\Data\CareerQuestionerData_model;
// use App\Models\hiring\career\posting\maintenance\action\CareerPostingMaintenanceAct_model;
// use App\Models\hiring\career\funneling\action\CareerFunnelingAction_model;

// BENAR — Hapus kode yang tidak dipakai. Jika butuh nanti, ambil dari Git history.
```

**Alasan:**
- Commented-out code membuat file sulit dibaca.
- Menciptakan kebingungan: apakah kode ini masih dipakai atau tidak?
- Git sudah menyimpan history — tidak perlu simpan di komentar.

**Pengecualian:** Komentar yang menjelaskan **mengapa** (why) suatu keputusan dibuat diperbolehkan. Yang dilarang adalah komentar berupa kode yang dinonaktifkan.

## 8. Konfigurasi Tidak Boleh Hardcoded
Semua nilai konfigurasi (database, SMTP, API key, timeout, dll.) wajib disimpan di file konfigurasi (`.env` atau `app/Config/`), **dilarang** di-hardcode di dalam controller atau model.

```php
// SALAH — Hardcoded
$config["SMTPHost"] = "mail.wismilak.com";
$config["SMTPPort"] = 25;
$email_smtp->setFrom("noreply@wismilak.co.id", "NOTIFICATION");
$hourDifference > 15  // token expiry

// BENAR — Dari konfigurasi
$config["SMTPHost"] = env('email.smtpHost');
$config["SMTPPort"] = env('email.smtpPort');
$email_smtp->setFrom(env('email.fromEmail'), env('email.fromName'));
$hourDifference > env('auth.tokenExpiryHours', 15)
```

## 9. Larangan Error Suppression Operator
**Dilarang** menggunakan operator `@` untuk menyembunyikan error.

```php
// SALAH — Error suppression
@$username = $_SERVER['PHP_AUTH_USER'];
@$password = $_SERVER['PHP_AUTH_PW'];
@$key = $_SERVER['HTTP_KEY'];

// BENAR — Handle dengan proper null check
$username = $_SERVER['PHP_AUTH_USER'] ?? null;
$password = $_SERVER['PHP_AUTH_PW'] ?? null;
$key = $_SERVER['HTTP_KEY'] ?? null;

if ($username === null || $password === null) {
    return $this->JSONResponse('Unauthorized', 'Kredensial tidak ditemukan', 401);
}
```
