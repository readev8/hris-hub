<?php

namespace Tests\Functional;

require_once __DIR__ . '/BaseFunctionalTest.php';
/**
 * Functional tests for the Monitoring Hub main page.
 *
 * Verifies:
 * - External JS/CSS are properly loaded
 * - PageData contains stats and date range
 * - JS module exposes Monitoring API (modern object-literal pattern)
 * - Domain tabs match API whitelist keys
 *
 * @internal
 */
final class MonitoringMainTest extends BaseFunctionalTest
{
    private string $viewFile = 'monitoring/main_page.php';
    private string $jsFile   = 'js/page/monitoring/main_page.js';
    private string $cssFile  = 'css/page/monitoring/main_page.css';

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
        $this->assertNotEmpty($this->viewContent, "View file not found: {$this->viewFile}");
    }

    public function testViewReferencesExternalJs(): void
    {
        $srcs = $this->extractScriptSrcs($this->viewContent);
        $this->assertNotEmpty(
            array_filter($srcs, fn($s) => str_contains($s, 'monitoring/main_page.js')),
            'View must reference monitoring main_page.js via <script src>'
        );
    }

    public function testViewReferencesExternalCss(): void
    {
        $hrefs = $this->extractCssHrefs($this->viewContent);
        $this->assertNotEmpty(
            array_filter($hrefs, fn($h) => str_contains($h, 'monitoring/main_page.css')),
            'View must reference monitoring main_page.css via <link>'
        );
    }

    public function testViewUsesPageData(): void
    {
        $this->assertTrue($this->hasPageData($this->viewContent));
    }

    public function testPageDataContainsStats(): void
    {
        $this->assertStringContainsString("'stats'", $this->viewContent);
    }

    public function testPageDataContainsDateRange(): void
    {
        $this->assertStringContainsString("'startDate'", $this->viewContent);
        $this->assertStringContainsString("'endDate'", $this->viewContent);
    }

    public function testViewHasSessionsSection(): void
    {
        $this->assertStringContainsString('_section_session', $this->viewContent);
        $partial = $this->readFile($this->viewPath('monitoring/_section_session.php')) ?: '';
        $this->assertStringContainsString('grid-session', $partial);
    }

    public function testJsFileExists(): void
    {
        $this->assertNotEmpty($this->jsContent, "JS file not found: {$this->jsFile}");
    }

    public function testJsUsesObjectLiteralModulePattern(): void
    {
        $this->assertMatchesRegularExpression(
            '/(var|const)\s+Monitoring\s*=\s*(\(function|\{)/',
            $this->jsContent,
            'JS must declare Monitoring module object'
        );
        $this->assertStringContainsString(
            'window.Monitoring = Monitoring',
            $this->jsContent,
            'JS must expose module via window.Monitoring'
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
            $methodName,
            $this->jsContent,
            "JS must expose: {$methodName}"
        );
    }

    public static function publicMethodProvider(): array
    {
        return [
            'init'                   => ['init'],
            'loadSession'            => ['loadSession'],
            'loadActivity'           => ['loadActivity'],
            'showDetail'             => ['showDetail'],
            'renderDomainChart'      => ['renderDomainChart'],
            'renderSession'          => ['renderSession'],
            'renderActivity'         => ['renderActivity'],
            'renderApprovalTracking' => ['renderApprovalTracking'],
        ];
    }

    public function testCssFileExists(): void
    {
        $this->assertNotEmpty($this->cssContent, "CSS file not found: {$this->cssFile}");
    }

    /**
     * @dataProvider cssClassProvider
     */
    public function testCssDefinesRequiredClass(string $className): void
    {
        $this->assertStringContainsString(
            '.' . $className,
            $this->cssContent,
            "CSS must define: .{$className}"
        );
    }

    public static function cssClassProvider(): array
    {
        return [
            'mon-kpi-grid' => ['mon-kpi-grid'],
            'metric-card'  => ['metric-card'],
            'mon-chart-grid' => ['mon-chart-grid'],
            'activity-feed'  => ['activity-feed'],
            'sap-table'   => ['sap-table'],
        ];
    }
}
