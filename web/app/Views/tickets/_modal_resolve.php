<div class="modal fade" id="resolveModal" tabindex="-1" aria-labelledby="resolveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sap-card" style="border:1px solid var(--sap-border);box-shadow:0 8px 32px rgba(0,0,0,0.15)">
            <div class="modal-header" style="border-bottom:1px solid var(--sap-border-light);padding:16px 20px">
                <h5 class="modal-title" id="resolveModalLabel" style="font-size:16px;font-weight:600;display:flex;align-items:center;gap:8px">
                    <i class="fas fa-check-circle" style="color:var(--sap-success);font-size:18px"></i>
                    Resolve Ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:20px">
                <div style="margin-bottom:12px;padding:10px 14px;background:var(--sap-background);border-radius:var(--sap-radius-sm);border-left:3px solid var(--sap-brand)">
                    <span style="font-size:12px;color:var(--sap-text-secondary)">Resolving:</span>
                    <strong style="font-size:14px;display:block;margin-top:2px;color:var(--sap-text)"><?= esc($ticket['title']) ?></strong>
                </div>
                <form id="resolveForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="sap-label">Resolution Summary <span class="text-danger">*</span></label>
                        <textarea class="sap-input" id="resolveNote" name="resolution_note" rows="4" required placeholder="Describe what was done to resolve this ticket, including steps taken, root cause, and any notes for future reference..." style="min-height:100px"></textarea>
                        <div class="sap-hint">Wajib diisi — jelaskan langkah penyelesaian, akar masalah, dan catatan untuk referensi masa depan.</div>
                    </div>
                    <div class="mb-0">
                        <label class="sap-label">Attachments <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 5 files)</span></label>
                        <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:24px;text-align:center;transition:all var(--sap-transition);cursor:pointer" id="resolveDropzone">
                            <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:var(--sap-text-muted);display:block;margin-bottom:8px"></i>
                            <p class="mb-2 text-secondary" style="font-size:13px">Drop files here or</p>
                            <label class="sap-btn sap-btn-secondary sap-btn-sm" style="cursor:pointer" onclick="event.stopPropagation()">
                                <i class="fas fa-paperclip"></i> Choose Files
                                <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,.xlsx,.xls,.docx,.doc,.pptx,.ppt,.csv" multiple hidden id="resolveFileInput">
                            </label>
                            <p class="mb-0 mt-1 text-muted" style="font-size:11px">Images, PDF, Excel, Word, PPT — max 5MB each</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2" id="resolvePreview"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--sap-border-light);padding:12px 20px;display:flex;justify-content:flex-end;gap:8px">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="sap-btn sap-btn-success" id="resolveSubmitBtn" onclick="submitResolve()">
                    <i class="fas fa-check-double"></i> Resolve Ticket
                </button>
            </div>
        </div>
    </div>
</div>
