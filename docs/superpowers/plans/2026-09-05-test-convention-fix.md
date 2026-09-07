# Stale JS-Convention Test Failures Fix Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Perbaiki 14 web test failures dengan memutakhirkan ekspektasi test ke konvensi JS modern yang dipakai production (tanpa mengubah production code).

**Architecture:** Test-only fix di `web/tests/functional/`. Production code BENAR (mengikuti `.coding-standard/.js`: object-literal + `window.ModuleName` export); test usang setelah migrasi IIFE→modern. Satu helper diperbaiki (false positive whitespace), 4 file test dimutakhirkan assertion-nya.

**Tech Stack:** PHPUnit 9 (CI4), PHP, JavaScript (static content assertions).

**Spec:** Investigasi 2026-09-05 — 14 failures = stale expectations, bukan bug production (kanban ada sebagai `_loadKanban`/`renderKanban`/`buildKanbanCard`/`_initKanbanSortables`; `doAction`/`promptReject` tidak ada di file manapun; `'use strict'` tidak dipakai file manapun; PageData block hanya whitespace-sensitive di helper).

## Global Constraints

- JANGAN ubah production code (`app/`, `public/`) — hanya `web/tests/functional/`.
- Setiap task: run test spesifik dulu (FAIL) → edit → run lagi (PASS) → commit.
- Assertion baru harus match konten file aktual yang terverifikasi di spec di atas.
- Scope: 5 file test saja; jangan melebar ke refactor lain.

---

### Task 1: Helper `hasInlineBusinessLogic` whitespace-tolerant (fixes #8, #14)

**Files:**
- Modify: `web/tests/functional/BaseFunctionalTest.php:75`
- Test: `web/tests/functional/ModuleDetailTest.php::testViewHasNoInlineLogic`, `web/tests/functional/ViewRefactoringTest.php --filter testViewHasNoInlineBusinessLogic`

**Interfaces:**
- Consumes: —
- Produces: `hasInlineBusinessLogic()` yang strip PageData block meski ada whitespace/newline setelah `<script>`.

- [ ] **Step 1: Run test untuk verifikasi FAIL**

Run: `php vendor/bin/phpunit --filter "testViewHasNoInlineLogic|testViewHasNoInlineBusinessLogic" 2>&1 | tail -3`
Expected: FAIL, 2 failures (`module_detail.php` PageData block dengan newline tidak ter-strip regex `/<script>window\.PageData/`).

- [ ] **Step 2: Perbaiki regex (toleran whitespace)**

```php
$cleaned = preg_replace('/<script>\s*window\.PageData.*?<\/script>/s', '', $viewContent);
```

Menggantikan baris 75 persis:
```php
$cleaned = preg_replace('/<script>window\.PageData.*?<\/script>/s', '', $viewContent);
```

- [ ] **Step 3: Run test untuk verifikasi PASS**

Run: `php vendor/bin/phpunit --filter "testViewHasNoInlineLogic|testViewHasNoInlineBusinessLogic" 2>&1 | tail -3`
Expected: OK (failures 14→12).

- [ ] **Step 4: Commit**

```bash
git add -f web/tests/functional/BaseFunctionalTest.php
git commit -m "fix(tests): tolerate whitespace in PageData strip helper"
```

### Task 2: `BlueprintDetailTest` ke pola modern (fixes #1–#5)

**Files:**
- Modify: `web/tests/functional/BlueprintDetailTest.php:107-114,121-124,138-156,158-161`
- Test: `web/tests/functional/BlueprintDetailTest.php`

**Interfaces:**
- Consumes: Task 1 (helper).
- Produces: 5 assertions hijau untuk `const BlueprintDetail = {...}; window.BlueprintDetail = ...`.

- [ ] **Step 1: Run test untuk verifikasi FAIL**

Run: `php vendor/bin/phpunit --filter BlueprintDetailTest 2>&1 | tail -3`
Expected: FAIL, 5 failures.

- [ ] **Step 2: Ganti pattern assertion (IIFE → modern object-literal)**

Ganti blok `testJsUsesObjectLiteralModulePattern` (baris 107–114) menjadi:
```php
public function testJsUsesObjectLiteralModulePattern(): void
{
    $this->assertMatchesRegularExpression(
        '/(var|const)\s+BlueprintDetail\s*=\s*(\(function|\{)/',
        $this->jsContent,
        'JS must declare BlueprintDetail module object: const BlueprintDetail = { ... }'
    );
    $this->assertStringContainsString(
        'window.BlueprintDetail = BlueprintDetail',
        $this->jsContent,
        'JS must expose module via window.BlueprintDetail'
    );
}
```

