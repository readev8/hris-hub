<?= $this->extend('template/index') ?>
<?= $this->section('styles') ?>
<style>
.spec-table { font-size: 12px; }
.spec-table th { background: var(--sap-background); font-weight: 600; white-space: nowrap; }
.spec-table td { vertical-align: middle; }
.spec-table input, .spec-table select, .spec-table textarea {
    width: 100%; padding: 4px 8px; border: 1px solid var(--sap-border);
    border-radius: var(--sap-radius); font-size: 12px; background: var(--sap-bg); color: var(--sap-text);
}
.spec-table input:focus, .spec-table select:focus, .spec-table textarea:focus {
    outline: none; border-color: var(--sap-brand);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid" style="max-width:1400px">
    <?php if (!$designPage): ?>
        <div class="sap-empty">
            <i class="fas fa-exclamation-triangle" style="color:var(--sap-error)"></i>
            <h4>Design page not found</h4>
            <p>The design page you're looking for doesn't exist or has been removed.</p>
            <a href="<?= site_url('blueprints') ?>" class="sap-btn sap-btn-secondary mt-3">Back</a>
        </div>
    <?php else: ?>
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('blueprints') ?>">Blueprints</a>
        <span class="sep">/</span>
        <a href="<?= site_url('blueprints/' . ($designPage['blueprint_id_encrypted'] ?? '')) ?>"><?= esc($designPage['blueprint_name'] ?? '') ?></a>
        <span class="sep">/</span>
        <span class="active"><?= esc($designPage['title'] ?? '') ?> — Specifications</span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 style="font-size:22px" class="mb-1"><i class="fas fa-list-alt me-2 text-secondary"></i><?= esc($designPage['title'] ?? '') ?> — Specifications</h1>
            <p class="text-secondary mb-0" style="font-size:13px">
                Module: <?= esc($designPage['module_name'] ?? '') ?>
                <span class="mx-1">·</span>
                <span id="specCountBadge"><?= count($designPage['page_specifications'] ?? []) ?> fields</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <?php if (has_permission('blueprints', 'can_update')): ?>
            <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="showAddSpec()">
                <i class="fas fa-plus"></i> Add Specification
            </button>
            <?php endif; ?>
            <a href="<?= site_url('blueprints/' . ($designPage['blueprint_id_encrypted'] ?? '')) ?>" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-arrow-left"></i> Back to Blueprint</a>
        </div>
    </div>

    <div class="sap-card">
        <div class="sap-card-body">
            <div id="specsContainer">
                <?php if (empty($designPage['page_specifications'])): ?>
                <div class="sap-empty" style="padding:40px">
                    <i class="fas fa-list-alt" style="font-size:36px"></i>
                    <h4>No specifications</h4>
                    <p>Add page specifications for this design page.</p>
                </div>
                <?php else: ?>
                <div style="overflow-x:auto">
                    <table class="sap-table spec-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Field Name</th>
                                <th>Data</th>
                                <th>Objective</th>
                                <th>Initial Data</th>
                                <th>Condition</th>
                                <th>Validation</th>
                                <th>I/D</th>
                                <th>Datatype</th>
                                <th>Control</th>
                                <th>UX</th>
                                <?php if (has_permission('blueprints', 'can_update')): ?>
                                <th style="width:80px">Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($designPage['page_specifications'] as $sp): ?>
                            <tr data-spec-id="<?= esc($sp['id_encrypted'] ?? $sp['id'], 'attr') ?>">
                                <td><input type="text" value="<?= esc($sp['field_name'] ?? '', 'attr') ?>" data-field="field_name" onchange="updateSpecField(this)"></td>
                                <td><input type="text" value="<?= esc($sp['data'] ?? '', 'attr') ?>" data-field="data" onchange="updateSpecField(this)"></td>
                                <td><input type="text" value="<?= esc($sp['objective'] ?? '', 'attr') ?>" data-field="objective" onchange="updateSpecField(this)"></td>
                                <td><input type="text" value="<?= esc($sp['initial_data'] ?? '', 'attr') ?>" data-field="initial_data" onchange="updateSpecField(this)"></td>
                                <td><input type="text" value="<?= esc($sp['condition'] ?? '', 'attr') ?>" data-field="condition" onchange="updateSpecField(this)"></td>
                                <td><input type="text" value="<?= esc($sp['validation'] ?? '', 'attr') ?>" data-field="validation" onchange="updateSpecField(this)"></td>
                                <td>
                                    <select data-field="input_display" onchange="updateSpecField(this)">
                                        <option value="Input"<?= ($sp['input_display'] ?? '') === 'Input' ? ' selected' : '' ?>>Input</option>
                                        <option value="Display"<?= ($sp['input_display'] ?? '') === 'Display' ? ' selected' : '' ?>>Display</option>
                                        <option value="Both"<?= ($sp['input_display'] ?? '') === 'Both' ? ' selected' : '' ?>>Both</option>
                                    </select>
                                </td>
                                <td>
                                    <select data-field="datatype" onchange="updateSpecField(this)">
                                        <?php foreach (['text'=>'Text','number'=>'Number','date'=>'Date','datetime'=>'DateTime','time'=>'Time','image'=>'Image','pdf'=>'PDF','excel'=>'Excel'] as $val => $lbl): ?>
                                        <option value="<?= $val ?>"<?= ($sp['datatype'] ?? '') === $val ? ' selected' : '' ?>><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select data-field="control_type" onchange="updateSpecField(this)">
                                        <?php foreach (['text'=>'Text','password'=>'Password','date'=>'Date','datetime'=>'DateTime','combobox'=>'Combobox','radiobutton'=>'Radio Button','checkbox'=>'Checkbox','multipleselect'=>'Multiple Select','uploadfile'=>'Upload File'] as $val => $lbl): ?>
                                        <option value="<?= $val ?>"<?= ($sp['control_type'] ?? '') === $val ? ' selected' : '' ?>><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><textarea data-field="ux" onchange="updateSpecField(this)" rows="1" style="min-height:30px"><?= esc($sp['ux'] ?? '') ?></textarea></td>
                                <?php if (has_permission('blueprints', 'can_update')): ?>
                                <td>
                                    <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="deleteSpec(this)"><i class="fas fa-trash"></i></button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<!-- Add Specification Modal -->
<div class="modal fade sap-modal" id="specModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-list-alt me-2"></i>Add Page Specification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="specForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="sap-label">Field Name <span class="text-danger">*</span></label>
                            <input type="text" name="field_name" class="sap-input" id="specFieldNameInput" required placeholder="e.g., Username">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Data</label>
                            <input type="text" name="data" class="sap-input" id="specDataInput" placeholder="e.g., varchar(100)">
                        </div>
                        <div class="col-md-12">
                            <label class="sap-label">Objective</label>
                            <input type="text" name="objective" class="sap-input" id="specObjectiveInput" placeholder="e.g., User identification">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Initial Data</label>
                            <input type="text" name="initial_data" class="sap-input" id="specInitialDataInput" placeholder="e.g., Empty">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Condition</label>
                            <input type="text" name="condition" class="sap-input" id="specConditionInput" placeholder="e.g., Required for login">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Validation</label>
                            <input type="text" name="validation" class="sap-input" id="specValidationInput" placeholder="e.g., Min 6 chars">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Input/Display</label>
                            <select name="input_display" class="sap-select" id="specInputDisplayInput">
                                <option value="Input">Input</option>
                                <option value="Display">Display</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Datatype</label>
                            <select name="datatype" class="sap-select" id="specDatatypeInput">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="datetime">DateTime</option>
                                <option value="time">Time</option>
                                <option value="image">Image</option>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Control Type</label>
                            <select name="control_type" class="sap-select" id="specControlTypeInput">
                                <option value="text">Text</option>
                                <option value="password">Password</option>
                                <option value="date">Date</option>
                                <option value="datetime">DateTime</option>
                                <option value="combobox">Combobox</option>
                                <option value="radiobutton">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="multipleselect">Multiple Select</option>
                                <option value="uploadfile">Upload File</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="sap-label">UX</label>
                            <textarea name="ux" class="sap-input" rows="2" id="specUxInput" placeholder="UX description or notes..." style="min-height:60px"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--sap-border)">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var designPageToken = '<?= esc($token) ?>';
