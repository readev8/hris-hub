/**
 * ============================================================================
 * Users Add User — Single-page minimalis
 * ============================================================================
 *
 * HRIS user search → select → role → submit.
 * Delegated click handler, keyboard nav, inline validation,
 * pre-check duplicate, server-driven role hints, dark-mode safe.
 *
 * Dependencies: jQuery, Toastr
 * Date: 2026-09-10
 */

var UserAdd = {

    // ── State ────────────────────────────────────────────────────────────────
    _timer: null,
    _user: null,
    _exists: false,
    _idx: -1,
    _rolesMap: {},

    // ── Helpers ──────────────────────────────────────────────────────────────

    esc: function (s) {
        if (s == null) return '';
        var m = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return String(s).replace(/[&<>"']/g, function (c) { return m[c]; });
    },

    // ── Rendering ────────────────────────────────────────────────────────────

    _skel: function () {
        var h = '';
        for (var i = 0; i < 3; i++) {
            h += '<div class="uw-sk-row">'
                + '<div class="uw-sk-avatar"></div>'
                + '<div class="uw-sk-lines"><div class="uw-sk-line"></div><div class="uw-sk-line"></div></div>'
                + '</div>';
        }
        return h;
    },

    _render: function (data, q) {
        var self = this;
        var html = '';
        var n = 0;

        if (!Array.isArray(data) || data.length === 0) {
            html = '<div class="uw-empty"><i class="fas fa-user-slash"></i>'
                + 'Tidak ditemukan hasil untuk "' + self.esc(q) + '"'
                + '<div class="uw-empty-suggest">Coba potongan nama lain, username, atau departemen</div>'
                + '</div>';
        } else {
            n = data.length;
            data.forEach(function (u, i) {
                var init = u.Nama ? u.Nama.charAt(0).toUpperCase() : '?';
                var dept = u.Dept || '-';
                var uid  = u.pegawaiid || '';
                var uname = u.Username || '';

                html += '<button type="button" class="uw-item" id="uw-opt-' + i + '" role="option" aria-selected="false"'
                    + ' data-idx="' + i + '"'
                    + ' data-id="' + self.esc(uid) + '"'
                    + ' data-name="' + self.esc(u.Nama || '') + '"'
                    + ' data-username="' + self.esc(uname) + '"'
                    + ' data-dept="' + self.esc(dept) + '">'
                    + '<div class="uw-item-avatar">' + self.esc(init) + '</div>'
                    + '<div class="uw-item-info">'
                    + '<div class="uw-item-name">' + self.esc(u.Nama || '-') + '</div>'
                    + '<div class="uw-item-meta">@' + self.esc(uname) + ' &middot; ' + self.esc(dept) + '</div>'
                    + '</div>'
                    + '<div class="uw-item-id">#' + self.esc(uid) + '</div>'
                    + '</button>';
            });
        }

        $('#uwPanel').html(html).show();
        $('#uwCount').text(n > 0 ? n + ' hasil ditemukan' : (q.length >= 2 ? 'Tidak ada hasil' : ''));
    },

    _setActive: function ($items, idx) {
        var self = this;
        $items.removeClass('uw-item--active').attr('aria-selected', 'false');
        self._idx = idx;
        var $it = $items.eq(idx);
        $it.addClass('uw-item--active').attr('aria-selected', 'true');
        if ($it[0]) {
            $it[0].scrollIntoView({ block: 'nearest' });
            $('#uwSearch').attr('aria-activedescendant', $it.attr('id'));
        }
    },

    // ── Select / deselect ────────────────────────────────────────────────────

    select: function (el) {
        var self = this;
        var $el = $(el);
        var userId   = $el.data('id');
        var name     = $el.data('name');
        var username = $el.data('username');
        var dept     = $el.data('dept');

        if (!userId) { toastr.warning('User ID tidak valid'); return; }

        self._user = { user_id: userId, full_name: name, username: username, dept: dept };

        $('#uwChipAvatar').text(name ? name.charAt(0).toUpperCase() : '?');
        $('#uwChipName').text(name);
        $('#uwChipUsername').text('@' + username);
        $('#uwChipDept').text(dept);
        $('#uwChipId').text('#' + userId);
        $('#uwChip').show();
        $('#uwDivider').show();
        $('#uwRole').show();
        $('#uwDivider2').show();
        $('#uwSummary').show();
        $('#uwActions').show();

        $('#uwUserId').val(userId);
        $('#uwUserName').val(name);
        $('#uwUserEmail').val(userId + '@external.local');

        $('#uwSearch').val('');
        $('#uwPanel').hide();
        $('#uwHint').show();
        $('#uwBtnClear').hide();
        $('#uwCount').text('');
        self._idx = -1;

        self._exists = false;
        $('#uwExists').hide();
        self._updateSummary();
        self._toggleBtn();

        setTimeout(function () { $('#uwRoleId').focus(); }, 100);

        self._checkExists(userId);
    },

    deselect: function () {
        var self = this;
        self._user = null;
        self._exists = false;
        self._idx = -1;
        $('#uwChip').hide();
        $('#uwDivider').hide();
        $('#uwRole').hide();
        $('#uwDivider2').hide();
        $('#uwSummary').hide();
        $('#uwActions').hide();
        $('#uwRoleId').val('').removeClass('is-invalid');
        $('#uwRoleHint').text('');
        $('#uwRoleError').text('').removeClass('is-visible');
        $('#uwExists').hide();
        $('#uwUserId').val('');
        $('#uwUserName').val('');
        $('#uwUserEmail').val('');
        self._toggleBtn();
    },

    // ── Summary ──────────────────────────────────────────────────────────────

    _updateSummary: function () {
        var u = this._user;
        if (!u) return;
        var roleId = $('#uwRoleId').val();
        var roleSlug = '';
        var roleName = '—';
        if (roleId && this._rolesMap[roleId]) {
            roleSlug = this._rolesMap[roleId].slug || '';
            roleName = this._rolesMap[roleId].name || '—';
        }
        $('#uwSumId').text(u.user_id || '—');
        $('#uwSumName').text(u.full_name || '—');
        $('#uwSumEmail').text((u.user_id || '') + '@external.local');
        var badge = '<span class="sap-badge ' + this.esc(roleSlug) + '">' + this.esc(roleName) + '</span>';
        $('#uwSumRole').html(badge);
    },

    _toggleBtn: function () {
        var ok = this._user && $('#uwRoleId').val() && !this._exists;
        $('#uwBtnSubmit').prop('disabled', !ok);
    },

    // ── Pre-check ────────────────────────────────────────────────────────────

    _checkExists: function (userId) {
        var self = this;
        $.ajax({
            url: site_url + '/users/ajax-lookup-user',
            method: 'POST',
            data: { user_id: userId },
            success: function (res) {
                var row = res.data && res.data.result ? res.data.result : null;
                if (res.status && row && row.already_added) {
                    self._exists = true;
                    $('#uwExistsText').html(
                        self.esc(row.full_name || row.Nama || 'Pengguna')
                        + ' sudah terdaftar di sistem. '
                        + '<a href="' + site_url + '/users">Lihat daftar pengguna</a>'
                    );
                    $('#uwExists').show();
                }
                self._toggleBtn();
            }
        });
    },

    // ── Submit ───────────────────────────────────────────────────────────────

    submit: function () {
        var self = this;
        var userId   = $('#uwUserId').val();
        var roleId   = $('#uwRoleId').val();
        var fullName = $('#uwUserName').val();
        var email    = $('#uwUserEmail').val();

        if (self._exists) {
            toastr.warning('Pengguna sudah terdaftar di sistem');
            return;
        }
        if (!roleId) {
            $('#uwRoleId').addClass('is-invalid');
            $('#uwRoleError').text('Pilih role terlebih dahulu').addClass('is-visible');
            $('#uwRoleId').focus();
            return;
        }

        var $btn = $('#uwBtnSubmit');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menambahkan...');

        $.post(site_url + '/users/ajax-add-by-userid', {
            user_id: userId, full_name: fullName, email: email, role_id: roleId
        }, function (res) {
            if (res.status) {
                toastr.success('Pengguna berhasil ditambahkan');
                window.location.href = site_url + '/users';
            } else {
                toastr.error(res.data && res.data.message ? res.data.message : 'Gagal menambahkan pengguna');
            }
        }).fail(function () {
            toastr.error('Gagal menghubungi server');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Tambah Pengguna');
        });
    },

    // ── Reset / init ─────────────────────────────────────────────────────────

    reset: function () {
        var self = this;
        self.deselect();
        $('#uwSearch').val('');
        $('#uwHint').show();
        $('#uwPanel').hide();
        $('#uwBtnClear').hide();
        $('#uwCount').text('');
        $('#uwSearch').attr('aria-expanded', 'false').attr('aria-activedescendant', '');
        self._idx = -1;
        $('#uwDivider').hide();
        $('#uwRole').hide();
        $('#uwDivider2').hide();
        $('#uwSummary').hide();
        $('#uwActions').hide();
        $('#uwSummary').find('.uw-summary-value').text('—');
        $('#uwSumRole').html('—');
    },

    init: function () {
        var self = UserAdd;

        // Build roles map
        if (typeof UW_ROLES !== 'undefined') {
            UW_ROLES.forEach(function (r) { self._rolesMap[String(r.raw_id)] = r; });
        }

        // ── Search input ────────────────────────────────────────────────
        $('#uwSearch').on('input', function () {
            var q = $(this).val().trim();
            clearTimeout(self._timer);

            if (q.length < 2) {
                $('#uwPanel').hide();
                $('#uwHint').text('Ketik minimal 2 karakter');
                $('#uwCount').text('');
                $('#uwBtnClear').toggle(q.length > 0);
                $('#uwSearch').attr('aria-expanded', 'false').attr('aria-activedescendant', '');
                self._idx = -1;
                return;
            }

            $('#uwHint').hide();
            $('#uwSpinner').show();
            $('#uwPanel').html(self._skel()).show();
            $('#uwBtnClear').show();
            self._idx = -1;

            self._timer = setTimeout(function () {
                $.ajax({
                    url: site_url + '/users/ajax-search-hris',
                    method: 'GET',
                    data: { q: q },
                    success: function (res) {
                        var data = res.data && res.data.result ? res.data.result : (res.data || []);
                        self._render(data, q);
                        $('#uwSpinner').hide();
                        $('#uwSearch').attr('aria-expanded', 'true');
                    },
                    error: function () {
                        $('#uwPanel').html(
                            '<div class="uw-empty"><i class="fas fa-exclamation-circle"></i>Gagal menghubungi server</div>'
                        ).show();
                        $('#uwSpinner').hide();
                        $('#uwSearch').attr('aria-expanded', 'false');
                    }
                });
            }, 300);
        });

        // ── Keyboard nav ────────────────────────────────────────────────
        $('#uwSearch').on('keydown', function (e) {
            var $items = $('#uwPanel .uw-item');
            if (!$items.length && e.key !== 'Escape') return;
            var idx = self._idx;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                idx = Math.min(idx + 1, $items.length - 1);
                self._setActive($items, idx);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                idx = Math.max(idx - 1, 0);
                self._setActive($items, idx);
            } else if (e.key === 'Enter' && idx >= 0) {
                e.preventDefault();
                self.select($items[idx]);
            } else if (e.key === 'Escape') {
                $('#uwPanel').hide();
                $('#uwSearch').attr('aria-expanded', 'false').attr('aria-activedescendant', '');
                self._idx = -1;
            }
        });

        // ── Clear button ────────────────────────────────────────────────
        $('#uwBtnClear').on('click', function () {
            $('#uwSearch').val('').focus();
            $('#uwPanel').hide();
            $('#uwHint').text('Ketik minimal 2 karakter');
            $('#uwBtnClear').hide();
            $('#uwCount').text('');
            $('#uwSearch').attr('aria-expanded', 'false').attr('aria-activedescendant', '');
            self._idx = -1;
        });

        // ── Click outside panel ─────────────────────────────────────────
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.uw-search').length) {
                $('#uwPanel').hide();
                $('#uwSearch').attr('aria-expanded', 'false');
            }
        });

        // ── Delegated item click (fixes C1 — items are dynamic) ────────
        $(document).on('click', '.uw-item', function () {
            self.select(this);
        });

        // ── Remove chip ─────────────────────────────────────────────────
        $('#uwBtnRemove').on('click', function () {
            self.deselect();
            self.reset();
        });

        // ── Role change ─────────────────────────────────────────────────
        $('#uwRoleId').on('change', function () {
            var v = $(this).val();
            if (v) {
                $(this).removeClass('is-invalid');
                $('#uwRoleError').text('').removeClass('is-visible');
                var desc = self._rolesMap[v] && self._rolesMap[v].description ? self._rolesMap[v].description : '';
                $('#uwRoleHint').text(desc);
            } else {
                $('#uwRoleHint').text('');
            }
            self._updateSummary();
            self._toggleBtn();
        });
    }
};

// ── Auto-init ────────────────────────────────────────────────────────────────

$(function () {
    UserAdd.init();
});

// ── Backward compat for onclick ──────────────────────────────────────────────
window.UserAdd = UserAdd;
