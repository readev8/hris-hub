<!-- Detail User Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--sap-card-bg);border:1px solid var(--sap-border);border-radius:16px">
            <div class="modal-header" style="border-bottom:1px solid var(--sap-border);padding:20px 24px">
                <h5 class="modal-title fw-semibold" style="font-size:16px">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:24px">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div id="detailAvatar" class="avatar-circle" style="width:56px;height:56px;font-size:22px;background:var(--sap-brand);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600">?</div>
                    <div>
                        <h5 id="detailName" class="mb-0 fw-semibold" style="font-size:17px">-</h5>
                        <span id="detailRole" class="sap-badge developer" style="font-size:12px"><span class="badge-dot"></span>-</span>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">User ID</small>
                        <span id="detailUserId" class="fw-medium" style="font-size:14px">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">Email</small>
                        <span id="detailEmail" class="fw-medium" style="font-size:14px">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">Status</small>
                        <span id="detailStatus">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block mb-1" style="font-size:12px">Created At</small>
                        <span id="detailCreated" class="fw-medium" style="font-size:14px">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--sap-border);padding:16px 24px">
                <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
