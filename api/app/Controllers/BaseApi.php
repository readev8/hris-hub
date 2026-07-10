<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

abstract class BaseApi extends ResourceController
{
    protected $request;
    protected $req;
    protected array $model_map = [];
    private array $instances = [];
    protected ?\App\Libraries\IdEncryption $api = null;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->req = $request;
        $this->api = new \App\Libraries\IdEncryption();
    }

    public function __get(string $name): mixed
    {
        if (isset($this->model_map[$name]) && !isset($this->instances[$name])) {
            $class = $this->model_map[$name];
            $this->instances[$name] = new $class();
        }
        return $this->instances[$name] ?? null;
    }

    protected function JSONResponse(string $message, mixed $data = null, int $code = 200): ResponseInterface
    {
        return $this->respond([
            'data' => [
                'statuscode' => $code,
                'message'    => $message,
                'result'     => $data,
            ],
            'status' => $code < 400,
        ], $code);
    }

    protected function cleanInput(mixed $input): mixed
    {
        if (empty($input)) {
            return is_array($input) ? [] : '';
        }
        if (is_array($input)) {
            return array_map([$this, 'cleanInput'], $input);
        }
        if (is_string($input)) {
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    protected function get_parameter(array $field_map, array $post): array
    {
        $out = [];
        foreach ($field_map as $input_name => $db_column) {
            if (array_key_exists($input_name, $post)) {
                $out[$db_column] = $post[$input_name];
            }
        }
        return $out;
    }

    protected function getCurrentUserId(): ?int
    {
        return $this->request->user_id ?? null;
    }

    protected function resolveId(string $encryptedId): ?int
    {
        $id = $this->api->decryptId($encryptedId);
        if ($id === null || $id <= 0) {
            return null;
        }
        return $id;
    }

    protected function db(): \CodeIgniter\Database\BaseConnection
    {
        return \Config\Database::connect();
    }
}
