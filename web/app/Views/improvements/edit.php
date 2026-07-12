<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:900px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('improvements') ?>">Improvements</a>
        <span class="sep">/</span>
        <a href="<?= site_url('improvements/' . $token) ?>"><?= esc($improvement['name'] ?? '') ?></a>
        <span class="sep">/</span>
        <span class="active">Edit</span>
    </div>

    <h1 class="mb-4">Edit Improvement</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="improvementForm">
                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-info-circle me-1"></i> General Information
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="sap-input" required value="<?= esc($improvement['name'] ?? '') ?>" placeholder="e.g., Implement SSO Login">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="sap-input" rows="5" required placeholder="Describe the improvement in detail..." style="min-height:120px"><?= esc($improvement['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-chart-line me-1"></i> Business Details
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Business Case <span class="text-danger">*</span></label>
                        <textarea name="business_case" class="sap-input" rows="3" required placeholder="Why is this improvement needed?" style="min-height:80px"><?= esc($improvement['business_case'] ?? '') ?></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="sap-label">Category</label>
                            <select name="category" class="sap-select">
                                <option value="">Select category...</option>
                                <option value="UI/UX" <?= ($improvement['category'] ?? '') == 'UI/UX' ? 'selected' : '' ?>>UI/UX</option>
                                <option value="Performance" <?= ($improvement['category'] ?? '') == 'Performance' ? 'selected' : '' ?>>Performance</option>
                                <option value="Security" <?= ($improvement['category'] ?? '') == 'Security' ? 'selected' : '' ?>>Security</option>
                                <option value="New Feature" <?= ($improvement['category'] ?? '') == 'New Feature' ? 'selected' : '' ?>>New Feature</option>
                                <option value="Integration" <?= ($improvement['category'] ?? '') == 'Integration' ? 'selected' : '' ?>>Integration</option>
                                <option value="Other" <?= ($improvement['category'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Priority</label>
                            <select name="priority" class="sap-select">
                                <option value="0" <?= ($improvement['priority'] ?? '') == 0 ? 'selected' : '' ?>>Low</option>
                                <option value="1" <?= ($improvement['priority'] ?? '') == 1 ? 'selected' : '' ?>>Medium</option>
                                <option value="2" <?= ($improvement['priority'] ?? '') == 2 ? 'selected' : '' ?>>High</option>
                                <option value="3" <?= ($improvement['priority'] ?? '') == 3 ? 'selected' : '' ?>>Critical</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-project-diagram me-1"></i> Scope & Assignment
                    </h5>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="sap-label">Project</label>
                            <select class="sap-select" id="projectSelect">
                                <option value="">Select Project...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="sap-label">Module</label>
                            <select class="sap-select" id="moduleSelect" disabled>
                                <option value="">Select Module...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="sap-label">Page</label>
                            <select class="sap-select" id="pageSelect" disabled>
                                <option value="">Select Page...</option>
                            </select>
                            <input type="hidden" name="page_id" id="pageIdValue" value="<?= esc($improvement['page_id'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="sap-label">Assignee / PIC</label>
                            <select name="assigned_to" class="sap-select" id="assigneeSelect">
                                <option value="">Select user...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Target Date</label>
                            <input type="date" name="target_date" class="sap-input" value="<?= esc($improvement['target_date'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="<?= site_url('improvements/' . $token) ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var token = '<?= esc($token) ?>';

$(function() {
    $.get(site_url + '/users/ajax-list', function(res) {
        var users = res.data || [];
        var html = '<option value="">Select user...</option>';
        var currentAssignee = '<?= esc($improvement['assigned_to'] ?? '') ?>';
        for (var i = 0; i < users.length; i++) {
            var selected = users[i].id === currentAssignee ? 'selected' : '';
            html += '<option value="' + users[i].id + '" ' + selected + '>' + (users[i].full_name || users[i].name) + '</option>';
        }
        $('#assigneeSelect').html(html);
    });

    function loadProjects() {
        $('#projectSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        $.ajax({
            url: site_url + '/master-projects/active',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                if (!Array.isArray(res)) { $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false); return; }
                var html = '<option value="">Select Project...</option>';
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
                }
                $('#projectSelect').html(html).prop('disabled', false);
                $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            },
            error: function() {
                toastr.error('Failed to load projects');
                $('#projectSelect').html('<option value="">Select Project...</option>').prop('disabled', false);
            }
        });
    }
    loadProjects();

    var activeModuleReq = null;
    var activePageReq = null;

    $('#projectSelect').on('change', function() {
        var pid = $(this).val();
        if (!pid) {
            $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', true);
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            $('#pageIdValue').val('');
            return;
        }
        if (activeModuleReq) activeModuleReq.abort();
        $('#moduleSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        activeModuleReq = $.ajax({
            url: site_url + '/master-projects/' + pid + '/modules',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                if (!Array.isArray(res)) { $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', false); return; }
                var html = '<option value="">Select Module...</option>';
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
                }
                $('#moduleSelect').html(html).prop('disabled', false);
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
                $('#pageIdValue').val('');
            },
            error: function() {
                toastr.error('Failed to load modules');
                $('#moduleSelect').html('<option value="">Select Module...</option>').prop('disabled', false);
            }
        });
    });

    $('#moduleSelect').on('change', function() {
        var mid = $(this).val();
        if (!mid) {
            $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', true);
            $('#pageIdValue').val('');
            return;
        }
        if (activePageReq) activePageReq.abort();
        $('#pageSelect').prop('disabled', true).html('<option value="">Loading...</option>');
        activePageReq = $.ajax({
            url: site_url + '/modules/' + mid + '/pages',
            type: 'GET',
            timeout: 10000,
            success: function(res) {
                if (!Array.isArray(res)) { $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', false); return; }
                var html = '<option value="">Select Page...</option>';
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + res[i].name + '</option>';
                }
                $('#pageSelect').html(html).prop('disabled', false);
            },
            error: function() {
                toastr.error('Failed to load pages');
                $('#pageSelect').html('<option value="">Select Page...</option>').prop('disabled', false);
            }
        });
    });

    $('#pageSelect').on('change', function() {
        $('#pageIdValue').val($(this).val());
    });

    $('#improvementForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Updating...');

        $.ajax({
            url: site_url + '/improvements/' + token + '/update',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    toastr.success('Improvement updated');
                    setTimeout(function() { window.location.href = site_url + '/improvements/' + token; }, 500);
                } else {
                    toastr.error(res.message || 'Failed to update');
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update');
                }
            },
            error: function() {
                toastr.error('Request failed');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
