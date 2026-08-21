<!-- Module Modal -->
<div class="modal fade sap-modal" id="moduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moduleModalTitle">Add Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="moduleForm">
                <input type="hidden" name="edit_id" id="moduleEditId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="sap-label">Module Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="moduleName" class="sap-input" required placeholder="e.g., Authentication">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" id="moduleDesc" class="sap-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-check"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
