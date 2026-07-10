# Backlog Minimal Cleanup Ninebox (Temuan dari Referensi)

Dokumen ini bukan instruksi refactor besar, hanya backlog cleanup ringan berdasarkan kondisi aktual.

## Prioritas 1 (Kritis)
1. Hapus debug aktif pada endpoint produksi:
   - `Management/Report/Manage::ajax_update_stages` (ada `var_dump` + `die`)
   - `Valuation/Data/Data::ajax_get_assessor_by_token` (ada `var_dump`)

2. Pastikan semua endpoint tetap return JSON valid setelah debug dihapus.

## Prioritas 2 (Stabilitas)
1. Konsistenkan input request baru ke `$this->request->getPost(...)`.
2. Tambahkan validasi field wajib untuk endpoint write/update yang belum punya guard.
3. Rapikan response error minimal (`statuscode/message`) di endpoint yang belum konsisten.

## Prioritas 3 (Code Hygiene)
1. Bersihkan import `use` yang tidak dipakai pada file controller.
2. Tinjau file kosong:
   - `Valuation/Report/DasboardSimple.php` (0 baris)
3. Review komentar legacy yang tidak relevan lagi.

## Prioritas 4 (Routes Konsistensi)
1. Untuk route baru, pilih satu gaya naming AJAX:
   - direkomendasikan `ajax-kebab-case`
2. Route lama tidak perlu diubah jika berisiko breaking.
3. Dokumentasikan alias route jika suatu saat dilakukan penyeragaman nama.

## Catatan Scope
- Cleanup dilakukan bertahap.
- Tidak mengubah kontrak API eksternal tanpa analisa dampak.
- Fokus awal pada endpoint Ninebox yang aktif dipakai.

