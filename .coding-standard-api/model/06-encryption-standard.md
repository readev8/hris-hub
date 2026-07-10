# Standar Enkripsi & Keamanan Kriptografi

Dokumen ini mengatur standar enkripsi yang wajib digunakan di seluruh sistem HRIS Self-Service, termasuk enkripsi ID dan data sensitif.

## 1. Larangan Menggunakan Enkripsi Lemah

### A. Dilarang: AES-128-CTR dengan IV Statis
Penggunaan `AES-128-CTR` dengan Initialization Vector (IV) yang sama untuk setiap operasi adalah **sangat berbahaya**.

```php
// SALAH — IV statis, AES-128, tanpa integrity check
function encryptId($string) {
    $ciphering = "AES-128-CTR";
    $encryption_iv = encryption_iv;  // IV STATIS — identik untuk semua data!
    $encryption_key = encryption_key;
    $encryption = openssl_encrypt($string, $ciphering, $encryption_key, 0, $encryption_iv);
    return urlencode($encryption);
}

// MASALAH:
// 1. IV statis → plaintext yang sama menghasilkan ciphertext yang sama (pattern leakage)
// 2. AES-128 → key size terlalu kecil, standar modern adalah AES-256
// 3. CTR mode tanpa HMAC → ciphertext bisa dimanipulasi tanpa terdeteksi
// 4. Prepend key ke plaintext BUKAN pengganti integrity check
```

### B. Dilarang: Base64 Encoding sebagai "Enkripsi"
`base64_encode()` **bukan** enkripsi. Ini hanya encoding yang bisa di-decode oleh siapapun.

```php
// SALAH — Ini encoding, BUKAN enkripsi
$token = base64_encode(json_encode(['id' => 1234]));
// Hasil: eyJpZCI6MTIzNH0= → bisa di-decode langsung

// BENAR — Gunakan enkripsi yang sesuai standar
$token = $this->encryptId(1234);
```

## 2. Standar Enkripsi ID (encryptId / decryptId)

### A. Wajib: AES-256-GCM
Gunakan cipher `aes-256-gcm` yang menyediakan **enkripsi + integrity** secara built-in (authenticated encryption).

```php
// Implementasi yang direkomendasikan
private function getEncryptionKey(): string {
    return hex2bin(env('encryption.key_hex'));
}

public function encryptId(string|int $id): string {
    $plaintext = (string) $id;
    $key = $this->getEncryptionKey();
    $iv = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));
    $tag = '';

    $ciphertext = openssl_encrypt(
        $plaintext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($ciphertext === false) {
        throw new \RuntimeException('Enkripsi gagal');
    }

    // Format: base64(iv + tag + ciphertext)
    $payload = $iv . $tag . $ciphertext;
    return $this->base64UrlEncode($payload);
}

public function decryptId(string $encrypted): int|null {
    $payload = $this->base64UrlDecode($encrypted);
    $key = $this->getEncryptionKey();
    $ivLength = openssl_cipher_iv_length('aes-256-gcm');
    $tagLength = 16; // GCM tag selalu 16 bytes

    $iv = substr($payload, 0, $ivLength);
    $tag = substr($payload, $ivLength, $tagLength);
    $ciphertext = substr($payload, $ivLength + $tagLength);

    $decrypted = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($decrypted === false || !is_numeric($decrypted)) {
        log_message('warning', 'Dekripsi ID gagal atau hasil bukan angka');
        return null;
    }

    return (int) $decrypted;
}

private function base64UrlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

private function base64UrlDecode(string $data): string|false {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}
```

### B. Aturan Wajib untuk Enkripsi ID
1. **Random IV** — Setiap operasi enkripsi harus menghasilkan IV baru menggunakan `random_bytes()`.
2. **AES-256** — Key minimal 256-bit.
3. **Authenticated Encryption** — Wajib menggunakan mode yang menyediakan integrity check (GCM) atau tambahkan HMAC secara manual.
4. **Validasi Hasil Dekripsi** — Setelah dekripsi, wajib memvalidasi bahwa hasilnya adalah angka yang valid.
5. **Return null jika gagal** — Jangan return string kosong atau throw error ke client.

## 3. Konfigurasi Key

### A. Penyimpanan Key
Encryption key wajib disimpan di file `.env` (tidak boleh di-hardcode di kode):

```env
# .env
encryption.key_hex=64_karakter_hex_string_untuk_aes_256
```

### B. Rotasi Key
Jika encryption key dicurigai bocor, lakukan rotasi key dan re-encrypt semua data yang tersimpan.

### C. Key Generation
Gunakan method berikut untuk generate key baru:
```php
$key = bin2hex(openssl_random_pseudo_bytes(32)); // 256-bit key
```

## 4. Keamanan Tambahan

### A. Dilarang Menyimpan Plaintext ID di Token
Token yang dikirim ke client harus berisi ciphertext saja, tanpa metadata yang bisa di-exploitasi.

### B. Timeout / Expiry pada Encrypted ID (Opsional)
Untuk endpoint yang sangat sensitif, pertimbangkan menambahkan timestamp ke dalam plaintext sebelum enkripsi, lalu validasi saat dekripsi:

```php
public function encryptIdWithExpiry(string|int $id, int $ttlSeconds = 3600): string {
    $payload = json_encode(['id' => $id, 'exp' => time() + $ttlSeconds]);
    return $this->encrypt($payload);
}

public function decryptIdWithExpiry(string $encrypted): int|null {
    $decrypted = $this->decrypt($encrypted);
    if ($decrypted === null) return null;

    $payload = json_decode($decrypted, true);
    if (!$payload || !isset($payload['id'], $payload['exp'])) return null;
    if (time() > $payload['exp']) {
        log_message('warning', 'Encrypted ID sudah expired');
        return null;
    }

    return (int) $payload['id'];
}
```
