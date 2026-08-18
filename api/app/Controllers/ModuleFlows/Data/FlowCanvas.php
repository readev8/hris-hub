<?php

namespace App\Controllers\ModuleFlows\Data;

use App\Controllers\BaseApi;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tables;

class FlowCanvas extends BaseApi
{
    public function get_canvas(): ResponseInterface
    {
        $db = $this->db();

        // ── Nodes on canvas (modules that have a position) ───────
        $nodes = $db->table(Tables::FLOW_NODE_POSITIONS . ' as p')
            ->select('p.module_id, p.pos_x, p.pos_y, m.name as module_name, m.master_project_id, mp.name as project_name')
            ->join(Tables::MODULES . ' as m', 'm.id = p.module_id AND m.active = 0', 'inner')
            ->join(Tables::MASTER_PROJECTS . ' as mp', 'mp.id = m.master_project_id AND mp.active = 0', 'left')
            ->where('p.active', 0)
            ->orderBy('m.name', 'ASC')
            ->get()
            ->getResultArray();

        $nodesOut = [];
        foreach ($nodes as $n) {
            $nodesOut[] = [
                'module_id'       => $this->api->encryptId($n['module_id']),
                'name'            => $n['module_name'],
                'project_id'      => $n['master_project_id'] ? $this->api->encryptId($n['master_project_id']) : null,
                'project_name'    => $n['project_name'],
                'pos_x'           => (int) $n['pos_x'],
                'pos_y'           => (int) $n['pos_y'],
            ];
        }

        // ── Available modules (live, not yet on canvas) ───────────
        $onCanvas = array_column($nodes, 'module_id');
        $avail = $db->table(Tables::MODULES . ' as m')
            ->select('m.id, m.name, m.master_project_id, mp.name as project_name')
            ->join(Tables::MASTER_PROJECTS . ' as mp', 'mp.id = m.master_project_id', 'left')
            ->where('m.active', 0)
            ->orderBy('mp.name', 'ASC')
            ->orderBy('m.name', 'ASC')
            ->get()
            ->getResultArray();

        $available = [];
        foreach ($avail as $a) {
            if (in_array((int) $a['id'], $onCanvas, true)) {
                continue;
            }
            $available[] = [
                'module_id'    => $this->api->encryptId($a['id']),
                'name'         => $a['name'],
                'project_id'   => $a['master_project_id'] ? $this->api->encryptId($a['master_project_id']) : null,
                'project_name' => $a['project_name'],
            ];
        }

        // ── Connections ───────────────────────────────────────────
        $conns = $db->table(Tables::FLOW_CONNECTIONS)
            ->where('active', 0)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $connections = [];
        foreach ($conns as $c) {
            $connections[] = [
                'id'      => $this->api->encryptId($c['id']),
                'from'    => $this->api->encryptId($c['from_module_id']),
                'to'      => $this->api->encryptId($c['to_module_id']),
            ];
        }

        return $this->JSONResponse('OK', [
            'nodes'       => $nodesOut,
            'available'   => $available,
            'connections' => $connections,
        ], 200);
    }
}
