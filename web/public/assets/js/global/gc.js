/**
 * GLOBAL CONSTANTS - HR-Plus
 * ===========================
 * Centralized constants untuk digunakan di seluruh aplikasi
 *
 * Usage:
 * - HTTP Status: CONSTANTS.HTTP_STATUS.OK
 * - Lock Status: CONSTANTS.LOCK_STATUS.LOCKED
 * - Messages: CONSTANTS.MESSAGES.ERROR.DEFAULT
 * - Button Namespace: window.NAV.ADD (jQuery object - cached)
 * - Button IDs: CONSTANTS.BUTTON_IDS.ADD (plain: "NV_ADD")
 *
 * Example Button Usage:
 * - NAV.ADD.on("click", handler)           // Direct jQuery object
 * - var $btn = NAV.UPDT                    // Cache reference
 * - NAV.DISP.show()                        // Direct manipulation
 * - $("#" + CONSTANTS.BUTTON_IDS.ADD).show() // Using string ID
 *
 * Note: NAV namespace is initialized after DOM ready and contains cached jQuery objects
 *       for better performance. Accessible from any JavaScript file as window.NAV.*
 *
 * Last Updated: 2026-01-03
 */

var CONSTANTS = {
  // ===========================
  // HTTP STATUS CODES
  // ===========================
  HTTP_STATUS: {
    // Success Responses
    OK: 200, // Standard response for successful HTTP requests
    CREATED: 201, // Request succeeded and a new resource was created
    ACCEPTED: 202, // Request accepted for processing
    NO_CONTENT: 204, // Request succeeded but no content to return

    // Redirection Messages
    MOVED_PERMANENTLY: 301, // URI of requested resource has been changed
    FOUND: 302, // Temporary redirect
    NOT_MODIFIED: 304, // Resource has not been modified since last request

    // Client Error Responses
    BAD_REQUEST: 400, // Server cannot or will not process request
    UNAUTHORIZED: 401, // Authentication is required
    FORBIDDEN: 403, // Server refuses to authorize request
    NOT_FOUND: 404, // Requested resource could not be found
    METHOD_NOT_ALLOWED: 405, // Request method not supported for resource
    NOT_ACCEPTABLE: 406, // Content negotiation failed
    REQUEST_TIMEOUT: 408, // Client failed to continue request
    CONFLICT: 409, // Conflict with current state of target resource
    UNPROCESSABLE_ENTITY: 422, // Request well-formed but semantic errors
    TOO_MANY_REQUESTS: 429, // User sent too many requests in a time window

    // Server Error Responses
    INTERNAL_SERVER_ERROR: 500, // Unexpected condition encountered
    NOT_IMPLEMENTED: 501, // Server does not recognize request method
    BAD_GATEWAY: 502, // Invalid response from upstream server
    SERVICE_UNAVAILABLE: 503, // Server temporarily unavailable
    GATEWAY_TIMEOUT: 504, // Upstream server did not receive timely response
  },

  // ===========================
  // LOCK STATUS
  // ===========================
  LOCK_STATUS: {
    LOCKED: "1", // Data terkunci
    UNLOCKED: "0", // Data terbuka
  },

  // ===========================
  // GRID/PAGINATION CONSTANTS
  // ===========================
  GRID: {
    DEFAULT_PAGE_SIZE: 20, // Default jumlah item per halaman
    SMALL_PAGE_SIZE: 10, // Page size untuk mobile
    LARGE_PAGE_SIZE: 50, // Page size untuk data besar
    DEFAULT_HEIGHT_OFFSET: 60, // Offset untuk menghitung tinggi grid
  },

  // ===========================
  // DATA VALIDATION
  // ===========================
  VALIDATION: {
    MAX_FILE_SIZE: 5242880, // 5MB in bytes
    ALLOWED_IMAGE_TYPES: ["image/jpeg", "image/png", "image/jpg", "image/gif"],
    MIN_PASSWORD_LENGTH: 8,
    MAX_PASSWORD_LENGTH: 32,
  },

  // ===========================
  // GENERAL STATUS
  // ===========================
  STATUS: {
    ACTIVE: "active",
    INACTIVE: "inactive",
    PENDING: "pending",
    APPROVED: "approved",
    REJECTED: "rejected",
    DELETED: "deleted",
  },

  // ===========================
  // COMMON MESSAGES
  // ===========================
  MESSAGES: {
    // Success Messages
    SUCCESS: {
      DEFAULT: "Operasi berhasil",
      SAVED: "Data berhasil disimpan",
      UPDATED: "Data berhasil diperbarui",
      DELETED: "Data berhasil dihapus",
      APPROVED: "Data berhasil disetujui",
      REJECTED: "Data berhasil ditolak",
    },

    // Error Messages
    ERROR: {
      DEFAULT: "Terjadi kesalahan",
      NETWORK: "Terjadi kesalahan jaringan",
      SERVER: "Terjadi kesalahan pada server",
      TIMEOUT: "Waktu permintaan habis",
      UNAUTHORIZED: "Anda tidak memiliki akses",
      FORBIDDEN: "Akses ditolak",
      NOT_FOUND: "Data tidak ditemukan",
      VALIDATION: "Validasi gagal",
      INIT_MODULE: "Gagal menginisialisasi module. Silahkan refresh.",
    },

    // Warning Messages
    WARNING: {
      DEFAULT: "Perhatian",
      CONFIRM_DELETE: "Apakah Anda yakin ingin menghapus data ini?",
      CONFIRM_UPDATE: "Apakah Anda yakin ingin mengubah data ini?",
      UNSAVED_CHANGES: "Ada perubahan yang belum disimpan",
      FORM_INCOMPLETE: "Lengkapi Form Anda",
      JOBCDESC_REQUIRED: "Silahkan input jobdesc pegawai saat penugasan",
      TASK_REQUIRED: "Lengkapi tugas yang akan diberikan",
    },

    // Info Messages
    INFO: {
      LOADING: "Memuat data...",
      PROCESSING: "Memproses data...",
      SAVING: "Menyimpan data...",
      NO_DATA: "Tidak ada data",
      NO_SELECTION: "Silahkan pilih data terlebih dahulu",
    },

    // Specific Messages
    SPECIFIC: {
      JOBCODE_LOCKED: "Jobcode sudah di Lock",
      JOBDESC_NOT_LOCKED: "Jobdesc belum di lock",
      INVALID_ID: "ID tidak valid",
      REQUIRED_FIELD: "Field ini wajib diisi",
      INVALID_FILE_TYPE: "Tipe file tidak valid",
      FILE_TOO_LARGE: "Ukuran file terlalu besar (maksimal 5MB)",
      REVIEW_SAVED: "Data Review Telah Tersimpan",
      JOBDESC_ADDED: "Jobdesc berhasil ditambahkan",
      JOBCDESC_FOUND:
        "Jobdesc ditemukan, silahkan memasukkan tugas sesuai dengan jobdesc yang paling relevan",
    },
  },

  // ===========================
  // DATE/TIME CONSTANTS
  // ===========================
  DATE: {
    FORMAT: {
      DATE_ONLY: "YYYY-MM-DD",
      DATETIME: "YYYY-MM-DD HH:mm:ss",
      TIME_ONLY: "HH:mm:ss",
      DISPLAY: "DD/MM/YYYY",
      DISPLAY_DATETIME: "DD/MM/YYYY HH:mm",
    },
    MOMENT_FORMAT: {
      DATE_ONLY: "YYYY-MM-DD",
      DATETIME: "YYYY-MM-DD HH:mm:ss",
      DISPLAY: "DD/MM/YYYY",
    },
  },

  // ===========================
  // REGEX PATTERNS
  // ===========================
  REGEX: {
    EMAIL: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    PHONE: /^[\d\s\-\+\(\)]+$/,
    NUMERIC: /^[0-9]+$/,
    ALPHANUMERIC: /^[a-zA-Z0-9]+$/,
    URL: /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/,
  },

  // ===========================
  // TIMEOUT VALUES (ms)
  // ===========================
  TIMEOUT: {
    AJAX: 30000, // 30 seconds
    UPLOAD: 60000, // 60 seconds
    SESSION_WARNING: 300000, // 5 minutes
    AUTO_SAVE: 5000, // 5 seconds
  },

  // ===========================
  // KEYBOARD CODES
  // ===========================
  KEY_CODES: {
    ENTER: 13,
    ESCAPE: 27,
    SPACE: 32,
    TAB: 9,
  },

  // ===========================
  // NAVIGATION BUTTON SELECTORS
  // ===========================
  // Format: jQuery objects (initialized after DOM ready)
  // Usage:window.NV_ADD.on("click", handler)
  // Note: Buttons are cached jQuery objects for better performance
  BUTTON_SELECTORS: {
    // Will be initialized in $(document).ready() at the bottom of this file
  },

  // Alternative format: Plain ID strings (without #)
  BUTTON_IDS: {
    // Action Buttons
    ACT: "NV_ACT",
    ADD: "NV_ADD",
    UPDT: "NV_UPDT",
    DISP: "NV_DISP",
    EXT: "NV_EXTD",
    SAVE: "NV_SAVE",
    CONF: "NV_CONF",
    PROC: "NV_PROC",

    // Approval Buttons
    APRV: "NV_APRV",
    FULLAPR: "NV_FULLAPR",
    RJCT: "NV_RJCT",
    REVW: "NV_REVW",

    // Status Buttons
    DEACT: "NV_DEACT",
    CLSD: "NV_CLSD",
    PNDG: "NV_PNDG",
    LOCK: "NV_LOCK",

    // Assignment & Allocation
    ASGN: "NV_ASGN",
    POST: "NV_POST",

    // Communication
    MAIL: "NV_MAIL",
    INFO: "NV_INFO",

    // Document Actions
    PAPER: "NV_PAPER",
    PERF: "NV_PERF",

    // Print Buttons
    PRNTEX: "NV_PRNTEX",
    PRNTJD: "NV_PRNTJD",
    PRNTJS: "NV_PRNTJS",
    PRNTPDF: "NV_PRNTPDF",

    // Navigation
    BACK: "NV_BACK",
    NW: "NV_NW",

    // Special Actions
    CLEAR_FILTER: "NV_CLEAR_FILTER",
    DACTCRAPRV: "NV_DACTCRAPRV",
    DACTCRCNF: "NV_DACTCRCNF",
    REOP: "NV_REOP",
    RLBK: "NV_RLBK",
    RVSE: "NV_RVSE",

    DACTCRAPV: "NV_DACTCRAPV",
  },

  // ===========================
  // BROWSER STORAGE KEYS
  // ===========================
  STORAGE_KEYS: {
    THEME: "hrplus_theme",
    SIDEBAR_STATE: "hrplus_sidebar",
    FILTERS: "hrplus_filters_",
    PREFERENCES: "hrplus_preferences",
  },
};

