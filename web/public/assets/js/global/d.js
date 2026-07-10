// FILE INI DIGUNAKAN UNTUK MENAMPUNG GLOBAL FUNCTION UNTUK PENGAKSESAN DATA KE ROUTES SERTA STANDARISASI NOTIFIKASI

toastr.options = {
  closeButton: true,
  debug: false,
  newestOnTop: false,
  progressBar: true,
  positionClass: "toast-top-right", // Other option: "toast-top-right"
  preventDuplicates: false,
  onclick: null,
  showDuration: "300",
  hideDuration: "1000",
  timeOut: "3000",
  extendedTimeOut: "1000",
  showEasing: "swing",
  hideEasing: "linear",
  showMethod: "fadeIn",
  hideMethod: "fadeOut",
};
$.post = function (url, data, response, sender = null) {
  /*
    contoh penggunaan
    $.post('<?= site_url('hiring/onjobtraining/task/action/update') ?>', {
        id: 1,
        is_active: 1,
        token: '<?= $this->security->get_csrf_hash() ?>'
    }, (res) => {
        if (res.statuscode == 200) {
            toastr.success(res.message, 'Berhasil')
        } else {
            toastr.error(res.message, 'Terjadi Kesalahan')
        }
    }, $("#btnUpdate"))
    */
  var sender_text = null; // FIX: Store original text before changing
  if (sender != null) {
    sender_text = $(sender).html(); // FIX: Save original button text
    $(sender).attr("disabled", "true");
    $(sender).html(
      `<span style='height:15px;width:15px' class="spinner-border spinner-border-reverse align-self-center loader-sm "></span> Memproses...`,
    );
  }

  regenerate().then((token) => {
    $.ajax({
      url: url,
      type: "POST",
      dataType: "JSON",
      data: data,
      headers: {
        "X-CSRF-TOKEN": token[$("#i").val()],
      },
      success: (res) => {
        if (sender != null) {
          $(sender).attr("disabled", false);
          $(sender).html(sender_text); // FIX: Restore original text
        }
        // Call the response callback with the result
        if (typeof response === "function") {
          response(res);
        }
      },
      error: (err) => {
        // FIX: Restore button on error
        if (sender != null) {
          $(sender).attr("disabled", false);
          $(sender).html(sender_text);
        }
        // FIX: Proper error handling
        toastr.error(
          typeof err === "string" ? err : "Terjadi Kesalahanxxx",
          "Error",
        );
        // Call the response callback with error object
        if (typeof response === "function") {
          response({ statuscode: 500, message: "Network error", data: null });
        }
      },
    });
  });
};
$.postForm = function (url, data, response, sender = null) {
  var sender_text = null;
  if (sender != null) {
    sender_text = $(sender).html();
    $(sender).attr("disabled", "true");
    $(sender).html(
      `<span style='height:15px;width:15px' class="spinner-border spinner-border-reverse align-self-center loader-sm "></span> Memproses...`,
    );
  }
  regenerate().then((token) => {
    $.ajax({
      url: url,
      type: "POST",
      dataType: "JSON",
      data: data,
      // Konfigurasi penting agar FormData bisa dikirim dengan benar
      processData: false, // Mencegah jQuery memproses data (mengubah jadi string)
      contentType: false, // Mencegah jQuery mengatur Content-Type header
      headers: {
        "X-CSRF-TOKEN": token[$("#i").val()],
      },
      success: (res) => {
        if (sender != null) {
          $(sender).attr("disabled", false);
          $(sender).html(sender_text);
        }
        return response(res); // FIX: Add return statement
      },
      error: (err) => {
        // FIX: Restore button on error
        if (sender != null) {
          $(sender).attr("disabled", false);
          $(sender).html(sender_text);
        }
        // FIX: Proper error handling
        toastr.error(
          typeof err === "string" ? err : "Terjadi Kesalahan",
          "Error",
        );
      },
    });
  });
};
$.get = function (url, data, response) {
  regenerate().then((token) => {
    $.ajax({
      type: "GET",
      url: url,
      data: data,
      dataType: "JSON",
      headers: {
        "X-CSRF-TOKEN": token[$("#i").val()],
      },
      success: (res) => {
        return response(res);
      },
      error: (err) => {
        // FIX: Proper error handling
        toastr.error(
          typeof err === "string" ? err : "Terjadi Kesalahan",
          "Error",
        );
        return response({
          statuscode: 500,
          message: "Network error",
          data: null,
        });
      },
    });
  });
};

