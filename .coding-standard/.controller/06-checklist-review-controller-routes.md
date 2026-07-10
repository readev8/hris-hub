# Checklist Review Controller dan Routes Ninebox

Gunakan checklist ini untuk PR yang menyentuh:
- `app/Controllers/Development/Ninebox/**`
- `app/Config/Routes.php` pada group `ninebox`

## 1) Struktur Controller
- [ ] Namespace dan path file sesuai.
- [ ] Class `extends BaseController`.
- [ ] Method endpoint memakai `public function` (untuk kode baru).
- [ ] Method render memiliki key data halaman utama (`title`, `content`, `js`, `navbutton`).

## 2) Input/Output Endpoint AJAX
- [ ] Input baru tidak menggunakan `$_POST` langsung.
- [ ] Endpoint mengembalikan JSON valid.
- [ ] Format response konsisten (`success`/`statuscode` + payload).
- [ ] Tidak ada `var_dump()` aktif.
- [ ] Tidak ada `die;` aktif.

## 3) Integrasi API dan Session
- [ ] Call API memakai helper `$this->api->get_data/post_data`.
- [ ] ID sensitif mengikuti pola encrypt/decrypt yang sudah dipakai endpoint terkait.
- [ ] Session key yang dipakai konsisten (`userId`, `pegawaiId`, `coverage_ninebox`).

## 4) Routes
- [ ] Route baru ditempatkan di `group('ninebox')`.
- [ ] HTTP method sesuai tujuan (`GET` page, `POST` aksi/data).
- [ ] Filter auth/permission dipasang untuk route internal yang relevan.
- [ ] Mapping controller::method valid dan file class ada.

## 5) Hygiene
- [ ] Tidak menambah import `use` yang tidak terpakai.
- [ ] Tidak meninggalkan comment debug besar.
- [ ] Tidak menambah file kosong yang ter-route.

