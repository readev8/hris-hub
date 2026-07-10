# Standar Response Output (JSON)

Dokumen ini mengatur format keluaran (*Output*) dari API agar konsisten dan mudah dikonsumsi oleh aplikasi Frontend maupun Mobile.

## 1. Method Pengendali Response
Setiap Controller wajib menggunakan method `JSONResponse()` yang diwarisi dari `BaseApi` untuk mengembalikan data.

## 2. Struktur JSON Baku
Format JSON harus mengikuti skema berikut:

```json
{
  "status": true,
  "data": {
    "statuscode": 200,
    "message": "Pesan deskriptif",
    "result": []
  }
}
```

### Penjelasan Field:
- **`status`**: Boolean (`true`/`false`). Menandakan apakah request berhasil diproses oleh router/filter. Biasanya selalu `true` jika mencapai controller.
- **`data.statuscode`**: Integer HTTP Status Code (200, 400, 404, 500, dll).
- **`data.message`**: String pesan untuk ditampilkan di UI (misal: "Data berhasil disimpan").
- **`data.result`**: Mixed (Array/Object/Null). Berisi data hasil query atau detail pesan error validasi.

## 3. Konsistensi Tipe Data Result
- Jika data tidak ditemukan, `result` sebaiknya berisi `null` atau `[]` (Array kosong), bukan mengembalikan string error.
- Jika terjadi error validasi, `result` berisi array asosiatif pesan error dari framework.

## 4. Keamanan dalam Output
Dilarang menyertakan field sensitif dalam `result`, seperti:
- Password (meskipun sudah di-hash).
- API Key.
- Raw Database ID (Wajib gunakan Token/Encrypted ID).
- Path sistem file absolut.

## 5. Metadata Tambahan (Optional)
Untuk data list/table, diperbolehkan menambahkan field pagination dalam `result`:
```json
"result": {
    "items": [...],
    "pagination": {
        "total": 100,
        "per_page": 20,
        "current_page": 1
    }
}
```

## 6. `JSONResponse()` Wajib Mengembalikan `ResponseInterface`
Method `JSONResponse()` **wajib** mengembalikan objek `ResponseInterface` dari CI4, bukan string hasil `json_encode()`.

```php
// SALAH — Mengembalikan string
protected function JSONResponse($message = '', $data = null, $code = 200)
{
    return json_encode([
        'data' => ['statuscode' => $code, 'message' => $message, 'result' => $data],
        'status' => true
    ]);
}

// BENAR — Mengembalikan ResponseInterface
protected function JSONResponse(string $message, mixed $data = null, int $code = 200): ResponseInterface
{
    return $this->respond([
        'data' => [
            'statuscode' => $code,
            'message' => $message,
            'result' => $data,
        ],
        'status' => true
    ], $code);
}
```

**Alasan:**
- `json_encode()` tidak men-set header `Content-Type: application/json`.
- CI4 Response object menangani header, caching, dan compression secara otomatis.
- Konsisten dengan middleware pipeline CI4.

## 7. Konsistensi Metode Response
**Pilih satu** metode response dan gunakan secara konsisten di seluruh controller. Dilarang mencampur metode yang berbeda.

```php
// SALAH — Campur aduk metode response
class MyController extends BaseApi {
    public function method_a() {
        return $this->JSONResponse('OK', $data, 200);         // metode A
    }
    public function method_b() {
        return $this->respond(['status' => true]);             // metode B
    }
    public function method_c() {
        return $this->failUnauthorized('Token tidak valid');   // metode C
    }
    public function method_d() {
        echo json_encode($data);                                // metode D — SANGAT SALAH
    }
}

// BENAR — Konsisten menggunakan JSONResponse()
class MyController extends BaseApi {
    public function method_a() {
        return $this->JSONResponse('OK', $data, 200);
    }
    public function method_b() {
        return $this->JSONResponse('OK', $data, 200);
    }
}
```
