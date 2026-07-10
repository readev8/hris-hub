# JavaScript Standards - HR Plus (AI-Friendly Edition)

> **PURPOSE:** This document defines JavaScript coding standards for HR Plus project.
> **GOAL:** Code consistency, maintainability, and high quality.
> **TARGET AUDIENCE:** Human developers and AI/CLI agents

---

## [METADATA] Document Information

```
language: en
version: 2.0.0
last_updated: 2024-01-15
target: [human, ai-agent, cli-agent]
priority_levels: [critical, high, medium, low, optional]
validation: automated
```

---

## 1. CORE PRINCIPLES [PRIORITY: CRITICAL] [TAG: #foundations]

### 1.1 Single Responsibility Principle [RULE-ID: JS-001]

**CATEGORY:** Architecture
**PRIORITY:** CRITICAL
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- Each file/function MUST have single responsibility
- Use object-literal module pattern
- Separate concerns into distinct modules

**MUST NOT:**
- MUST NOT mix UI logic with business logic
- MUST NOT put API calls in UI renderer modules
- MUST NOT combine unrelated functionality

**RATIONALE:** Single responsibility improves maintainability and testing

**EXAMPLE:**
```javascript
// ✅ CORRECT (PREFERRED)
const NineboxCreate = { /* orchestration only */ }
const CreateAPI = { /* API calls only */ }
const CreateUI = { /* UI rendering only */ }

// ❌ INCORRECT (AVOID)
const NineboxCreate = { /* mixed concerns */ }
```

**AUTOMATED CHECK:** ESLINT complexity rules
**RELATED RULES:** JS-002, JS-003, JS-004
**TAGS:** #architecture #module-pattern #separation-of-concerns

---

### 1.2 DRY Principle [RULE-ID: JS-002]

**CATEGORY:** Quality
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST avoid code duplication
- Extract reusable code into functions
- Create helper modules for common operations

**MUST NOT:**
- MUST NOT copy-paste similar code blocks
- MUST NOT repeat logic across multiple files

**RATIONALE:** DRY reduces maintenance burden and bug surface

**EXAMPLE:**
```javascript
// ✅ CORRECT: Create reusable helper
function formatCurrency(amount) {
  return amount.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
}

// Use everywhere
formatCurrency(1000);
formatCurrency(2000);

// ❌ INCORRECT: Repeat formatting logic
const amount1 = 1000.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
const amount2 = 2000.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
```

**AUTOMATED CHECK:** Code duplication detection tools
**RELATED RULES:** JS-001, JS-020
**TAGS:** #quality #maintainability #dry

---

### 1.3 KISS Principle [RULE-ID: JS-003]

**CATEGORY:** Quality
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use simple and clear solutions
- Prefer readable code over clever code
- Keep functions short and focused

**MUST NOT:**
- MUST NOT use overly complex one-liners
- MUST NOT write convoluted logic

**RATIONALE:** Simple code is easier to understand, debug, and maintain

**EXAMPLE:**
```javascript
// ✅ CORRECT: Simple and clear
function isPositiveNumber(value) {
  return typeof value === 'number' && value > 0;
}

// ❌ INCORRECT: Overly complex
const isPositiveNumber=v=>typeof v=='number' && v>0;
```

**AUTOMATED CHECK:** Code complexity metrics
**RELATED RULES:** JS-001, JS-002
**TAGS:** #quality #readability #kiss

---

### 1.4 Security First [RULE-ID: JS-004]

**CATEGORY:** Security
**PRIORITY:** CRITICAL
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST validate all inputs
- MUST sanitize all outputs
- MUST use parameterized queries
- MUST implement CSRF protection

**MUST NOT:**
- MUST NOT trust user input
- MUST NOT render unescaped HTML
- MUST NOT concatenate SQL strings

**RATIONALE:** Security vulnerabilities can lead to data breaches and attacks

**EXAMPLE:**
```javascript
// ✅ CORRECT: Validate and sanitize
const userInput = $("#input").val();
const sanitized = window.sanitizeHTML(userInput);
$element.text(sanitized);

// ❌ CRITICAL SECURITY ISSUE
const userInput = $("#input").val();
$element.html(userInput);  // XSS vulnerability!
```

**AUTOMATED CHECK:** Security linters, OWASP guidelines
**RELATED RULES:** JS-100, JS-101, JS-102
**TAGS:** #security #xss #sql-injection #csrf #critical

---

## 2. NAMING CONVENTIONS [PRIORITY: HIGH] [TAG: #naming]

### 2.1 Variable and Function Naming [RULE-ID: JS-020]

**CATEGORY:** Naming
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** camelCase
**VALIDATION:** ESLINT camelcase rule

**MUST:**
- MUST use camelCase for variables and functions
- MUST use descriptive names
- MUST start with lowercase letter

**MUST NOT:**
- MUST NOT use snake_case
- MUST NOT use PascalCase (reserved for modules)
- MUST NOT use abbreviations

**EXAMPLE:**
```javascript
// ✅ CORRECT (PREFERRED)
const employeeId = "EMP001";
const performaRata = 8.5;
const assessmentData = {};
function loadEmployeeData() {}

// ❌ INCORRECT (AVOID)
const employee_id = "EMP001";  // snake_case
const PerformaRata = 8.5;     // PascalCase
function ldEmpD() {}           // Abbreviated
```

**AUTOMATED CHECK:** ESLINT rule `naming/camelcase`
**RELATED RULES:** JS-021, JS-022
**TAGS:** #naming #variables #functions

---

### 2.2 Constant Naming [RULE-ID: JS-021]

**CATEGORY:** Naming
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** UPPER_SNAKE_CASE
**VALIDATION:** ESLINT uppercase rule

**MUST:**
- MUST use UPPER_SNAKE_CASE for constants
- MUST be descriptive
- MUST be declared at module level

**MUST NOT:**
- MUST NOT use camelCase for constants
- MUST NOT use lowercase for constants

**EXAMPLE:**
```javascript
// ✅ CORRECT
const API_BASE_URL = "/api/v1";
const MAX_RATING = 5;
const MIN_RATING = 1;

// ❌ INCORRECT
const apiBaseUrl = "/api/v1";
const max_rating = 5;
```

**AUTOMATED CHECK:** ESLINT rule `naming/uppercase`
**RELATED RULES:** JS-020, JS-023
**TAGS:** #naming #constants

---

### 2.3 jQuery Object Variables [RULE-ID: JS-022]

**CATEGORY:** Naming
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** $prefix
**VALIDATION:** ESLINT plugin

**MUST:**
- MUST prefix jQuery objects with $
- MUST use camelCase after $
- MUST cache selectors

**MUST NOT:**
- MUST NOT use without $ prefix
- MUST NOT use PascalCase after $

