<?php

namespace Tests\Functional;

require_once __DIR__ . '/BaseFunctionalTest.php';
/**
 * Functional tests for the Ticket Edit page refactoring.
 *
 * Verifies:
 * - External JS/CSS are properly loaded
 * - PageData contains all required fields (token, currentPageId, currentAssigneeId)
 * - JS module exposes correct public API
 * - Segmented control styles exist in CSS
 * - No inline business logic remains
 *
 * @internal
 */
final class TicketEditTest extends BaseFunctionalTest
{
    private string $viewFile = 'tickets/edit.php';
    private string $jsFile   = 'js/page/tickets/edit.js';
    private string $cssFile  = 'css/page/tickets/edit.css';

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
            array_filter($srcs, fn($s) => str_contains($s, 'edit.js')),
            'View must reference edit.js'
        );
    }

    public function testViewReferencesExternalCss(): void
    {
        $hrefs = $this->extractCssHrefs($this->viewContent);
        $this->assertNotEmpty(
            array_filter($hrefs, fn($h) => str_contains($h, 'edit.css')),
            'View must reference edit.css'
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

    public function testPageDataContainsCurrentPageId(): void
    {
        $this->assertStringContainsString("'currentPageId'", $this->viewContent);
    }

    public function testPageDataContainsCurrentAssigneeId(): void
    {
        $this->assertStringContainsString("'currentAssigneeId'", $this->viewContent);
    }

    // ── JS Module Tests ───────────────────────────────────────

    public function testJsFileExists(): void
    {
        $this->assertNotEmpty($this->jsContent, "JS file not found: {$this->jsFile}");
    }

    public function testJsUsesObjectLiteralModulePattern(): void
    {
        $this->assertMatchesRegularExpression(
            '/var\s+TicketEdit\s*=\s*\(function\s*\(\)/',
            $this->jsContent,
            'JS must use: var TicketEdit = (function() { ... })()'
        );
    }

    public function testJsReadsPageData(): void
    {
        $this->assertStringContainsString('window.PageData', $this->jsContent);
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
            'removeAttachment' => ['removeAttachment'],
        ];
    }

    public function testJsUsesStrictMode(): void
    {
        $this->assertStringContainsString("'use strict';", $this->jsContent);
    }

    // ── Segmented Control (Priority) Tests ────────────────────

    public function testJsBindsPriorityControl(): void
    {
        $this->assertStringContainsString(
            'prioritySegments',
            $this->jsContent,
            'JS must bind priority segmented control'
        );
    }

    public function testJsHandlesTicketTypeChange(): void
    {
        $this->assertStringContainsString(
            'ticketType',
            $this->jsContent,
            'JS must handle ticket type change for bug trace section'
        );
    }

    public function testJsHandlesCascadingDropdowns(): void
    {
        $this->assertStringContainsString('projectSelect', $this->jsContent);
        $this->assertStringContainsString('moduleSelect', $this->jsContent);
        $this->assertStringContainsString('pageSelect', $this->jsContent);
    }

    public function testJsHandlesFileUpload(): void
    {
        $this->assertStringContainsString('uploadAttachments', $this->jsContent);
    }

    // ── CSS Tests ─────────────────────────────────────────────

    public function testCssFileExists(): void
    {
        $this->assertNotEmpty($this->cssContent, "CSS file not found: {$this->cssFile}");
    }

    public function testCssHasFileHeader(): void
    {
        $this->assertStringContainsString('@package', $this->cssContent);
    }

    /**
     * @dataProvider cssRuleProvider
     */
    public function testCssContainsRule(string $selector): void
    {
        $this->assertStringContainsString(
            $selector,
            $this->cssContent,
            "CSS must contain rule for: {$selector}"
        );
    }

    public static function cssRuleProvider(): array
    {
        return [
            'seg-option[data-value="0"]'  => ['seg-option[data-value="0"]'],
            'seg-option[data-value="2"]'  => ['seg-option[data-value="2"]'],
            'seg-option[data-value="3"]'  => ['seg-option[data-value="3"]'],
            'btn-remove-attachment'       => ['btn-remove-attachment'],
            'dropzone-area'               => ['dropzone-area'],
        ];
    }
}
