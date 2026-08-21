<!-- Blueprint Module Assignment Modal -->
<div class="modal fade sap-modal" id="blueprintModuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-link me-2"></i>Assign Blueprint Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <input type="hidden" id="assignModuleId">
                <div id="blueprintModulesLoading" class="text-center p-4">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--sap-brand)"></i>
                    <p class="mt-2 mb-0 text-secondary" style="font-size:13px">Loading blueprint modules...</p>
                </div>
                <div id="blueprintModulesEmpty" class="text-center p-4" style="display:none">
                    <i class="fas fa-inbox" style="font-size:36px;color:var(--sap-text-muted)"></i>
                    <h5 class="mt-2">No blueprint modules found</h5>
                    <p class="mb-0 text-secondary" style="font-size:13px">Create a blueprint with modules first.</p>
                </div>
                <div id="blueprintModulesList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
