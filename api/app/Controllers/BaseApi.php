<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Config\Enums;
use App\Models\Roles\PermissionCheck_model;

abstract class BaseApi extends ResourceController
{
    protected $request;
    protected $req;
    protected array $model_map = [];
    private array $instances = [];
    protected ?\App\Libraries\IdEncryption $api = null;
    private ?PermissionCheck_model $permModel = null;

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

    protected function getCurrentUserRole(): ?int
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return null;
        $user = $this->db()->table('users')->where('id', $userId)->get()->getRowArray();
        return $user ? (int)($user['role_id'] ?? $user['role']) : null;
    }

    protected function getCurrentUserRecord(): ?array
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return null;
        return $this->db()->table('users')->where('id', $userId)->get()->getRowArray();
    }

    protected function checkPermission(string $module, string $action = 'can_view'): bool
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return false;
        if (!$this->permModel) {
            $this->permModel = new PermissionCheck_model();
        }
        return $this->permModel->hasPermission($userId, $module, $action);
    }

    protected function checkTicketOwnership(int $ticketId): bool
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return false;

        $ticket = $this->db()->table('tickets')->where('id', $ticketId)->get()->getRowArray();
        if (!$ticket) return false;

        $role = $this->getCurrentUserRole();
        if ($role === Enums::ADMIN) return true;
        if ((int)$ticket['creator_id'] === $userId) return true;
        if (isset($ticket['assignee_id']) && $ticket['assignee_id'] && (int)$ticket['assignee_id'] === $userId) return true;

        return false;
    }
}
