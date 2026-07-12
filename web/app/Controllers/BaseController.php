<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected \App\Libraries\ApiClient $api;
    protected ?int $userId = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->api = new \App\Libraries\ApiClient();

        $session = service('session');
        $user = $session->get('user');
        $this->userId = $user['id'] ?? null;
        $this->api->setUserId($this->userId);

        helper(['url', 'ui', 'permission']);
    }

    protected function view(string $name, array $data = []): string
    {
        $data['base_url'] = base_url();
        $data['site_url'] = site_url();
        return view($name, $data);
    }
}
