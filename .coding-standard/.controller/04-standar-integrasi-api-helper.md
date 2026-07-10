# Standarisasi Integrasi API dan Helper di Controller

## 1) Pola Integrasi API
Controller Ninebox memakai helper API berikut:
- `$this->api->get_data('endpoint', $param)`
- `$this->api->post_data('endpoint', $param)`
- `$this->api->encryptId(...)`
- `$this->api->decryptId(...)`

## 2) Aturan Parameter API
- Parameter dikumpulkan dalam `$param` atau `$parameter` (array).
- Gunakan nama key yang selaras dengan endpoint existing.
- Data session penting:
  - `session()->get('userId')`
  - `session()->get('pegawaiId')`

## 3) Token Handling
Pola token page saat ini:
- Route param token diterima di `token($token)`.
- Token diproses dengan:
  - `explode(...)`
  - `decryptId(...)`
  - atau call API `ninebox/p` untuk mapping.

Standar:
- Semua parsing token dilakukan di layer controller action, bukan view.

## 4) Session Coverage
Endpoint data dropdown seperti divisi/departemen bergantung pada:
- `coverage_ninebox` di session.

Standar:
- Set session coverage pada method render/report sebelum endpoint data dipakai.

## 5) Logging
Untuk action kritikal (create/update/calibration):
- Boleh `log_message('info', ...)` untuk jejak proses.
- Wajib `log_message('error', ...)` saat gagal/exception.

## 6) Minimal Cleanup
- Hindari import `use ...` yang tidak terpakai pada controller baru.
- Hindari duplikasi field array (`note` ditulis dua kali, dll) pada parameter API baru.

