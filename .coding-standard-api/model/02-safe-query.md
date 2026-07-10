# Standar Query Aman (Anti-SQL Injection)

Keamanan data adalah prioritas utama. SQL Injection harus dicegah di level Model.

## 1. Larangan String Concatenation
**Dilarang Keras** menggabungkan variabel input langsung ke dalam string query SQL.

```php
// BURUK (Sangat Berbahaya)
$sql = "SELECT * FROM pegawai WHERE id = " . $id;

// BAIK (Gunakan Binding)
$sql = "SELECT * FROM pegawai WHERE id = ?";
$this->db->query($sql, [$id]);
```

## 2. Penggunaan Query Builder
Sangat direkomendasikan menggunakan Query Builder CI4 karena sudah melakukan escaping secara otomatis.

```php
$this->db->table('pegawai')
         ->where('id', $id)
         ->get();
```

## 3. Parameter Binding
Jika harus menggunakan query manual (`$this->db->query()`), wajib menggunakan Named Bindings atau Question Mark Bindings.

```php
$sql = "UPDATE cuti SET status = :status: WHERE id = :id:";
$this->db->query($sql, [
    'status' => 'Approved',
    'id'     => $id
]);
```

## 4. Validasi Tipe Data di Query
Selalu pastikan variabel yang masuk ke query sudah sesuai tipenya (integer untuk ID, string untuk nama).

## 5. IN Clause Dinamis (Wajib Gunakan Query Builder)
Untuk query dengan `WHERE id IN (...)` yang menerima daftar ID dinamis, **dilarang** menggabungkan string langsung. Gunakan `whereIn()` dari Query Builder.

```php
// SALAH — String concatenation pada IN clause (SQL Injection!)
function checkJurusanId($id, &$error = null)
{
    $total = sizeof(explode(',', $id));
    $query = $this->db->query("
        SELECT count(*) as total
        FROM hr_selfservice.jurusan WHERE id IN (" . $id . ")
    ")->getResultArray();
}

// BENAR — Gunakan Query Builder whereIn()
function checkJurusanId($id, &$error = null)
{
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

## 6. Dynamic Filter / Sort / Pagination (setGridQuery Pattern)
Untuk fitur grid/table yang menerima filter, sort, dan pagination dari client, **dilarang** melakukan string concatenation pada field name, operator, value, sort direction, LIMIT, dan OFFSET.

### A. Whitelist Field Names
Daftar field yang boleh digunakan dalam filter/sort harus didefinisikan secara eksplisit (whitelist).

```php
// SALAH — Field, value, operator, LIMIT, OFFSET langsung dari input user
$query .= $filter['logic'] . " " . $value['field'] . " LIKE '" . $value['value'] . "%' ";
$query .= " ORDER BY " . $sort[0]['field'] . " " . $sort[0]['dir'];
$query .= " LIMIT " . $param['take'];
$query .= " OFFSET " . $param['skip'];

// BENAR — Gunakan Query Builder untuk setiap bagian
$allowedFields = ['nip', 'nama', 'perusahaan', 'departemen', 'jabatan'];
$allowedDirections = ['ASC', 'DESC'];
$allowedOperators = [
    'startswith'   => 'LIKE',
    'contains'     => 'LIKE',
    'endswith'     => 'LIKE',
    'eq'           => '=',
    'neq'          => '!=',
];

$builder = $this->db->table("({$baseQuery}) AS report");

// Filter dengan whitelist
if (!empty($param['filter'])) {
    $filters = json_decode($param['filter'], true);
    if (!empty($filters['filters'])) {
        foreach ($filters['filters'] as $f) {
            $f = (array) $f;
            if (!in_array($f['field'], $allowedFields)) continue;
            // ... apply filter menggunakan Query Builder
        }
    }
}

// Sort dengan whitelist
if (!empty($param['sort'])) {
    $sort = json_decode($param['sort'], true);
    if (in_array($sort[0]['field'], $allowedFields) && in_array(strtoupper($sort[0]['dir']), $allowedDirections)) {
        $builder->orderBy($sort[0]['field'], $sort[0]['dir']);
    }
}

// Pagination — gunakan integer casting
$take = (int) ($param['take'] ?? 20);
$skip = (int) ($param['skip'] ?? 0);
$builder->limit($take, $skip);
```

### B. Operator Mapping
Petakan operator dari client ke SQL yang aman:

| Client Operator | SQL | Implementasi Query Builder |
| :--- | :--- | :--- |
| `startswith` | `LIKE 'val%'` | `$builder->like('field', $value, 'after')` |
| `contains` | `LIKE '%val%'` | `$builder->like('field', $value)` |
| `endswith` | `LIKE '%val'` | `$builder->like('field', $value, 'before')` |
| `eq` | `= val` | `$builder->where('field', $value)` |
| `neq` | `!= val` | `$builder->where('field !=', $value)` |

## 7. Contoh Pelanggaran yang Sering Ditemukan (JANGAN DITIRU)

### Pelanggaran A: Variable langsung di query tanpa binding
```php
// SALAH — Ditemukan di AdmLeaveAct_model::isFullApprove()
$q = "select ... from ...
      where pengajuanijinid = " . $pengajuanijinid . " group by ...";
$result = $this->db->query($q)->getResultArray();

// BENAR
$q = "select ... from ... where pengajuanijinid = ? group by ...";
$result = $this->db->query($q, [$pengajuanijinid])->getResultArray();
```

### Pelanggaran B: Privilege/authorization filter langsung concat
```php
// SALAH — Ditemukan di Dashboard_model::getValuation()
$filterAuth .= " and p.perusahaanid in ( " . $dashboardAuth["perusahaanid"] . " ) ";
$filterDivisiid = " and p.DivisiID in (" . $divisiid . " )";
$qAccess = "... WHERE pb.tahun = $tahun and pb.bulan = $bulan ...";

// BENAR — Gunakan parameter binding atau Query Builder
$perusahaanIds = explode(',', $dashboardAuth["perusahaanid"]);
// Opsi 1: Query Builder
$builder->whereIn('p.perusahaanid', $perusahaanIds);
// Opsi 2: Named binding untuk raw query
$placeholders = implode(',', array_fill(0, count($perusahaanIds), '?'));
$q = "... WHERE p.perusahaanid IN ({$placeholders}) AND pb.tahun = ? ...";
$this->db->query($q, array_merge($perusahaanIds, [$tahun, $bulan]));
```

### Pelanggaran C: Pagination tanpa integer casting
```php
// SALAH
$query .= " LIMIT " . $param['take'];
$query .= " OFFSET " . $param['skip'];

// BENAR
$take = (int) ($param['take'] ?? 20);
$skip = (int) ($param['skip'] ?? 0);
$builder->limit($take, $skip);
```
