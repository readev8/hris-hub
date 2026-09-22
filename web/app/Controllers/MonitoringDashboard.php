<?php

namespace App\Controllers;

/**
 * ============================================================================
 * MONITORING DASHBOARD CONTROLLER
 * ============================================================================
 *
 * Description: Dashboard monitoring penggunaan aplikasi (log login) dan log akses aplikasi.
 *
 * Responsibilities:
 * - Render halaman monitoring dashboard dengan statistik login dan akses
 * - Proxy data statistik, log login, log akses, dan export dari API
 */
class MonitoringDashboard extends BaseController
{
    public function index(): string
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');

        $stats = $this->fetchStats($startDate, $endDate);

        return $this->view('monitoring-dashboard/main_page', [
            'title'      => 'Monitoring Penggunaan Aplikasi',
            'stats'      => $stats,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'debug'      => ENVIRONMENT === 'development',
        ]);
    }

    public function ajaxStats()
    {
        if (!$this->guard()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        if (function_exists('session_write_close')) {
            @session_write_close();
        }

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-d');

        return $this->response->setJSON([
            'status' => true,
            'stats'  => $this->fetchStats($startDate, $endDate),
        ]);
    }

    public function ajaxLoginLogs()
    {
        if (!$this->guard()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        if (function_exists('session_write_close')) {
            @session_write_close();
        }

        $t0 = microtime(true);
        $result = $this->api->get_data('monitoring/list', array_merge($this->request->getGet() ?? [], ['table' => 'session']));
        log_message('debug', '[MonitoringDashboard][WebProxy] endpoint=monitoring/list table=session http=' . ($result['_http_code'] ?? 0) . ' ms=' . (int) ((microtime(true) - $t0) * 1000));

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'MonitoringDashboard login logs API failed: ' . json_encode($result));
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memuat data login']);
        }

        return $this->response->setJSON([
            'status'     => true,
            'items'      => $result['data']['result']['items'] ?? [],
            'pagination' => $result['data']['result']['pagination'] ?? [],
        ]);
    }

    public function ajaxAccessLogs()
    {
        if (!$this->guard()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        if (function_exists('session_write_close')) {
            @session_write_close();
        }

        $t0 = microtime(true);
        $result = $this->api->get_data('monitoring/activity', $this->request->getGet() ?? []);
        log_message('debug', '[MonitoringDashboard][WebProxy] endpoint=monitoring/activity http=' . ($result['_http_code'] ?? 0) . ' ms=' . (int) ((microtime(true) - $t0) * 1000));

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'MonitoringDashboard access logs API failed: ' . json_encode($result));
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memuat log akses']);
        }

        return $this->response->setJSON([
            'status' => true,
            'items'  => $result['data']['result']['items'] ?? [],
        ]);
    }

    public function ajaxTrend()
    {
        if (!$this->guard()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        if (function_exists('session_write_close')) {
            @session_write_close();
        }

        $t0 = microtime(true);
        $result = $this->api->get_data('monitoring/trend', $this->request->getGet() ?? []);
        log_message('debug', '[MonitoringDashboard][WebProxy] endpoint=monitoring/trend http=' . ($result['_http_code'] ?? 0) . ' ms=' . (int) ((microtime(true) - $t0) * 1000));

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'MonitoringDashboard trend API failed: ' . json_encode($result));
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memuat tren']);
        }

        return $this->response->setJSON([
            'status' => true,
            'items'  => $result['data']['result']['items'] ?? [],
        ]);
    }

    public function ajaxDetail()
    {
        if (!$this->guard()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        if (function_exists('session_write_close')) {
            @session_write_close();
        }

        $table = preg_replace('/[^a-z_]/', '', (string) ($this->request->getGet('table') ?? ''));
        $id = preg_replace('/[^0-9]/', '', (string) ($this->request->getGet('id') ?? ''));
        if ($table === '' || $id === '') {
            return $this->response->setJSON(['status' => false, 'message' => 'Parameter tidak valid']);
        }

        $t0 = microtime(true);
        $result = $this->api->get_data('monitoring/detail', ['table' => $table, 'id' => $id]);
        log_message('debug', '[MonitoringDashboard][WebProxy] endpoint=monitoring/detail table=' . $table . ' http=' . ($result['_http_code'] ?? 0) . ' ms=' . (int) ((microtime(true) - $t0) * 1000));

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'MonitoringDashboard detail API failed: ' . json_encode($result));
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memuat detail']);
        }

        return $this->response->setJSON([
            'status' => true,
            'row'    => $result['data']['result'] ?? [],
        ]);
    }

    public function export()
    {
        if (!$this->guard()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        if (function_exists('session_write_close')) {
            @session_write_close();
        }

        $table = preg_replace('/[^a-z_]/', '', (string) ($this->request->getGet('table') ?? 'session'));
        $format = strtolower((string) ($this->request->getGet('format') ?? 'csv')) === 'xlsx' ? 'xlsx' : 'csv';
        $query = http_build_query(array_merge($this->request->getGet() ?? [], ['table' => $table, 'format' => $format]));
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
        log_message('debug', '[MonitoringDashboard][WebProxy] endpoint=monitoring/export table=' . $table . ' format=' . $format . ' http=' . $httpCode . ' bytes=' . (is_string($csv) ? strlen($csv) : 0));

        if ($csv === false || $httpCode !== 200) {
            log_message('error', 'MonitoringDashboard export API failed, HTTP ' . $httpCode);
            return $this->response->setStatusCode(502)->setJSON(['status' => false, 'message' => 'Gagal mengekspor data']);
        }

        if ($format === 'xlsx') {
            return $this->response
                ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->setHeader('Content-Disposition', 'attachment; filename="monitoring-dashboard-' . $table . '-' . date('Ymd') . '.xlsx"')
                ->setBody($csv);
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="monitoring-dashboard-' . $table . '-' . date('Ymd') . '.csv"')
            ->setBody($csv);
    }

    private function fetchStats(string $startDate, string $endDate): array
    {
        $t0 = microtime(true);
        $result = $this->api->get_data('monitoring/stats', [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);
        log_message('debug', '[MonitoringDashboard][WebProxy] endpoint=monitoring/stats http=' . ($result['_http_code'] ?? 0) . ' ms=' . (int) ((microtime(true) - $t0) * 1000));

        if (!$result || !($result['status'] ?? false)) {
            log_message('error', 'MonitoringDashboard stats API failed: ' . json_encode($result));
            return [];
        }

        return $result['data']['result'] ?? [];
    }

    private function guard(): bool
    {
        return session()->has('user');
    }
}
