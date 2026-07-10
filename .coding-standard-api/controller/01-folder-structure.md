# Standar Struktur Folder & Penamaan Controller

Dokumen ini menetapkan standar organisasi file dan konvensi penamaan untuk lapisan Controller dalam arsitektur API HRIS Self-Service.

## 1. Modularisasi Folder
Setiap sub-modul dalam sistem wajib dibagi ke dalam tiga kategori utama guna memisahkan tanggung jawab (Separation of Concerns):

| Folder | Tanggung Jawab | Contoh Aktivitas |
| :--- | :--- | :--- |
| **`Action/`** | Operasi Penulisan (Mutasi) | Create, Update, Delete, Post, Approval, Activate |
| **`Data/`** | Operasi Pengambilan (Spesifik) | Get Detail, Get Master Data pendukung Form |
| **`Report/`** | Penyajian Data (Bulk) | Get Lists, Monitoring, Dashboard, Export |

**Contoh Struktur:**
```text
app/Controllers/Hiring/
├── Request/
│   ├── Action/
│   │   └── Fpk.php
│   ├── Data/
│   │   └── MasterData.php
│   └── Report/
│       └── Lists.php
```

## 2. Konvensi Penamaan

### A. Nama File & Class
Wajib menggunakan **`PascalCase`** (UpperCamelCase).
- **Benar**: `PersonnelAction.php`, `JobCode.php`
- **Salah**: `personnelAction.php`, `job_code.php`

### B. Nama Method (Endpoint)
Wajib menggunakan **`snake_case`**. Nama method akan menjadi bagian dari URL, sehingga harus konsisten dengan standar REST API yang umumnya menggunakan lowercase dan underscore.
- **Benar**: `public function create_new_request()`
- **Salah**: `public function createNewRequest()`, `public function CreateNewRequest()`

## 3. Pewarisan (Inheritance)
Setiap controller **Wajib** mewarisi `App\Controllers\BaseApi`.
```php
namespace App\Controllers\Module\SubModule\Action;

use App\Controllers\BaseApi;

class MyController extends BaseApi {
    // ...
}
```

## 4. Larangan Instansiasi Model di `initController`
Dilarang keras melakukan instansiasi model secara massal di dalam method `initController` pada `BaseApi` atau controller manapun.
- **Standar**: Gunakan **Lazy Loading** (Load on Demand). Model hanya di-load saat dipanggil pertama kali.
- **Tujuan**: Mengurangi penggunaan memori dan mempercepat waktu respon API.

## 5. Pembatasan Model Map per Controller (Principle of Least Privilege)

`BaseApi` menyediakan mekanisme lazy-loading melalui property `$model_map`. Untuk menjaga keamanan dan maintainability, berlaku aturan berikut:

### A. Model Map Harus Didefinisikan di Masing-Masing Controller
Setiap controller **wajib** mendeklarasikan `$model_map` sendiri yang hanya berisi model yang dibutuhkan oleh controller tersebut. **Dilarang** mendaftarkan semua model di `BaseApi`.

```php
// SALAH — Semua model didaftarkan di BaseApi (God Object)
class BaseApi extends ResourceController {
    protected $model_map = [
        'adm_leave_act' => AdmLeaveAct_model::class,
        'ocha_jobcode_act' => OchaJobcodeAct_model::class,
        'devel_ninebox_data' => DevelNineboxData_model::class,
        // ... 140+ model lainnya
    ];
}

// BENAR — Model hanya didaftarkan di controller yang membutuhkan
class Approval extends BaseApi {
    protected array $model_map = [
        'adm_leave_act' => AdmLeaveAct_model::class,
        'adm_leave_data' => AdmLeaveData_model::class,
        'api' => Api_model::class,
        'notification' => Notification_model::class,
    ];
}
```

### B. Batasan Jumlah Model per Controller
- **Maksimal**: 10 model per controller.
- Jika controller membutuhkan lebih dari 10 model, pertimbangkan untuk **memecah controller** menjadi beberapa file yang lebih kecil (misal: pisahkan `Approval.php` dan `Submission.php`).

### C. Model Global (Boleh di BaseApi)
Hanya model yang benar-benar digunakan oleh **semua** controller yang boleh didaftarkan di `BaseApi`:
- `Api_model` (enkripsi/dekripsi)
- `MasterDataCheck_model` (validasi referensi)

### D. Keuntungan
- **Security**: Controller tidak bisa mengakses model yang bukan otoritasnya (mencegah IDOR internal).
- **Performance**: Hanya model yang dipakai yang di-load ke memori.
- **Maintainability**: Penambahan modul baru tidak perlu mengedit `BaseApi.php`.
