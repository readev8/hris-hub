<?= $this->extend('template/index') ?>

<?= $this->section('styles') ?>
<style>
.field-error {
    font-size: 12px;
    color: var(--sap-error);
    margin-top: 4px;
    display: none;
}
.field-error.visible { display: block; }

/* Improvement Search Input */
.improvement-search-wrapper {
    position: relative;
}
.improvement-search-input-wrap {
    position: relative;
    display: flex;
    gap: 8px;
}
.improvement-search-input {
    flex: 1;
    padding-left: 40px !important;
    height: 46px !important;
    font-size: 15px !important;
    border-radius: 12px !important;
    background: var(--sap-bg);
    border: 1.5px solid var(--sap-border-input) !important;
    transition: border-color 200ms ease, box-shadow 200ms ease;
    cursor: pointer;
}
.improvement-search-input:focus {
    border-color: var(--sap-brand) !important;
    box-shadow: 0 0 0 3px rgba(0,112,242,0.12) !important;
}
.improvement-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sap-text-muted);
    font-size: 14px;
    z-index: 2;
}
.improvement-search-btn {
    height: 46px;
    padding: 0 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
}
.improvement-hint {
    font-size: 12px;
    color: var(--sap-text-muted);
    margin-top: 6px;
    padding-left: 2px;
}
/* Selected Improvement Card */
.improvement-selected-card {
    margin-top: 12px;
    padding: 14px 16px;
    background: var(--sap-surface);
    border: 1.5px solid var(--sap-brand);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    animation: sapFadeInUp 200ms ease both;
}
.improvement-selected-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}
.improvement-selected-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--sap-brand);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.improvement-selected-text {
    flex: 1;
    min-width: 0;
}
.improvement-selected-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--sap-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.improvement-selected-meta {
    font-size: 12px;
    color: var(--sap-text-muted);
    margin-top: 2px;
}
.improvement-selected-remove {
    background: none;
    border: none;
    color: var(--sap-text-muted);
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    transition: all 150ms ease;
    flex-shrink: 0;
}
.improvement-selected-remove:hover {
    background: var(--sap-error-bg);
    color: var(--sap-error);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="max-width:900px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('blueprints') ?>">Blueprints</a>
        <span class="sep">/</span>
        <span class="active">Create</span>
    </div>

    <h1 class="mb-4">Create Blueprint</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="blueprintForm">
                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-link me-1"></i> Select Improvement
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label" for="improvementDisplay">Improvement <span class="text-danger">*</span></label>
                        <div class="improvement-search-wrapper">
                            <div class="improvement-search-input-wrap">
                                <i class="fas fa-link improvement-search-icon"></i>
                                <input type="text" class="sap-input improvement-search-input" id="improvementDisplay"
                                       placeholder="Click search to select improvement..." readonly>
                                <input type="hidden" name="improvement_id" id="improvementId" value="">
                                <button type="button" class="sap-btn sap-btn-primary improvement-search-btn" onclick="openImprovementModal()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="improvement-hint" id="improvementHint">Click search to find and select an improvement</div>
                            <!-- Selected Improvement Card (hidden by default) -->
                            <div id="improvementSelectedCard" class="improvement-selected-card" style="display:none">
                                <div class="improvement-selected-info">
                                    <div class="improvement-selected-icon"><i class="fas fa-lightbulb"></i></div>
                                    <div class="improvement-selected-text">
                                        <div class="improvement-selected-name" id="selectedImprovementName">-</div>
                                        <div class="improvement-selected-meta" id="selectedImprovementMeta">-</div>
                                    </div>
                                </div>
                                <button type="button" class="improvement-selected-remove" onclick="clearImprovement()" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="field-error" id="error-improvement_id" role="alert"></div>
                    </div>
                </div>

                <div class="mb-4 pb-3" style="border-bottom:1px dashed var(--sap-border)">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-info-circle me-1"></i> Blueprint Details
                    </h5>
                    <div class="mb-3">
                        <label class="sap-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="sap-input" required placeholder="e.g., SSO Login Implementation">
                        <div class="field-error" id="error-name" role="alert"></div>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" class="sap-input" rows="5" placeholder="Describe the blueprint scope and objectives..." style="min-height:120px"></textarea>
                        <div class="field-error" id="error-description" role="alert"></div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-paper-plane"></i> Create Blueprint
                    </button>
                    <a href="<?= site_url('blueprints') ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal fade sap-modal" id="improvementSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Search Improvement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="improvementSearchLoading" class="text-center p-4" style="display:none">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--sap-brand)"></i>
                    <p class="mt-2 mb-0 text-secondary" style="font-size:13px">Loading improvements...</p>
                </div>
                <div id="improvementSearchEmpty" class="text-center p-4" style="display:none">
                    <i class="fas fa-inbox" style="font-size:36px;color:var(--sap-text-muted)"></i>
                    <h5 class="mt-2">No improvements found</h5>
                    <p class="mb-0 text-secondary" style="font-size:13px">All improvements already have blueprints, or try a different search.</p>
                </div>
                <table id="improvementSearchTable" class="sap-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Creator</th>
                            <th>Created</th>
                            <th style="width:80px">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var improvementSearchTable = null;
var selectedImprovementData = null;

function sapBadge(name) {
    var clsMap = {'Draft':'draft','Pending IT Approval':'pending','Pending Dept Approval':'pending','Approved':'approved','Rejected':'rejected'};
    var cls = clsMap[name] || 'closed';
    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

function openImprovementModal() {
    var modal = new bootstrap.Modal(document.getElementById('improvementSearchModal'));
    modal.show();
    $('#improvementSearchLoading').show();
    $('#improvementSearchEmpty').hide();
    $('#improvementSearchTable').hide();

    if (!improvementSearchTable) {
        improvementSearchTable = $('#improvementSearchTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: site_url + '/improvements/ajax-list',
                data: { exclude_blueprint: 1 },
                dataSrc: function(json) {
                    $('#improvementSearchLoading').hide();
                    if (!json.data || json.data.length === 0) {
                        $('#improvementSearchEmpty').show();
                        $('#improvementSearchTable').hide();
                    } else {
                        $('#improvementSearchEmpty').hide();
                        $('#improvementSearchTable').show();
                    }
                    return json.data || [];
                }
            },
            columns: [
                { data: 'name', render: function(d) { return '<span class="fw-medium">' + d + '</span>'; } },
                { data: 'status_name', render: function(d) { return sapBadge(d); } },
                { data: 'priority_name', defaultContent: '-' },
                { data: 'creator_name', defaultContent: '-' },
                { data: 'created_at', render: function(d) { return '<span class="text-muted" style="font-size:13px">' + d + '</span>'; } },
                {
                    data: 'id',
                    orderable: false,
                    render: function(d) {
                        return '<button class="sap-btn sap-btn-primary sap-btn-sm" onclick="selectImprovement(\'' + d + '\', this)"><i class="fas fa-check"></i> Select</button>';
                    }
                }
            ],
            order: [[4, 'desc']],
            language: { searchPlaceholder: 'Search by name...', emptyTable: 'No improvements available' },
            drawCallback: function() {
                $('#improvementSearchLoading').hide();
            }
        });
    } else {
        improvementSearchTable.ajax.reload();
    }
}

