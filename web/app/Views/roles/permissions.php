<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('dashboard') ?>">Dashboard</a>
        <span class="sep">/</span>
        <a href="<?= site_url('roles') ?>">Roles</a>
        <span class="sep">/</span>
        <span class="active">Permissions: <?= esc($role['name'] ?? '') ?></span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1">Permissions</h1>
            <p class="text-secondary mb-0" style="font-size:13px">
                Role: <strong><?= esc($role['name'] ?? '') ?></strong>
                <?php if (!empty($role['description'])): ?> &middot; <?= esc($role['description']) ?><?php endif; ?>
            </p>
        </div>
        <button class="sap-btn sap-btn-primary" onclick="saveAllPermissions()">
            <i class="fas fa-save"></i> Save Permissions
        </button>
    </div>

    <div class="sap-card">
        <div class="sap-card-body p-0">
            <table class="sap-table mb-0" id="permissionsTable" style="width:100%">
                <thead>
                    <tr>
                        <th style="width:200px">Module</th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>View</span>
                                <input type="checkbox" id="toggleAllCanView" class="form-check-input" onchange="toggleColumn('can_view', this.checked)">
                            </div>
                        </th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>Create</span>
                                <input type="checkbox" id="toggleAllCanCreate" class="form-check-input" onchange="toggleColumn('can_create', this.checked)">
                            </div>
                        </th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>Update</span>
                                <input type="checkbox" id="toggleAllCanUpdate" class="form-check-input" onchange="toggleColumn('can_update', this.checked)">
                            </div>
                        </th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>Delete</span>
                                <input type="checkbox" id="toggleAllCanDelete" class="form-check-input" onchange="toggleColumn('can_delete', this.checked)">
                            </div>
                        </th>
                        <th style="text-align:center;width:100px">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                                <span>Approve</span>
                                <input type="checkbox" id="toggleAllCanApprove" class="form-check-input" onchange="toggleColumn('can_approve', this.checked)">
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody id="permissionsBody">
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var roleData = <?= json_encode($role ?? []) ?>;
var modulesData = <?= json_encode($modules ?? []) ?>;
var permissionsMap = {};

function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

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

function toggleColumn(action, checked) {
    $('[data-action="' + action + '"]').prop('checked', checked);
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

function saveAllPermissions() {
    var perms = collectPermissions();
    $.post(site_url + '/roles/' + roleData.id + '/permissions', { permissions: JSON.stringify(perms) }, function(res) {
        if (res.status) {
            toastr.success('Permissions saved');
        } else {
            toastr.error(res.data?.message || 'Failed to save permissions');
        }
    });
}

$(function() {
    initPermissionsMap();
    renderPermissionsTable();
});
</script>
<?= $this->endSection() ?>
