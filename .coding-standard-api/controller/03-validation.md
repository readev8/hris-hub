# Standar Validasi Data & Rules

Dokumen ini mengatur cara Controller memvalidasi integritas data sebelum dieksekusi oleh logic bisnis atau disimpan ke database.

## 1. Lokasi Logic Validasi
Logic validasi wajib diletakkan di dalam Controller (sebagai filter pertama) sebelum memanggil Model.
- Gunakan method `rules()` dalam class yang sama atau panggil *Validation Service*.

## 2. Struktur Method `rules()`
Method `rules()` harus mengembalikan array yang berisi aturan validasi CodeIgniter 4.

**Contoh Standar:**
```php
public function rules() {
    return [
        'input-pegawaiid' => [
            'rules'  => 'required|numeric',
            'errors' => [
                'required' => 'Pegawai wajib dipilih.',
                'numeric'  => 'Format Pegawai ID tidak valid.'
            ]
        ],
        'input-email' => [
            'rules'  => 'required|valid_email',
            'errors' => [
                'valid_email' => 'Format alamat email salah.'
            ]
        ]
    ];
}
```

## 3. Custom Database Validation
Untuk pengecekan keberadaan data di database (misal: cek `pegawaiid` aktif), gunakan *anonymous function* (Closure).

**Ketentuan:**
- Logic query database **Dilarang** ditulis di dalam Closure.
- Panggil method dari `MasterDataCheck_model` atau model pengecek lainnya.

**Contoh:**
```php
'input-pegawaiid' => [
    'rules' => [
        'required',
        function ($str, $data, &$error) {
            // Panggil model pengecek
            return $this->master_data_check->checkPegawaiId($str, $error);
        }
    ]
]
```

## 4. Alur Validasi di Method Action
Validasi harus dilakukan di awal method. Jika gagal, segera kembalikan response error.

**Template:**
```php
public function create() {
    $post_data = $this->cleanInput($this->req->getVar());
    $rules = $this->rules();

    if (!$this->validate($rules)) {
        return $this->JSONResponse(
            'Lengkapi data dengan benar', 
            $this->validator->getErrors(), 
            412 // Precondition Failed
        );
    }
    
    // Lanjut ke logic bisnis...
}
```

## 5. Pesan Error (User-Friendly)
Pesan error harus menggunakan bahasa yang mudah dipahami oleh pengguna akhir (bahasa Indonesia) dan deskriptif mengenai bagian mana yang salah.

## 6. Validasi WAJIB Sebelum Pemrosesan Data (Critical)
Validasi **harus** dijalankan **sebelum** data diproses, dimodifikasi, atau disiapkan untuk disimpan. Dilarang menyiapkan data terlebih dahulu baru memvalidasi setelahnya.

```php
// SALAH — Data diproses dulu, baru divalidasi
public function insertJobcode()
{
    $data = $this->request->getPost();

    // Data sudah disiapkan/dimodifikasi...
    $initial_dept = $this->data->getDepartemenById($data['departemenid']);
    $jobcode = $initial_divisi . "." . $initial_dept . "." . $sequence;
    $prepared_data = [
        'id' => $this->generate_uuid(),
        'jobcode' => $jobcode,
        // ...
    ];

    // Validasi dilakukan SETELAH data diproses — TERLAMBAT!
    if (!$this->validate($validationRules)) {
        return $this->JSONResponse('Error', $this->validator->getErrors(), 412);
    }

    $result = $this->model->insert($prepared_data);
}

// BENAR — Validasi di awal, baru proses data
public function insertJobcode()
{
    $data = $this->cleanInput($this->req->getVar());

    // Validasi DULU sebelum apapun
    if (!$this->validate($this->rules())) {
        return $this->JSONResponse('Lengkapi data dengan benar', $this->validator->getErrors(), 412);
    }

    // Baru setelah validasi lolos, proses data
    $initial_dept = $this->data->getDepartemenById($data['departemenid']);
    $prepared_data = [/* ... */];
    $result = $this->model->insert($prepared_data);
}
```

**Alasan:**
- Mencegah pemrosesan data yang tidak valid (waste of resource).
- Mencegah side-effect (misal: auto-increment number range terpakai padahal data gagal validasi).
- Memastikan flow kode selalu: **Input → Sanitasi → Validasi → Proses → Response**.

## 7. Validasi Batch/Array Items
Untuk endpoint yang menerima array of items (batch operation), setiap item wajib divalidasi secara individual sebelum diproses.

```php
// Contoh pola standar validasi batch
public function insertBatch()
{
    $data = $this->cleanInput($this->req->getVar());
    $items = $data['items'] ?? [];

    if (!is_array($items) || empty($items)) {
        return $this->JSONResponse('Error', 'Items harus diisi', 400);
    }

    $errors = [];
    foreach ($items as $key => $item) {
        $validation = \Config\Services::validation();
        if (!$validation->setRules($this->rules())->run((array) $item)) {
            $errors[$key] = $validation->getErrors();
        }
    }

    if (!empty($errors)) {
        return $this->JSONResponse('Lengkapi data dengan benar', $errors, 412);
    }

    // Lanjut proses batch...
}
```