function selectImprovement(id, btn) {
    var rowData = improvementSearchTable.row($(btn).closest('tr')).data();
    selectedImprovementData = rowData;
    $('#improvementId').val(id);
    $('#improvementDisplay').val(rowData.name);
    $('#selectedImprovementName').text(rowData.name);
    var meta = [];
    if (rowData.status_name) meta.push('Status: ' + rowData.status_name);
    if (rowData.priority_name) meta.push('Priority: ' + rowData.priority_name);
    $('#selectedImprovementMeta').text(meta.join(' • '));
    $('#improvementSelectedCard').slideDown(200);
    $('#improvementHint').hide();
    $('#error-improvement_id').removeClass('visible').text('');
    bootstrap.Modal.getInstance(document.getElementById('improvementSearchModal')).hide();
}

function clearImprovement() {
    selectedImprovementData = null;
    $('#improvementId').val('');
    $('#improvementDisplay').val('');
    $('#improvementSelectedCard').slideUp(200);
    $('#improvementHint').show();
}

$(function() {
    function clearFieldErrors() {
        $('.field-error').removeClass('visible').text('');
        $('.sap-input, .sap-select').removeClass('is-invalid');
    }

    $('[name="name"], [name="description"]').on('input change', function() {
        $(this).removeClass('is-invalid');
        var fieldName = $(this).attr('name');
        $('#error-' + fieldName).removeClass('visible').text('');
    });

    $('#blueprintForm').on('submit', function(e) {
        e.preventDefault();
        clearFieldErrors();

        if (!$('#improvementId').val()) {
            $('#error-improvement_id').text('Improvement wajib dipilih').addClass('visible');
            toastr.error('Please fix the errors below');
            return;
        }

        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Creating...');

        $.ajax({
            url: site_url + '/blueprints/create',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status && res.redirect) {
                    toastr.success('Blueprint created');
                    setTimeout(function() { window.location.href = res.redirect; }, 500);
                } else {
                    if (res.errors && typeof res.errors === 'object') {
                        Object.keys(res.errors).forEach(function(field) {
                            var input = $('[name="' + field + '"]');
                            if (input.length) {
                                input.addClass('is-invalid');
                                $('#error-' + field).text(res.errors[field]).addClass('visible');
                            }
                        });
                        toastr.error('Please fix the errors below');
                    } else {
                        toastr.error(res.message || 'Failed to create blueprint');
                    }
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Create Blueprint');
                }
            },
            error: function(xhr) {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch(e) {}
                if (res && res.redirect) {
                    window.location.href = res.redirect;
                    return;
                }
                var msg = res && res.message ? res.message : 'Request failed (HTTP ' + xhr.status + ')';
                toastr.error(msg);
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Create Blueprint');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
