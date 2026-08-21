/**
 * ============================================================================
 * Users Add User
 * ============================================================================
 *
 * HRIS user search with dropdown, selected card, and role assignment.
 *
 * Dependencies: jQuery, Bootstrap, Toastr
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const UserAddUser = {

    // ===========================
    // STATE
    // ===========================

    _searchTimer: null,
    _selectedUser: null,

    // ===========================
    // PUBLIC API (called from onclick in view)
    // ===========================

    selectUser: function (el) {
        var $el = $(el);
        var userId = $el.data('id');
        var name = $el.data('name');
        var username = $el.data('username');
        var dept = $el.data('dept');

        if (!userId) {
            toastr.warning('User ID tidak valid');
            return;
        }

        UserAddUser._selectedUser = { user_id: userId, full_name: name, username: username, dept: dept };

        var initial = name ? name.charAt(0).toUpperCase() : '?';
        $('#selectedAvatar').text(initial);
        $('#selectedName').text(name);
        $('#selectedMeta').html('@' + username + ' &middot; ' + dept + ' &middot; <span style="font-family:monospace">#' + userId + '</span>');
        $('#selectedUserCard').slideDown(200);

        $('#hrisSearch').val('');
        $('#hrisDropdown').hide();
        $('#hrisHint').hide();

        $('#addUserId').val(userId);
        $('#addUserName').val(name);
        $('#addUserEmail').val(userId + '@external.local');
        $('#addUserIdDisplay').val(userId);
        $('#addUserNameDisplay').val(name);
        $('#assignCard').slideDown(200);
    },

    addUser: function () {
        var userId = $('#addUserId').val();
        var roleId = $('#addRoleId').val();
        var fullName = $('#addUserName').val();
        var email = $('#addUserEmail').val();

        if (!roleId) {
            toastr.warning('Pilih role terlebih dahulu');
            return;
        }

        var $btn = $('#btnAddUser');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Adding...');

        $.post(site_url + '/users/ajax-add-by-userid', {
            user_id: userId,
            full_name: fullName,
            email: email,
            role_id: roleId
        }, function (res) {
            if (res.status) {
                toastr.success('User berhasil ditambahkan');
                window.location.href = site_url + '/users';
            } else {
                toastr.error(res.data && res.data.message ? res.data.message : 'Gagal menambahkan user');
            }
        }).fail(function () {
            toastr.error('Gagal menghubungi server');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Add User');
        });
    },

    resetForm: function () {
        UserAddUser._selectedUser = null;
        $('#hrisSearch').val('');
        $('#selectedUserCard').hide();
        $('#assignCard').hide();
        $('#addRoleId').val('');
        $('#hrisHint').show();
        $('#hrisDropdown').hide();
    },

    // ===========================
    // EVENTS
    // ===========================

    init: function () {
        var self = UserAddUser;

        $('#hrisSearch').on('input', function () {
            var q = $(this).val().trim();
            clearTimeout(self._searchTimer);

            if (q.length < 2) {
                $('#hrisDropdown').hide();
                $('#hrisHint').show();
                return;
            }

            $('#hrisHint').hide();
            $('#hrisSpinner').show();
            $('#hrisDropdown').hide();

            self._searchTimer = setTimeout(function () {
                $.ajax({
                    url: site_url + '/users/ajax-search-hris',
                    method: 'GET',
                    data: { q: q },
                    success: function (res) {
                        var data = res.data && res.data.result ? res.data.result : (res.data || []);
                        var html = '';

                        if (!Array.isArray(data) || data.length === 0) {
                            html = '<div class="hris-dropdown-empty"><i class="fas fa-user-slash"></i>Tidak ditemukan hasil untuk "' + q + '"</div>';
                        } else {
                            data.forEach(function (u, i) {
                                var initial = (u.Nama || '?').charAt(0).toUpperCase();
                                var dept = u.Dept || '-';
                                html += '<div class="hris-dropdown-item" data-idx="' + i + '" onclick="UserAddUser.selectUser(this)" ' +
                                    'data-id="' + (u.pegawaiid || '') + '" ' +
                                    'data-name="' + (u.Nama || '') + '" ' +
                                    'data-username="' + (u.Username || '') + '" ' +
                                    'data-dept="' + dept + '">' +
                                    '<div class="hris-dropdown-avatar">' + initial + '</div>' +
                                    '<div class="hris-dropdown-info">' +
                                    '<div class="hris-dropdown-name">' + (u.Nama || '-') + '</div>' +
                                    '<div class="hris-dropdown-meta">@' + (u.Username || '-') + ' &middot; ' + dept + '</div>' +
                                    '</div>' +
                                    '<div class="hris-dropdown-id">#' + (u.pegawaiid || '-') + '</div>' +
                                    '</div>';
                            });
                        }

                        $('#hrisDropdown').html(html).show();
                        $('#hrisSpinner').hide();
                    },
                    error: function () {
                        $('#hrisDropdown').html('<div class="hris-dropdown-empty"><i class="fas fa-exclamation-circle"></i>Gagal menghubungi server</div>').show();
                        $('#hrisSpinner').hide();
                    }
                });
            }, 300);
        });

        $('#hrisSearch').on('keydown', function (e) {
            var $items = $('#hrisDropdown .hris-dropdown-item');
            if (!$items.length) return;

            var $active = $('#hrisDropdown .hris-dropdown-item.active');
            var idx = $active.length ? $active.index() : -1;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                $items.removeClass('active');
                idx = Math.min(idx + 1, $items.length - 1);
                $items.eq(idx).addClass('active');
                if ($items.eq(idx)[0]) $items.eq(idx)[0].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                $items.removeClass('active');
                idx = Math.max(idx - 1, 0);
                $items.eq(idx).addClass('active');
                if ($items.eq(idx)[0]) $items.eq(idx)[0].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter' && idx >= 0) {
                e.preventDefault();
                self.selectUser($items[idx]);
            } else if (e.key === 'Escape') {
                $('#hrisDropdown').hide();
            }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.hris-search-wrapper').length) {
                $('#hrisDropdown').hide();
            }
        });

        $('#btnRemoveUser').on('click', function () {
            self.resetForm();
        });
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    UserAddUser.init();
});

// ===========================
// EXPORTS
// ===========================

window.UserAddUser = UserAddUser;

// Backward compatibility for onclick handlers in views
window.selectUser = function (el) { UserAddUser.selectUser(el); };
window.addUser = function () { UserAddUser.addUser(); };
window.resetForm = function () { UserAddUser.resetForm(); };
