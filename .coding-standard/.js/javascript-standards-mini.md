# JavaScript Standards - HR Plus (Mini)

> **PURPOSE:** Standar ringkas untuk file JS kecil (<400 baris)
> **GOAL:** Konsisten, aman, mudah dirawat tanpa memecah banyak file
> **TARGET:** Developer + AI/CLI agent

---

## [METADATA]

```
language: id
version: 1.0.0
last_updated: 2026-02-22
target: [human, ai-agent, cli-agent]
scope: small-js (<400 lines)
priority_levels: [critical, high]
```

---

## 1) RULE UTAMA: 1 FILE JIKA KECIL

**MUST:**
- Jika file <400 baris, gunakan **1 file** dengan **section modular**.
- Pecah file hanya jika:
  - >400 baris, atau
  - 2+ fitur tidak terkait, atau
  - butuh reuse lintas page.

**REKOMENDASI STRUKTUR (URUTAN):**
1. constants
2. state
3. api
4. ui
5. validator
6. events
7. main/init

**CONTOH (SINGLE-FILE MODULE PATTERN):**
```javascript
const Create = { init() {}, bindEvents() {} };
const CreateAPI = { save() {} };
const CreateUI = { render() {} };
const CreateValidator = { validate() {} };
```

---

## 2) CORE PRINCIPLES (CRITICAL/HIGH)

### 2.1 Single Responsibility (JS-001)
**MUST:** 1 tanggung jawab per modul/section.
**MUST NOT:** campur UI + API dalam satu fungsi.

### 2.2 DRY (JS-002)
**MUST:** reuse helper, jangan copy-paste logic.

### 2.3 Security First (JS-004)
**MUST:** validasi input + sanitasi output + CSRF.
**CONTOH:**
```javascript
$el.text(userInput); // aman
// $el.html(userInput); // XSS
```

---

## 3) NAMING (HIGH)

- **camelCase** untuk function/variable.
- **UPPER_SNAKE_CASE** untuk constants.
- **jQuery object** diawali `$` (contoh: `$form`, `$btn`).

---

## 4) GLOBAL UTILITIES (WAJIB CEK SEBELUM BUAT HELPER BARU)

**MUST:**
- Cek `public/assets/js/global` sebelum membuat helper baru.
- Jangan duplikasi fungsi yang sudah ada.
- Jika helper dipakai lintas page, taruh di global.

**UTILITAS UTAMA & KAPAN DIPAKAI:**
- `secure-ajax.js`: gunakan `$.secureAjax` untuk request yang butuh CSRF.
- `gc.js`: gunakan `CONSTANTS` untuk status/message/ID/button.
- `populate.js`: gunakan helper populate select2/radio/error/datatable.
- `sweetalert.js`: gunakan `$.swal_*` / `window.swal_*` untuk dialog.
- `toastr.js`: gunakan `window.toastr_*` untuk notifikasi ringan.
- `custom.js`: helper umum (decode entity, format, loader, tooltip).
- `kendotable.js`: helper grid `window.kendo_table*`.
- `highchart.js`, `fullcalendar-loader.js`, `select2.js`: gunakan wrapper yang tersedia.

**RULE:** jika wrapper global ada, **jangan panggil library langsung** dari file page.

---

## 5) VALIDATION (CRITICAL)

- Semua input user **wajib** divalidasi sebelum submit/API.
- Gunakan `$.populateError` jika tersedia untuk error standar.

---

## 6) ERROR HANDLING (HIGH)

- Semua API call **wajib** handle error.
- Tampilkan pesan generic ke user, detail di log.

---

## 7) SECURITY (CRITICAL)

- Output user ke DOM wajib pakai `.text()` atau sanitasi.
- CSRF via `$.secureAjax` atau mekanisme setara.

---

## 8) DOCUMENTATION (HIGH)

- Header file ringkas: fitur + tanggal + owner.
- JSDoc hanya untuk fungsi publik utama.

---

## RINGKAS CHECKLIST

- [ ] File <400 baris tetap 1 file dengan section modular.
- [ ] Pisahkan API/UI/Validator/Events meski dalam 1 file.
- [ ] Pakai helper global sebelum buat helper baru.
- [ ] Validasi input + sanitasi output.
- [ ] Error handling wajib.
- [ ] Naming konsisten.