$.getInputValue = function (selector = "form") {
  return new Promise((resolve, reject) => {
    try {
      const $form = $(selector);
      const data = {};

      // Get all text/password/email/number inputs and textareas
      $form
        .find(
          'input[type="text"], input[type="password"], input[type="email"], input[type="number"], textarea',
        )
        .each(function () {
          if (this.id) {
            data[this.id] = this.value;
          }
        });

      // Get all select2 elements
      $form.find(".select2").each(function () {
        if (this.id) {
          data[this.id] = $(this).val();
        }
      });

      resolve(data);
    } catch (err) {
      reject(err);
    }
  });
};
const regenerate = () => {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: `${site_url}/request/get`,
      type: "GET",
      dataType: "JSON",
      success: (res) => {
        resolve(res);
      },
      error: (err) => {
        console.error("CSRF token regeneration failed:", err);
        toastr.error("Gagal memperbarui token keamanan", "Error");
        reject(err);
      },
    });
  });
};

// ============================================================================
// SECURITY HELPER FUNCTIONS
// ============================================================================

/**
 * Sanitize HTML to prevent XSS attacks
 *
 * Escapes special characters to prevent HTML/JavaScript injection
 *
 * @param {string} unsafe - Unsafe string that may contain HTML
 * @returns {string} Sanitized string with escaped HTML entities
 * @example
 * sanitizeHTML('<script>alert("XSS")</script>') // returns "&lt;script&gt;..."
 */
