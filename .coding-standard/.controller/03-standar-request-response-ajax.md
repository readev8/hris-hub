# Standarisasi Request dan Response AJAX Controller

## 1) Pengambilan Input Request
Baseline Ninebox saat ini:
- Utama: `$this->request->getPost('field')`
- Ada legacy: `$_POST[...]` (diperbolehkan sementara, tidak untuk kode baru)

Standar untuk kode baru:
- Gunakan `$this->request->getPost(...)` secara konsisten.
- Untuk payload kompleks: `$data = $this->request->getPost();`

## 2) Validasi Minimum
Untuk endpoint write/update:
- Cek field wajib.
- Cek format data penting (angka, JSON, token).
- Return JSON error jika input invalid.

## 3) Format Response JSON
Minimal salah satu pola berikut:
- Pola A:
  - `success` (bool)
  - `data` / `result`
  - `message` (opsional)
- Pola B:
  - `statuscode` (int)
  - `message`
  - `data` / `result`

## 4) Cara Return Response
As-is current:
- `echo json_encode($result);`

Rekomendasi bertahap (tidak wajib pada existing):
- `return $this->response->setJSON($result);`

## 5) Error Handling
Untuk method kompleks:
- Bungkus dengan `try/catch`.
- `log_message('error', ...)` saat exception.
- Kembalikan JSON error standar.

## 6) Larangan Minimum
- Dilarang `var_dump()` aktif di endpoint produksi.
- Dilarang `die;` aktif di endpoint produksi.
- Dilarang menampilkan trace/debug mentah ke user.

