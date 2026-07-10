# Standarisasi View: Asset CSS/JS

## 1) Prinsip Umum
- Asset global ditempatkan di `template/partial/css.php` dan `template/partial/js.php`.
- Asset page-level dimuat melalui array `$css` dan `$js`.
- Gunakan helper (`asset_url`, `link_tag`, `script_tag`) untuk konsistensi path/versioning.

## 2) Aturan CSS
- Dilarang menambahkan `<style>` inline pada `main_page.php` dan modal, kecuali sangat kecil dan sementara.
- CSS page-level harus dipindah ke:
  `public/assets/css/page/<module>/<feature>.css`
- Penamaan file CSS mengikuti path modul.

## 3) Aturan JS
- Dilarang menaruh script bisnis halaman inline di `main_page.php`.
- JS page-level harus dipindah ke:
  `public/assets/js/page/<module>/<feature>.js`
- Inline script di partial global hanya untuk bootstrap ringan aplikasi.

## 4) Urutan Load
Urutan rekomendasi:
1. Library mandatory (contoh jQuery, bootstrap).
2. Plugin global.
3. Utility global.
4. Page-level JS dari `$js`.

## 5) Dependency
- Jangan load plugin yang tidak dipakai halaman.
- Hindari duplikasi plugin versi berbeda pada halaman yang sama.

## 6) Legacy Cleanup Rule
- File backup (`.bak`, `.backup-*`, `.old`, checkpoint) tidak boleh jadi referensi aktif.
- File backup dipindah ke lokasi arsip atau dihapus setelah validasi tim.

