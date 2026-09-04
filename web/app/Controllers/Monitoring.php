<?php

namespace App\Controllers;

/**
 * ============================================================================
 * MONITORING CONTROLLER
 * ============================================================================
 *
 * Description: Halaman monitoring seluruh kegiatan HR (hr_selfservice + wine_hris).
 *
 * Responsibilities:
 * - Render halaman monitoring dengan statistik per domain
 * - Proxy data statistik, daftar, dan export CSV dari API
 */
class Monitoring extends BaseController
{
    public function index(): string
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');

        $stats = $this->fetchStats($startDate, $endDate);

        return $this->view('monitoring/main_page', [
            'title'      => 'Monitoring HRIS',
            'stats'      => $stats,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);
    }

    public function ajaxStats()
    {
        if (!$this->guard()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');

        return $this->response->setJSON([
            'status' => true,
            'stats'  => $this->fetchStats($startDate, $endDate),
        ]);
    }

    public function ajaxList()
    {
        if (!$this->guard()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $result = $this->api->get_data('monitoring/list', $this->request->getGet() ?? []);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Monitoring list API failed: ' . json_encode($result));
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memuat data']);
        }

        return $this->response->setJSON([
            'status'     => true,
            'items'      => $result['data']['result']['items'] ?? [],
            'pagination' => $result['data']['result']['pagination'] ?? [],
        ]);
    }

    public function export()
    {
        if (!$this->guard()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $table = preg_replace('/[^a-z_]/', '', (string) ($this->request->getGet('table') ?? ''));
        $query = http_build_query(array_merge($this->request->getGet() ?? [], ['table' => $table]));
        $url = rtrim(env('api.base_url', 'http://localhost:8888/'), '/') . '/monitoring/export?' . $query;

        $headers = ['X-API-Key: ' . env('api.service_key', '')];
        if ($this->userId !== null) {
            $headers[] = 'X-User-Id: ' . $this->userId;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_PROXY          => false,
        ]);
        $csv = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($csv === false || $httpCode !== 200) {
            log_message('error', 'Monitoring export API failed, HTTP ' . $httpCode);
            return $this->response->setStatusCode(502)->setJSON(['status' => false, 'message' => 'Gagal mengekspor data']);
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="monitoring-' . $table . '-' . date('Ymd') . '.csv"')
            ->setBody($csv);
    }

    private function fetchStats(string $startDate, string $endDate): array
    {
        $result = $this->api->get_data('monitoring/stats', [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'Monitoring stats API failed: ' . json_encode($result));
            return [];
        }

        return $result['data']['result'] ?? [];
    }

    private function guard(): bool
    {
        return session()->has('user');
    }
}
