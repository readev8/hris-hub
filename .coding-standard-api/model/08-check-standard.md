# Standar Model Pengecekan (Check Model)

Model tipe **Check** (suffix `_Check_model.php`) digunakan khusus untuk memvalidasi data sebelum dilakukan proses mutasi (INSERT, UPDATE, DELETE) atau untuk pengecekan aturan bisnis yang bersifat reusable.

## 1. Tanggung Jawab
- Validasi keberadaan data (Foreign Key check).
- Validasi status data (misal: apakah pegawai masih aktif).
- Validasi aturan bisnis (misal: apakah periode penilaian sudah ditutup).
- Validasi input unik di luar constraint database jika diperlukan logic kompleks.

## 2. Naming Convention
### A. Nama File & Class
- **Suffix**: `_Check_model.php`.
- **Contoh**: `DevelNineboxCheck_model.php`, `AdmLeaveCheck_model.php`.

### B. Nama Method
- **Prefix**: Wajib menggunakan prefix `check`.
- **Format**: `check[NamaEntitas][Kriteria/Kondisi]` (camelCase).
- **Contoh**: `checkPegawaiId`, `checkPeriodeAktif`, `checkQuotaCuti`.

## 3. Method Signature
Setiap method dalam Check Model **wajib** mengikuti pola signature berikut:

```php
public function checkSomething($id, &$error = null): bool
```

- **Parameter Pertama**: Data yang akan dicek (ID, Code, dll).
- **Parameter Kedua**: Referensi variabel `$error` (default `null`). Variabel ini akan diisi dengan pesan kesalahan jika validasi gagal.
- **Return Type**: Wajib `bool` (`true` jika valid, `false` jika tidak valid).

## 4. Aturan Implementasi (Best Practices)

### A. Gunakan Query Builder & selectCount
Untuk efisiensi performa, gunakan `selectCount` karena kita hanya perlu tahu apakah data ada atau tidak, bukan mengambil seluruh kolomnya.

```php
// BENAR
$result = $this->db->table('pegawai')
    ->selectCount('id', 'total')
    ->where('id', $id)
    ->get()
    ->getRow()
    ->total;
```

### B. Larangan Mutasi Data
Dilarang keras melakukan operasi `INSERT`, `UPDATE`, atau `DELETE` di dalam Check Model. Check Model bersifat *read-only*.

### C. Pengisian Pesan Error
Pesan error harus deskriptif dan diisi hanya jika method mengembalikan nilai `false`.

```php
if ($result > 0) {
    return true;
}

$error = "Pesan kesalahan yang user-friendly di sini.";
return false;
```

## 5. Contoh Implementasi Lengkap

```php
<?php

namespace App\Models\development\ninebox\check;

use CodeIgniter\Model;

class DevelNineboxCheck_model extends Model
{
    /**
     * Memastikan ID Pegawai ada dan aktif
     */
    public function checkPegawaiId($id, &$error = null): bool
    {
        $result = $this->db->table('wine_hris.pegawai')
            ->selectCount('id', 'total')
            ->where('id', $id)
            ->where('status_aktif', 1)
            ->get()
            ->getRow()
            ->total;

        if ($result > 0) {
            return true;
        }

        $error = "Pegawai ID tidak valid atau sudah tidak aktif.";
        return false;
    }

    /**
     * Memastikan periode penilaian masih terbuka
     */
    public function checkPeriodeOpen($periode, &$error = null): bool
    {
        $result = $this->db->table('hr_selfservice.ninebox_config')
            ->where('periode', $periode)
            ->where('is_open', 1)
            ->countAllResults();

        if ($result > 0) {
            return true;
        }

        $error = "Periode penilaian {$periode} sudah ditutup.";
        return false;
    }
}
```

## 6. Integrasi di Controller
Check Model biasanya digunakan di dalam aturan validasi custom pada Controller.

```php
$validationRules = [
    'pegawaiid' => [
        'rules' => [
            'required',
            function ($str, $data, &$error) {
                $id = $this->api->decryptId($str);
                return $this->devel_ninebox_check->checkPegawaiId($id, $error);
            }
        ]
    ]
];
```
