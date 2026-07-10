# Checklist Review View

Gunakan checklist ini saat review PR yang menyentuh `app/Views`.

## 1) Contract Data
- [ ] Variabel wajib layout tersedia (`title`, `content`, `js`).
- [ ] Variabel opsional memiliki fallback/default.
- [ ] Tidak ada `Undefined variable` berpotensi muncul.

## 2) Output Safety
- [ ] Semua output dinamis sudah memakai `esc()` sesuai konteks.
- [ ] Tidak ada output mentah dari request (`$_POST`/`$_GET`) di view.
- [ ] Tidak ada XSS obvious pada attribute/script inline.

## 3) Struktur File
- [ ] Nama file sesuai konvensi (`main_page`, `_modal_`, `_section_`).
- [ ] Partial ditempatkan di lokasi yang benar.
- [ ] Tidak ada file backup baru di folder aktif.

## 4) Asset
- [ ] CSS/JS page-level dipanggil via `$css`/`$js`.
- [ ] Tidak ada inline style/script yang seharusnya dipindah ke asset file.
- [ ] Tidak ada duplikasi include library yang sama.

## 5) Maintainability
- [ ] ID/class penting konsisten dengan standar.
- [ ] Struktur markup mudah dibaca.
- [ ] Komentar hanya yang relevan dan singkat.

## 6) Smoke Check Minimum
- [ ] Halaman tampil normal desktop.
- [ ] Halaman tampil normal mobile.
- [ ] Modal dapat dibuka/ditutup normal.
- [ ] JS utama halaman berjalan tanpa error console kritikal.

