<?php
/**
 * ============================================================================
 * MONITORING — MODAL DETAIL BARIS
 * ============================================================================
 *
 * Description: Modal generik key-value untuk drill-down satu baris data.
 *              Diisi via JS dari GET /monitoring/ajax-detail.
 */
?>
<div class="modal fade mon-modal-glass" id="monDetailModal" tabindex="-1" aria-labelledby="monDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="monDetailTitle">Detail Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <table class="sap-table" id="mon-detail-table">
                    <tbody><tr><td class="text-center text-muted">Memuat…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
