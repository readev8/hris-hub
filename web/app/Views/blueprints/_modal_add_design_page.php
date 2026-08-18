<div class="modal fade sap-modal" id="designPageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-palette me-2"></i>Add Design Page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="designPageForm">
                <div class="modal-body">
                    <input type="hidden" name="design_page_id" id="designPageFormId">
                    <input type="hidden" name="blueprint_token" value="<?= esc($token, 'attr') ?>">
                    <div class="mb-3">
                        <label class="sap-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="sap-input" id="designPageTitleInput" required placeholder="e.g., Login Page">
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Description</label>
                        <textarea name="description" class="sap-input" rows="4" id="designPageDescInput" placeholder="Describe the design page..." style="min-height:100px"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="sap-label">Images <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 10, JPG/PNG/WebP)</span></label>
                        <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:16px;text-align:center;cursor:pointer" id="designPageDropzone">
                            <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:var(--sap-text-muted);display:block;margin-bottom:4px"></i>
                            <p class="mb-0 text-secondary" style="font-size:12px">Drop images here or click to browse</p>
                            <button type="button" class="sap-btn sap-btn-secondary sap-btn-sm mt-2" onclick="event.stopPropagation(); $(this).closest('.mb-3').find('input[type=file]').click();">
                                <i class="fas fa-image"></i> Pilih Gambar
                            </button>
                            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple hidden>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2" id="designPageFilePreview"></div>
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