**EXAMPLE:**
```javascript
// ✅ CORRECT (PREFERRED)
const $container = $("#container");
const $row = $(".data-row");
const $badge = $("#badge-123");

// ❌ INCORRECT (AVOID)
const container = $("#container");
const Container = $("#container");
const $Container = $("#container");  // camelCase after $
```

**AUTOMATED CHECK:** ESLINT rule `naming/jquery-prefix`
**RELATED RULES:** JS-020
**TAGS:** #naming #jquery

---

### 2.4 Module Naming [RULE-ID: JS-023]

**CATEGORY:** Naming
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** PascalCase
**VALIDATION:** ESLINT rule

**MUST:**
- MUST use PascalCase for module names
- MUST be descriptive
- MUST end with module type if applicable

**MUST NOT:**
- MUST NOT use camelCase
- MUST NOT use lowercase

**EXAMPLE:**
```javascript
// ✅ CORRECT (PREFERRED)
const NineboxCreate = {};
const UpdateState = {};
const VendorAPI = {};

// ❌ INCORRECT (AVOID)
const nineboxCreate = {};
const updateState = {};
```

**AUTOMATED CHECK:** ESLINT rule `naming/pascalcase`
**RELATED RULES:** JS-020
**TAGS:** #naming #modules

---

### 2.5 Private Method Naming [RULE-ID: JS-024]

**CATEGORY:** Naming
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** _prefix
**VALIDATION:** ESLINT rule

**MUST:**
- MUST prefix private methods with underscore
- MUST be used only within module

**MUST NOT:**
- MUST NOT call private methods from outside module
- MUST NOT use without underscore prefix

**EXAMPLE:**
```javascript
// ✅ CORRECT (PREFERRED)
const Module = {
  loadData: function() {},           // Public
  _loadDataInternal: function() {},  // Private
  _formatError: function() {}        // Private
};

// ❌ INCORRECT (AVOID)
const Module = {
  loadData: function() {},
  loadDataInternal: function() {},  // Missing underscore
};
```

**AUTOMATED CHECK:** ESLINT rule `naming/private-method`
**RELATED RULES:** JS-020
**TAGS:** #naming #encapsulation #private

---

## 3. FILE STRUCTURE [PRIORITY: HIGH] [TAG: #structure]

### 3.1 Standard File Structure [RULE-ID: JS-030]

**CATEGORY:** Structure
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST follow standard section organization
- MUST include section headers
- MUST have clear file documentation

**MUST NOT:**
- MUST NOT mix sections arbitrarily
- MUST NOT skip required sections

**TEMPLATE:**
```javascript
/**
 * ============================================================================
 * MODULE NAME
 * ============================================================================
 *
 * Description: Brief description
 * Author: Your Name
 * Date: YYYY-MM-DD
 */

// ===========================
// INITIALIZATION
// ===========================

// ===========================
// DATA LOADING
// ===========================

// ===========================
// ERROR HANDLING
// ===========================

// ===========================
// PUBLIC API
// ===========================

// ===========================
// UTILITY FUNCTIONS
// ===========================

// ===========================
// AUTO-INITIALIZATION
// ===========================

// ===========================
// EXPORTS
// ===========================
```

**AUTOMATED CHECK:** File structure validation
**RELATED RULES:** JS-031, JS-032
**TAGS:** #structure #file-organization

---

### 3.2 File Organization by Concern [RULE-ID: JS-031]

**CATEGORY:** Structure
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST organize files by single concern
- MUST use standard file naming

**FILE PURPOSES:**
| File | Purpose |
|------|---------|
| `main.js` | Entry point, orchestration, initialization |
| `constants.js` | Static configuration, API endpoints, thresholds |
| `state.js` | State management with getters/setters |
| `api.js` | AJAX/API communication layer |
| `calculator.js` | Business logic, calculations |
| `validator.js` | Input validation functions |
| `ui-renderer.js` | DOM manipulation, rendering UI |
| `events.js` | Event handlers |
| `helpers.js` | Utility functions, legacy support |

**AUTOMATED CHECK:** File naming validation
**RELATED RULES:** JS-030, JS-001
**TAGS:** #structure #file-organization #separation-of-concerns

---

### 3.3 Module Pattern [RULE-ID: JS-032]

**CATEGORY:** Structure
**PRIORITY:** CRITICAL
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use object-literal module pattern
- MUST export modules to window object
- MUST follow standard module structure

**MUST NOT:**
- MUST NOT use global variables without module pattern
- MUST NOT mix modules

**TEMPLATE:**
```javascript
/**
 * ============================================================================
 * MODULE NAME
 * ============================================================================
 */
const ModuleName = {
  // Private state
  _data: null,

  // Public API
  init: function() {},

  // Private methods
  _privateMethod: function() {}
};

// Export
window.ModuleName = ModuleName;
```

**AUTOMATED CHECK:** Module pattern validation
**RELATED RULES:** JS-030, JS-050
**TAGS:** #structure #module-pattern #architecture

---

## 4. STATE MANAGEMENT [PRIORITY: HIGH] [TAG: #state-management]

### 4.1 State Module Pattern [RULE-ID: JS-050]

**CATEGORY:** State
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** Object-literal with getters/setters
**RATIONALE:** Encapsulation prevents direct state manipulation

**MUST:**
- MUST use getters/setters to access state
- MUST use Map for collections
- MUST initialize state properly

**MUST NOT:**
- MUST NOT access private state directly
- MUST NOT modify state without setters

**EXAMPLE:**
```javascript
// ✅ CORRECT
const ModuleState = {
  _data: new Map(),
  setData: function(key, value) {
    this._data.set(key, value);
  },
  getData: function(key) {
    return this._data.get(key);
  }
};

// ❌ INCORRECT (DANGEROUS)
const state = {
  data: new Map()
};
// Direct access: state.data.get(key) // DANGEROUS
```

**AUTOMATED CHECK:** ESLINT no-underscore-dangle
**RELATED RULES:** JS-051, JS-001
**TAGS:** #state-management #encapsulation #getter-setter

---

### 4.2 Using Map for Collections [RULE-ID: JS-051]

**CATEGORY:** State
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** ES6 Map
**RATIONALE:** Map provides better performance and API than Object

**MUST:**
- MUST use Map for keyed collections
- MUST use Map methods appropriately

**MUST NOT:**
- MUST NOT use Object for Map-like collections
- MUST NOT use array for key-value pairs

**EXAMPLE:**
```javascript
// ✅ CORRECT: Use Map for rating data
const ratings = new Map();

// Setting
ratings.set(competencyId, { rating, comment, timestamp });

// Getting
const rating = ratings.get(competencyId);

// Checking existence
if (ratings.has(competencyId)) {
  // Rating exists
}

// Iterating
ratings.forEach((value, key) => {
  console.log(`Competency ${key}: Rating ${value.rating}`);
});

// Converting to array
const ratingsArray = Array.from(ratings.entries()).map(([id, rating]) => ({
  kompetensi_id: id,
  nilai_rating: rating.rating,
  komentar: rating.comment,
}));

// Deleting
ratings.delete(competencyId);

// Clearing all
ratings.clear();
```