var designPageData = <?= json_encode($designPage) ?>;
var designPageSpecs = <?= json_encode($designPage['page_specifications'] ?? []) ?>;

function escHtml(str) {
    return $('<div>').text(str || '').html();
}

function updateSpecField(el) {
    var row = $(el).closest('tr');
    var specId = row.data('spec-id');
    var field = $(el).data('field');
    var value = $(el).val();
    var data = {};
    data[field] = value;
    $.ajax({
        url: site_url + '/blueprints/page-specifications/' + specId + '/update',
        type: 'POST',
        data: data,
        success: function(res) {
            if (res.status) {
                toastr.success('Updated');
            } else {
                toastr.error(res.message || 'Failed to update');
            }
        },
        error: function() { toastr.error('Update failed'); }
    });
}

function deleteSpec(el) {
    var row = $(el).closest('tr');
    var specId = row.data('spec-id');
    Swal.fire({
        title: 'Delete Specification?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(site_url + '/blueprints/page-specifications/' + specId + '/delete', {}, function(res) {
                if (res.status) {
                    toastr.success('Specification deleted');
                    row.fadeOut(300, function() { $(this).remove(); updateSpecCount(); });
                } else {
                    toastr.error(res.message || 'Failed');
                }
            });
        }
    });
}

function updateSpecCount() {
    var count = $('#specsContainer tbody tr').length;
    $('#specCountBadge').text(count + ' field' + (count !== 1 ? 's' : ''));
    if (count === 0) {
        $('#specsContainer').html('<div class="sap-empty" style="padding:40px"><i class="fas fa-list-alt" style="font-size:36px"></i><h4>No specifications</h4><p>Add page specifications for this design page.</p></div>');
    }
}

function showAddSpec() {
    $('#specForm')[0].reset();
    $('#specModal').modal('show');
}

$('#specForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: site_url + '/blueprints/design-pages/' + designPageToken + '/page-specifications',
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if (res.status) {
                toastr.success('Specification added');
                $('#specModal').modal('hide');
                location.reload();
            } else {
                toastr.error(res.message || 'Failed');
            }
        },
        error: function() { toastr.error('Request failed'); }
    });
});
</script>
<?= $this->endSection() ?>
