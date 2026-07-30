<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RateLimitFilter implements FilterInterface
{
    private int $maxRequests = 30;
    private int $windowSeconds = 60;

    public function before(RequestInterface $request, $arguments = null)
    {
        $ip = $request->getIPAddress();
        $cache = \Config\Services::cache();
        $key = 'rate_track_' . md5($ip);

        $count = (int) ($cache->get($key) ?? 0);
        $count++;

        if ($count === 1) {
            $cache->save($key, $count, $this->windowSeconds);
        } else {
            $cache->save($key, $count, $this->windowSeconds);
        }

        if ($count > $this->maxRequests) {
            return service('response')
                ->setStatusCode(429)
                ->setJSON([
                    'data' => [
                        'statuscode' => 429,
                        'message'    => 'Too many requests. Please try again later.',
                        'result'     => null,
                    ],
                    'status' => false,
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
