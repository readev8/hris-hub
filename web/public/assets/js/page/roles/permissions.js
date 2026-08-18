/**
 * ============================================================================
 * Roles Permissions
 * ============================================================================
 *
 * Permission matrix for a role with column toggles and save.
 *
 * Dependencies: jQuery, Bootstrap, Toastr
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const RolePermissions = {

    // ===========================
    // STATE
    // ===========================

    _roleData: {},
    _modulesData: [],
    _permissionsMap: {},

    // ===========================
    // HELPERS
    // ===========================

    escHtml: function (s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    // ===========================
    // UI
    // ===========================

    initPermissionsMap: function () {
        var self = RolePermissions;
        var perms = self._roleData.permissions || [];
        for (var i = 0; i < perms.length; i++) {
            self._permissionsMap[perms[i].module_slug] = {
                can_view: parseInt(perms[i].can_view) || 0,
                can_create: parseInt(perms[i].can_create) || 0,
                can_update: parseInt(perms[i].can_update) || 0,
                can_delete: parseInt(perms[i].can_delete) || 0,
                can_approve: parseInt(perms[i].can_approve) || 0,
            };
        }
    },

    renderPermissionsTable: function () {
        var self = RolePermissions;
        var html = '';
        for (var i = 0; i < self._modulesData.length; i++) {
            var m = self._modulesData[i];
            var perm = self._permissionsMap[m.slug] || { can_view: 0, can_create: 0, can_update: 0, can_delete: 0, can_approve: 0 };

            html += '<tr>';
            html += '<td class="fw-medium"><i class="' + self.escHtml(m.icon || 'fas fa-cube') + '" style="margin-right:8px;color:var(--sap-text-muted)"></i>' + self.escHtml(m.name) + '</td>';
            html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_view"' + (perm.can_view ? ' checked' : '') + '></td>';
            html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_create"' + (perm.can_create ? ' checked' : '') + '></td>';
            html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_update"' + (perm.can_update ? ' checked' : '') + '></td>';
            html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_delete"' + (perm.can_delete ? ' checked' : '') + '></td>';
            html += '<td style="text-align:center"><input type="checkbox" class="form-check-input perm-check" data-module="' + m.slug + '" data-action="can_approve"' + (perm.can_approve ? ' checked' : '') + '></td>';
            html += '</tr>';
        }
        $('#permissionsBody').html(html);
    },

    // ===========================
    // ACTIONS
    // ===========================

    toggleColumn: function (action, checked) {
        $('[data-action="' + action + '"]').prop('checked', checked);
    },

    collectPermissions: function () {
        var self = RolePermissions;
        var perms = [];
        for (var i = 0; i < self._modulesData.length; i++) {
            var m = self._modulesData[i];
            var modPerms = { module_slug: m.slug, can_view: 0, can_create: 0, can_update: 0, can_delete: 0, can_approve: 0 };

            $('[data-module="' + m.slug + '"]').each(function () {
                var action = $(this).data('action');
                if ($(this).is(':checked')) modPerms[action] = 1;
            });

            perms.push(modPerms);
        }
        return perms;
    },

    saveAllPermissions: function () {
        var self = RolePermissions;
        var perms = self.collectPermissions();
        $.post(site_url + '/roles/' + self._roleData.id + '/permissions', { permissions: JSON.stringify(perms) }, function (res) {
            if (res.status) {
                toastr.success('Permissions saved');
            } else {
                toastr.error(res.data && res.data.message ? res.data.message : 'Failed to save permissions');
            }
        }).fail(function (xhr) {
            toastr.error('Failed to save permissions (HTTP ' + xhr.status + ')');
        });
    },

    // ===========================
    // AUTO-INITIALIZATION
    // ===========================

    init: function () {
        var self = RolePermissions;
        var pageData = window.PageData || {};
        self._roleData = pageData.role || {};
        self._modulesData = pageData.modules || [];

        self.initPermissionsMap();
        self.renderPermissionsTable();
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    RolePermissions.init();
});

// ===========================
// EXPORTS
// ===========================

window.RolePermissions = RolePermissions;

// Backward compatibility for onclick handlers in views
window.toggleColumn = function (action, checked) { RolePermissions.toggleColumn(action, checked); };
window.saveAllPermissions = function () { RolePermissions.saveAllPermissions(); };
