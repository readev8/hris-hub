/**
 * TrackingStore — localStorage utility for anonymous ticket tracking codes
 * @package App\Views\public
 * @file    tracking-store.js
 */
var TrackingStore = (function () {
    'use strict';
    var KEY = 'anon_tickets';

    function getCodes() {
        try {
            var raw = localStorage.getItem(KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    function saveCodes(arr) {
        var unique = [];
        var seen = {};
        for (var i = 0; i < arr.length; i++) {
            var code = String(arr[i]).trim().toUpperCase();
            if (code && !seen[code]) {
                seen[code] = true;
                unique.push(code);
            }
        }
        localStorage.setItem(KEY, JSON.stringify(unique));
    }

    function addCode(code) {
        code = String(code).trim().toUpperCase();
        if (!code) return false;
        var codes = getCodes();
        if (codes.indexOf(code) !== -1) return false;
        codes.push(code);
        saveCodes(codes);
        return true;
    }

    function removeCode(code) {
        code = String(code).trim().toUpperCase();
        var codes = getCodes();
        var filtered = [];
        for (var i = 0; i < codes.length; i++) {
            if (codes[i] !== code) filtered.push(codes[i]);
        }
        saveCodes(filtered);
    }

    function hasCode(code) {
        code = String(code).trim().toUpperCase();
        return getCodes().indexOf(code) !== -1;
    }

    function clearAll() {
        localStorage.removeItem(KEY);
    }

    return {
        getCodes: getCodes,
        saveCodes: saveCodes,
        addCode: addCode,
        removeCode: removeCode,
        hasCode: hasCode,
        clearAll: clearAll
    };
})();
