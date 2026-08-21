<!-- Page Modal -->
<div class="modal fade sap-modal" id="pageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageModalTitle">Add Page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="pageForm">
                <input type="hidden" name="module_id" id="pageModuleId" value="<?= $module['id'] ?>">
                <input type="hidden" name="edit_id" id="pageEditId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="sap-label">Page Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="pageName" class="sap-input" required placeholder="e.g., Login Page">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">URL Path</label>
                        <input type="text" name="url_path" id="pageUrl" class="sap-input" placeholder="e.g., /auth/login">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" id="pageDesc" class="sap-input" rows="2"></textarea>
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