**AUTOMATED CHECK:** Code analysis tools
**RELATED RULES:** JS-050
**TAGS:** #state-management #es6 #map

---

### 4.3 State Access Patterns [RULE-ID: JS-052]

**CATEGORY:** State
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use getters/setters to access state
- MUST not access private state directly

**MUST NOT:**
- MUST NOT access private state from outside module

**EXAMPLE:**
```javascript
// ✅ CORRECT: Use getters/setters
ModuleState.setPegawaiId("EMP001");
const pegawaiId = ModuleState.getPegawaiId();

// ✅ CORRECT: Use Map methods
ModuleState.setRating(123, 4, "Karyawan yang baik");
const rating = ModuleState.getRating(123);

// ❌ INCORRECT: Direct access to private state
ModuleState.pegawaiId = "EMP001";  // Don't do this
const id = ModuleState.ratings[123];  // Don't do this
```

**AUTOMATED CHECK:** Encapsulation validation
**RELATED RULES:** JS-050, JS-051
**TAGS:** #state-management #encapsulation #access-patterns

---

## 5. EVENT HANDLING [PRIORITY: MEDIUM] [TAG: #event-handling]

### 5.1 Event Delegation [RULE-ID: JS-060]

**CATEGORY:** Events
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** jQuery delegated event binding
**RATIONALE:** Delegation works with dynamically created elements

**MUST:**
- MUST use event delegation with $(document).on()
- MUST prevent default behavior for forms/links
- MUST use data attributes for passing data

**MUST NOT:**
- MUST NOT use direct event binding on dynamic elements
- MUST NOT forget preventDefault on forms/links

**EXAMPLE:**
```javascript
// ✅ CORRECT: Use event delegation
$(document).on("click", ".btn-submit", function() {
  // Handle click
});

// ✅ CORRECT: Prevent default behavior
$(document).on("click", ".btn-submit", function(e) {
  e.preventDefault();
  // Handle click
});

// ✅ CORRECT: Use data attributes for passing data
$(document).on("click", "[data-action='delete']", function() {
  const id = $(this).data("id");
  // Handle delete
});

// ❌ INCORRECT: Direct event binding on dynamically created elements
$(".btn-submit").on("click", function() {
  // Won't work for dynamically created elements
});

// ❌ INCORRECT: Missing preventDefault
$(document).on("click", "a", function() {
  // Will follow link before handler completes
});
```

**AUTOMATED CHECK:** Event binding analysis
**RELATED RULES:** JS-061
**TAGS:** #event-handling #delegation #jquery

---

### 5.2 Event Handler Initialization [RULE-ID: JS-061]

**CATEGORY:** Events
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST initialize all event handlers in dedicated init function
- MUST separate event handlers by concern

**MUST NOT:**
- MUST NOT scatter event initialization
- MUST NOT mix event handlers

**EXAMPLE:**
```javascript
// ✅ CORRECT
const CreateEvents = {
  init: function() {
    this.initStarRating();
    this.initCommentTracking();
    this.initNavigation();
    this.initFormValidation();
  },

  initStarRating: function() {
    $(document).on("click", ".star-rating .material-icons", function() {
      // Handle star click
    });
  },

  initNavigation: function() {
    $(document).on("click", "#btn-submit", function() {
      // Handle submit
    });
  }
};
```

**AUTOMATED CHECK:** Code organization validation
**RELATED RULES:** JS-060
**TAGS:** #event-handling #initialization #organization

---

### 5.3 Custom Events [RULE-ID: JS-062]

**CATEGORY:** Events
**PRIORITY:** LOW
**SCOPE:** CONTEXT-DEPENDENT
**STATUS:** ACTIVE

**MUST:**
- MUST use descriptive event names with namespaces
- MUST trigger events with meaningful data

**MUST NOT:**
- MUST NOT use generic event names without namespace

**EXAMPLE:**
```javascript
// ✅ CORRECT: Dispatch custom event
$(document).trigger("rating:changed", {
  competencyId: 123,
  rating: 4,
  timestamp: new Date().toISOString()
});

// ✅ CORRECT: Listen to custom event
$(document).on("rating:changed", function(e, data) {
  console.log("Rating changed:", data.competencyId, data.rating);
  // Handle change
});

// ✅ CORRECT: One-time event listener
$(document).one("data:loaded", function() {
  console.log("Data loaded for first time");
});
```

**AUTOMATED CHECK:** Event naming validation
**RELATED RULES:** JS-060
**TAGS:** #event-handling #custom-events #namespaces

---

## 6. AJAX/API CALLS [PRIORITY: MEDIUM] [TAG: #api]

### 6.1 API Layer Pattern [RULE-ID: JS-070]

**CATEGORY:** API
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** API module with Promise-based methods
**RATIONALE:** Separation of concerns and error handling

**MUST:**
- MUST create dedicated API module
- MUST return Promises
- MUST handle errors properly
- MUST use global $.post() override

**MUST NOT:**
- MUST NOT mix API calls with business logic
- MUST NOT skip error handling

**EXAMPLE:**
```javascript
// ✅ CORRECT
const CreateAPI = {
  getPegawaiInfo: function(pegawaiId) {
    return new Promise((resolve, reject) => {
      $.post(
        `${site_url}${CreateConstants.API.GET_INFO_PEGAWAI}`,
        { token: pegawaiId },
        (response) => {
          if (response && response.statuscode === CONSTANTS.HTTP_STATUS.OK) {
            resolve(response.result);
          } else {
            reject({
              message: response?.message || "Unknown Error",
              code: response?.statuscode,
            });
          }
        }
      );
    });
  }
};
```

**AUTOMATED CHECK:** API structure validation
**RELATED RULES:** JS-071, JS-072
**TAGS:** #api #promises #separation-of-concerns

---

### 6.2 Using Global $.post() Override [RULE-ID: JS-071]

**CATEGORY:** API
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use global $.post() for all API calls
- MUST pass button selector for loading state
- MUST handle success and error properly

**MUST NOT:**
- MUST NOT use direct $.ajax() without CSRF handling
- MUST NOT skip loading indicators

**EXAMPLE:**
```javascript
// ✅ CORRECT: Use global $.post() with loading indicator
$.post(
  url,
  data,
  function(response) {
    // Handle success
    console.log(response);
  },
  "#btn-submit"  // Button selector for loading state
);

// ✅ CORRECT: Handle success and error properly
$.post(url, data, function(response) {
  if (response && response.statuscode === CONSTANTS.HTTP_STATUS.OK) {
    toastr.success("Data berhasil disimpan", "Berhasil");
  } else {
    toastr.error(response?.message || "Terjadi kesalahan", "Error");
  }
}, "#btn-submit");

// ❌ INCORRECT: Direct $.ajax without CSRF handling
$.ajax({
  url: url,
  type: 'POST',
  data: data,
  success: function(response) {
    // Missing CSRF token
  }
});
```

