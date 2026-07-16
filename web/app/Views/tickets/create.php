<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:800px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('tickets') ?>">Tickets</a>
        <span class="sep">/</span>
        <span class="active">Create</span>
    </div>

    <h1 class="mb-4">Create Ticket</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="ticketForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="sap-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="sap-input" required minlength="5" placeholder="Brief description of the issue or request">
                    <div class="sap-hint">Minimal 5 characters</div>
                </div>
                <div class="mb-3">
                    <label class="sap-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="sap-input" rows="6" required placeholder="Detailed description including steps to reproduce for bugs..." style="min-height:120px"></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="sap-label">Type</label>
                        <select name="type" class="sap-select" id="ticketType">
                            <option value="2">Task</option>
                            <option value="1">Issue</option>
                            <option value="0">Bug</option>
                            <option value="3">Change Request</option>
                            <option value="4">Data Request</option>
                            <option value="5">Change Data Request</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="sap-label">Priority</label>
                        <div class="segmented-control" id="prioritySegments">
                            <button type="button" class="seg-option" data-value="0">Low</button>
                            <button type="button" class="seg-option active" data-value="1">Medium</button>
                            <button type="button" class="seg-option" data-value="2">High</button>
                            <button type="button" class="seg-option" data-value="3">Critical</button>
                        </div>
                        <input type="hidden" name="priority" id="priorityValue" value="1">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="sap-label">Due Date</label>
                        <input type="date" name="due_date" class="sap-input">
                    </div>
                </div>

                <div class="mb-4" id="bugTraceSection" style="display:none">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-project-diagram me-1"></i> Affected Page <span class="text-danger">*</span>
                    </h5>
                    <p class="text-secondary" style="font-size:13px">Select the page affected by this ticket</p>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select class="sap-select" id="projectSelect">
                                <option value="">Select Project...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="sap-select" id="moduleSelect" disabled>
                                <option value="">Select Module...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="sap-select" id="pageSelect" disabled>
                                <option value="">Select Page...</option>
                            </select>
                            <input type="hidden" name="page_id" id="pageIdValue">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="sap-label">Images <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 3, JPG/PNG, max 2MB each)</span></label>
                    <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:24px;text-align:center;transition:all var(--sap-transition);cursor:pointer" id="dropzone">
                        <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:var(--sap-text-muted);display:block;margin-bottom:8px"></i>
                        <p class="mb-2 text-secondary" style="font-size:13px">Drop images here or</p>
                        <label class="sap-btn sap-btn-secondary sap-btn-sm" style="cursor:pointer" onclick="event.stopPropagation()">
                            <i class="fas fa-paperclip"></i> Choose Files
                            <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,.xlsx,.xls,.docx,.doc,.pptx,.ppt,.csv" multiple hidden>
                        </label>
                        <p class="mb-0 mt-1 text-muted" style="font-size:11px">Images, PDF, Excel, Word, PPT — max 5MB each</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2" id="imagePreview"></div>
                </div>
                <div class="mb-3" style="padding:12px 16px;border-radius:var(--sap-radius-sm);border:1px solid var(--sap-border);background:var(--sap-surface)">
                    <div class="form-check" style="margin:0">
                        <input type="checkbox" name="needs_approval" value="1" id="needsApproval" class="form-check-input" style="width:18px;height:18px;cursor:pointer">
                        <label for="needsApproval" class="form-check-label" style="cursor:pointer;margin-left:8px">
                            <strong style="color:var(--sap-text)">Requires Approval</strong>
                            <span class="text-muted d-block" style="font-size:12px;margin-top:2px">2-stage approval: IT Manager → Dept Head</span>
                        </label>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                    <a href="<?= site_url('tickets') ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/tickets/create.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('public/assets/js/page/tickets/create.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
