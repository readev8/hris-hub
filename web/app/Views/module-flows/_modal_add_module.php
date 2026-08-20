<!-- Modal: Add Module to Canvas -->
<div class="modal fade" id="addModuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--sap-card-bg);border:1px solid var(--sap-border);border-radius:16px">
            <div class="modal-header" style="border-bottom:1px solid var(--sap-border);padding:20px 24px">
                <h5 class="modal-title fw-semibold" style="font-size:16px">
                    <i class="fas fa-diagram-project me-2" style="color:var(--sap-brand)"></i>Add Module to Canvas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:24px">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="moduleSelect">
                        Select Module <span class="text-danger">*</span>
                    </label>
                    <select id="moduleSelect" class="form-select" style="width:100%">
                        <option value="">Search and select a module...</option>
                    </select>
                </div>
                <div id="selectedModuleInfo" style="display:none;padding:14px 16px;background:var(--sap-surface);border:1px solid var(--sap-border);border-radius:var(--sap-radius)">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-cube" style="color:var(--sap-brand);font-size:14px"></i>
                        <span class="fw-semibold" id="selectedModuleName" style="font-size:14px"></span>
                    </div>
                    <div style="color:var(--sap-text-secondary);font-size:12px;margin-top:4px;padding-left:22px" id="selectedModuleProject"></div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--sap-border);padding:16px 24px;display:flex;gap:8px;justify-content:flex-end">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="sap-btn sap-btn-primary" id="btnAddModule" disabled>
                    <i class="fas fa-plus me-1"></i> Add to Canvas
                </button>
            </div>
        </div>
    </div>
</div>