**AUTOMATED CHECK:** API call analysis
**RELATED RULES:** JS-070, JS-072
**TAGS:** #api #jquery #csrf

---

### 6.3 Error Handling in API Calls [RULE-ID: JS-072]

**CATEGORY:** API
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST handle errors with .catch() or try-catch
- MUST show user-friendly error messages
- MUST log errors for debugging

**MUST NOT:**
- MUST NOT ignore errors
- MUST NOT show raw error messages to users

**EXAMPLE:**
```javascript
// ✅ CORRECT: Promise chain with error handling
CreateAPI.loadAllDataSequential(pegawaiId, periode)
  .then((data) => {
    console.log("Data loaded:", data);
    CreateUI.renderAll(data);
  })
  .catch((error) => {
    console.error("Failed to load data:", error);
    CreateUI.hideLoading();
    toastr.error(error.message || "Gagal memuat data", "Error");
  });

// ✅ CORRECT: Async/await with try-catch
async function loadData() {
  try {
    const data = await CreateAPI.loadAllDataSequential(pegawaiId, periode);
    CreateUI.renderAll(data);
  } catch (error) {
    console.error("Error:", error);
    CreateUI.hideLoading();
    toastr.error(error.message || "Gagal memuat data", "Error");
  }
}
```

**AUTOMATED CHECK:** Error handling validation
**RELATED RULES:** JS-070, JS-100
**TAGS:** #api #error-handling #promises

---

## 7. UI RENDERING [PRIORITY: MEDIUM] [TAG: #ui]

### 7.1 jQuery DOM Manipulation [RULE-ID: JS-080]

**CATEGORY:** UI
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use .text() instead of .html() for user content
- MUST cache jQuery selectors
- MUST use .on() for event delegation
- MUST chain operations when appropriate

**MUST NOT:**
- MUST NOT use .html() with untrusted user input (XSS risk)
- MUST NOT repeat selector lookups
- MUST NOT use direct .click() on dynamic elements

**EXAMPLE:**
```javascript
// ✅ CORRECT: Cache jQuery selectors
const $container = $("#container");
const $grid = $("#competency-grid");

// ✅ CORRECT: Use .text() instead of .html() for user content
$element.text(userInput);  // Escapes HTML

// ❌ CRITICAL SECURITY ISSUE
$element.html(userInput);  // DANGEROUS!

// ✅ CORRECT: Use .on() for event delegation
$(document).on("click", ".dynamic-button", function() {
  // Handle click
});

// ❌ INCORRECT: Direct .click() on static elements
$(".button").click(function() {
  // Won't work for dynamically created elements
});

// ✅ CORRECT: Chain operations
$container
  .empty()
  .append($element)
  .fadeIn(300)
  .addClass("active");
```

**AUTOMATED CHECK:** DOM manipulation analysis
**RELATED RULES:** JS-081, JS-082
**TAGS:** #ui #jquery #dom #security

---

### 7.2 Safe Text Setting [RULE-ID: JS-081]

**CATEGORY:** UI
**PRIORITY:** CRITICAL
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** Sanitization before rendering
**RATIONALE:** Prevent XSS attacks

**MUST:**
- MUST sanitize all user input before rendering
- MUST use .text() for setting text
- MUST use safeSetText helper

**MUST NOT:**
- MUST NOT use .html() with user input
- MUST NOT render unescaped content

**EXAMPLE:**
```javascript
// ✅ CORRECT: Safe text setting
CreateUI.safeSetText($element, userInput);

// ✅ CORRECT: Sanitize manually
const safeText = window.sanitizeHTML(userInput);
$element.text(safeText);

// ❌ CRITICAL SECURITY ISSUE
$element.html(userInput);  // XSS VULNERABILITY!
```

**AUTOMATED CHECK:** Security validation
**RELATED RULES:** JS-080, JS-100
**TAGS:** #ui #security #xss #critical

---

### 7.3 Animation Patterns [RULE-ID: JS-082]

**CATEGORY:** UI
**PRIORITY:** LOW
**SCOPE:** CONTEXT-DEPENDENT
**STATUS:** ACTIVE

**MUST:**
- MUST use CSS animations for performance
- MUST use setTimeout for sequencing
- MUST provide feedback for user actions

**MUST NOT:**
- MUST NOT use blocking animations
- MUST NOT animate everything unnecessarily

**EXAMPLE:**
```javascript
// ✅ CORRECT: Fade in with delay
$element.css("animation-delay", `${index * 0.1}s`);
$element.addClass("slide-in-up");

// ✅ CORRECT: Scale animation
$badge.css("transform", "scale(0.9)");
$badge.css("opacity", "0.5");
setTimeout(() => {
  $badge.css("transform", "scale(1)");
  $badge.css("opacity", "1");
}, 200);

// ✅ CORRECT: Progress bar animation
progressBar.css("width", "0%");
setTimeout(() => {
  progressBar.css("width", `${percentage}%`);
}, 100);

// ✅ CORRECT: Fade in/out
$element.fadeIn(300, function() {
  console.log("Fade in complete");
});

$element.fadeOut(300, function() {
  $element.remove();
});
```

**AUTOMATED CHECK:** Animation analysis
**RELATED RULES:** JS-080
**TAGS:** #ui #animation #user-experience

---

## 8. VALIDATION [PRIORITY: CRITICAL] [TAG: #validation]

### 8.1 Validation Module Pattern [RULE-ID: JS-090]

**CATEGORY:** Validation
**PRIORITY:** CRITICAL
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**PATTERN:** Validation object with standardized return format
**RATIONALE:** Consistent validation across application

**MUST:**
- MUST create dedicated validator module
- MUST return { isValid, message } objects
- MUST validate all inputs
- MUST check for security issues

**MUST NOT:**
- MUST NOT skip validation
- MUST NOT use inconsistent validation logic

**TEMPLATE:**
```javascript
// ✅ CORRECT
const ModuleValidator = {
  validateNumeric: function(value, min = 1, max = 5, required = true) {
    if (required && (value === null || value === undefined || value === "")) {
      return { isValid: false, message: "Nilai wajib diisi" };
    }

    const num = parseFloat(value);

    if (isNaN(num)) {
      return { isValid: false, message: "Nilai harus berupa angka" };
    }

    if (num < min || num > max) {
      return { isValid: false, message: `Nilai harus antara ${min} dan ${max}` };
    }

    return { isValid: true, message: "" };
  },

  validateComment: function(comment, options = {}) {
    const opts = {
      maxLength: 1000,
      checkSQLInjection: true,
      checkXSS: true,
      ...options,
    };

    // Validation logic
    // ...

    return { isValid: true, message: "" };
  }
};
```

**AUTOMATED CHECK:** Validation coverage
**RELATED RULES:** JS-091, JS-092
**TAGS:** #validation #security #input-validation #critical