window.sanitizeHTML = function (unsafe) {
  if (typeof unsafe !== "string") {
    return "";
  }

  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;")
    .replace(/\//g, "&#x2F;");
};

/**
 * Create safe DOM element with text content
 *
 * Alternative to .html() for safe content rendering
 *
 * @param {string} tagName - HTML tag name (e.g., 'div', 'span', 'td')
 * @param {string} text - Text content (will be escaped)
 * @param {Object} attributes - Optional object with HTML attributes
 * @returns {jQuery} jQuery object with safe DOM element
 * @example
 * createSafeElement('td', 'User Input', { class: 'text-center' })
 */
window.createSafeElement = function (tagName, text, attributes = {}) {
  const $element = $(`<${tagName}>`);

  // Set text content (automatically escaped by jQuery)
  if (text !== null && text !== undefined) {
    $element.text(String(text));
  }

  // Set additional attributes
  Object.keys(attributes).forEach((key) => {
    if (key === "class") {
      $element.addClass(attributes[key]);
    } else if (key === "id") {
      $element.attr("id", attributes[key]);
    } else {
      $element.attr(key, attributes[key]);
    }
  });

  return $element;
};

/**
 * Build safe table row from data object
 *
 * Creates a table row with escaped cell content to prevent XSS
 *
 * @param {Array} data - Array of cell values (will be escaped)
 * @param {Array} tags - Optional array of tag names for each cell (default: 'td')
 * @param {Array} attributes - Optional array of attribute objects for each cell
 * @returns {jQuery} jQuery object with complete <tr> element
 * @example
 * buildSafeTableRow(['John', 'Doe', 'Manager'], ['td', 'td', 'td'])
 */
window.buildSafeTableRow = function (data, tags = null, attributes = null) {
  const $row = $("<tr>");

  data.forEach((cellData, index) => {
    const tagName = tags && tags[index] ? tags[index] : "td";
    const cellAttrs = attributes && attributes[index] ? attributes[index] : {};

    const $cell = createSafeElement(tagName, cellData, cellAttrs);
    $cell.appendTo($row);
  });

  return $row;
};

/**
 * Validate comment input for security
 *
 * Checks comment length and detects dangerous patterns
 *
 * @param {string} comment - Comment text to validate
 * @param {Object} options - Validation options
 * @returns {Object} Validation result { isValid: boolean, message: string }
 * @example
 * validateComment('This is a safe comment', { maxLength: 1000 })
 */
window.validateComment = function (comment, options = {}) {
  const defaults = {
    maxLength: 1000,
    minLength: 0,
    allowHTML: false,
    allowedTags: [], // e.g., ['b', 'i', 'u']
  };

  const settings = { ...defaults, ...options };

  // Check if comment is a string
  if (typeof comment !== "string") {
    return {
      isValid: false,
      message: "Komentar harus berupa teks",
    };
  }

  // Check length
  if (comment.length < settings.minLength) {
    return {
      isValid: false,
      message: `Komentar terlalu pendek (minimum ${settings.minLength} karakter)`,
    };
  }

  if (comment.length > settings.maxLength) {
    return {
      isValid: false,
      message: `Komentar terlalu panjang (maksimum ${settings.maxLength} karakter)`,
    };
  }

  // If HTML is not allowed, check for dangerous patterns
  if (!settings.allowHTML) {
    const dangerousPatterns = [
      /<script/i,
      /javascript:/i,
      /onerror\s*=/i,
      /onload\s*=/i,
      /onclick\s*=/i,
      /onmouseover\s*=/i,
      /<iframe/i,
      /<object/i,
      /<embed/i,
      /<link/i,
      /<style/i,
      /<meta/i,
      /expression\s*\(/i,
      /vbscript:/i,
      /data:text\/html/i,
    ];

    for (const pattern of dangerousPatterns) {
      if (pattern.test(comment)) {
        return {
          isValid: false,
          message: "Komentar mengandung karakter yang tidak diperbolehkan",
        };
      }
    }
  }

  // Check for SQL injection patterns
  const sqlInjectionPatterns = [
    /(\bOR\b|\bAND\b).*=.*=/i,
    /';.*DROP\s+TABLE/i,
    /UNION\s+SELECT/i,
    /--.*$/i,
    /\/\*.*\*\//i,
    /exec\s*\(/i,
  ];

  for (const pattern of sqlInjectionPatterns) {
    if (pattern.test(comment)) {
      return {
        isValid: false,
        message: "Format komentar tidak valid",
      };
    }
  }

  return {
    isValid: true,
    message: "Valid",
  };
};

/**
 * Sanitize and truncate text
 *
 * Sanitizes HTML and limits text length
 *
 * @param {string} text - Text to sanitize
 * @param {number} maxLength - Maximum length (0 = no limit)
 * @param {string} suffix - Suffix to add if truncated (default: '...')
 * @returns {string} Sanitized and truncated text
 * @example
 * sanitizeText('Long text...', 10) // returns "Long te..."
 */
window.sanitizeText = function (text, maxLength = 0, suffix = "...") {
  if (typeof text !== "string") {
    return "";
  }

  // First sanitize HTML
  let sanitized = sanitizeHTML(text);

  // Then truncate if needed
  if (maxLength > 0 && sanitized.length > maxLength) {
    return sanitized.substring(0, maxLength) + suffix;
  }

  return sanitized;
};

/**
 * Safe jQuery text setter with sanitization
 *
 * Alternative to .text() that also sanitizes if needed
 *
 * @param {jQuery} $element - jQuery element to set text
 * @param {string} text - Text content (will be escaped)
 * @param {Object} options - Options { maxLength: number, suffix: string }
 * @returns {jQuery} Same element for chaining
 * @example
 * safeSetText($('#myElement'), userInput, { maxLength: 100 })
 */
window.safeSetText = function ($element, text, options = {}) {
  if (!$element.length) {
    console.warn("safeSetText: Element not found");
    return $element;
  }

  const defaults = {
    maxLength: 0,
    suffix: "...",
  };

  const settings = { ...defaults, ...options };

  // Sanitize and truncate
  const processedText = sanitizeText(
    String(text || ""),
    settings.maxLength,
    settings.suffix,
  );

  // Set text (jQuery automatically escapes)
  $element.text(processedText);

  return $element;
};

/**
 * Safe jQuery HTML setter with sanitization
 *
 * Alternative to .html() that sanitizes content first
 *
 * @param {jQuery} $element - jQuery element to set HTML
 * @param {string} html - HTML content (will be sanitized)
 * @param {boolean} allowBasicHTML - Allow basic HTML tags (b, i, u, br, p)
 * @returns {jQuery} Same element for chaining
 * @example
 * safeSetHtml($('#myElement'), userInput)
 * safeSetHtml($('#myElement'), userInput, true) // Allow <b>, <i>, etc.
 */
window.safeSetHtml = function ($element, html, allowBasicHTML = false) {
  if (!$element.length) {
    console.warn("safeSetHtml: Element not found");
    return $element;
  }

  let sanitized = sanitizeHTML(String(html || ""));

  // If basic HTML is allowed, restore certain tags
  if (allowBasicHTML) {
    const allowedTags = ["b", "i", "u", "em", "strong", "br", "p", "span"];
    const allowedPatterns = allowedTags.map((tag) => `&lt;\/?${tag}&gt;`);

    // Restore allowed tags (simple implementation)
    // Note: This is a basic implementation. For production, use a library like DOMPurify
    allowedTags.forEach((tag) => {
      sanitized = sanitized.replace(
        new RegExp(`&lt;(${tag})&gt;`, "gi"),
        "<$1>",
      );
      sanitized = sanitized.replace(
        new RegExp(`&lt;\/(${tag})&gt;`, "gi"),
        "</$1>",
      );
    });
  }

  $element.html(sanitized);

  return $element;
};

/**
 * Validate numeric input range
 *
 * @param {number} value - Value to validate
 * @param {number} min - Minimum value
 * @param {number} max - Maximum value
 * @param {boolean} isInteger - Must be integer (default: true)
 * @returns {Object} Validation result { isValid: boolean, message: string }
 * @example
 * validateNumeric(5, 1, 5) // { isValid: true, message: 'Valid' }
 * validateNumeric(6, 1, 5) // { isValid: false, message: 'Nilai harus antara 1-5' }
 */
window.validateNumeric = function (value, min, max, isInteger = true) {
  const numValue = Number(value);

  if (isNaN(numValue)) {
    return {
      isValid: false,
      message: "Nilai harus berupa angka",
    };
  }

  if (isInteger && !Number.isInteger(numValue)) {
    return {
      isValid: false,
      message: "Nilai harus berupa bilangan bulat",
    };
  }

  if (numValue < min || numValue > max) {
    return {
      isValid: false,
      message: `Nilai harus antara ${min} - ${max}`,
    };
  }

  return {
    isValid: true,
    message: "Valid",
  };
};

/**
 * Log security-relevant events (for security monitoring)
 *
 * @param {string} event - Event type (e.g., 'XSS_ATTEMPT', 'SQL_INJECTION_ATTEMPT')
 * @param {string} details - Event details
 * @param {Object} data - Additional data
 * @example
 * logSecurityEvent('XSS_ATTEMPT', 'Script tag detected in comment', { userId: 123 })
 */
window.logSecurityEvent = function (event, details, data = {}) {
  const securityLog = {
    timestamp: new Date().toISOString(),
    event: event,
    details: details,
    page: window.location.href,
    userAgent: navigator.userAgent,
    data: data,
  };

  // In development, log to console
  if (typeof console !== "undefined" && console.warn) {
    console.warn("🔒 Security Event:", securityLog);
  }

  // In production, you can send to server for logging
  // Example:
  // $.post(site_url + 'security/log', securityLog);

  return securityLog;
};
