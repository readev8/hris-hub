# Standar Manajemen Transaksi Database

Untuk menjaga integritas data (ACID), setiap operasi yang melibatkan lebih dari satu tabel atau lebih dari satu proses `write` wajib menggunakan fitur Transaction.

## 1. Penggunaan `transStart()` & `transComplete()`
Gunakan blok transaksi standar CI4. Fitur ini secara otomatis akan melakukan `rollback` jika ada query yang gagal atau exception yang dilempar.

```php
public function submitApplication($data) {
    $this->db->transStart();

    $this->db->table('hr_application')->insert($data['main']);
    $this->db->table('hr_log')->insert($data['log']);

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        // Logika jika gagal
        return false;
    }
    return true;
}
```

## 2. Granularitas Transaksi
Jangan membungkus terlalu banyak logic (seperti pemanggilan API eksternal) di dalam blok transaksi database. Transaksi hanya boleh berisi operasi database.

## 3. Strict Mode
Selalu gunakan `transStart(true)` jika ingin menjalankan transaksi dalam *Strict Mode*, di mana jika satu query gagal, semua query sebelumnya akan di-rollback meskipun tidak ada error fatal.

## 4. Standarisasi: Hanya Gunakan `transStart()` / `transComplete()`
**Dilarang** mencampur pola transaksi yang berbeda dalam satu project. Standar yang wajib digunakan adalah `transStart()` / `transComplete()`.

```php
// SALAH — Pola transBegin/transCommit/transRollback (TIDAK BOLEH DIPAKAI)
public function createLeaveApproval($id) {
    try {
        $this->db->transBegin();
        $this->db->table('pengajuan_ijin_approve')->update($data, [...]);
        $this->db->table('pengajuan_ijin')->update($statusData, [...]);
        $this->db->transCommit();
        return ['success' => true];
    } catch (\Throwable $th) {
        $this->db->transRollback();
        return ['success' => false];
    }
}

// SALAH — transStart tapi manual rollback (inkonsisten)
public function createDelegation($data) {
    try {
        $this->db->transStart();
        $this->db->table('delegasi')->insert($data);
        if ($this->db->affectedRows() > 0) {
            $this->db->transCommit();   // CAMPUR ADUK - tidak konsisten
        } else {
            $this->db->transRollback(); // CAMPUR ADUK - tidak konsisten
        }
    } catch (\Throwable $th) {
        $this->db->transRollback();
    }
}

// BENAR — Pola transStart/transComplete yang konsisten
public function createLeaveApproval($id) {
    $this->db->transStart();

    $this->db->table('pengajuan_ijin_approve')->update($data, [...]);
    $this->db->table('pengajuan_ijin')->update($statusData, [...]);

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        log_message('error', "Gagal menyimpan approval untuk pengajuan ID: {$id}");
        return ['success' => false, 'message' => 'Data gagal tersimpan'];
    }

    return ['success' => true, 'message' => 'Data berhasil tersimpan'];
}
```

**Alasan standarisasi:**
- `transStart()/transComplete()` otomatis menangani rollback pada exception.
- `transBegin()/transCommit()/transRollback()` memerlukan manual rollback di catch block — rawan kelupaan.
- Konsistensi memudahkan code review dan debugging.

## 5. Return Value Konsisten dalam Transaction
Semua method Act_model yang menggunakan transaksi **wajib** mengembalikan array dengan format:

```php
[
    'success' => bool,    // true jika berhasil, false jika gagal
    'message' => string,  // Pesan deskriptif
]
```

Dilarang return `void`, `int`, atau format yang berbeda-beda.

```php
// SALAH — Return format tidak konsisten
// Method A return bool
// Method B return array dengan key 'status'
// Method C return array dengan key 'success'
// Method D return string

// BENAR — Return format konsisten
return ['success' => true, 'message' => 'Data berhasil tersimpan'];
return ['success' => false, 'message' => 'Data gagal tersimpan'];
```
