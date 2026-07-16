<?php

namespace Tests\Functional;

require_once __DIR__ . '/BaseFunctionalTest.php';
/**
 * Functional tests for the Blueprint Detail page refactoring.
 *
 * Verifies:
 * - External JS/CSS are properly loaded
 * - PageData contains all required fields
 * - JS module exposes correct public API
 * - No inline business logic remains
 * - onclick handlers use BlueprintDetail.* prefix
 *
 * @internal
 */
final class BlueprintDetailTest extends BaseFunctionalTest
{
    private string $viewFile = 'blueprints/detail.php';
    private string $jsFile   = 'js/page/blueprints/detail.js';
    private string $cssFile  = 'css/page/blueprints/detail.css';

    private string $viewContent = '';
    private string $jsContent   = '';
    private string $cssContent  = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->viewContent = $this->readFile($this->viewPath($this->viewFile)) ?: '';
        $this->jsContent   = $this->readFile($this->assetPath($this->jsFile)) ?: '';
        $this->cssContent  = $this->readFile($this->assetPath($this->cssFile)) ?: '';
    }

    // ── View Structure Tests ──────────────────────────────────

    public function testViewFileExists(): void
    {
        $this->assertNotEmpty($this->viewContent, "View file not found: {$this->viewFile}");
    }

    public function testViewReferencesExternalJs(): void
    {
        $srcs = $this->extractScriptSrcs($this->viewContent);
        $this->assertNotEmpty(
            array_filter($srcs, fn($s) => str_contains($s, 'detail.js')),
            'View must reference detail.js via <script src>'
        );
    }

    public function testViewReferencesExternalCss(): void
    {
        $hrefs = $this->extractCssHrefs($this->viewContent);
        $this->assertNotEmpty(
            array_filter($hrefs, fn($h) => str_contains($h, 'detail.css')),
            'View must reference detail.css via <link>'
        );
    }

    public function testViewUsesPageData(): void
    {
        $this->assertTrue($this->hasPageData($this->viewContent));
    }

    public function testViewHasNoInlineLogic(): void
    {
        $this->assertFalse($this->hasInlineBusinessLogic($this->viewContent));
    }

    public function testViewHasNoInlineStyle(): void
    {
        $cleaned = preg_replace('/<\?php.*?\?>/s', '', $this->viewContent);
        $this->assertFalse((bool) preg_match('/<style[\s>]/i', $cleaned));
    }

    // ── PageData Structure Tests ──────────────────────────────

    public function testPageDataContainsToken(): void
    {
        $this->assertStringContainsString("'token'", $this->viewContent);
    }

    public function testPageDataContainsUserPermissions(): void
    {
        $this->assertStringContainsString("'userPermissions'", $this->viewContent);
    }

    public function testPageDataContainsModules(): void
    {
        $this->assertStringContainsString("'modules'", $this->viewContent);
    }

    public function testPageDataContainsCurrentModuleId(): void
    {
        $this->assertStringContainsString("'currentModuleId'", $this->viewContent);
    }

    // ── JS Module Tests ───────────────────────────────────────

    public function testJsFileExists(): void
    {
        $this->assertNotEmpty($this->jsContent, "JS file not found: {$this->jsFile}");
    }

    public function testJsUsesObjectLiteralModulePattern(): void
    {
        $this->assertMatchesRegularExpression(
            '/var\s+BlueprintDetail\s*=\s*\(function\s*\(\)/',
            $this->jsContent,
            'JS must use object-literal module pattern: var BlueprintDetail = (function() { ... })()'
        );
    }

    public function testJsReadsPageData(): void
    {
        $this->assertStringContainsString('window.PageData', $this->jsContent);
    }

    public function testJsExposesPublicApi(): void
    {
        $this->assertStringContainsString('return {', $this->jsContent);
    }

    /**
     * @dataProvider publicMethodProvider
     */
    public function testJsExposesPublicMethod(string $methodName): void
    {
        $this->assertStringContainsString(
            $methodName . ':',
            $this->jsContent,
            "JS public API must expose: {$methodName}"
        );
    }

    public static function publicMethodProvider(): array
    {
        return [
            'selectModule'        => ['selectModule'],
            'showAddModule'       => ['showAddModule'],
            'editModule'          => ['editModule'],
            'deleteModule'        => ['deleteModule'],
            'showAddScenario'     => ['showAddScenario'],
            'editScenario'        => ['editScenario'],
            'deleteScenario'      => ['deleteScenario'],
            'showAddDesignPage'   => ['showAddDesignPage'],
            'editDesignPage'      => ['editDesignPage'],
            'deleteDesignPage'    => ['deleteDesignPage'],
            'switchTab'           => ['switchTab'],
            'doAction'            => ['doAction'],
            'promptReject'        => ['promptReject'],
            'confirmDelete'       => ['confirmDelete'],
        ];
    }

    public function testJsUsesStrictMode(): void
    {
        $this->assertStringContainsString("'use strict';", $this->jsContent);
    }

    // ── CSS Tests ─────────────────────────────────────────────

    public function testCssFileExists(): void
    {
        $this->assertNotEmpty($this->cssContent, "CSS file not found: {$this->cssFile}");
    }

    public function testCssHasFileHeader(): void
    {
        $this->assertStringContainsString('@package', $this->cssContent);
        $this->assertStringContainsString('@file', $this->cssContent);
    }

    /**
     * @dataProvider cssClassProvider
     */
    public function testCssDefinesRequiredClass(string $className): void
    {
        $this->assertStringContainsString(
            '.' . $className,
            $this->cssContent,
            "CSS must define class: .{$className}"
        );
    }

    public static function cssClassProvider(): array
    {
        return [
            'card-item'          => ['card-item'],
            'card-item-title'    => ['card-item-title'],
            'card-item-desc'     => ['card-item-desc'],
            'card-item-actions'  => ['card-item-actions'],
            'card-item-images'   => ['card-item-images'],
            'field-error'        => ['field-error'],
            'module-actions'     => ['module-actions'],
            'dropzone-area'      => ['dropzone-area'],
            'file-preview-thumb' => ['file-preview-thumb'],
        ];
    }

    // ── onclick Handler Tests ─────────────────────────────────

    public function testOnclickHandlersUseModulePrefix(): void
    {
        // Extract onclick values from the HTML portion (before script sections)
        $htmlContent = preg_replace('/<\?= \$this->section\(\'(styles|scripts|modals)\'\).*?\$this->endSection\(\) ?>/s', '', $this->viewContent);

        $onclickMethods = $this->extractOnclickMethods($htmlContent);

        if (empty($onclickMethods)) {
            // No onclick handlers found in HTML — acceptable
            return;
        }

        foreach ($onclickMethods as $method) {
            // Method should be lowercase camelCase
            $this->assertMatchesRegularExpression(
                '/^[a-z]/',
                $method,
                "onclick handler method should be camelCase, found: {$method}"
            );
        }
    }
}