// ===========================
// INITIALIZE BUTTON SELECTORS
// ===========================
// Initialize button jQuery objects after DOM ready
// These are cached jQuery objects for better performance
$(document).ready(function () {
  // Action Buttons
  window.NAV = {
    // Action Buttons
    ACT: $("#NV_ACT"), // Take Action
    ADD: $("#NV_ADD"), // Add New
    NW: $("#NV_NW"), // New
    UPDT: $("#NV_UPDT"), // Update
    DISP: $("#NV_DISP"), // Display
    EXTD: $("#NV_EXTD"), // Extend
    SAVE: $("#NV_SAVE"), // Save
    CONF: $("#NV_CONF"), // Confirm
    PROC: $("#NV_PROC"), // Process

    // Approval Buttons
    APRV: $("#NV_APRV"), // Approve
    FULLAPR: $("#NV_FULLAPR"), // Full Approve
    RJCT: $("#NV_RJCT"), // Reject
    REVW: $("#NV_REVW"), // Review

    // Status Buttons
    DEACT: $("#NV_DEACT"), // Deactivate
    CLSD: $("#NV_CLSD"), // Closed
    PNDG: $("#NV_PNDG"), // Pending
    LOCK: $("#NV_LOCK"), // Lock

    // Assignment & Allocation
    ASGN: $("#NV_ASGN"), // Assign
    POST: $("#NV_POST"), // Post

    // Communication
    MAIL: $("#NV_MAIL"), // Mail/Email
    INFO: $("#NV_INFO"), // Info

    // Document Actions
    PAPER: $("#NV_PAPER"), // Paper
    PERF: $("#NV_PERF"), // Performance

    // Print Buttons
    PRNTEX: $("#NV_PRNTEX"), // Print Excel
    PRNTJD: $("#NV_PRNTJD"), // Print Job Description
    PRNTJS: $("#NV_PRNTJS"), // Print Job Spec
    PRNTPDF: $("#NV_PRNTPDF"), // Print PDF

    // Navigation
    BACK: $("#NV_BACK"), // Back
    NW: $("#NV_NW"), // New

    // Special Actions
    CLEAR_FILTER: $("#NV_CLEAR_FILTER"), // Clear Filter
    DACTCRAPRV: $("#NV_DACTCRAPRV"), // Deactivate Approve
    DACTCRCNF: $("#NV_DACTCRCNF"), // Deactivate Confirm
    REOP: $("#NV_REOP"), // Reopen
    RLBK: $("#NV_RLBK"), // Rollback
    RVSE: $("#NV_RVSE"), // Revise

    //document actions
    DACTCRAPV: $("#NV_DACTCRAPV"),

    // Import Actions
    DWNTMPL: $("#NV_DWNTMPL"), // Download Template
  };
  // Log initialization for debugging
  if (typeof console !== "undefined" && console.log) {
    // Debug logging removed for production
  }
  }
});

// Freeze constants untuk mencegah modifikasi
// Comment out baris ini jika ingin dynamic constants
// Object.freeze(CONSTANTS);

// Export untuk module systems jika diperlukan
if (typeof module !== "undefined" && module.exports) {
  module.exports = CONSTANTS;
}
