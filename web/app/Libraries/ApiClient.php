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

    public function delete_data(string $endpoint, array $params = []): ?array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->request('DELETE', $url, $params);
    }

    /**
     * AES-128-CTR encryption — same as myhr/plus
     */
    public function encrypt(string $string): string
    {
        $key = env('myhr.encryption_key');
        $iv  = env('myhr.encryption_iv');
        return openssl_encrypt($string, 'AES-128-CTR', $key, 0, $iv);
    }

    /**
     * POST to myhr/plus auth API — matches myhr/plus post_data_non_token_authentication format
     */
    public function postToMyhrAuth(string $endpoint, array $data): ?array
    {
        $apiKey = env('myhr.api_key');
        $apiU   = env('myhr.api_u');
        $apiP   = env('myhr.api_p');

        $baseUrl = rtrim(env('myhr.auth_url', 'http://localhost:8888/api-authentication/public'), '/');
        $url = $baseUrl . '/' . ltrim($endpoint, '/') . '?key=' . $apiKey;

        // Add key to data (matches myhr/plus format)
        $data['key'] = $apiKey;

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: MyAgent/1.0',
            'key: ' . $apiKey,
            'Authorization: Basic ' . base64_encode($apiU . ':' . $apiP),
        ];

        log_message('debug', 'MyhrAuth POST URL: ' . $url);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        log_message('debug', 'MyhrAuth Response [' . $httpCode . ']: ' . mb_substr($response ?? '', 0, 500));

        if ($error) {
            log_message('error', 'MyhrAuth cURL error: ' . $error . ' | URL: ' . $url);
            return null;
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'MyhrAuth JSON error: ' . json_last_error_msg());
            return null;
        }

        // myhr/plus response: {status: bool, token: str, id: str, privilage: [], role_rights: [], message: str}
        // Return full response — caller checks ['status']
        $decoded['_http_code'] = $httpCode;
        return $decoded;
    }

    /**
     * GET from myhr/plus API — matches myhr/plus get_data_authentication format
     * Token passed as query parameter, key appended, Authorization header only
     */
    public function getFromMyhrApi(string $endpoint, string $token, array $params = []): ?array
    {
        $apiKey = env('myhr.api_key');
        $apiU   = env('myhr.api_u');
        $apiP   = env('myhr.api_p');

        $baseUrl = rtrim(env('myhr.user_api_url', 'http://localhost:8888/myhr/api-hris-selfservice-refactor/public'), '/');
        $url = $baseUrl . '/' . ltrim($endpoint, '/') . '?token=' . $token . '&key=' . $apiKey;
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $url .= '&' . $k . '=' . $v;
            }
        }

        $headers = [
            'Authorization: Basic ' . base64_encode($apiU . ':' . $apiP),
        ];

        log_message('debug', 'MyhrApi GET URL: ' . $url);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        log_message('debug', 'MyhrApi Response [' . $httpCode . ']: ' . mb_substr($response ?? '', 0, 500));

        if ($error) {
            log_message('error', 'MyhrApi cURL error: ' . $error . ' | URL: ' . $url);
            return null;
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'MyhrApi JSON error: ' . json_last_error_msg());
            return null;
        }

        // myhr/plus returns $decoded["data"] — extract the inner data
        $result = $decoded['data'] ?? $decoded;
        $result['_http_code'] = $httpCode;
        return $result;
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
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

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
