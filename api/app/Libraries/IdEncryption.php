<?php

namespace App\Libraries;

class IdEncryption
{
    private string $key;

    public function __construct()
    {
        $hex = env('encryption.key_hex');
        if (!$hex) {
            throw new \RuntimeException('encryption.key_hex not set in .env');
        }
        $this->key = hex2bin($hex);
    }

    public function encryptId(string|int $id): string
    {
        $plaintext = (string) $id;
        $ivLen = openssl_cipher_iv_length('aes-256-gcm');
        $iv = random_bytes($ivLen);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }
        $payload = $iv . $tag . $ciphertext;
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    public function decryptId(string $encrypted): ?int
    {
        $payload = base64_decode(strtr($encrypted, '-_', '+/'));
        if ($payload === false || $payload === '') {
            return null;
        }
        $ivLen = openssl_cipher_iv_length('aes-256-gcm');
        $tagLen = 16;
        if (strlen($payload) < $ivLen + $tagLen) {
            return null;
        }
        $iv = substr($payload, 0, $ivLen);
        $tag = substr($payload, $ivLen, $tagLen);
        $ciphertext = substr($payload, $ivLen + $tagLen);
        $decrypted = openssl_decrypt($ciphertext, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($decrypted === false || !is_numeric($decrypted)) {
            return null;
        }
        return (int) $decrypted;
    }

    public function massEncrypt(array $items, string $idField = 'id', string $tokenField = 'token'): array
    {
        foreach ($items as &$row) {
            if (!empty($row[$idField])) {
                $row[$tokenField] = $this->encryptId($row[$idField]);
            }
        }
        return $items;
    }
}
