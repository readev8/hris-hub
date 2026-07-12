<?php

namespace App\Controllers;

class Tracking extends BaseController
{
    public function index()
    {
        $code = $this->request->getGet('code');
        return $this->view('tracking/index', [
            'title' => 'Track Ticket',
            'code'  => $code,
        ]);
    }

    public function lookup(string $code)
    {
        $result = $this->api->get_data('tickets/track/' . $code);
        return $this->response->setJSON($result);
    }
}
