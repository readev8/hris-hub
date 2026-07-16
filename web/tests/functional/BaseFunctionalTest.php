<?php

namespace Tests\Functional;

use CodeIgniter\Test\CIUnitTestCase;

abstract class BaseFunctionalTest extends CIUnitTestCase
{
    protected string $webRoot    = '';
    protected string $viewsPath  = '';
    protected string $publicPath = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->webRoot    = realpath(__DIR__ . '/../../') ?: __DIR__ . '/../../';
        $this->viewsPath  = $this->webRoot . '/app/Views';
        $this->publicPath = $this->webRoot . '/public';
    }

    protected function viewPath(string $relativePath): string
    {
        return $this->viewsPath . '/' . ltrim($relativePath, '/');
    }

    protected function assetPath(string $relativePath): string
    {
        return $this->publicPath . '/assets/' . ltrim($relativePath, '/');
    }

    protected function readFile(string $path): string|false
    {
        if (!is_file($path)) {
            return false;
        }
        return file_get_contents($path);
    }

    protected function assertFileExistsAndNotEmpty(string $path, string $message = ''): void
    {
        $this->assertFileExists($path, $message ?: "File not found: {$path}");
        $content = file_get_contents($path);
        $this->assertNotEmpty($content, $message ?: "File is empty: {$path}");
    }

    protected function extractScriptSrcs(string $viewContent): array
    {
        $results = [];
        // Match base_url('public/assets/js/...') or site_url('assets/js/...')
        $pattern = '/(?:base_url|site_url)\(\s*[\x27"]([^\'"]*assets\/js\/[^\'"]*)[\x27"]/i';
        if (preg_match_all($pattern, $viewContent, $m)) {
            $results = $m[1];
        }
        return array_unique($results);
    }

    protected function extractCssHrefs(string $viewContent): array
    {
        $results = [];
        // Match base_url('public/assets/css/...') or site_url('assets/css/...')
        $pattern = '/(?:base_url|site_url)\(\s*[\x27"]([^\'"]*assets\/css\/[^\'"]*)[\x27"]/i';
        if (preg_match_all($pattern, $viewContent, $m)) {
            $results = $m[1];
        }
        return array_unique($results);
    }

    protected function hasPageData(string $viewContent): bool
    {
        return (bool) preg_match('/window\.PageData\s*=\s*</', $viewContent);
    }

    protected function hasInlineBusinessLogic(string $viewContent): bool
    {
        $cleaned = preg_replace('/<script>window\.PageData.*?<\/script>/s', '', $viewContent);
        // Remove external script references (handles both static and PHP dynamic src)
        $cleaned = preg_replace('/<script\s+src=["\x27].*?<\/script>/i', '', $cleaned);
        $cleaned = preg_replace('/<\?php.*?\?>/s', '', $cleaned);

        $scriptBlocks = [];
        if (preg_match_all('/<script>(.+?)<\/script>/s', $cleaned, $matches)) {
            $scriptBlocks = $matches[1];
        }
        $scriptBlocks = array_filter(array_map('trim', $scriptBlocks));

        return count($scriptBlocks) > 0;
    }

    protected function extractPageDataJson(string $viewContent): ?string
    {
        if (preg_match('/window\.PageData\s*=\s*(\<\?=.+?\?\>)\s*;/s', $viewContent, $matches)) {
            $phpContent = $matches[1];
            if (preg_match('/json_encode\((\[.+?\])\)/s', $phpContent, $jsonMatch)) {
                return $jsonMatch[1];
            }
        }
        return null;
    }

    protected function extractInlineFunctionNames(string $viewContent): array
    {
        $cleaned = preg_replace('/<script>window\.PageData.*?<\/script>/s', '', $viewContent);
        $cleaned = preg_replace('/<script\s+src=["\x27][^"\x27]+["\x27}><\/script>/i', '', $cleaned);

        $functions = [];
        if (preg_match_all('/function\s+(\w+)\s*\(/', $cleaned, $matches)) {
            $functions = $matches[1];
        }
        return $functions;
    }

    protected function extractOnclickMethods(string $viewContent): array
    {
        $methods = [];
        if (preg_match_all('/onclick=["\x27][A-Z]\w+\.(\w+)\(/', $viewContent, $matches)) {
            $methods = $matches[1];
        }
        return $methods;
    }
}
