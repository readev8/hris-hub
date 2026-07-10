# Standarisasi Routes Ninebox (Berdasarkan Routes.php)

## 1) Scope Routes
Rujukan utama:
- `app/Config/Routes.php`
- Blok: `$routes->group('ninebox', static function ($routes) { ... })`

## 2) Struktur Group
Pola existing:
- Parent group: `ninebox`
- Nested group dengan filter auth:
  - `$routes->group('', ['filter' => 'auth'], static function ($routes) { ... })`

Standar:
- Route baru Ninebox wajib berada di group `ninebox`.
- Untuk page internal, gunakan filter auth.

## 3) Konvensi Naming Existing
Ditemukan dua gaya:
- `ajax-kebab-case` (contoh: `ajax-get-ninebox`)
- `ajax_snake_case` (contoh: `ajax_create_ninebox_valuation`)

Standar As-Is:
- Route lama dibiarkan.
- Route baru direkomendasikan konsisten ke `ajax-kebab-case`.

## 4) Konvensi HTTP Method
- `GET` untuk page render/read tertentu.
- `POST` untuk create/update/fetch data terfilter.

## 5) Filter Permission
Pola existing:
- Variabel permission map:
  - `$main_valuation`, `$main_valuation_create`, dll.
- Route page kritikal memakai filter:
  - `['filter' => 'permission:default,' . $main_xxx]`

Standar:
- Pertahankan pola map variable untuk route menu utama.
- Endpoint action sensitif disarankan pakai filter permission sesuai menu.

## 6) Mapping Route ke Controller
Format baku mapping:
- `'route-path', 'Development\Ninebox\...\Class::method'`

Aturan:
- Namespace route harus match path class aktual.
- Hindari route ke class/file kosong.

