<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Add User</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Search for an HRIS user and assign a role</p>
    </div>
    <a href="<?= site_url('/users') ?>" class="sap-btn sap-btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Users
    </a>
</div>

<!-- Step 1: Search HRIS User -->
<div class="sap-card mb-4">
    <div class="sap-card-header">
        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Step 1 — Find HRIS User</h5>
    </div>
    <div class="sap-card-body">
        <div class="hris-search-wrapper">
            <label class="form-label fw-semibold">Search by name, username, or department</label>
            <div class="hris-search-input-wrap">
                <i class="fas fa-search hris-search-icon"></i>
                <input type="text" class="form-control hris-search-input" id="hrisSearch" 
                       placeholder="Type at least 2 characters..." autocomplete="off">
                <div class="hris-search-spinner" id="hrisSpinner" style="display:none">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
            <div class="hris-search-hint" id="hrisHint">Ketik minimal 2 karakter untuk mencari</div>
            <div class="hris-search-dropdown" id="hrisDropdown" style="display:none"></div>
        </div>

        <!-- Selected User Card -->
        <div id="selectedUserCard" class="hris-selected-card" style="display:none">
            <div class="hris-selected-header">
                <div class="hris-selected-avatar" id="selectedAvatar">?</div>
                <div class="hris-selected-info">
                    <div class="hris-selected-name" id="selectedName">-</div>
                    <div class="hris-selected-meta" id="selectedMeta">-</div>
                </div>
                <button type="button" class="hris-selected-remove" id="btnRemoveUser" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Step 2: Assign Role -->
<div class="sap-card mb-4" id="assignCard" style="display:none">
    <div class="sap-card-header">
        <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i>Step 2 — Assign Role</h5>
    </div>
    <div class="sap-card-body">
        <input type="hidden" id="addUserId">
        <input type="hidden" id="addUserName">
        <input type="hidden" id="addUserEmail">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">User ID</label>
                <input type="text" class="form-control" id="addUserIdDisplay" readonly style="background:var(--sap-surface)">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" class="form-control" id="addUserNameDisplay" readonly style="background:var(--sap-surface)">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                <select class="form-select" id="addRoleId">
                    <option value="">Select Role</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= esc($r['raw_id']) ?>"><?= esc($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="button" class="sap-btn sap-btn-primary" id="btnAddUser" onclick="addUser()">
                <i class="fas fa-plus me-1"></i> Add User
            </button>
            <button type="button" class="sap-btn sap-btn-secondary" onclick="resetForm()">
                <i class="fas fa-times me-1"></i> Cancel
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
/* Form elements */
.form-control, .form-select {
    border-radius: var(--sap-radius);
    border: 1.5px solid var(--sap-border-input);
    font-size: 14px;
    padding: 8px 12px;
    height: 42px;
    transition: border-color 200ms ease, box-shadow 200ms ease;
}
.form-control:focus, .form-select:focus {
    border-color: var(--sap-brand);
    box-shadow: 0 0 0 3px rgba(0,112,242,0.12);
    outline: none;
}
.form-control[readonly] {
    background: var(--sap-surface) !important;
    opacity: 0.7;
}
.form-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--sap-text);
    margin-bottom: 6px;
}

/* HRIS Search */
.hris-search-wrapper {
    position: relative;
}
.hris-search-input-wrap {
    position: relative;
}
.hris-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sap-text-muted);
    font-size: 14px;
    z-index: 2;
}
.hris-search-input {
    padding-left: 40px !important;
    padding-right: 40px !important;
    height: 46px !important;
    font-size: 15px !important;
    border-radius: 12px !important;
    background: var(--sap-bg);
    border: 1.5px solid var(--sap-border-input) !important;
    transition: border-color 200ms ease, box-shadow 200ms ease;
}
.hris-search-input:focus {
    border-color: var(--sap-brand) !important;
    box-shadow: 0 0 0 3px rgba(0,112,242,0.12) !important;
}
.hris-search-spinner {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sap-brand);
    font-size: 14px;
}
.hris-search-hint {
    font-size: 12px;
    color: var(--sap-text-muted);
    margin-top: 6px;
    padding-left: 2px;
}

/* Dropdown */
.hris-search-dropdown {
    position: absolute;
    top: calc(100% - 8px);
    left: 0;
    right: 0;
    background: var(--sap-card-bg);
    border: 1px solid var(--sap-border);
    border-radius: 12px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    max-height: 360px;
    overflow-y: auto;
    z-index: 100;
    padding: 6px;
}
.hris-dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    cursor: pointer;
    transition: background 150ms ease;
}
.hris-dropdown-item:hover,
.hris-dropdown-item.active {
    background: var(--sap-surface);
}
.hris-dropdown-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--sap-brand);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 15px;
    flex-shrink: 0;
}
.hris-dropdown-info {
    flex: 1;
    min-width: 0;
}
.hris-dropdown-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--sap-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.hris-dropdown-meta {
    font-size: 12px;
    color: var(--sap-text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 1px;
}
.hris-dropdown-id {
    font-size: 11px;
    color: var(--sap-text-muted);
    background: var(--sap-surface);
    padding: 2px 8px;
    border-radius: 6px;
    flex-shrink: 0;
    font-family: monospace;
}
.hris-dropdown-empty {
    padding: 24px;
    text-align: center;
    color: var(--sap-text-muted);
    font-size: 13px;
}
.hris-dropdown-empty i {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
    opacity: 0.4;
}

/* Selected card */
.hris-selected-card {
    margin-top: 16px;
    background: linear-gradient(135deg, rgba(0,112,242,0.04), rgba(0,112,242,0.08));
    border: 1px solid rgba(0,112,242,0.2);
    border-radius: 12px;
    padding: 16px;
}
.hris-selected-header {
    display: flex;
    align-items: center;
    gap: 12px;
}
.hris-selected-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--sap-brand);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
    flex-shrink: 0;
}
.hris-selected-info {
    flex: 1;
    min-width: 0;
}
.hris-selected-name {
    font-weight: 600;
    font-size: 16px;
    color: var(--sap-text);
}
.hris-selected-meta {
    font-size: 13px;
    color: var(--sap-text-muted);
    margin-top: 2px;
}
.hris-selected-remove {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: var(--sap-text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 150ms ease;
    flex-shrink: 0;
}
.hris-selected-remove:hover {
    background: #fef1f1;
    color: #ee5448;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var searchTimer = null;
var selectedUser = null;

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

$('#btnRemoveUser').on('click', function() {
    selectedUser = null;
    $('#selectedUserCard').slideUp(200);
    $('#assignCard').slideUp(200);
    $('#addRoleId').val('');
    $('#hrisHint').show();
});

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
</script>
<?= $this->endSection() ?>
