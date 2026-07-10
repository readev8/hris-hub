# Standar Keamanan Controller (Security First)

Dokumen ini berisi panduan teknis untuk memitigasi celah keamanan yang umum terjadi pada API HRIS.

## 1. Pencegahan SQL Injection
Meskipun CodeIgniter 4 memiliki proteksi bawaan, Controller wajib memastikan data dikirim ke Model dengan cara yang aman.
- **Wajib**: Gunakan **Query Binding** atau CI4 **Query Builder**.
- **Dilarang**: Mengirimkan string query mentah yang digabungkan dengan variabel input ke Model.
- **Validation**: Gunakan rule `numeric`, `alpha_dash`, atau `is_natural` pada input untuk memastikan karakter berbahaya tidak masuk ke query database.

## 2. Pencegahan Mass Assignment
Penyerang dapat menyuntikkan field tambahan ke dalam request JSON/POST untuk mengubah kolom database yang tidak diizinkan (misal: `is_admin`, `status_approval`).
- **Mitigasi**: Gunakan Abstraksi `$field_map` dan helper `get_parameter()`. Hanya field yang didefinisikan secara eksplisit di controller yang boleh diteruskan ke Model.

## 3. Pencegahan IDOR (Insecure Direct Object Reference)
IDOR terjadi ketika user dapat mengakses data user lain hanya dengan mengganti ID di parameter.
- **Standar**: Selain melakukan `decryptId()`, Controller wajib memvalidasi otoritas user terhadap data tersebut.
- **Contoh**: Jika menarik data detail cuti, Model/Controller wajib menyertakan filter `UserID` (dari session/token) dalam query-nya, bukan hanya `CutiID`.

## 4. Pencegahan Information Disclosure
Pesan error yang terlalu detail dapat memberikan informasi mengenai infrastruktur server kepada penyerang.
- **Aturan**: Jangan pernah mengembalikan detail stack trace database atau path file fisik ke JSON response di lingkungan Production.

## 5. Proteksi Parameter Sensitif
Setiap parameter yang bersifat identitas (Primary Key) **Wajib** dienkripsi menggunakan `$this->api->encryptId()`.
- Hindari URL seperti: `/api/pegawai/detail/1234`
- Gunakan URL: `/api/pegawai/detail/eyJpZCI6MTIzNH0` (Tokenized)

## 6. Validasi Otoritas (ACL)
Pengecekan hak akses (Role Based Access Control) harus dilakukan di awal sebelum logic bisnis dijalankan.
- Gunakan CI4 Filters untuk pengecekan global.
- Gunakan pengecekan manual di Controller untuk otoritas data yang lebih spesifik (Object-level security).

## 7. Rate Limiting (Pencegahan Brute Force & DoS)
Setiap endpoint API wajib memiliki pembatasan jumlah request untuk mencegah penyalahgunaan.

### A. Implementasi via CI4 Filter
Buat filter `ThrottleFilter` yang membatasi jumlah request per IP dan per User:
- **Default**: 60 request/menit per IP untuk endpoint biasa.
- **Endpoint sensitif** (login, approval, password reset): 10 request/menit per IP.
- Jika limit terlampaui, kembalikan response **429 Too Many Requests**.

### B. Response Rate Limit
```json
{
    "data": {
        "statuscode": 429,
        "message": "Terlalu banyak request. Silakan coba lagi dalam beberapa saat.",
        "result": null
    },
    "status": false
}
```

## 8. CORS (Cross-Origin Resource Sharing)
Karena API diakses oleh web server, konfigurasi CORS wajib diatur dengan ketat.

### A. Aturan CORS
- **Origin**: Hanya whitelist domain yang diizinkan. Dilarang menggunakan `*` di production.
- **Methods**: Hanya izinkan `GET`, `POST`, `PUT`, `PATCH`, `OPTIONS`.
- **Headers**: Hanya izinkan header yang dibutuhkan (`Content-Type`, `Authorization`, `Key`, `Token`).
- **Credentials**: Aktifkan hanya jika diperlukan.

### B. Implementasi via CI4 Filter
```php
// Contoh CORS Filter
$response->setHeader('Access-Control-Allow-Origin', 'https://approved-domain.com');
$response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, OPTIONS');
$response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Key, Token');
$response->setHeader('Access-Control-Max-Age', '86400');
```

## 9. Validasi Content-Type Header
Setiap endpoint yang menerima body (POST/PUT/PATCH) wajib memvalidasi header `Content-Type`.

- **Wajib**: `Content-Type: application/json` untuk API.
- **Dilarang**: Menerima request dengan Content-Type selain JSON (kecuali file upload yang menggunakan `multipart/form-data`).
- Jika Content-Type tidak sesuai, kembalikan response **415 Unsupported Media Type**.

## 10. File Upload Security
Jika endpoint menerima file upload, wajib menerapkan pembatasan berikut:

### A. Validasi File
- **Whitelist extension**: Hanya izinkan ekstensi yang disetujui (misal: `pdf`, `jpg`, `png`, `docx`).
- **Whitelist MIME type**: Validasi MIME type, bukan hanya ekstensi.
- **Max file size**: Tentukan batas ukuran per jenis dokumen (misal: maks 5MB).
- **Rename file**: Jangan gunakan original filename dari user. Generate nama unik.

### B. Penyimpanan
- Simpan file **di luar public webroot** (gunakan `WRITEPATH . 'uploads/'`).
- Jangan pernah simpan file di `public/`.
- Simpan metadata file (path asli, ukuran, MIME type) di database, bukan path fisik.

### C. Akses File
- File tidak boleh diakses langsung via URL.
- Buat endpoint khusus untuk download file yang memeriksa otoritas user sebelum mengirim file.
