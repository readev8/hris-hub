<!-- Modal: Add Module to Canvas -->
<div class="modal fade" id="addModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-diagram-project me-2"></i>Add Module to Canvas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Select Module <span class="text-danger">*</span></label>
                    <select id="moduleSelect" class="form-select" style="width:100%">
                        <option value="">Search and select a module...</option>
                    </select>
                </div>
                <div id="selectedModuleInfo" class="p-3 bg-light rounded" style="display:none">
                    <div class="fw-semibold" id="selectedModuleName"></div>
                    <div class="text-muted small" id="selectedModuleProject"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnAddModule" disabled>
                    <i class="fas fa-plus"></i> Add to Canvas
                </button>
            </div>
        </div>
    </div>
</div>
