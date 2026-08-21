/**
 * ============================================================================
 * TrackingStore
 * ============================================================================
 *
 * localStorage utility for anonymous ticket tracking codes.
 * Manages add, remove, lookup, and persistence of tracking codes.
 *
 * Dependencies: none
 * Date: 2026-08-18
 */

const TrackingStore = {

    // ===========================
    // CONSTANTS
    // ===========================

    STORAGE_KEY: 'anon_tickets',

    // ===========================
    // PUBLIC API
    // ===========================

    getCodes: function () {
        try {
            var raw = localStorage.getItem(this.STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    },

    saveCodes: function (arr) {
        var unique = [];
        var seen = {};
        for (var i = 0; i < arr.length; i++) {
            var code = String(arr[i]).trim().toUpperCase();
            if (code && !seen[code]) {
                seen[code] = true;
                unique.push(code);
            }
        }
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(unique));
    },

    addCode: function (code) {
        code = String(code).trim().toUpperCase();
        if (!code) return false;
        var codes = this.getCodes();
        if (codes.indexOf(code) !== -1) return false;
        codes.push(code);
        this.saveCodes(codes);
        return true;
    },

    removeCode: function (code) {
        code = String(code).trim().toUpperCase();
        var codes = this.getCodes();
        var filtered = [];
        for (var i = 0; i < codes.length; i++) {
            if (codes[i] !== code) filtered.push(codes[i]);
        }
        this.saveCodes(filtered);
    },

    hasCode: function (code) {
        code = String(code).trim().toUpperCase();
        return this.getCodes().indexOf(code) !== -1;
    },

    clearAll: function () {
        localStorage.removeItem(this.STORAGE_KEY);
    }
};

window.TrackingStore = TrackingStore;
