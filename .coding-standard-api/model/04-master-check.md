# Standar Validasi Master Data Terpusat

Untuk menghindari redundansi query dan memastikan validasi yang konsisten, semua pengecekan referensi data (Foreign Key/Master Data) wajib dipusatkan.

## 1. Penggunaan `MasterDataCheck_model`
Setiap kali Controller atau Model lain butuh memastikan apakah suatu `PegawaiID`, `JabatanID`, atau `UnitID` itu ada dan aktif, wajib memanggil `MasterDataCheck_model`.

## 2. Struktur Method Validasi
Method validasi harus mengembalikan `boolean` dan menerima referensi variabel `$error` untuk menyimpan pesan kesalahan.

```php
// Contoh di MasterDataCheck_model
public function checkPegawaiId($id, &$error = null): bool {
    $data = $this->db->table('pegawai')
                     ->where('id', $id)
                     ->where('status', 'Aktif')
                     ->get()
                     ->getRow();
    
    if (!$data) {
        $error = "Pegawai tidak ditemukan atau sudah tidak aktif.";
        return false;
    }
    return true;
}
```

## 3. Manfaat Terpusat
- Perubahan logic (misal: penambahan filter `is_deleted = 0`) cukup dilakukan di satu tempat.
- Mengurangi beban database karena query yang identik bisa di-cache.

## 4. Keamanan pada Method `MasterDataCheck_model`
Karena `MasterDataCheck_model` adalah gerbang validasi pertama, **keamanannya harus lebih ketat** dari model lain.

### A. Dilarang String Concatenation di Method Validasi
Walaupun method ini hanya melakukan `SELECT`, input yang diterima tetap berpotensi mengandung serangan SQL Injection.

```php
// SALAH — IN clause langsung concat (ditemukan di checkJurusanId & checkCompetencyStages)
function checkJurusanId($id, &$error = null) {
    $total = sizeof(explode(',', $id));
    $query = $this->db->query("
        SELECT count(*) as total
        FROM hr_selfservice.jurusan WHERE id IN (" . $id . ")
    ")->getResultArray();
}

// BENAR — Gunakan Query Builder whereIn()
function checkJurusanId($id, &$error = null) {
    $ids = explode(',', $id);
    $total = count($ids);
    if ($total === 0) return true;

    $result = $this->db->table('hr_selfservice.jurusan')
        ->selectCount('id', 'total')
        ->whereIn('id', $ids)
        ->get()
        ->getRow()
        ->total;

    if ($total == $result) return true;
    $error = 'Jurusan ID tidak valid atau tidak ditemukan';
    return false;
}
```

### B. Validasi Input Sebelum Query
Setiap method di `MasterDataCheck_model` wajib memvalidasi tipe data input sebelum menjalankan query:

```php
// BENAR — Validasi input terlebih dahulu
function checkPegawaiId($id, &$error = null): bool {
    // Validasi bahwa input adalah angka
    if (!is_numeric($id) || $id <= 0) {
        $error = 'Pegawai ID harus berupa angka positif.';
        return false;
    }

    $result = $this->db->table('wine_hris.vPegawai_Pivot')
        ->selectCount('id', 'total')
        ->where('id', $id)
        ->where('statuskeluar', 0)
        ->get()
        ->getRow()
        ->total;

    if ($result > 0) return true;
    $error = 'Pegawai ID tidak valid atau tidak ditemukan';
    return false;
}
```

### C. Gunakan Query Builder secara Konsisten
Semua method di `MasterDataCheck_model` **wajib** menggunakan Query Builder CI4 (`$this->db->table()`) sebagai ganti raw query. Raw query hanya diperbolehkan untuk kasus yang sangat kompleks dan tetap wajib menggunakan parameter binding.
