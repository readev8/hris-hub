# Standar HTTP Client (Pemanggilan API Eksternal)

Dokumen ini mengatur standar untuk melakukan HTTP request ke API eksternal atau antar-service dalam sistem HRIS.

## 1. Larangan `file_get_contents()` untuk API Call
**Dilarang keras** menggunakan `file_get_contents()` untuk melakukan HTTP request ke API. Gunakan cURL atau library HTTP client yang proper.

```php
// SALAH — file_get_contents untuk API call
$response = file_get_contents(trim($link), false, stream_context_create($options));

// MASALAH:
// 1. Tidak ada proper error handling (hanya return false on failure)
// 2. Tidak ada timeout configuration (bisa hang selamanya)
// 3. Tidak ada retry mechanism
// 4. Sulit di-mock untuk testing
// 5. Performance lebih buruk dibanding cURL
```

### A. Gunakan cURL atau CodeIgniter HTTP Client
```php
// BENAR — Menggunakan cURL dengan proper configuration
function callApi(string $url, array $params = [], string $method = 'GET'): ?array {
    $curl = curl_init();

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . env('api.auth_token'),
    ];

    $options = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 30,        // Wajib set timeout
        CURLOPT_CONNECTTIMEOUT => 10,        // Wajib set connection timeout
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,       // WAJIB true di production
        CURLOPT_SSL_VERIFYHOST => 2,          // WAJIB 2 di production
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($params);
    }

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        $errorMsg = curl_error($curl);
        curl_close($curl);
        log_message('error', "API call failed: {$url} | Error: {$errorMsg}");
        return null;
    }

    curl_close($curl);

    if ($httpCode >= 400) {
        log_message('error', "API returned HTTP {$httpCode}: {$url}");
        return null;
    }

    return json_decode($response, true);
}
```

## 2. SSL Verification WAJIB Diaktifkan
**Dilarang** menonaktifkan SSL verification dalam kondisi apapun, termasuk environment development.

```php
// SALAH — SSL verification disabled (memungkinkan MITM attack)
"ssl" => array(
    "verify_peer" => false,
    "verify_peer_name" => false,
),
CURLOPT_SSL_VERIFYPEER => false,
CURLOPT_SSL_VERIFYHOST => 0,

// BENAR — SSL verification aktif
CURLOPT_SSL_VERIFYPEER => true,
CURLOPT_SSL_VERIFYHOST => 2,
```

**Jika SSL verification gagal di development:**
- Install CA certificate bundle di server development.
- Jangan pernah menonaktifkan SSL verification sebagai "solusi".
- Konfigurasi path ke CA bundle jika diperlukan:
  ```php
  CURLOPT_CAINFO => env('ssl.cainfo', '/etc/ssl/certs/ca-certificates.crt'),
  ```

## 3. Timeout Configuration
Setiap HTTP request **wajib** memiliki timeout. Tidak ada pengecualian.

| Parameter | Nilai Default | Keterangan |
| :--- | :--- | :--- |
| `CURLOPT_CONNECTTIMEOUT` | 10 detik | Timeout untuk koneksi |
| `CURLOPT_TIMEOUT` | 30 detik | Timeout total untuk request |
| `CURLOPT_TIMEOUT` (untuk bulk/heavy) | 120 detik | Untuk operasi berat (sync data, report) |

```php
// SALAH — Tidak ada timeout (bisa hang selamanya)
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    // TIDAK ADA CURLOPT_TIMEOUT — BAHAYA!
]);

// BENAR — Timeout selalu diset
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30,
]);
```

## 4. Error Handling untuk API Call
Setiap pemanggilan API wajib menangani skenario gagal:

```php
// Template error handling standar
public function callExternalApi(string $url, array $params): array {
    try {
        $response = $this->executeCurl($url, $params);

        if ($response === null) {
            log_message('error', "API call returned null: {$url}");
            return ['success' => false, 'message' => 'Gagal menghubungi service eksternal'];
        }

        return ['success' => true, 'data' => $response];

    } catch (\Throwable $th) {
        log_message('error', "[API Call] URL: {$url} | Error: " . $th->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan pada service eksternal'];
    }
}
```

## 5. Logging untuk API Call
Setiap API call ke service eksternal wajib dicatat ke log dengan informasi:
- URL yang dipanggil
- HTTP method
- Response code
- Waktu yang dibutuhkan (jika memungkinkan)

```php
$startTime = microtime(true);
$response = curl_exec($curl);
$duration = round((microtime(true) - $startTime) * 1000, 2);

log_message('info', "[API] {$method} {$url} | HTTP {$httpCode} | {$duration}ms");
```

## 6. Larangan Hardcoded API URL dan Credentials
Semua URL dan credentials untuk API eksternal wajib disimpan di file `.env` atau `app/Config/`.

```php
// SALAH — Hardcoded
$link = "https://api.wim-bms.com/" . $url . "?key=" . KEY;
'header' => "Authorization: Basic " . base64_encode(U_ . ":" . P_)

// BENAR — Dari konfigurasi
$link = env('api.base_url') . "/" . $url . "?key=" . env('api.key');
'header' => "Authorization: Bearer " . env('api.auth_token')
```

## 7. Dilarang `var_dump` / `die` untuk Debug Response
```php
// SALAH
$response = file_get_contents($link, ...);
var_dump($response);
die;

// SALAH
public function test() {
    echo "lalala";
}

// BENAR
$response = $this->executeCurl($url, $params);
log_message('debug', "API Response: " . json_encode($response));
```
