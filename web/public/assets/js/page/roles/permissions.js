/**
 * ============================================================================
 * Roles Permissions
 * ============================================================================
 *
 * Description: Permission matrix for a role with column toggles and save
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// HELPERS
// ===========================

function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ===========================
// STATE
// ===========================

var roleData = window.PageData ? window.PageData.role : {};
var modulesData = window.PageData ? window.PageData.modules : [];
var permissionsMap = {};

// ===========================
// PUBLIC API (called from onclick in view)
// ===========================

function toggleColumn(action, checked) {
    $('[data-action="' + action + '"]').prop('checked', checked);
}

function saveAllPermissions() {
    var perms = collectPermissions();
    $.post(site_url + '/roles/' + roleData.id + '/permissions', { permissions: JSON.stringify(perms) }, function(res) {
        if (res.status) {
            toastr.success('Permissions saved');
        } else {
            toastr.error(res.data?.message || 'Failed to save permissions');
        }
    }).fail(function(xhr) {
        toastr.error('Gagal menyimpan permissions (HTTP ' + xhr.status + ')');
    });
}

// ===========================
// HELPERS
// ===========================

function initPermissionsMap() {
    var perms = roleData.permissions || [];
    for (var i = 0; i < perms.length; i++) {
        permissionsMap[perms[i].module_slug] = {
            can_view: parseInt(perms[i].can_view) || 0,
            can_create: parseInt(perms[i].can_create) || 0,
            can_update: parseInt(perms[i].can_update) || 0,
            can_delete: parseInt(perms[i].can_delete) || 0,
            can_approve: parseInt(perms[i].can_approve) || 0,
        };
    }
}

function renderPermissionsTable() {
    var html = '';
    for (var i = 0; i < modulesData.length; i++) {
        var m = modulesData[i];
        var perm = permissionsMap[m.slug] || { can_view: 0, can_create: 0, can_update: 0, can_delete: 0, can_approve: 0 };

        html += '<tr>';
        html += '<td class="fw-medium"><i class="' + escHtml(m.icon || 'fas fa-cube') + '" style="margin-right:8px;color:var(--sap-text-muted)"></i>' + escHtml(m.name) + '</td>';
        html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_view"' + (perm.can_view ? ' checked' : '') + '></td>';
        html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_create"' + (perm.can_create ? ' checked' : '') + '></td>';
        html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_update"' + (perm.can_update ? ' checked' : '') + '></td>';
        html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_delete"' + (perm.can_delete ? ' checked' : '') + '></td>';
        html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_approve"' + (perm.can_approve ? ' checked' : '') + '></td>';
        html += '</tr>';
    }
    $('#permissionsBody').html(html);
}

function collectPermissions() {
    var perms = [];
    for (var i = 0; i < modulesData.length; i++) {
        var m = modulesData[i];
        var modPerms = { module_slug: m.slug, can_view: 0, can_create: 0, can_update: 0, can_delete: 0, can_approve: 0 };

        $('[data-module="' + m.slug + '"]').each(function() {
            var action = $(this).data('action');
            if ($(this).is(':checked')) modPerms[action] = 1;
        });

        perms.push(modPerms);
    }
    return perms;
}

// ===========================
// INITIALIZATION
// ===========================

$(function() {
    initPermissionsMap();
    renderPermissionsTable();
});
