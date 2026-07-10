# Standarisasi View: Kontrak Data

## 1) Data Layout (template/index.php)
Minimal data yang harus tersedia:
- `$title` (string): judul halaman
- `$content` (string): path view konten halaman
- `$js` (array): daftar JS page-level

Data opsional:
- `$page_title` (string): title browser/tab
- `$css` (array): daftar CSS page-level
- `$navbutton` (array): daftar navbutton dinamis
- `$active_modul` (string): modul aktif sidebar
- `$active_menu` (string): menu aktif sidebar

## 2) Aturan Default Value
Setiap variabel opsional wajib memakai default:
- String: `''`
- Array: `[]`
- Boolean: `false`

Tujuan:
- Hindari warning `Undefined variable`.
- Menjaga layout tetap render walau data parsial.

## 3) Aturan Data untuk main_page.php
`main_page.php` hanya menerima data yang dibutuhkan UI.
Nama variabel harus deskriptif, contoh:
- `$jobcodeInitial`
- `$jobFamilyOptions`
- `$canEdit`

Hindari:
- Variabel generik seperti `$data` dipakai berulang sampai tidak jelas isinya.

## 4) Aturan Data untuk Partial Modal
Modal wajib menerima data explicit, contoh:
- `$modalId`
- `$modalTitle`
- `$items`

Jika tidak ada data dari controller, gunakan hardcoded statis seperlunya.

## 5) Dokumentasi Contract
Setiap view utama wajib punya blok komentar singkat di bagian atas:
- data wajib
- data opsional
- default yang diasumsikan

Contoh:
```php
<?php
// Required: $title, $content, $js
// Optional: $page_title, $css, $navbutton, $active_modul, $active_menu
?>
```