---

### 8.2 Security Validation [RULE-ID: JS-091]

**CATEGORY:** Validation
**PRIORITY:** CRITICAL
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST validate for XSS patterns
- MUST validate for SQL injection patterns
- MUST validate for HTML tags
- MUST reject invalid input

**MUST NOT:**
- MUST NOT accept unvalidated input
- MUST NOT skip security checks

**EXAMPLE:**
```javascript
// ✅ CORRECT: Check for XSS patterns
const xssPatterns = [
  /<script/i,
  /javascript:/i,
  /on\w+\s*=/i,
  /<iframe/i,
];

for (const pattern of xssPatterns) {
  if (pattern.test(comment)) {
    return { isValid: false, message: "Script tidak diizinkan" };
  }
}

// ✅ CORRECT: Check for SQL injection patterns
const sqlPatterns = [
  /(\bOR\b|\bAND\b).*=.*=/i,
  /';.*DROP\s+TABLE/i,
  /UNION\s+SELECT/i,
];

for (const pattern of sqlPatterns) {
  if (pattern.test(comment)) {
    return { isValid: false, message: "Karakter tidak diizinkan" };
  }
}
```

**AUTOMATED CHECK:** Security validation coverage
**RELATED RULES:** JS-090, JS-100, JS-101
**TAGS:** #validation #security #xss #sql-injection #critical

---

### 8.3 Validation Usage [RULE-ID: JS-092]

**CATEGORY:** Validation
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST validate all user inputs
- MUST show validation feedback
- MUST prevent submission if invalid

**MUST NOT:**
- MUST NOT submit invalid data
- MUST NOT ignore validation errors

**EXAMPLE:**
```javascript
// ✅ CORRECT: Validate individual field
const $input = $("#rating-input");
const result = CreateValidator.validateRating($input.val());

if (!result.isValid) {
  $input.addClass("is-invalid");
  $input.siblings(".invalid-feedback").text(result.message);
}

// ✅ CORRECT: Validate before submission
const validation = CreateValidator.validateSubmission();
if (!validation.isValid) {
  // Show errors
  validation.errors.forEach((error) => {
    toastr.error(error, "Validasi Error");
  });
  return;
}

// ✅ CORRECT: Real-time validation on input
$(document).on("input", ".validate-me", function() {
  const $input = $(this);
  const result = CreateValidator.validateField($input);

  if (!result.isValid) {
    $input.addClass("is-invalid");
    $input.siblings(".invalid-feedback").text(result.message);
  } else {
    $input.removeClass("is-invalid");
    $input.addClass("is-valid");
  }
});
```

**AUTOMATED CHECK:** Validation usage analysis
**RELATED RULES:** JS-090, JS-091
**TAGS:** #validation #user-feedback #forms

---

## 9. ERROR HANDLING [PRIORITY: HIGH] [TAG: #error-handling]

### 9.1 Error Handling Patterns [RULE-ID: JS-100]

**CATEGORY:** Error Handling
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use try-catch for initialization
- MUST use .catch() for Promises
- MUST log errors with context
- MUST show user-friendly error messages

**MUST NOT:**
- MUST NOT ignore errors
- MUST NOT show raw error details to users

**EXAMPLE:**
```javascript
// ✅ CORRECT: Try-catch for initialization
init: function(pegawaiId, periode) {
  try {
    // Initialize state
    CreateState.init(pegawaiId, periode);

    // Show loading
    CreateUI.showLoading();

    // Load data
    this._loadDataSequential(pegawaiId, periode)
      .then(() => {
        console.log("Module initialized successfully");
      })
      .catch((error) => {
        console.error("Initialization failed:", error);
        CreateUI.hideLoading();
        const errorMsg = this._formatErrorMessage(error);
        toastr.error(errorMsg, CONSTANTS.MESSAGES.ERROR.DEFAULT);
      });
  } catch (error) {
    console.error("Init error:", error);
    toastr.error("Gagal menginisialisasi module. Silahkan refresh.", "Error");
  }
}

// ✅ CORRECT: Promise chain with .catch()
CreateAPI.loadAllDataSequential(pegawaiId, periode)
  .then((data) => {
    console.log("Data loaded:", data);
    CreateUI.renderAll(data);
  })
  .catch((error) => {
    console.error("Failed to load data:", error);
    CreateUI.hideLoading();
    toastr.error(error.message || "Gagal memuat data", "Error");
  });
```

**AUTOMATED CHECK:** Error handling coverage
**RELATED RULES:** JS-101, JS-102
**TAGS:** #error-handling #promises #try-catch

---

### 9.2 Generic Error Messages [RULE-ID: JS-101]

**CATEGORY:** Error Handling
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use generic error messages for better UX
- MUST provide context-specific messages when available
- MUST avoid exposing technical details

**MUST NOT:**
- MUST NOT show raw error messages to users
- MUST NOT expose stack traces

**EXAMPLE:**
```javascript
// ✅ CORRECT: Generic error messages for better UX
_formatErrorMessage: function(error) {
  if (error.message && !error.message.includes("Failed to fetch")) {
    return error.message;
  }

  const genericMessages = [
    "Gagal memuat data. Silahkan coba lagi.",
    "Terjadi kesalahan jaringan.",
    "Silahkan refresh halaman dan coba lagi.",
  ];

  return genericMessages[Math.floor(Math.random() * genericMessages.length)];
}

// ✅ CORRECT: Context-specific error messages
const errorMessages = {
  "network": "Gagal terhubung ke server. Silahkan periksa koneksi internet Anda.",
  "validation": "Data tidak valid. Silahkan periksa input Anda.",
  "permission": "Anda tidak memiliki izin untuk melakukan aksi ini.",
  "not_found": "Data tidak ditemukan.",
  "server": "Terjadi kesalahan pada server. Silahkan coba lagi nanti.",
};
```

**AUTOMATED CHECK:** Error message validation
**RELATED RULES:** JS-100
**TAGS:** #error-handling #user-experience #security

---

### 9.3 Error Logging [RULE-ID: JS-102]

**CATEGORY:** Error Handling
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST log errors with context
- MUST include timestamp
- MUST log security events separately

**MUST NOT:**
- MUST NOT log sensitive data
- MUST NOT log without context

**EXAMPLE:**
```javascript
// ✅ CORRECT: Log errors with context
console.error("Failed to load employee data:", {
  pegawaiId: pegawaiId,
  error: error,
  timestamp: new Date().toISOString(),
});

// ✅ CORRECT: Log security events
if (typeof window.logSecurityEvent === "function") {
  window.logSecurityEvent("XSS_ATTEMPT", "Script tag detected", {
    input: userInput,
    source: "competency_comment",
  });
}

// ✅ CORRECT: Log user actions
console.log("User action:", {
  action: "rating_updated",
  competencyId: competencyId,
  rating: rating,
  timestamp: new Date().toISOString(),
});
```

