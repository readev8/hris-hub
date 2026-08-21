<?php

namespace App\Controllers;

/**
 * ============================================================================
 * REQUEST CONTROLLER
 * ============================================================================
 *
 * Description: Endpoint publik untuk mengambil CSRF token.
 *   Digunakan oleh JS global (d.js) untuk regenerate token.
 */
class Request extends BaseController
{
    public function get()
    {
        $tokenName = csrf_token();
        $hash = csrf_hash();

        return $this->response->setJSON([
            $tokenName => $hash,
            'token' => [$tokenName => $hash],
        ]);
    }
}
