<?php

namespace Tests\Functional;

require_once __DIR__ . '/BaseFunctionalTest.php';

/**
 * Verify that all external JS and CSS files referenced by views actually exist
 * and are non-empty on disk.
 *
 * @internal
 */
final class AssetExistenceTest extends BaseFunctionalTest
{
    /**
     * Shared CSS files that should exist.
     */
    private array $sharedCssFiles = [
        'css/page/_shared/badge-helpers.css',
        'css/page/_shared/kanban-board.css',
        'css/page/_shared/field-errors.css',
        'css/page/_shared/column-search.css',
        'css/page/_shared/segmented-control.css',
    ];

    /**
     * Shared JS files that should exist.
     */
    private array $sharedJsFiles = [
        'js/page/_shared/badge-helpers.js',
        'js/page/_shared/kanban-board.js',
        'js/page/_shared/field-errors.js',
    ];

    // ── Shared Asset Tests ────────────────────────────────────

    public function testSharedCssFilesExist(): void
    {
        foreach ($this->sharedCssFiles as $relativePath) {
            $fullPath = $this->assetPath($relativePath);
            $this->assertFileExistsAndNotEmpty($fullPath, "Shared CSS missing: {$relativePath}");
        }
    }

    public function testSharedJsFilesExist(): void
    {
        foreach ($this->sharedJsFiles as $relativePath) {
            $fullPath = $this->assetPath($relativePath);
            $this->assertFileExistsAndNotEmpty($fullPath, "Shared JS missing: {$relativePath}");
        }
    }

    // ── Per-View Asset Tests ──────────────────────────────────

    /**
     * @dataProvider viewJsAssetProvider
     */
    public function testJsFileExistsForView(string $viewRelativePath, string $jsRelativePath): void
    {
        $fullPath = $this->assetPath($jsRelativePath);
        $this->assertFileExistsAndNotEmpty(
            $fullPath,
            "JS file missing for view {$viewRelativePath}: {$jsRelativePath}"
        );
    }

    /**
     * @dataProvider viewCssAssetProvider
     */
    public function testCssFileExistsForView(string $viewRelativePath, string $cssRelativePath): void
    {
        $fullPath = $this->assetPath($cssRelativePath);
        $this->assertFileExistsAndNotEmpty(
            $fullPath,
            "CSS file missing for view {$viewRelativePath}: {$cssRelativePath}"
        );
    }

    /**
     * Data provider: view → JS asset mapping.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function viewJsAssetProvider(): array
    {
        return [
            'dashboard/main_page → js'       => ['dashboard/main_page.php',       'js/page/dashboard/main_page.js'],
            'improvements/main_page → js'    => ['improvements/main_page.php',    'js/page/improvements/main_page.js'],
            'improvements/create → js'       => ['improvements/create.php',       'js/page/improvements/create.js'],
            'improvements/edit → js'         => ['improvements/edit.php',         'js/page/improvements/edit.js'],
            'improvements/detail → js'       => ['improvements/detail.php',       'js/page/improvements/detail.js'],
            'blueprints/main_page → js'      => ['blueprints/main_page.php',      'js/page/blueprints/main_page.js'],
            'blueprints/create → js'         => ['blueprints/create.php',         'js/page/blueprints/create.js'],
            'blueprints/edit → js'           => ['blueprints/edit.php',           'js/page/blueprints/edit.js'],
            'blueprints/detail → js'         => ['blueprints/detail.php',         'js/page/blueprints/detail.js'],
            'blueprints/page_specifications → js' => ['blueprints/page_specifications.php', 'js/page/blueprints/page_specifications.js'],
            'tickets/main_page → js'         => ['tickets/main_page.php',         'js/page/tickets/main_page.js'],
            'tickets/create → js'            => ['tickets/create.php',            'js/page/tickets/create.js'],
            'tickets/detail → js'            => ['tickets/detail.php',            'js/page/tickets/detail.js'],
            'tickets/edit → js'              => ['tickets/edit.php',              'js/page/tickets/edit.js'],
            'master-projects/detail → js'    => ['master-projects/detail.php',    'js/page/master-projects/detail.js'],
            'master-projects/module_detail → js' => ['master-projects/module_detail.php', 'js/page/master-projects/module_detail.js'],
            'approvals/main_page → js'       => ['approvals/main_page.php',       'js/page/approvals/main_page.js'],
            'roles/main_page → js'           => ['roles/main_page.php',           'js/page/roles/main_page.js'],
            'roles/permissions → js'         => ['roles/permissions.php',         'js/page/roles/permissions.js'],
            'users/main_page → js'           => ['users/main_page.php',           'js/page/users/main_page.js'],
            'users/add_user → js'            => ['users/add_user.php',            'js/page/users/add_user.js'],
            'monitoring/main_page → js'      => ['monitoring/main_page.php',      'js/page/monitoring/main_page.js'],
        ];
    }

    /**
     * Data provider: view → CSS asset mapping.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function viewCssAssetProvider(): array
    {
        return [
            'dashboard/main_page → css'       => ['dashboard/main_page.php',       'css/page/dashboard/main_page.css'],
            'improvements/main_page → css'    => ['improvements/main_page.php',    'css/page/improvements/main_page.css'],
            'improvements/create → css'       => ['improvements/create.php',       'css/page/improvements/create.css'],
            'improvements/edit → css'         => ['improvements/edit.php',         'css/page/improvements/edit.css'],
            'improvements/detail → css'       => ['improvements/detail.php',       'css/page/improvements/detail.css'],
            'blueprints/main_page → css'      => ['blueprints/main_page.php',      'css/page/blueprints/main_page.css'],
            'blueprints/create → css'         => ['blueprints/create.php',         'css/page/blueprints/create.css'],
            'blueprints/edit → css'           => ['blueprints/edit.php',           'css/page/blueprints/edit.css'],
            'blueprints/detail → css'         => ['blueprints/detail.php',         'css/page/blueprints/detail.css'],
            'blueprints/page_specifications → css' => ['blueprints/page_specifications.php', 'css/page/blueprints/page_specifications.css'],
            'tickets/main_page → css'         => ['tickets/main_page.php',         'css/page/tickets/main_page.css'],
            'tickets/create → css'            => ['tickets/create.php',            'css/page/tickets/create.css'],
            'tickets/detail → css'            => ['tickets/detail.php',            'css/page/tickets/detail.css'],
            'tickets/edit → css'              => ['tickets/edit.php',              'css/page/tickets/edit.css'],
            'master-projects/detail → css'    => ['master-projects/detail.php',    'css/page/master-projects/detail.css'],
            'master-projects/module_detail → css' => ['master-projects/module_detail.php', 'css/page/master-projects/module_detail.css'],
            'approvals/main_page → css'       => ['approvals/main_page.php',       'css/page/approvals/main_page.css'],
            'roles/main_page → css'           => ['roles/main_page.php',           'css/page/roles/main_page.css'],
            'roles/permissions → css'         => ['roles/permissions.php',         'css/page/roles/permissions.css'],
            'users/main_page → css'           => ['users/main_page.php',           'css/page/users/main_page.css'],
            'users/add_user → css'            => ['users/add_user.php',            'css/page/users/add_user.css'],
            'monitoring/main_page → css'      => ['monitoring/main_page.php',      'css/page/monitoring/main_page.css'],
        ];
    }
}
