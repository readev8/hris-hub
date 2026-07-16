<?php

namespace Tests\Functional;

require_once __DIR__ . '/BaseFunctionalTest.php';
/**
 * Functional tests for the Master Projects Module Detail page refactoring.
 *
 * Verifies:
 * - External JS/CSS are properly loaded
 * - PageData contains projectId, moduleId, pageIds
 * - JS module exposes correct public API (page CRUD, module edit, bug list, kanban)
 * - Kanban CSS classes exist
 * - No inline business logic remains
 *
 * @internal
 */
final class ModuleDetailTest extends BaseFunctionalTest
{
    private string $viewFile = 'master-projects/module_detail.php';
    private string $jsFile   = 'js/page/master-projects/module_detail.js';
    private string $cssFile  = 'css/page/master-projects/module_detail.css';

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

    public function testViewFileExists(): void
    {
        $this->assertNotEmpty($this->viewContent);
    }

    public function testViewReferencesExternalJs(): void
    {
        $srcs = $this->extractScriptSrcs($this->viewContent);
        $this->assertNotEmpty(array_filter($srcs, fn($s) => str_contains($s, 'module_detail.js')));
    }

    public function testViewReferencesExternalCss(): void
    {
        $hrefs = $this->extractCssHrefs($this->viewContent);
        $this->assertNotEmpty(array_filter($hrefs, fn($h) => str_contains($h, 'module_detail.css')));
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

    // ── PageData Structure ────────────────────────────────────

    public function testPageDataContainsProjectId(): void
    {
        $this->assertStringContainsString("'projectId'", $this->viewContent);
    }

    public function testPageDataContainsModuleId(): void
    {
        $this->assertStringContainsString("'moduleId'", $this->viewContent);
    }

    public function testPageDataContainsPageIds(): void
    {
        $this->assertStringContainsString("'pageIds'", $this->viewContent);
    }

    public function testPageDataUsesArrayColumn(): void
    {
        $this->assertStringContainsString('array_column', $this->viewContent);
    }

    // ── JS Module Tests ───────────────────────────────────────

    public function testJsFileExists(): void
    {
        $this->assertNotEmpty($this->jsContent);
    }

    public function testJsUsesObjectLiteralModulePattern(): void
    {
        $this->assertMatchesRegularExpression(
            '/var\s+ModuleDetail\s*=\s*\(function/',
            $this->jsContent
        );
    }

    public function testJsReadsPageData(): void
    {
        $this->assertStringContainsString('window.PageData', $this->jsContent);
    }

    public function testJsReadsPageIds(): void
    {
        $this->assertStringContainsString('pageIds', $this->jsContent);
    }

    /**
     * @dataProvider publicMethodProvider
     */
    public function testJsExposesPublicMethod(string $methodName): void
    {
        $this->assertStringContainsString($methodName . ':', $this->jsContent);
    }

    public static function publicMethodProvider(): array
    {
        return [
            'switchDetailTab' => ['switchDetailTab'],
            'openPageModal'   => ['openPageModal'],
            'editPage'        => ['editPage'],
            'deletePage'      => ['deletePage'],
            'editModule'      => ['editModule'],
            'deleteModule'    => ['deleteModule'],
            'showBugList'     => ['showBugList'],
        ];
    }

    public function testJsUsesStrictMode(): void
    {
        $this->assertStringContainsString("'use strict';", $this->jsContent);
    }

    public function testJsHasKanbanBoard(): void
    {
        $this->assertStringContainsString('loadKanban', $this->jsContent);
        $this->assertStringContainsString('renderKanban', $this->jsContent);
        $this->assertStringContainsString('buildKanbanCard', $this->jsContent);
        $this->assertStringContainsString('initKanbanSortables', $this->jsContent);
    }

    public function testJsKanbanFiltersByPageIds(): void
    {
        $this->assertStringContainsString('pageIds.indexOf', $this->jsContent);
    }

    public function testJsHasBugListModal(): void
    {
        $this->assertStringContainsString('showBugList', $this->jsContent);
        $this->assertStringContainsString('bugListModal', $this->jsContent);
    }

    public function testCssFileExists(): void
    {
        $this->assertNotEmpty($this->cssContent);
    }

    /**
     * @dataProvider cssClassProvider
     */
    public function testCssDefinesRequiredClass(string $className): void
    {
        $this->assertStringContainsString('.' . $className, $this->cssContent);
    }

    public static function cssClassProvider(): array
    {
        return [
            'kanban-board'       => ['kanban-board'],
            'kanban-column'      => ['kanban-column'],
            'kanban-card'        => ['kanban-card'],
            'kanban-card-title'  => ['kanban-card-title'],
            'kanban-empty'       => ['kanban-empty'],
            'page-icon-wrapper'  => ['page-icon-wrapper'],
            'table-code'         => ['table-code'],
        ];
    }

    public function testOnclickHandlersUseModulePrefix(): void
    {
        $htmlContent = preg_replace('/<\?= \$this->section\(\'(styles|scripts|modals)\'\).*?\$this->endSection\(\) ?>/s', '', $this->viewContent);
        $methods = $this->extractOnclickMethods($htmlContent);

        foreach ($methods as $method) {
            $this->assertMatchesRegularExpression('/^[a-z]/', $method);
        }
    }
}
