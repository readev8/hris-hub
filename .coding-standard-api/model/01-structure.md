# Standar Struktur Model (Modular Architecture)

Untuk meningkatkan maintainability dan skalabilitas, setiap modul wajib membagi Model menjadi tiga kategori spesifik.

## 1. Pembagian Tipe Model
Nama file harus diakhiri dengan suffix yang sesuai dengan fungsinya:

| Tipe | Suffix | Deskripsi |
| :--- | :--- | :--- |
| **Action** | `_Act_model.php` | Menangani semua operasi mutasi data (INSERT, UPDATE, DELETE). |
| **Data** | `_Data_model.php` | Menangani pengambilan detail data tunggal atau data statis. |
| **Report** | `_Rpt_model.php` | Menangani pengambilan list data, pagination, dan filter untuk Grid/Tabel. |
| **Check** | `_Check_model.php` | Menangani semua validasi keberadaan data, status aktif, dan aturan bisnis sebelum mutasi. |

## 2. Lokasi Folder
Model harus diletakkan dalam sub-folder sesuai kategorinya:
- `app/Models/[module]/action/`
- `app/Models/[module]/data/`
- `app/Models/[module]/report/`
- `app/Models/[module]/check/`

## 3. Aturan Penamaan Class & Method
- **Class Name**: PascalCase (contoh: `AdmLeaveAct_model`).
- **Method Name**: camelCase (contoh: `submitLeaveRequest`, `getLeaveDetail`).
- **Property Name**: snake_case (contoh: `protected $table_name`).

## 4. Dasar Class (Inheritance)
Setiap model wajib meng-extend Base Model yang sesuai untuk mendapatkan fitur lazy-loading dan helper standar:
- Action Model extend `BaseAction`
- Data Model extend `BaseData`
- Report Model extend `BaseReport`

## 5. Larangan Instansiasi Model di Constructor (Critical)
**Dilarang keras** melakukan instansiasi model lain di dalam `__construct()` pada `BaseAction`, `BaseData`, `BaseReport`, atau model apapun.

```php
// SALAH — Instansiasi model di constructor (Eager Loading)
class BaseAction extends Model
{
    protected $ojt_task_fpkt_act;
    protected $ojt_task_jobdesc_act;

    function __construct()
    {
        $this->ojt_task_fpkt_act = new OnjobtrainingTaskFpktAct_model();
        $this->ojt_task_jobdesc_act = new OnjobtrainingTaskJobdescAct_model();
        // ... 6 model di-instantiate setiap kali BaseAction dipanggil!
    }
}

// BENAR — Lazy Loading: model hanya di-load saat dibutuhkan
class BaseAction extends Model
{
    protected array $model_map = [
        'ojt_task_fpkt_act' => OnjobtrainingTaskFpktAct_model::class,
        'ojt_task_jobdesc_act' => OnjobtrainingTaskJobdescAct_model::class,
    ];

    protected array $instances = [];

    public function __get(string $name): mixed
    {
        if (isset($this->model_map[$name]) && !isset($this->instances[$name])) {
            $this->instances[$name] = new $this->model_map[$name]();
        }
        return $this->instances[$name] ?? null;
    }
}
```

**Alasan:**
- Setiap `new Model()` memanggil constructor yang mungkin membuat koneksi DB baru.
- Tidak semua model yang di-instantiate di constructor akan digunakan.
- Menyebabkan pemborosan memori dan memperlambat response time.

## 6. Constructor Hanya untuk Dependency Dasar
Constructor pada model base **hanya boleh** menginisialisasi hal berikut:
- Koneksi database: `$this->db = db_connect();`
- URI (jika diperlukan): `$this->uri = new \CodeIgniter\HTTP\URI(current_url(true));`

**Tidak boleh** menginisialisasi:
- Model lain (gunakan lazy loading)
- Session (session bukan urusan model)
- Service yang berat
