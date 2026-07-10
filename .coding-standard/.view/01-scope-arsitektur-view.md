# Standarisasi View: Scope dan Arsitektur

## 1) Scope
Dokumen ini hanya mengatur layer `View` pada CodeIgniter:
- `app/Views/template/index.php`
- `app/Views/template/partial/*.php`
- `app/Views/**/main_page.php`
- `app/Views/**/_modal_*.php`
- `app/Views/**/_section_*.php` atau fragment sejenis

Di luar scope:
- Controller, Model, Service, Repository
- Business logic API/backend
- Query database

## 2) Arsitektur Wajib
Struktur baku view:
- `template/index.php`: shell layout global (head, navbar, sidebar, content, global js)
- `template/partial/css.php`: daftar global stylesheet + page-level stylesheet
- `template/partial/navbar.php`: navbar + navbutton area
- `template/partial/sidebar.php`: menu sidebar
- `template/partial/js.php`: global script + page-level script
- `module/.../main_page.php`: konten utama halaman
- `module/.../_modal_*.php`: modal spesifik halaman

## 3) Contract Rendering
Alur render baku:
1. Controller memanggil `view('template/index', $data)`.
2. `template/index.php` memanggil `view($content)`.
3. `template/partial/js.php` memuat file dari array `$js`.
4. `template/partial/css.php` memuat file dari array `$css` (jika ada).

## 4) Larangan Scope
- View tidak boleh memuat business rule (misal kalkulasi domain utama).
- View tidak boleh melakukan akses data langsung selain data yang sudah dikirimkan.
- View tidak boleh mendefinisikan API endpoint hardcoded di banyak tempat.

## 5) Tujuan Utama
- Konsistensi antar modul.
- Memudahkan review dan onboarding.
- Mengurangi duplikasi script/style inline.
- Memperjelas batas antara UI rendering dan business logic.

