# Standarisasi View: Naming dan Struktur File

## 1) Naming Wajib
- Halaman utama: `main_page.php`
- Modal: `_modal_<nama>.php`
- Section/fragment: `_section_<nama>.php`
- Table/list fragment: `_table_<nama>.php` atau `_list_<nama>.php`

Semua nama file:
- Huruf kecil.
- Gunakan underscore `_`.
- Tidak gunakan spasi atau karakter khusus.

## 2) Struktur Markup Wajib
Urutan di `main_page.php`:
1. Wrapper halaman (`container`/`row` utama).
2. Blok filter/search (jika ada).
3. Blok form/grid utama.
4. Include partial modal di bagian akhir file.

## 3) Aturan ID/Class
- ID elemen input: `input-<nama_field>`.
- ID tombol aksi: `btn-<aksi>`.
- ID container grid/list: `grid-<domain>`.
- Class utilitas tetap boleh pakai framework (bootstrap/select2/kendo).

## 4) Aturan Include View
- Gunakan `echo view('path/view')` dengan path konsisten.
- Path view tidak boleh dibentuk dari input user langsung.
- Untuk partial module, tetap berada di folder modul yang sama bila khusus fitur itu.

## 5) Aturan Komentar
- Komentar hanya untuk konteks penting.
- Hindari komentar yang menjelaskan hal trivial.
- Komentar TODO wajib menyertakan konteks aksi dan modul.

