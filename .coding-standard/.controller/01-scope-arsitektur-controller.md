# Standarisasi Controller: Scope dan Arsitektur

## 1) Scope
Dokumen ini mengatur standar kode untuk:
- `app/Controllers/Development/Ninebox/**`
- Referensi routing pada `app/Config/Routes.php` khusus blok `group('ninebox')`

Dokumen ini tidak mengatur:
- View/UI
- Model/Repository
- Struktur database
- Modul selain Ninebox

## 2) Arsitektur Folder Controller Ninebox
Pola yang dipakai saat ini:
- `Valuation/Action`: aksi create/update/display/token flow
- `Valuation/Report`: halaman report/list/dashboard + endpoint data report
- `Valuation/Data`: endpoint data pendukung (dropdown, detail, lookup)
- `Management/Action|Report`: management flow dan data management
- `ReplacementTableChart/Action`: action page replacement chart

## 3) Tanggung Jawab Per Layer
- `Action`:
  - Render halaman action tertentu (`token`, `index`)
  - Proses write/update ke API (`post_data`)
- `Report`:
  - Render halaman list/dashboard
  - Endpoint AJAX untuk data report
- `Data`:
  - Endpoint AJAX master/detail data pendukung page
  - Tidak memuat render view utama

## 4) Kontrak Dasar Controller Page
Method render page (`index`/`token`) mengikuti pola:
1. Ambil active menu: `$active = $this->getActiveURL('development');`
2. Definisikan `$data` (`title`, `content`, `js`, `css?`, `navbutton`, `active_modul`, `active_menu`)
3. Set data tambahan jika perlu (token, pegawai, tahun, coverage)
4. Set `$data['data'] = $data` bila diperlukan oleh view
5. `return view('template/index' | 'template/index_development', $data);`

## 5) Prinsip As-Is + Minimal Cleanup
- Ikuti pola existing Ninebox sebagai baseline.
- Hindari refactor besar pada standar ini.
- Lakukan penertiban ringan untuk hal berisiko tinggi (debug aktif, response tidak konsisten, input mentah).

