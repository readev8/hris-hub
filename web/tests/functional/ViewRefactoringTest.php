<?php

namespace Tests\Functional;

require_once __DIR__ . '/BaseFunctionalTest.php';
/**
 * Verify that refactored views properly reference external JS/CSS files,
 * use PageData for data passing, and contain no inline business logic.
 *
 * @internal
 */
final class ViewRefactoringTest extends BaseFunctionalTest
{
    /**
     * Views that should be fully refactored (external JS/CSS, PageData).
     *
     * @var string[]
     */
    private array $refactoredViews = [
        'dashboard/main_page.php',
        'improvements/main_page.php',
        'improvements/create.php',
        'improvements/edit.php',
        'improvements/detail.php',
        'blueprints/main_page.php',
        'blueprints/create.php',
        'blueprints/edit.php',
        'blueprints/detail.php',
        'blueprints/page_specifications.php',
        'tickets/main_page.php',
        'tickets/create.php',
        'tickets/detail.php',
        'tickets/edit.php',
        'master-projects/detail.php',
        'master-projects/module_detail.php',
        'approvals/main_page.php',
        'roles/main_page.php',
        'roles/permissions.php',
        'users/main_page.php',
        'users/add_user.php',
    ];

    /**
     * Views that use window.PageData for data passing.
     * DataTable AJAX views don't use PageData.
     *
     * @var string[]
     */
    private array $pageDataViews = [
        'dashboard/main_page.php',
        'improvements/edit.php',
        'improvements/detail.php',
        'blueprints/edit.php',
        'blueprints/detail.php',
        'blueprints/page_specifications.php',
        'tickets/edit.php',
        'tickets/detail.php',
        'master-projects/detail.php',
        'master-projects/module_detail.php',
        'roles/permissions.php',
        'users/main_page.php',
    ];

    /**
     * Views that have inline <script> with DataTable/Chart config (acceptable).
     *
     * @var string[]
     */
    private array $inlineConfigViews = [
        'dashboard/main_page.php',
        'improvements/main_page.php',
    ];

    // ── External Reference Tests ──────────────────────────────

    /**
     * @dataProvider refactoredViewProvider
     */
    public function testViewReferencesExternalJsFile(string $viewRelativePath): void
    {
        $viewPath = $this->viewPath($viewRelativePath);
        $content  = $this->readFile($viewPath);
        $this->assertNotFalse($content, "View file not found: {$viewRelativePath}");

        $scriptSrcs = $this->extractScriptSrcs($content);
        $externalJs = array_filter($scriptSrcs, fn($src) => str_contains($src, 'assets/js/'));

        $this->assertNotEmpty(
            $externalJs,
            "View {$viewRelativePath} does not reference any external JS file via <script src>"
        );
    }

    /**
     * @dataProvider refactoredViewProvider
     */
    public function testViewReferencesExternalCssFile(string $viewRelativePath): void
    {
        $viewPath = $this->viewPath($viewRelativePath);
        $content  = $this->readFile($viewPath);
        $this->assertNotFalse($content, "View file not found: {$viewRelativePath}");

        $cssHrefs  = $this->extractCssHrefs($content);
        $externalCss = array_filter($cssHrefs, fn($href) => str_contains($href, 'assets/css/'));

        $this->assertNotEmpty(
            $externalCss,
            "View {$viewRelativePath} does not reference any external CSS file via <link>"
        );
    }

    // ── PageData Tests ────────────────────────────────────────

    /**
     * @dataProvider pageDataProvider
     */
    public function testViewUsesPageDataForDataPassing(string $viewRelativePath): void
    {
        $viewPath = $this->viewPath($viewRelativePath);
        $content  = $this->readFile($viewPath);
        $this->assertNotFalse($content, "View file not found: {$viewRelativePath}");

        $this->assertTrue(
            $this->hasPageData($content),
            "View {$viewRelativePath} does not use window.PageData for data passing"
        );
    }

    // ── No Inline Business Logic Tests ────────────────────────

    /**
     * @dataProvider inlineLogicExcludedProvider
     */
    public function testViewHasNoInlineBusinessLogic(string $viewRelativePath): void
    {
        $viewPath = $this->viewPath($viewRelativePath);
        $content  = $this->readFile($viewPath);
        $this->assertNotFalse($content, "View file not found: {$viewRelativePath}");

        $hasInline = $this->hasInlineBusinessLogic($content);

        $this->assertFalse(
            $hasInline,
            "View {$viewRelativePath} still contains inline business logic <script> blocks"
        );
    }

    // ── No Inline <style> Blocks ─────────────────────────────