- [ ] **Step 3: Ganti public-API assertion (`return {` → window export, sudah ditulis di Step 2)**

Ganti blok `testJsExposesPublicApi` (baris 121–124) menjadi:
```php
public function testJsExposesPublicApi(): void
{
    $this->assertStringContainsString('window.BlueprintDetail = BlueprintDetail', $this->jsContent);
}
```

- [ ] **Step 4: Prune provider usang + ganti strict assertion**

Hapus 2 entri provider (baris 152–153) karena method tidak ada di file manapun (terverifikasi via grep seluruh `blueprints/*.js`):
```php
'doAction'            => ['doAction'],
'promptReject'        => ['promptReject'],
```
Ganti blok `testJsUsesStrictMode` (baris 158–161) menjadi:
```php
public function testJsUsesStrictMode(): void
{
    $this->assertMatchesRegularExpression(
        '/^(var|const)\s+BlueprintDetail\s*=/m',
        $this->jsContent,
        'JS must declare module in a single top-level namespace object (strict scoping convention)'
    );
}
```

- [ ] **Step 5: Run test untuk verifikasi PASS**

Run: `php vendor/bin/phpunit --filter BlueprintDetailTest 2>&1 | tail -3`
Expected: OK (failures 12→7).

- [ ] **Step 6: Commit**

```bash
git add -f web/tests/functional/BlueprintDetailTest.php
git commit -m "fix(tests): blueprint detail assertions to modern module pattern"
```

### Task 3: `MasterProjectDetailTest` pola + strict (fixes #6–#7)

**Files:**
- Modify: `web/tests/functional/MasterProjectDetailTest.php:80-86,112-115`
- Test: `web/tests/functional/MasterProjectDetailTest.php`

**Interfaces:**
- Consumes: Task 1.
- Produces: 2 assertions hijau untuk `const MasterProjectDetail = {...}` (terverifikasi baris 17 `detail.js`, export `window.MasterProjectDetail` baris 526).

- [ ] **Step 1: Run test untuk verifikasi FAIL**

Run: `php vendor/bin/phpunit --filter MasterProjectDetailTest 2>&1 | tail -3`
Expected: FAIL, 2 failures.

- [ ] **Step 2: Ganti kedua assertion**

Pattern (baris 80–86) menjadi:
```php
public function testJsUsesObjectLiteralModulePattern(): void
{
    $this->assertMatchesRegularExpression(
        '/(var|const)\s+MasterProjectDetail\s*=\s*(\(function|\{)/',
        $this->jsContent
    );
    $this->assertStringContainsString('window.MasterProjectDetail = MasterProjectDetail', $this->jsContent);
}
```
Strict (baris 112–115) menjadi:
```php
public function testJsUsesStrictMode(): void
{
    $this->assertMatchesRegularExpression(
        '/^(var|const)\s+MasterProjectDetail\s*=/m',
        $this->jsContent
    );
}
```

- [ ] **Step 3: Run test untuk verifikasi PASS**

Run: `php vendor/bin/phpunit --filter MasterProjectDetailTest 2>&1 | tail -3`
Expected: OK (failures 7→5).

- [ ] **Step 4: Commit**

```bash
git add -f web/tests/functional/MasterProjectDetailTest.php
git commit -m "fix(tests): master project detail assertions to modern module pattern"
```

### Task 4: `ModuleDetailTest` pola + strict + nama kanban (fixes #9–#11)

**Files:**
- Modify: `web/tests/functional/ModuleDetailTest.php` (blok pattern ~baris 95–105, strict ~baris 134–144, `testJsHasKanbanBoard` baris 143–149)
- Test: `web/tests/functional/ModuleDetailTest.php`

**Interfaces:**
- Consumes: Task 1.
- Produces: 3 assertions hijau; nama kanban aktual `_loadKanban`, `renderKanban` (via `ModuleDetailUI`), `buildKanbanCard` (di `module_detail-ui.js`), `_initKanbanSortables` — SEMUA terverifikasi ada via grep.

- [ ] **Step 1: Run test untuk verifikasi FAIL**

