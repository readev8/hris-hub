<?php
/**
 * ============================================================================
 * PUBLIC TICKETS - CREATE
 * ============================================================================
 *
 * Description: Anonymous ticket submission form (public access)
 *
 * Required: none
 * Optional: none
 * Template: template/anonymous
 */
?>
<?= $this->extend('template/anonymous') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/_shared/anonymous-layout.css') ?>?v=<?= config('App')->assetVersion ?>">
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/tickets_create.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="anon-container">
    <div class="anon-breadcrumb">
        <a href="<?= site_url('public/tickets') ?>">My Tickets</a>
        <span class="sep">/</span>
        <span class="active">Submit a Ticket</span>
    </div>

    <div class="anon-page-header">
        <h1>Submit a Ticket</h1>
        <p class="anon-subtitle">Laporkan bug, masalah, atau permintaan. Anda akan menerima kode pelacakan untuk memantau status tiket.</p>
    </div>

    <form id="ticketForm" class="anon-form" enctype="multipart/form-data" novalidate>
        <div class="anon-hp" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="anon-form-group">
            <label class="anon-label" for="ticketTitle">Title <span class="text-danger">*</span></label>
            <input type="text" id="ticketTitle" name="title" class="anon-input" required minlength="5" maxlength="255" placeholder="e.g., Login page broken on mobile">
            <div class="anon-field-error" data-field="title" aria-live="polite"></div>
        </div>

        <div class="anon-form-group">
            <label class="anon-label" for="ticketDescription">Description <span class="text-danger">*</span></label>
            <textarea id="ticketDescription" name="description" class="anon-input" rows="5" required minlength="10" placeholder="Describe the issue in detail..."></textarea>
            <div class="anon-field-error" data-field="description" aria-live="polite"></div>
        </div>

        <div class="anon-form-row">
            <div class="anon-form-group" style="flex:1">
                <label class="anon-label" for="ticketType">Type <span class="text-danger">*</span></label>
                <select id="ticketType" name="type" class="anon-select" required>
                    <option value="">-- Select Type --</option>
                    <option value="2">Task</option>
                    <option value="1">Issue</option>
                    <option value="0">Bug</option>
                    <option value="3">Change Request</option>
                    <option value="4">Data Request</option>
                    <option value="5">Change Data Request</option>
                </select>
                <div class="anon-field-error" data-field="type"></div>
            </div>

            <div class="anon-form-group" style="flex:1">
                <label class="anon-label">Priority <span class="text-danger">*</span></label>
                <div class="anon-segmented-control" id="prioritySegments">
                    <button type="button" class="seg-option" data-value="0" tabindex="0">Low</button>
                    <button type="button" class="seg-option active" data-value="1" tabindex="0">Medium</button>
                    <button type="button" class="seg-option" data-value="2" tabindex="0">High</button>
                    <button type="button" class="seg-option" data-value="3" tabindex="0">Critical</button>
                </div>
                <input type="hidden" name="priority" id="priorityValue" value="1">
            </div>
        </div>

        <div id="bugTraceSection" class="anon-form-group" style="display:none">
            <label class="anon-label">Affected Page</label>
            <p class="anon-hint">Pilih proyek, modul, dan halaman yang terdampak.</p>
            <div class="anon-form-row">
                <div class="anon-form-group" style="flex:1">
                    <label class="anon-label" for="projectSelect">Project</label>
                    <select id="projectSelect" class="anon-select">
                        <option value="">-- Select Project --</option>
                    </select>
                </div>
                <div class="anon-form-group" style="flex:1">
                    <label class="anon-label" for="moduleSelect">Module</label>
                    <select id="moduleSelect" class="anon-select" disabled>
                        <option value="">-- Select Module --</option>
                    </select>
                </div>
                <div class="anon-form-group" style="flex:1">
                    <label class="anon-label" for="pageSelect">Page</label>
                    <select id="pageSelect" class="anon-select" disabled>
                        <option value="">-- Select Page --</option>
                    </select>
                    <input type="hidden" name="page_id" id="pageIdValue">
                </div>
            </div>
        </div>

        <div class="anon-form-group">
            <label class="anon-label">Attachments <span class="text-muted">(optional, max 3 files, 2MB each)</span></label>
            <div id="dropzone" class="anon-dropzone">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Drag & drop files here or <span class="anon-dropzone-browse">browse</span></p>
                <input type="file" name="images[]" id="fileInput" multiple accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,.xlsx,.xls,.docx,.doc,.pptx,.ppt,.csv" hidden>
            </div>
            <div id="imagePreview" class="anon-preview-grid"></div>
        </div>

        <div class="anon-form-actions">
            <a href="<?= site_url('public/tickets') ?>" class="anon-btn anon-btn-secondary">Cancel</a>
            <button type="submit" id="submitBtn" class="anon-btn anon-btn-primary">
                <i class="fas fa-paper-plane"></i> Submit Ticket
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['ajaxBaseUrl' => site_url('public/tickets')], JSON_HEX_TAG | JSON_HEX_APOS) ?>;</script>
<script src="<?= base_url('public/assets/js/page/public/_shared/tracking-store.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/assets/js/page/public/tickets_create.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
