/**
 * ============================================================================
 * APP SHELL
 * ============================================================================
 *
 * Bootstrap handler global untuk shell UI:
 * dark mode toggle, sidebar, menu aktif, counter animation,
 * navbar scroll, dan global 403 refresh.
 *
 * Dependencies: jQuery, Toastr
 * Date: 2026-09-10
 */
$(function () {
    // ===========================
    // DARK MODE TOGGLE
    // ===========================
    var $html = $(document.documentElement);
    var darkModeToggle = $('#darkModeToggle');

    // Theme already restored in <head> inline script (anti-flash).
    // Sync icon state on DOM ready.
    if ($html.hasClass('dark-mode')) {
        darkModeToggle.find('i').removeClass('fa-moon').addClass('fa-sun');
    }

    darkModeToggle.on('click', function () {
        $html.toggleClass('dark-mode');
        var isDark = $html.hasClass('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        // Sync Bootstrap 5.3 native dark mode
        if (isDark) {
            $html[0].dataset.bsTheme = 'dark';
        } else {
            delete $html[0].dataset.bsTheme;
        }
        $(this).find('i').toggleClass('fa-moon fa-sun');
        // Notify chart/theme subscribers
        window.dispatchEvent(new CustomEvent('sap:theme-changed', { detail: { isDark: isDark } }));
    });

    // ===========================
    // SIDEBAR TOGGLE
    // ===========================
    $('[data-toggle="sidebar"]').on('click', function () {
        $('body').toggleClass('sidebar-open');
    });
    $('#sidebarOverlay').on('click', function () {
        $('body').removeClass('sidebar-open');
    });
    $('.dropdown-toggle').dropdown();

    $('#sidebarToggle').on('click', function () {
        $html.toggleClass('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', $html.hasClass('sidebar-collapsed'));
    });

    // ===========================
    // ACTIVE MENU ITEM
    // ===========================
    var path = window.location.pathname;
    $('.sidebar-nav li a').each(function () {
        var href = $(this).attr('href');
        if (href && path.indexOf(href) === 0 && href !== '/') {
            $(this).addClass('active');
        } else if (href === '/' || href === site_url + '/' || href === site_url + '/dashboard') {
            if (path === '/' || path === '/dashboard' || path === site_url + '/' || path === site_url + '/dashboard') {
                $(this).addClass('active');
            }
        }
    });

    // ===========================
    // COUNTER ANIMATION
    // ===========================
    $('.sap-count-up').each(function () {
        var $el = $(this);
        var target = parseInt($el.text().replace(/\D/g, '')) || 0;
        if (target > 0) {
            $el.text('0');
            var duration = 600;
            var start = performance.now();
            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                $el.text(Math.floor(eased * target));
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }
    });

    // ===========================
    // NAVBAR SCROLL EFFECT
    // ===========================
    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 10) {
            $('.shell-bar').addClass('scrolled');
        } else {
            $('.shell-bar').removeClass('scrolled');
        }
    });
});

// ===========================
// GLOBAL 403 HANDLER
// ===========================
$(document).on('ajaxError', function (event, jqXHR, settings, thrownError) {
    if (jqXHR.status === 403) {
        var msg = 'Anda tidak memiliki izin untuk aksi ini';
        try {
            var res = JSON.parse(jqXHR.responseText);
            if (res.data && res.data.message) msg = res.data.message;
        } catch (e) {}

        if (!window._permRefreshing) {
            window._permRefreshing = true;
            toastr.error(msg);
            $.post(site_url + '/auth/refresh-permissions', function (res) {
                if (res.status) {
                    userPermissions = res.permissions;
                    toastr.info('Permissions diperbarui. Memuat ulang...');
                    setTimeout(function () { location.reload(); }, 1500);
                }
            }).always(function () {
                window._permRefreshing = false;
            });
        }
    }
});
