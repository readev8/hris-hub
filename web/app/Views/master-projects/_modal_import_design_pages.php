<!-- Import Design Pages Modal -->
<div class="modal fade sap-modal" id="importDesignPagesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Import Design Pages</h5>
                    <small class="text-muted" id="importModalSubtitle">Loading available design pages...</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="import-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="importSearchInput" class="import-search-input" placeholder="Search by page name, module, or blueprint...">
                </div>
                <div id="importSelectAllWrap" class="import-select-all-wrap" style="display:none">
                    <label class="import-select-all-label">
                        <input type="checkbox" id="importSelectAll" class="form-check-input">
                        <span id="importSelectAllText">Select All</span>
                    </label>
                    <span class="import-count-badge" id="importCountBadge">0 selected</span>
                </div>
                <div id="importDesignPagesLoading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--sap-brand)"></i>
                    <p class="mt-2 mb-0 text-secondary" style="font-size:13px">Loading available design pages...</p>
                </div>
                <div id="importDesignPagesEmpty" class="text-center py-4" style="display:none">
                    <i class="fas fa-inbox" style="font-size:36px;color:var(--sap-text-muted)"></i>
                    <h5 class="mt-2">No design pages available</h5>
                    <p class="mb-0 text-secondary" style="font-size:13px">Assign blueprint modules to this module first, then create design pages.</p>
                </div>
                <div id="importDesignPagesList" class="import-design-list" style="max-height:45vh;overflow-y:auto"></div>
                <div id="importDesignPagesSkipped" class="import-skipped-info" style="display:none"></div>
            </div>
            <div class="modal-footer">
                <span class="import-selected-info" id="importSelectedInfo">0 pages selected</span>
                <div class="d-flex gap-2">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="sap-btn sap-btn-primary" id="importBtn" onclick="ModuleDetail.importSelectedDesignPages()" disabled>
                        <i class="fas fa-file-import"></i> Import Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
