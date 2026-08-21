<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--sap-card-bg);border:1px solid var(--sap-border);border-radius:16px">
            <div class="modal-header" style="border-bottom:1px solid var(--sap-border);padding:20px 24px">
                <h5 class="modal-title fw-semibold" style="font-size:16px">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:24px">
                <input type="hidden" id="editId">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">User ID</label>
                        <input type="text" class="form-control" id="editUserId" readonly style="background:var(--sap-surface);opacity:0.7">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="editRoleId">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= esc($r['raw_id']) ?>"><?= esc($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editFullName">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="editEmail">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--sap-border);padding:16px 24px;display:flex;gap:8px;justify-content:flex-end">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="sap-btn sap-btn-primary" id="btnSaveEdit" onclick="saveEdit()">
                    <i class="fas fa-check me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