**AUTOMATED CHECK:** Logging validation
**RELATED RULES:** JS-100
**TAGS:** #error-handling #logging #debugging

---

## 10. SECURITY [PRIORITY: CRITICAL] [TAG: #security]

### 10.1 XSS Prevention [RULE-ID: JS-110]

**CATEGORY:** Security
**PRIORITY:** CRITICAL
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**THREAT:** Cross-Site Scripting
**IMPACT:** HIGH
**VALIDATION:** Automated + Manual

**MUST:**
- MUST use .text() instead of .html() for user content
- MUST sanitize all user input before rendering
- MUST validate comments for script tags
- MUST use safeSetText helper

**MUST NOT:**
- MUST NOT use .html() with untrusted user input
- MUST NOT render raw user content without sanitization
- MUST NOT allow inline scripts in user-generated content

**EXAMPLE:**
```javascript
// ✅ CORRECT: Use .text() instead of .html()
$element.text(userInput);  // Escapes HTML

// ✅ CORRECT: Use safe text setting
CreateUI.safeSetText($element, userInput);

// ✅ CORRECT: Validate comments for XSS patterns
const comment = $("#comment-input").val();
const result = CreateValidator.validateComment(comment, {
  checkXSS: true,
});

if (!result.isValid) {
  alert(result.message);
  return;
}

// ❌ CRITICAL SECURITY ISSUE
$element.html(userInput);  // XSS VULNERABILITY!
```

**VALIDATION:**
1. Automated: ESLINT security plugin
2. Manual: Code review required
3. Testing: XSS test suite

**REFERENCES:** OWASP XSS Prevention Cheat Sheet
**RELATED RULES:** JS-111, JS-112, JS-081
**TAGS:** #security #xss #sanitization #critical

---

### 10.2 SQL Injection Prevention [RULE-ID: JS-111]

**CATEGORY:** Security
**PRIORITY:** CRITICAL
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**THREAT:** SQL Injection
**IMPACT:** HIGH
**VALIDATION:** Automated + Manual

**MUST:**
- MUST check for SQL injection patterns
- MUST validate input before sending to server
- MUST use parameterized queries on server-side

**MUST NOT:**
- MUST NOT concatenate SQL strings
- MUST NOT accept unvalidated input

**EXAMPLE:**
```javascript
// ✅ CORRECT: Check for SQL injection patterns
const sqlPatterns = [
  /(\bOR\b|\bAND\b).*=.*=/i,
  /';.*DROP\s+TABLE/i,
  /UNION\s+SELECT/i,
  /--$/i,
  /\/\*/i,
];

function checkSQLInjection(input) {
  for (const pattern of sqlPatterns) {
    if (pattern.test(input)) {
      return true;
    }
  }
  return false;
}

// ✅ CORRECT: Validate input before sending to server
const comment = $("#comment-input").val();
if (checkSQLInjection(comment)) {
  alert("Input mengandung karakter yang tidak diizinkan");
  return;
}
```

**VALIDATION:**
1. Automated: Input validation
2. Manual: Code review required
3. Testing: SQL injection test suite

**RELATED RULES:** JS-110, JS-112, JS-091
**TAGS:** #security #sql-injection #validation #critical

---

### 10.3 CSRF Protection [RULE-ID: JS-112]

**CATEGORY:** Security
**PRIORITY:** CRITICAL
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**THREAT:** Cross-Site Request Forgery
**IMPACT:** HIGH
**VALIDATION:** Automated

**MUST:**
- MUST auto-regenerate CSRF token for all requests
- MUST use global $.post() override
- MUST include CSRF token in headers

**MUST NOT:**
- MUST NOT skip CSRF token
- MUST NOT use direct AJAX without CSRF

**EXAMPLE:**
```javascript
// ✅ CORRECT: Auto-regenerate CSRF token for all requests
// (Handled by global $.post() override from d.js)

// Manual implementation example:
function sendRequest(url, data) {
  return regenerate().then((token) => {
    return $.ajax({
      url: url,
      type: 'POST',
      dataType: 'JSON',
      data: data,
      headers: {
        'X-CSRF-TOKEN': token[$("#i").val()]
      },
    });
  });
}

// Usage
sendRequest(url, data)
  .then((response) => {
    // Handle response
  })
  .catch((error) => {
    // Handle error
  });
}
```

**VALIDATION:**
1. Automated: CSRF token validation
2. Manual: Code review required

**RELATED RULES:** JS-110, JS-111
**TAGS:** #security #csrf #authentication #critical

---

## 11. DOCUMENTATION [PRIORITY: HIGH] [TAG: #documentation]

### 11.1 JSDoc Comments [RULE-ID: JS-120]

**CATEGORY:** Documentation
**PRIORITY:** HIGH
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use JSDoc for all public functions
- MUST document parameters with @param
- MUST document return values with @returns
- MUST describe function purpose

**MUST NOT:**
- MUST NOT skip documentation for public APIs
- MUST NOT use incomplete JSDoc

**EXAMPLE:**
```javascript
// ✅ CORRECT
/**
 * Initialize Ninebox Create Module
 * @param {string} pegawaiId - Employee ID/token
 * @param {string} periode - Assessment period
 * @returns {Promise<void>}
 */
init: function(pegawaiId, periode) {
  // Implementation
}

/**
 * Calculate all metrics in one call
 * @returns {Object} { potensi, performa, box, percentage, fulfilled, total }
 */
calculateAll: function() {
  // Implementation
}

/**
 * Validate numeric value
 * @param {*} value - Value to validate
 * @param {number} min - Minimum value (default: 1)
 * @param {number} max - Maximum value (default: 5)
 * @param {boolean} required - Is required (default: true)
 * @returns {Object} Validation result { isValid, message }
 */
validateNumeric: function(value, min = 1, max = 5, required = true) {
  // Implementation
}
```

**AUTOMATED CHECK:** JSDoc validation tools
**RELATED RULES:** JS-121, JS-122
**TAGS:** #documentation #jsdoc #functions

---

### 11.2 File Headers [RULE-ID: JS-121]

**CATEGORY:** Documentation
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST include file header with module description
- MUST document dependencies
- MUST include author and date
- MUST describe features

**MUST NOT:**
- MUST NOT skip file headers
- MUST NOT use incomplete headers

**TEMPLATE:**
```javascript
/**
 * ============================================================================
 * MODULE NAME
 * ============================================================================
 *
 * Module description.
 *
 * Features:
 * - Feature 1 description
 * - Feature 2 description
 * - Feature 3 description
 *
 * Dependencies:
 * - jQuery
 * - Bootstrap
 * - Toastr
 *
 * Author: Your Name
 * Date: YYYY-MM-DD
 * Version: 1.0.0
 */
```

**AUTOMATED CHECK:** Header validation
**RELATED RULES:** JS-120
**TAGS:** #documentation #file-headers #metadata

