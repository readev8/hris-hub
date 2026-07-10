# Standar Error Handling & Logging

Dokumen ini mengatur cara Controller menangani kesalahan (*Error/Exception*) agar sistem tetap stabil dan tidak membocorkan informasi sensitif.

## 1. Penggunaan Try-Catch
Setiap method dalam Controller yang melakukan operasi database atau logic kompleks **Wajib** dibungkus dalam blok `try-catch`.

**Ketentuan:**
- Gunakan `catch (\Throwable $th)` untuk menangkap segala jenis kesalahan, termasuk *Type Error* dan *Fatal Error*.

## 2. Pemisahan Response Berdasarkan Environment
Jangan pernah mengirimkan detail error sistem ke pengguna di environment **Production**.

**Standar Implementasi:**
```php
try {
    // ... logic ...
} catch (\Throwable $th) {
    // Log detail error ke file log server
    log_message('error', "[{endpoint}] Error: " . $th->getMessage() . " | Trace: " . $th->getTraceAsString());

    $errorMessage = (ENVIRONMENT === 'development') 
        ? $th->getMessage() 
        : 'Terjadi kendala teknis pada sistem. Silakan hubungi administrator.';

    return $this->JSONResponse('Sistem Error', $errorMessage, 500);
}
```

## 3. Pencatatan Log (Logging)
Pencatatan log sangat penting untuk proses debugging dan audit.
- Gunakan `log_message('error', ...)` untuk kesalahan sistem.
- Gunakan `log_message('info', ...)` untuk mencatat aktivitas penting (misal: "User A melakukan approval pada ID B").

## 4. HTTP Status Code Mapping
Gunakan status code yang sesuai agar klien (Frontend/Mobile) dapat merespon dengan benar:

| Code | Arti | Penggunaan di Controller |
| :--- | :--- | :--- |
| **200** | OK | Berhasil (Query data, Update data). |
| **201** | Created | Berhasil membuat data baru. |
| **400** | Bad Request | Gagal karena input user tidak masuk akal. |
| **401** | Unauthorized | Token kadaluarsa atau tidak ada akses API. |
| **403** | Forbidden | User tidak punya otoritas (misal: IDOR check gagal). |
| **404** | Not Found | Data yang dicari setelah dekripsi ID tidak ada. |
| **412** | Precondition Failed | Gagal pada tahap Validasi `rules()`. |
| **500** | Internal Error | Database down atau bug pada kode. |

## 5. Penggunaan Exit/Die
Dilarang menggunakan `die()` atau `exit()` di dalam Controller. Selalu gunakan `return` untuk mengembalikan response agar siklus hidup framework berjalan sempurna.

## 6. Larangan Debug Code di Blok Catch
**Dilarang keras** menggunakan `var_dump()`, `print_r()`, `echo`, atau `dd()` di dalam blok `catch` maupun di bagian manapun dari Controller atau Model.

```php
// SALAH — Debug code di production
catch (\Throwable $th) {
    var_dump($th->getMessage());
}

// SALAH — Die di catch block
catch (\Throwable $th) {
    var_dump($response);
    die;
}

// SALAH — Echo di dalam logic
public function test()
{
    echo "lalala";
}

// BENAR — Log ke file, kirim response aman ke client
catch (\Throwable $th) {
    log_message('error', "[" . __METHOD__ . "] " . $th->getMessage() . " | Trace: " . $th->getTraceAsString());

    $errorMessage = (ENVIRONMENT === 'production')
        ? 'Terjadi kendala teknis pada sistem.'
        : $th->getMessage();

    return $this->JSONResponse('Sistem Error', $errorMessage, 500);
}
```

## 7. Wajib Cek Environment di Setiap Error Response
Setiap blok `catch` **wajib** memeriksa `ENVIRONMENT` sebelum mengirimkan pesan error ke klien. Tidak ada pengecualian.

**Template Wajib untuk Blok Catch:**
```php
catch (\Throwable $th) {
    // 1. WAJIB: Log detail error ke server
    log_message('error', "[{class}::{method}] {message} | File: {file}:{line}", [
        'class'  => __CLASS__,
        'method' => __METHOD__,
        'message' => $th->getMessage(),
        'file'    => $th->getFile(),
        'line'    => $th->getLine(),
    ]);

    // 2. WAJIB: Cek environment sebelum kirim response
    $errorMessage = (ENVIRONMENT === 'production')
        ? 'Terjadi kendala teknis pada sistem. Silakan hubungi administrator.'
        : $th->getMessage();

    // 3. WAJIB: Return response (bukan echo/die)
    return $this->JSONResponse('Sistem Error', $errorMessage, 500);
}
```

## 8. Konsistensi HTTP Status Code untuk Error
Selain tabel di Section 4, berikut aturan tambahan untuk konsistensi:

| Skenario | Kode | Contoh |
| :--- | :--- | :--- |
| Validasi gagal (`rules()`) | **412** | Field required kosong, format email salah |
| Business logic gagal | **400** | Sisa cuti tidak mencukupi, data sudah ada |
| Error sistem / database | **500** | Query gagal, exception tidak terduga |

**Dilarang** menggunakan kode 500 untuk kegagalan validasi atau business logic.
