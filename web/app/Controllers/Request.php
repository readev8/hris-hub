<?php

namespace App\Controllers;

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