---

### 11.3 Section Headers [RULE-ID: JS-122]

**CATEGORY:** Documentation
**PRIORITY:** LOW
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use consistent section headers
- MUST organize code into logical sections

**MUST NOT:**
- MUST NOT mix sections arbitrarily

**EXAMPLE:**
```javascript
// ✅ CORRECT
/**
 * ============================================================================
 * INITIALIZATION
 * ============================================================================
 *
 * Menginisialisasi module dan setup awal.
 */

// ===========================
// DATA LOADING
// ===========================

// ===========================
// ERROR HANDLING
// ===========================

// ===========================
// PUBLIC API
// ===========================

// ===========================
// UTILITY FUNCTIONS
// ===========================
```

**AUTOMATED CHECK:** Section organization validation
**RELATED RULES:** JS-120, JS-030
**TAGS:** #documentation #organization #sections

---

## 12. CONSTANTS [PRIORITY: MEDIUM] [TAG: #constants]

### 12.1 Constants Module Structure [RULE-ID: JS-130]

**CATEGORY:** Constants
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST create dedicated constants module
- MUST organize constants by category
- MUST export constants to window object
- MUST use UPPER_SNAKE_CASE

**MUST NOT:**
- MUST NOT use magic numbers
- MUST NOT scatter constants

**EXAMPLE:**
```javascript
// ✅ CORRECT
const ModuleConstants = {
  // API Endpoints
  API: {
    BASE_URL: "/api/v1",
    GET_INFO: "ninebox/ajax-get-info-pegawai",
    GET_DATA: "ninebox/ajax-get-data",
    SUBMIT: "ninebox/submit-data",
  },

  // Thresholds
  THRESHOLDS: {
    PERFORMANCE: {
      HIGH: 8.7,
      MEDIUM: 8.0,
      LOW: 0,
    },
    POTENTIAL: {
      HIGH: 80,
      MEDIUM: 60,
      LOW: 0,
    },
  },

  // UI Configuration
  UI: {
    ANIMATION_DELAY: 0.1,
    ANIMATION_DURATION: 300,
    TOOLTIP_POSITION: "top",
  },

  // Messages
  MESSAGES: {
    SUCCESS: {
      SAVED: "Data berhasil disimpan",
      SUBMITTED: "Data berhasil disubmit",
    },
    ERROR: {
      DEFAULT: "Terjadi kesalahan",
      NETWORK: "Gagal terhubung ke server",
    },
  },

  // HTTP Status Codes
  HTTP_STATUS: {
    OK: 200,
    CREATED: 201,
    BAD_REQUEST: 400,
    UNAUTHORIZED: 401,
    FORBIDDEN: 403,
    NOT_FOUND: 404,
    SERVER_ERROR: 500,
  },
};

// Export
window.ModuleConstants = ModuleConstants;
```

**AUTOMATED CHECK:** Constants validation
**RELATED RULES:** JS-131, JS-021
**TAGS:** #constants #configuration #organization

---

### 12.2 Using Constants [RULE-ID: JS-131]

**CATEGORY:** Constants
**PRIORITY:** MEDIUM
**SCOPE:** ALWAYS
**STATUS:** ACTIVE

**MUST:**
- MUST use constants for magic numbers
- MUST use constants for messages
- MUST use constants for API endpoints
- MUST use constants for UI configuration

**MUST NOT:**
- MUST NOT use magic numbers
- MUST NOT hardcode strings

**EXAMPLE:**
```javascript
// ✅ CORRECT: Use constants for magic numbers
if (performa >= ModuleConstants.THRESHOLDS.PERFORMANCE.HIGH) {
  // Handle high performance
}

// ✅ CORRECT: Use constants for messages
toastr.success(ModuleConstants.MESSAGES.SUCCESS.SAVED, "Berhasil");

// ✅ CORRECT: Use constants for API endpoints
const url = `${site_url}${ModuleConstants.API.GET_INFO}`;

// ✅ CORRECT: Use constants for UI configuration
$element.css("animation-delay", `${index * ModuleConstants.UI.ANIMATION_DELAY}s`);

// ✅ CORRECT: Use constants for HTTP status codes
if (response.statuscode === ModuleConstants.HTTP_STATUS.OK) {
  // Handle success
}
```

**AUTOMATED CHECK:** Magic number detection
**RELATED RULES:** JS-130, JS-021
**TAGS:** #constants #maintainability #no-magic-numbers

---

## [MACHINE-READABLE] Rules Database

```json
{
  "version": "2.0.0",
  "language": "javascript",
  "rules": [
    {
      "id": "JS-001",
      "name": "Single Responsibility Principle",
      "category": "architecture",
      "priority": "critical",
      "tags": ["#architecture", "#module-pattern", "#separation-of-concerns"],
      "scope": "always",
      "must": [
        "Each file/function must have single responsibility",
        "Use object-literal module pattern",
        "Separate concerns into distinct modules"
      ],
      "must_not": [
        "Must not mix UI logic with business logic",
        "Must not put API calls in UI renderer modules",
        "Must not combine unrelated functionality"
      ],
      "automated_check": "ESLINT complexity rules",
      "references": ["JS-002", "JS-003", "JS-004"]
    },
    {
      "id": "JS-004",
      "name": "Security First",
      "category": "security",
      "priority": "critical",
      "tags": ["#security", "#xss", "#sql-injection", "#csrf", "#critical"],
      "scope": "always",
      "must": [
        "Must validate all inputs",
        "Must sanitize all outputs",
        "Must use parameterized queries",
        "Must implement CSRF protection"
      ],
      "must_not": [
        "Must not trust user input",
        "Must not render unescaped HTML",
        "Must not concatenate SQL strings"
      ],
      "automated_check": "Security linters, OWASP guidelines",
      "references": ["JS-100", "JS-101", "JS-102", "JS-110", "JS-111", "JS-112"]
    },
    {
      "id": "JS-020",
      "name": "Variable and Function Naming",
      "category": "naming",
      "priority": "high",
      "tags": ["#naming", "#variables", "#functions"],
      "scope": "always",
      "pattern": "camelCase",
      "must": [
        "Must use camelCase for variables and functions",
        "Must use descriptive names",
        "Must start with lowercase letter"
      ],
      "must_not": [
        "Must not use snake_case",
        "Must not use PascalCase (reserved for modules)",
        "Must not use abbreviations"
      ],
      "automated_check": "ESLINT rule naming/camelcase",
      "references": ["JS-021", "JS-022", "JS-023"]
    },
    {
      "id": "JS-050",
      "name": "State Module Pattern",
      "category": "state",
      "priority": "high",
      "tags": ["#state-management", "#encapsulation", "#getter-setter"],
      "scope": "always",
      "pattern": "Object-literal with getters/setters",
      "must": [
        "Must use getters/setters to access state",
        "Must use Map for collections",
        "Must initialize state properly"
      ],
      "must_not": [
        "Must not access private state directly",
        "Must not modify state without setters"
      ],
      "automated_check": "ESLINT no-underscore-dangle",
      "references": ["JS-051", "JS-052"]
    },
    {
      "id": "JS-090",
      "name": "Validation Module Pattern",
      "category": "validation",
      "priority": "critical",
      "tags": ["#validation", "#security", "#input-validation", "#critical"],
      "scope": "always",
      "pattern": "Validation object with standardized return format",
      "must": [
        "Must create dedicated validator module",
        "Must return { isValid, message } objects",
        "Must validate all inputs",
        "Must check for security issues"
      ],
      "must_not": [
        "Must not skip validation",
        "Must not use inconsistent validation logic"
      ],
      "automated_check": "Validation coverage",
      "references": ["JS-091", "JS-092"]
    },
    {
      "id": "JS-100",
      "name": "Error Handling Patterns",
      "category": "error-handling",
      "priority": "high",
      "tags": ["#error-handling", "#promises", "#try-catch"],
      "scope": "always",
      "must": [
        "Must use try-catch for initialization",
        "Must use .catch() for Promises",
        "Must log errors with context",
        "Must show user-friendly error messages"
      ],
      "must_not": [
        "Must not ignore errors",
        "Must not show raw error details to users"
      ],
      "automated_check": "Error handling coverage",
      "references": ["JS-101", "JS-102"]
    },
    {
      "id": "JS-110",
      "name": "XSS Prevention",
      "category": "security",
      "priority": "critical",
      "tags": ["#security", "#xss", "#sanitization", "#critical"],
      "threat": "Cross-Site Scripting",
      "impact": "HIGH",
      "validation": "Automated + Manual",
      "must": [
        "Must use .text() instead of .html() for user content",
        "Must sanitize all user input before rendering",
        "Must validate comments for script tags",
        "Must use safeSetText helper"
      ],
      "must_not": [
        "Must not use .html() with untrusted user input",
        "Must not render raw user content without sanitization",
        "Must not allow inline scripts in user-generated content"
      ],
      "validation": [
        "Automated: ESLINT security plugin",
        "Manual: Code review required",
        "Testing: XSS test suite"
      ],
      "references": ["JS-111", "JS-112", "JS-081"],
      "external_references": "OWASP XSS Prevention Cheat Sheet"
    }
  ]
}
```

