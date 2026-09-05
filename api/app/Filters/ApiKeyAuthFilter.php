<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKeyAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $apiKey = $request->getHeaderLine('X-API-Key');
        $expectedKey = env('api.service_key');

        if (!$apiKey || !$expectedKey || $apiKey !== $expectedKey) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'data' => [
                        'statuscode' => 401,
                        'message'    => 'Unauthorized: invalid API key',
                        'result'     => null,
                    ],
                    'status' => false,
                ]);
        }

        // X-User-Id dibaca langsung oleh BaseApi::getCurrentUserId() dari header
        // (tanpa dynamic property — deprecated di PHP 8.2+).
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
