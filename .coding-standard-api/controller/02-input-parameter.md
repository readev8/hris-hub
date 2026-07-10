# Standar Penerimaan Parameter & Input

Dokumen ini mengatur cara Controller menerima, membersihkan, dan mengolah data yang dikirim oleh klien.

## 1. Pengambilan Input
Selalu gunakan properti request dari CodeIgniter atau helper yang tersedia di `BaseApi`.
- **Method**: Gunakan `$this->req->getVar()` untuk fleksibilitas (GET/POST) atau `$this->request->getPost()`.

## 2. Sanitasi Wajib
Setiap variabel input **Wajib** melewati proses sanitasi untuk mencegah serangan Cross-Site Scripting (XSS).
- **Standar**: Gunakan `$this->cleanInput()` yang memanggil `htmlspecialchars`.
```php
$post_data = $this->cleanInput($this->req->getVar());
```

## 3. Field Mapping Abstraction
Untuk keamanan dan fleksibilitas (mencegah kebocoran nama kolom database), gunakan properti `$field_map` untuk memetakan nama input dari UI ke kolom database.

**Contoh Implementasi:**
```php
private $field_map = [
    'input-pegawaiid' => 'pegawaiid',
    'input-jabatanid' => 'jabatanid',
    'createdby'       => 'createdby'
];

public function create() {
    $post_data = $this->cleanInput($this->req->getVar());
    
    // Gunakan helper get_parameter untuk mengambil hanya field yang terdaftar
    $parameter = $this->get_parameter($this->field_map, $post_data);
}
```

## 4. Keamanan ID (Enkripsi/Dekripsi)
Dilarang mengekspos ID integer asli (Primary Key) di URL atau Payload JSON Publik.
- **Input**: Setiap ID yang diterima (Token) wajib didekripsi menggunakan `$this->api->decryptId()`.
- **Output**: Setiap ID yang dikirim ke klien wajib dienkripsi menggunakan `$this->api->encryptId()` atau `$this->mass_encrypt()`.

**Contoh:**
```php
// Dekripsi ID dari token
$pegawai_id = $this->api->decryptId($post_data['token']);

if (!$pegawai_id) {
    return $this->JSONResponse('Token tidak valid', null, 412);
}
```

## 5. Validasi Tipe Data
Pastikan tipe data yang diterima sesuai dengan ekspektasi (misal: array, string, atau numeric) sebelum diproses lebih lanjut ke lapisan Model.

## 6. Larangan Penggunaan Superglobal Langsung
**Dilarang keras** mengakses `$_POST`, `$_GET`, `$_REQUEST`, atau `$GLOBALS` secara langsung di dalam Controller.

```php
// SALAH — Mengakses superglobal langsung
$description = $_POST['description'];
$tipe = $_POST['tipe'];

// BENAR — Melalui CI4 Request object + cleanInput()
$post_data = $this->cleanInput($this->req->getVar());
$description = $post_data['description'] ?? null;
```

**Alasan:**
- Superglobal tidak melewati sanitasi XSS.
- Tidak konsisten dengan arsitektur CI4.
- Menyulitkan testing (tidak bisa di-mock).

## 7. Penanganan Input Bertipe Array (Nested Data)
Jika request mengandung data array (misalnya: batch items), `cleanInput()` harus mampu melakukan sanitasi secara rekursif.

**Implementasi yang direkomendasikan di `BaseApi`:**
```php
protected function cleanInput(mixed $input): mixed
{
    if (empty($input)) {
        return is_array($input) ? [] : '';
    }

    if (is_array($input)) {
        return array_map([$this, 'cleanInput'], $input);
    }

    if (is_string($input)) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    // int, float, bool — kembalikan apa adanya
    return $input;
}
```

**Aturan tambahan:**
- Jika menerima file upload, **jangan** masukkan ke `cleanInput()`. Gunakan `$this->request->getFile()` secara terpisah.
- Untuk input JSON body, gunakan `$this->request->getJSON(true)` lalu sanitasi hasilnya.

## 8. Wajib Sanitasi di Setiap Endpoint
Setiap method controller yang menerima input **wajib** memanggil `cleanInput()`. Tidak ada pengecualian.

```php
// SALAH — Input tidak disanitasi
public function updateRtc()
{
    $data = $this->req->getVar();
    // langsung dipakai tanpa cleanInput()
}

// BENAR
public function updateRtc()
{
    $data = $this->cleanInput($this->req->getVar());
}
```
