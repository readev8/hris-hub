<?php

namespace App\Libraries;

class ApiClient
{
    private string $baseUrl;
    private string $apiKey;
    private ?int $userId = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('api.base_url', 'http://localhost:8080/'), '/');
        $this->apiKey  = env('api.service_key', '');
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function get_data(string $endpoint, array $params = []): ?array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $this->request('GET', $url);
    }

    public function post_data(string $endpoint, array $params = []): ?array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->request('POST', $url, $params);
    }

    private function request(string $method, string $url, ?array $data = null): ?array
    {
        $headers = [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json',
        ];
        if ($this->userId !== null) {
            $headers[] = 'X-User-Id: ' . $this->userId;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'ApiClient cURL error: ' . $error);
            return null;
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'ApiClient JSON decode error: ' . json_last_error_msg());
            return null;
        }

        $decoded['_http_code'] = $httpCode;
        return $decoded;
    }
}
