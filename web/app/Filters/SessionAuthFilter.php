<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SessionAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');
        if (!$session->has('user')) {
            $isAjax = $request->hasHeader('X-Requested-With')
                && $request->getHeader('X-Requested-With')->getValue() === 'XMLHttpRequest';

            if ($isAjax || $request->getMethod() === 'POST') {
                $response = service('response');
                $response->setStatusCode(401);
                $response->setContentType('application/json');
                $response->setBody(json_encode([
                    'status'  => false,
                    'message' => 'Sesi telah berakhir, silakan login kembali',
                    'redirect' => '/login',
                ]));
                return $response;
            }

            $currentUrl = current_url();
            return redirect()->to('/login?redirect=' . urlencode($currentUrl));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
