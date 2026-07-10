# Standarisasi Struktur Controller Ninebox

## 1) Format Class
- Namespace harus mengikuti path file aktual.
- Class harus `extends BaseController`.
- Gunakan `public function` untuk method endpoint baru.

## 2) Struktur Method Render Halaman
Urutan yang dianjurkan:
1. Ambil context active menu.
2. Siapkan array data halaman.
3. Siapkan data tambahan (API/session/token).
4. Return view template.

Contoh pola:
```php
public function index()
{
    $active = $this->getActiveURL('development');
    $data = array(
        'title' => '...',
        'content' => '...',
        'js' => array('...'),
        'navbutton' => $this->permissionList(),
        'active_modul' => @$active['modul'],
        'active_menu' => @$active['menu'],
    );

    $data['data'] = $data;
    return view('template/index_development', $data);
}
```

## 3) Key Data yang Konsisten
Pada controller page, minimal:
- `title`
- `content`
- `js`
- `navbutton`
- `active_modul`
- `active_menu`

Opsional:
- `css`
- `token`
- `pegawai`
- `tahun`

## 4) Pemilihan Template Layout
- `template/index`: untuk action/detail flow biasa.
- `template/index_development`: untuk halaman dashboard/list Ninebox yang saat ini memakai layout development.
- Jangan ganti layout existing tanpa kebutuhan jelas.

## 5) Session Context
Jika halaman membutuhkan cakupan data:
- Set `coverage_ninebox` di method render, sesuai pola modul report/management.

## 6) Aturan Minimal Cleanup
- Hindari method kosong tanpa catatan.
- Hindari file controller kosong aktif.
- Komentar dibiarkan singkat dan relevan.

