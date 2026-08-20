<!-- Modal: Add Module to Canvas -->
<div class="modal fade sap-modal" id="addModuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    <i class="fas fa-diagram-project me-2" style="color:var(--sap-brand)"></i>Add Module to Canvas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="moduleSelect">
                        Select Module <span class="text-danger">*</span>
                    </label>
                    <select id="moduleSelect" class="form-select" style="width:100%">
                        <option value="">Search and select a module...</option>
                    </select>
                </div>
                <div id="selectedModuleInfo" class="d-none">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-cube" style="color:var(--sap-brand);font-size:14px"></i>
                        <span class="fw-semibold" id="selectedModuleName" style="font-size:14px"></span>
                    </div>
                    <div class="text-muted small ms-4" id="selectedModuleProject"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="sap-btn sap-btn-primary" id="btnAddModule" disabled>
                    <i class="fas fa-plus me-1"></i> Add to Canvas
                </button>
            </div>
        </div>
    </div>
</div>