---

## [MACHINE-READABLE] Tag Index

| Tag | Count | Rule IDs |
|-----|-------|----------|
| #architecture | 4 | JS-001, JS-002, JS-003, JS-032 |
| #security | 4 | JS-004, JS-110, JS-111, JS-112 |
| #naming | 5 | JS-020, JS-021, JS-022, JS-023, JS-024 |
| #structure | 3 | JS-030, JS-031, JS-032 |
| #state-management | 3 | JS-050, JS-051, JS-052 |
| #event-handling | 3 | JS-060, JS-061, JS-062 |
| #api | 3 | JS-070, JS-071, JS-072 |
| #ui | 3 | JS-080, JS-081, JS-082 |
| #validation | 3 | JS-090, JS-091, JS-092 |
| #error-handling | 3 | JS-100, JS-101, JS-102 |
| #documentation | 3 | JS-120, JS-121, JS-122 |
| #constants | 2 | JS-130, JS-131 |
| #module-pattern | 2 | JS-001, JS-032 |
| #separation-of-concerns | 2 | JS-001, JS-031 |
| #quality | 2 | JS-002, JS-003 |
| #xss | 2 | JS-110, JS-081 |
| #sql-injection | 2 | JS-111, JS-091 |
| #csrf | 1 | JS-112 |
| #jquery | 2 | JS-022, JS-080 |
| #encapsulation | 2 | JS-050, JS-052 |
| #es6 | 1 | JS-051 |
| #map | 1 | JS-051 |
| #dom | 1 | JS-080 |
| #user-experience | 1 | JS-082 |
| #critical | 4 | JS-001, JS-004, JS-090, JS-110, JS-111, JS-112 |
| #high | 5 | JS-002, JS-020, JS-021, JS-022, JS-023, JS-030, JS-031, JS-032, JS-050, JS-092, JS-100 |
| #medium | 6 | JS-003, JS-024, JS-060, JS-070, JS-130, JS-131 |
| #low | 3 | JS-062, JS-082, JS-122 |

---

## [MACHINE-READABLE] Priority Matrix

| Priority | Count | Rule IDs |
|----------|-------|----------|
| CRITICAL | 6 | JS-001, JS-004, JS-090, JS-110, JS-111, JS-112 |
| HIGH | 12 | JS-002, JS-003, JS-020, JS-021, JS-022, JS-023, JS-030, JS-031, JS-032, JS-050, JS-051, JS-052, JS-092, JS-100 |
| MEDIUM | 6 | JS-024, JS-060, JS-061, JS-062, JS-070, JS-071, JS-072, JS-101, JS-102, JS-130, JS-131 |
| LOW | 3 | JS-062, JS-082, JS-122 |

---

## [MACHINE-READABLE] Category Index

| Category | Count | Rule IDs |
|----------|-------|----------|
| Architecture | 4 | JS-001, JS-002, JS-003, JS-032 |
| Security | 4 | JS-004, JS-110, JS-111, JS-112 |
| Naming | 5 | JS-020, JS-021, JS-022, JS-023, JS-024 |
| Structure | 3 | JS-030, JS-031, JS-032 |
| State | 3 | JS-050, JS-051, JS-052 |
| Events | 3 | JS-060, JS-061, JS-062 |
| API | 3 | JS-070, JS-071, JS-072 |
| UI | 3 | JS-080, JS-081, JS-082 |
| Validation | 3 | JS-090, JS-091, JS-092 |
| Error Handling | 3 | JS-100, JS-101, JS-102 |
| Documentation | 3 | JS-120, JS-121, JS-122 |
| Constants | 2 | JS-130, JS-131 |
| Quality | 2 | JS-002, JS-003 |

---

## Summary

This document provides comprehensive JavaScript standards for HR Plus project. By following these standards, we achieve:

1. **Consistency** - Consistent code is easier to read and understand
2. **Maintainability** - Structured code is easier to maintain
3. **Security** - Security best practices applied consistently
4. **Quality** - Better validation and error handling reduces bugs
5. **Development Speed** - Clear patterns speed up feature development

**Key Points:**
- MUST always use object-literal module pattern
- MUST validate all inputs and sanitize outputs
- MUST use JSDoc for function documentation
- MUST use comment headers for file organization
- MUST prioritize security in all code
- MUST test code before deployment

**References:**
- [MDN Web Docs](https://developer.mozilla.org/)
- [jQuery Documentation](https://api.jquery.com/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [OWASP Security Guidelines](https://owasp.org/)

---

*Last Updated: 2024-01-15*
*Version: 2.0.0*
*Format: AI-Friendly*