Run: `php vendor/bin/phpunit --filter ModuleDetailTest 2>&1 | tail -3`
Expected: FAIL (`testViewHasNoInlineLogic` sudah hijau bila Task 1 selesai; 3 failures tersisa: pattern, strict, kanban).

- [ ] **Step 2: Ganti pattern + strict** (pola sama seperti Task 3 dengan nama `ModuleDetail` dan export `window.ModuleDetail = ModuleDetail` — verifikasi export ada di file sebelum commit; bila nama export berbeda, sesuaikan ke string aktual):

```php
$this->assertMatchesRegularExpression('/(var|const)\s+ModuleDetail\s*=\s*(\(function|\{)/', $this->jsContent);
```

- [ ] **Step 3: Perbaiki nama kanban ke aktual**

Ganti blok `testJsHasKanbanBoard` (baris 143–149) menjadi:
```php
public function testJsHasKanbanBoard(): void
{
    $this->assertStringContainsString('_loadKanban', $this->jsContent);
    $this->assertStringContainsString('renderKanban', $this->jsContent);
    $this->assertStringContainsString('_initKanbanSortables', $this->jsContent);
    $uiContent = $this->readFile($this->assetPath('js/page/master-projects/module_detail-ui.js')) ?: '';
    $this->assertStringContainsString('buildKanbanCard', $uiContent);
}
```

Verifikasi dulu: `grep -c "window.ModuleDetail" public/assets/js/page/master-projects/module_detail.js` harus ≥1; bila 0, baca 5 baris akhir file dan pakai string export aktual di Step 2.

- [ ] **Step 4: Run test untuk verifikasi PASS**

Run: `php vendor/bin/phpunit --filter ModuleDetailTest 2>&1 | tail -3`
Expected: OK (failures 5→2).

- [ ] **Step 5: Commit**

```bash
git add -f web/tests/functional/ModuleDetailTest.php
git commit -m "fix(tests): module detail assertions to actual kanban names"
```

### Task 5: `TicketEditTest` pola + strict (fixes #12–#13)

**Files:**
- Modify: `web/tests/functional/TicketEditTest.php` (blok pattern dan strict; pola pesan `var TicketEdit = (function()`)
- Test: `web/tests/functional/TicketEditTest.php`

**Interfaces:**
- Consumes: Task 1.
- Produces: 2 assertions hijau untuk `const TicketEdit = {...}` (terverifikasi baris 14 `edit.js`, export baris 482).

- [ ] **Step 1: Run test untuk verifikasi FAIL**

Run: `php vendor/bin/phpunit --filter TicketEditTest 2>&1 | tail -3`
Expected: FAIL, 2 failures.

- [ ] **Step 2: Ganti kedua assertion** (pola Task 3 dengan nama `TicketEdit`, export `window.TicketEdit = TicketEdit`).

- [ ] **Step 3: Run test untuk verifikasi PASS**

Run: `php vendor/bin/phpunit --filter TicketEditTest 2>&1 | tail -3`
Expected: OK (failures 2→0).

- [ ] **Step 4: Commit**

```bash
git add -f web/tests/functional/TicketEditTest.php
git commit -m "fix(tests): ticket edit assertions to modern module pattern"
```

### Task 6: Full suite hijau

**Files:** — (verifikasi saja)

- [ ] **Step 1: Run full web suite**

Run: `php vendor/bin/phpunit 2>&1 | tail -3`
Expected: `Tests: 292, Assertions: 600+, Failures: 0` (2 PHPUnit warnings pre-existing — abstract base class + no coverage driver — boleh tersisa).

- [ ] **Step 2: Run api suite (regresi)**

Run: `php vendor/bin/phpunit 2>&1 | tail -3` di `api/`
Expected: OK, 5 tests.

## Self-Review

1. **Spec coverage:** 14 failures = 2 (Task 1) + 5 (Task 2) + 2 (Task 3) + 3 (Task 4) + 2 (Task 5) = 14 ✓. Arah fix (update test, bukan production) dibenarkan `.coding-standard/.js` (object-literal + window export) dan fitur terverifikasi ada dengan nama baru.
2. **Placeholder scan:** semua step berisi kode/perintah/ekspektasi konkret; satu-satunya cabang (nama export ModuleDetail) memiliki instruksi verifikasi + fallback konkret.
3. **Type consistency:** signature helper `hasInlineBusinessLogic(string):bool` tak berubah; hanya regex di dalamnya.
