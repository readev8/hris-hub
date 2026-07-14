# Fix: Module Detail Navigation — Encrypted ID Mismatch

## Root Cause

`encryptId()` in `api/app/Libraries/IdEncryption.php` uses AES-256-GCM with `random_bytes()` IV (line 22). Same ID encrypts to different ciphertext each time.

In `web/app/Controllers/MasterProjects.php` line 98:
```php
if ($m['id'] === $encryptedModuleId) {
```
- `$m['id']` — encrypted by API when generating project detail (IV #1)
- `$encryptedModuleId` — encrypted at page render time (IV #2)

These NEVER match → controller redirects → page reload.

## Fix: New API Endpoint for Module Detail

### File 1: CREATE `api/app/Controllers/MasterProjects/Data/ModuleDetail.php`

```php
<?php

namespace App\Controllers\MasterProjects\Data;

use App\Controllers\BaseApi;

class ModuleDetail extends BaseApi
{
    public function get_detail(string $encryptedModuleId)
    {
        $moduleId = $this->resolveId($encryptedModuleId);
        if (!$moduleId) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Module not found',
            ]);
        }

        // Fetch module
        $module = $this->db()->table('modules')
            ->where('id', $moduleId)
            ->get()
            ->getRowArray();

        if (!$module) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Module not found',
            ]);
        }

        // Fetch pages with bug counts
        $pages = $this->db()->table('pages')
            ->where('module_id', $moduleId)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $result = [
            'id'          => $this->api->encryptId($module['id']),
            'name'        => $module['name'],
            'description' => $module['description'],
            'sort_order'  => (int) $module['sort_order'],
            'pages'       => [],
        ];

        foreach ($pages as $p) {
            $bugStats = $this->db()->table('tickets')
                ->select("
                    COUNT(*) as bug_total,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as bug_open,
                    SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as bug_resolved
                ")
                ->where('page_id', $p['id'])
                ->where('type', 0)  // TICKET_TYPE_BUG
                ->get()
                ->getRowArray();

            $result['pages'][] = [
                'id'            => $this->api->encryptId($p['id']),
                'name'          => $p['name'],
                'url_path'      => $p['url_path'],
                'description'   => $p['description'],
                'sort_order'    => (int) $p['sort_order'],
                'bug_total'     => (int) ($bugStats['bug_total'] ?? 0),
                'bug_open'      => (int) ($bugStats['bug_open'] ?? 0),
                'bug_resolved'  => (int) ($bugStats['bug_resolved'] ?? 0),
            ];
        }

        return $this->response->setJSON([
            'status' => true,
            'data'   => ['result' => $result],
        ]);
    }
}
```

### File 2: MODIFY `api/app/Config/Routes.php`

Add before line 76 (`modules/(:any)/pages`):

```php
$routes->get('modules/(:any)',                'MasterProjects\Data\ModuleDetail::get_detail/$1');
```

So the routes become:
```
$routes->get('master-projects/(:any)/modules', ...);  // existing
$routes->get('modules/(:any)',                'MasterProjects\Data\ModuleDetail::get_detail/$1');  // NEW
$routes->get('modules/(:any)/pages',          'MasterProjects\Report\MasterProjectList::get_pages/$1');  // existing
```

### File 3: MODIFY `web/app/Controllers/MasterProjects.php`

Replace `moduleDetail()` method (lines 83-113):

```php
public function moduleDetail(string $encryptedProjectId, string $encryptedModuleId)
{
    if (!$this->guard()) {
        return redirect()->to('/dashboard');
    }

    // Fetch project for breadcrumb and header
    $projectResult = $this->api->get_data('master-projects/' . $encryptedProjectId);
    $project = $projectResult['data']['result'] ?? null;

    if (!$project) {
        return redirect()->to('/master-projects');
    }

    // Fetch module detail from new API endpoint
    $moduleResult = $this->api->get_data('modules/' . $encryptedModuleId);
    $module = $moduleResult['data']['result'] ?? null;

    if (!$module) {
        return redirect()->to('/master-projects/' . $encryptedProjectId);
    }

    return $this->view('master-projects/module_detail', [
        'title'   => esc($module['name']),
        'project' => $project,
        'module'  => $module,
    ]);
}
```

## Summary
- Root cause: AES-256-GCM random IV makes encrypted ID comparison impossible
- Fix: New API endpoint `GET modules/{encryptedId}` that decrypts ID server-side
- Web controller calls this endpoint instead of comparing encrypted IDs
- 3 files: 1 create, 2 modify