    /**
     * @dataProvider refactoredViewProvider
     */
    public function testViewHasNoInlineStyleBlocks(string $viewRelativePath): void
    {
        $viewPath = $this->viewPath($viewRelativePath);
        $content  = $this->readFile($viewPath);
        $this->assertNotFalse($content, "View file not found: {$viewRelativePath}");

        // Remove PHP sections
        $cleaned = preg_replace('/<\?php.*?\?>/s', '', $content);

        // Check for <style> blocks
        $hasInlineStyle = (bool) preg_match('/<style[\s>]/i', $cleaned);

        $this->assertFalse(
            $hasInlineStyle,
            "View {$viewRelativePath} still contains inline <style> blocks"
        );
    }

    // ── External File Existence Tests ─────────────────────────

    /**
     * @dataProvider refactoredViewProvider
     */
    public function testReferencedExternalFilesExist(string $viewRelativePath): void
    {
        $viewPath = $this->viewPath($viewRelativePath);
        $content  = $this->readFile($viewPath);
        $this->assertNotFalse($content, "View file not found: {$viewRelativePath}");

        // Check JS files
        $scriptSrcs = $this->extractScriptSrcs($content);
        foreach ($scriptSrcs as $src) {
            if (!str_contains($src, 'assets/js/')) {
                continue;
            }
            // Extract path after /assets/
            if (preg_match('#/assets/(.+?)(?:\?v=|$)#', $src, $matches)) {
                $assetPath = $this->publicPath . '/assets/' . $matches[1];
                $this->assertFileExistsAndNotEmpty(
                    $assetPath,
                    "Referenced JS file not found for {$viewRelativePath}: {$src}"
                );
            }
        }

        // Check CSS files
        $cssHrefs = $this->extractCssHrefs($content);
        foreach ($cssHrefs as $href) {
            if (!str_contains($href, 'assets/css/')) {
                continue;
            }
            if (preg_match('#/assets/(.+?)(?:\?v=|$)#', $href, $matches)) {
                $assetPath = $this->publicPath . '/assets/' . $matches[1];
                $this->assertFileExistsAndNotEmpty(
                    $assetPath,
                    "Referenced CSS file not found for {$viewRelativePath}: {$href}"
                );
            }
        }
    }

    // ── Data Provider ─────────────────────────────────────────

    /**
     * @return array<string, array{0: string}>
     */
    public static function refactoredViewProvider(): array
    {
        $views = [
            'dashboard/main_page.php',
            'improvements/main_page.php',
            'improvements/create.php',
            'improvements/edit.php',
            'improvements/detail.php',
            'blueprints/main_page.php',
            'blueprints/create.php',
            'blueprints/edit.php',
            'blueprints/detail.php',
            'blueprints/page_specifications.php',
            'tickets/main_page.php',
            'tickets/create.php',
            'tickets/detail.php',
            'tickets/edit.php',
            'master-projects/detail.php',
            'master-projects/module_detail.php',
            'approvals/main_page.php',
            'roles/main_page.php',
            'roles/permissions.php',
            'users/main_page.php',
            'users/add_user.php',
        ];

        $data = [];
        foreach ($views as $view) {
            $data[$view] = [$view];
        }
        return $data;
    }

    /**
     * Data provider for views that use window.PageData.
     *
     * @return array<string, array{0: string}>
     */
    public static function pageDataProvider(): array
    {
        $views = [
            'dashboard/main_page.php',
            'improvements/edit.php',
            'improvements/detail.php',
            'blueprints/edit.php',
            'blueprints/detail.php',
            'blueprints/page_specifications.php',
            'tickets/edit.php',
            'tickets/detail.php',
            'master-projects/detail.php',
            'master-projects/module_detail.php',
            'roles/permissions.php',
            'users/main_page.php',
        ];

        $data = [];
        foreach ($views as $view) {
            $data[$view] = [$view];
        }
        return $data;
    }

    /**
     * Data provider: views that should have no inline business logic.
     * Excludes DataTable/Chart config views.
     *
     * @return array<string, array{0: string}>
     */
    public static function inlineLogicExcludedProvider(): array
    {
        $allViews = [
            'dashboard/main_page.php',
            'improvements/main_page.php',
            'improvements/create.php',
            'improvements/edit.php',
            'improvements/detail.php',
            'blueprints/main_page.php',
            'blueprints/create.php',
            'blueprints/edit.php',
            'blueprints/detail.php',
            'blueprints/page_specifications.php',
            'tickets/main_page.php',
            'tickets/create.php',
            'tickets/detail.php',
            'tickets/edit.php',
            'master-projects/detail.php',
            'master-projects/module_detail.php',
            'approvals/main_page.php',
            'roles/main_page.php',
            'roles/permissions.php',
            'users/main_page.php',
            'users/add_user.php',
        ];

        // Exclude views with DataTable/Chart config
        $excluded = ['dashboard/main_page.php', 'improvements/main_page.php'];
        $views = array_diff($allViews, $excluded);

        $data = [];
        foreach ($views as $view) {
            $data[$view] = [$view];
        }
        return $data;
    }
}
