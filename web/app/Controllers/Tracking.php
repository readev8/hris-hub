<?php

namespace App\Controllers;

/**
 * ============================================================================
 * TRACKING CONTROLLER
 * ============================================================================
 *
 * Description: Halaman publik untuk melacak status tiket berdasarkan
 *   tracking code.
 *
 * Responsibilities:
 * - Render form pencarian tracking code
 * - Proxy lookup tiket via API berdasarkan kode
 */
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
