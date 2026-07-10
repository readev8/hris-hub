# Standarisasi View: Output dan Security

## 1) Aturan Escaping
Semua output dinamis di view wajib escaped sesuai konteks:
- Konten HTML: `esc($var)`
- Attribute HTML: `esc($var, 'attr')`
- URL query/segment: gunakan helper URL dan escape output akhir

## 2) Pengecualian Escaping
Output boleh tidak di-escape hanya jika:
- Memang trusted HTML dari sistem internal.
- Ada komentar alasan di baris terdekat.
- Sudah diproteksi dari sisi sumber data.

## 3) Larangan Praktik Tidak Aman
- Jangan render langsung `$_POST`, `$_GET`, `$_REQUEST`.
- Jangan menyisipkan data user ke dalam `<script>` tanpa encoding yang tepat.
- Jangan menyisipkan data user ke attribute event handler inline (`onclick`, dll).

## 4) Aturan CSRF/UI Token
- Token yang ditampilkan di view harus melalui helper framework.
- Jangan menulis token statis/hardcoded.

## 5) Session Access di View
- Session di view hanya untuk konteks presentasi global.
- Jangan letakkan logika autentikasi/otorisasi kompleks di view.
- Akses session berulang wajib diminimalkan (cache ke variabel lokal jika perlu).

## 6) Review Security Minimum
Sebelum merge, cek:
- Semua `<?= ?>` untuk data dinamis sudah di-escape.
- Tidak ada inline JS yang menyusun string dari input user mentah.
- Tidak ada endpoint sensitif diexpose tanpa kebutuhan.

