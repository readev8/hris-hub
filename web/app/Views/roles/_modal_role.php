<!-- Role Modal -->
<div class="modal fade sap-modal" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalTitle">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="roleForm">
                <input type="hidden" name="edit_id" id="roleEditId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="sap-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="roleName" class="sap-input" required placeholder="e.g., QA Engineer">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="roleSlug" class="sap-input" required placeholder="e.g., qa_engineer" pattern="[a-z0-9_-]+">
                        <span class="sap-hint">Lowercase letters, numbers, dashes, underscores only</span>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" id="roleDesc" class="sap-input" rows="2"></textarea>
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
