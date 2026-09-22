/**
 * ============================================================================
 * PAGE LOADER
 * ============================================================================
 *
 * 3-layer loading system:
 * 1. Splash preloader (first paint / full navigation)
 * 2. Top progress bar (internal navigation / form GET)
 * 3. AJAX overlay (long requests >300ms debounce)
 *
 * Also provides: DataTable processing theme, button loading helper.
 *
 * Dependencies: jQuery
 * Date: 2026-09-10
 */
(function () {
    'use strict';

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var FADE_MS = reducedMotion ? 0 : 350;
    var AJAX_DEBOUNCE_MS = 300;
    var FAILSAFE_MS = 2500;
    var progressRAF = null;

    // ===========================
    // 1. SPLASH PRELOADER
    // ===========================
    var $loader = $('#sapPageLoader');
    if ($loader.length) {
        function hideSplash() {
            $loader.addClass('sap-loader-fade');
            setTimeout(function () {
                $loader.remove();
            }, FADE_MS + 50);
        }
        if (document.readyState === 'complete') {
            hideSplash();
        } else {
            $(window).on('load', hideSplash);
        }
        // Fail-safe
        setTimeout(function () {
            if ($loader.length) hideSplash();
        }, FAILSAFE_MS);
    }

    // ===========================
    // 2. TOP PROGRESS BAR
    // ===========================
    var $topProgress = $('#sapTopProgress');
    var progressValue = 0;
    var progressTarget = 0;
    var isProgressing = false;

    function animateProgress() {
        if (!isProgressing) return;
        if (progressValue < progressTarget) {
            progressValue += (progressTarget - progressValue) * 0.15;
            if (progressTarget - progressValue < 0.5) progressValue = progressTarget;
        }
        $topProgress.css('transform', 'scaleX(' + (progressValue / 100) + ')');
        if (progressValue < 100) {
            progressRAF = requestAnimationFrame(animateProgress);
        }
    }

    function startProgress() {
        progressValue = 0;
        progressTarget = 20;
        isProgressing = true;
        $topProgress.addClass('active').css('transform', 'scaleX(0)');
        animateProgress();
    }

    function advanceProgress(target) {
        progressTarget = target;
        if (!isProgressing) startProgress();
    }

    function completeProgress() {
        progressTarget = 100;
        isProgressing = true;
        animateProgress();
        setTimeout(function () {
            isProgressing = false;
            progressValue = 0;
            $topProgress.removeClass('active').css('transform', 'scaleX(0)');
            if (progressRAF) cancelAnimationFrame(progressRAF);
        }, 400);
    }

    // ===========================
    // 3. AJAX OVERLAY (debounced)
    // ===========================
    var $overlay = $('#sapAjaxOverlay');
    var activeRequests = 0;
    var overlayTimer = null;
    var overlayVisible = false;

    function showOverlay() {
        if (overlayVisible) return;
        overlayVisible = true;
        $overlay.addClass('active').attr('aria-hidden', 'false');
    }

    function hideOverlay() {
        overlayVisible = false;
        $overlay.removeClass('active').attr('aria-hidden', 'true');
    }

    // Global AJAX hooks
    $(document).on('ajaxSend', function () {
        activeRequests++;
        if (activeRequests === 1) {
            startProgress();
            clearTimeout(overlayTimer);
            overlayTimer = setTimeout(function () {
                if (activeRequests > 0) showOverlay();
            }, AJAX_DEBOUNCE_MS);
        } else if (activeRequests > 1) {
            advanceProgress(Math.min(90, 20 + activeRequests * 15));
        }
    });

    $(document).on('ajaxComplete', function () {
        activeRequests = Math.max(0, activeRequests - 1);
        if (activeRequests === 0) {
            clearTimeout(overlayTimer);
            hideOverlay();
            completeProgress();
        } else {
            advanceProgress(Math.min(95, 20 + activeRequests * 15));
        }
    });

    // ===========================
    // INTERNAL NAVIGATION TRACKING
    // ===========================
    $(document).on('click', 'a[href]', function (e) {
        var href = $(this).attr('href');
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey) return;
        // Same-origin only
        try {
            var url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            // Skip file downloads
            if (/\.\w{2,5}($|\?)/.test(url.pathname) && !url.pathname.endsWith('.php')) return;
        } catch (err) { return; }
        startProgress();
    });

    $(document).on('submit', 'form', function () {
        startProgress();
    });

    $(window).on('beforeunload', function () {
        startProgress();
        advanceProgress(60);
    });

    // ===========================
    // 4. BUTTON LOADING HELPER
    // ===========================
    window.setBtnLoading = function ($btn, loading) {
        if (!$btn || !$btn.length) return;
        if (loading) {
            $btn.data('sap-original-html', $btn.html());
            $btn.attr('disabled', true).attr('aria-disabled', 'true');
            $btn.html('<span class="sap-spinner" aria-hidden="true"></span> Memproses...');
        } else {
            var original = $btn.data('sap-original-html');
            $btn.attr('disabled', false).removeAttr('aria-disabled');
            if (original !== undefined) $btn.html(original);
        }
    };

    // ===========================
    // 5. DATATABLES PROCESSING THEME
    // ===========================
    if ($.fn && $.fn.dataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                processing: '<div class="sap-dt-processing" role="status" aria-live="polite">' +
                    'Memuat data...' +
                    '</div>'
            },
            ajax: {
                timeout: 15000
            }
        });

        // Global safety net: suppress default DataTable alert on AJAX error,
        // show toast + hide processing overlay. Throttled to avoid toast storms.
        $.fn.dataTable.ext.errMode = 'none';
        var _dtErrorLast = 0;
        $(document).on('error.dt', function (e, settings, techNote, message) {
            if (console) console.warn('DataTables [' + (settings.sTableId || '?') + ']: ' + message);
            var now = Date.now();
            if (now - _dtErrorLast < 5000) return;
            _dtErrorLast = now;
            if (typeof toastr !== 'undefined') {
                toastr.error('Gagal memuat data tabel. Silakan muat ulang halaman.', 'Error');
            }
        });
    }

})();
