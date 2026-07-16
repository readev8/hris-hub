/**
 * ============================================================================
 * Users Add User
 * ============================================================================
 *
 * Description: HRIS user search with dropdown, selected card, and role assignment
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// STATE
// ===========================

var searchTimer = null;
var selectedUser = null;

// ===========================
// PUBLIC API (called from onclick in view)
// ===========================

function selectUser(el) {
    var $el = $(el);
    var userId = $el.data('id');
    var name = $el.data('name');
    var username = $el.data('username');
    var dept = $el.data('dept');

    if (!userId) {
        toastr.warning('User ID tidak valid');
        return;
    }

    selectedUser = { user_id: userId, full_name: name, username: username, dept: dept };

    var initial = name ? name.charAt(0).toUpperCase() : '?';
    $('#selectedAvatar').text(initial);
    $('#selectedName').text(name);
    $('#selectedMeta').html('@' + username + ' &middot; ' + dept + ' &middot; <span style="font-family:monospace">#' + userId + '</span>');
    $('#selectedUserCard').slideDown(200);

    $('#hrisSearch').val('');
    $('#hrisDropdown').hide();
    $('#hrisHint').hide();

    // Prepare Step 2
    $('#addUserId').val(userId);
    $('#addUserName').val(name);
    $('#addUserEmail').val(userId + '@external.local');
    $('#addUserIdDisplay').val(userId);
    $('#addUserNameDisplay').val(name);
    $('#assignCard').slideDown(200);
}

function addUser() {
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

    $.ajax({
        url: site_url + '/users/ajax-add-by-userid',
        method: 'POST',
        data: {
            user_id: userId,
            full_name: fullName,
            email: email,
            role_id: roleId
        },
        success: function(res) {
            if (res.status) {
                toastr.success('User berhasil ditambahkan');
                window.location.href = site_url + '/users';
            } else {
                toastr.error(res.data?.message || 'Gagal menambahkan user');
            }
        },
        error: function() {
            toastr.error('Gagal menghubungi server');
        },
        complete: function() {
            $btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Add User');
        }
    });
}

function resetForm() {
    selectedUser = null;
    $('#hrisSearch').val('');
    $('#selectedUserCard').hide();
    $('#assignCard').hide();
    $('#addRoleId').val('');
    $('#hrisHint').show();
    $('#hrisDropdown').hide();
}

// ===========================
// INITIALIZATION
// ===========================

$(function() {
    $('#hrisSearch').on('input', function() {
        var q = $(this).val().trim();
        clearTimeout(searchTimer);

        if (q.length < 2) {
            $('#hrisDropdown').hide();
            $('#hrisHint').show();
            return;
        }

        $('#hrisHint').hide();
        $('#hrisSpinner').show();
        $('#hrisDropdown').hide();

        searchTimer = setTimeout(function() {
            $.ajax({
                url: site_url + '/users/ajax-search-hris',
                method: 'GET',
                data: { q: q },
                success: function(res) {
                    var data = res.data?.result || res.data || [];
                    var html = '';

                    if (!Array.isArray(data) || data.length === 0) {
                        html = '<div class="hris-dropdown-empty"><i class="fas fa-user-slash"></i>Tidak ditemukan hasil untuk "' + q + '"</div>';
                    } else {
                        data.forEach(function(u, i) {
                            var initial = (u.Nama || '?').charAt(0).toUpperCase();
                            var dept = u.Dept || '-';
                            html += '<div class="hris-dropdown-item" data-idx="' + i + '" onclick="selectUser(this)" ' +
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
                error: function() {
                    $('#hrisDropdown').html('<div class="hris-dropdown-empty"><i class="fas fa-exclamation-circle"></i>Gagal menghubungi server</div>').show();
                    $('#hrisSpinner').hide();
                }
            });
        }, 300);
    });

    $('#hrisSearch').on('keydown', function(e) {
        var $items = $('#hrisDropdown .hris-dropdown-item');
        if (!$items.length) return;

        var $active = $('#hrisDropdown .hris-dropdown-item.active');
        var idx = $active.length ? $active.index() : -1;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            $items.removeClass('active');
            idx = Math.min(idx + 1, $items.length - 1);
            $items.eq(idx).addClass('active');
            $items.eq(idx)[0]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            $items.removeClass('active');
            idx = Math.max(idx - 1, 0);
            $items.eq(idx).addClass('active');
            $items.eq(idx)[0]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter' && idx >= 0) {
            e.preventDefault();
            selectUser($items[idx]);
        } else if (e.key === 'Escape') {
            $('#hrisDropdown').hide();
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.hris-search-wrapper').length) {
            $('#hrisDropdown').hide();
        }
    });

    $('#btnRemoveUser').on('click', function() {
        selectedUser = null;
        $('#selectedUserCard').slideUp(200);
        $('#assignCard').slideUp(200);
        $('#addRoleId').val('');
        $('#hrisHint').show();
    });
});
