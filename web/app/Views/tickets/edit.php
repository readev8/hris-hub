<?php
/**
 * ============================================================================
 * TICKETS - EDIT
 * ============================================================================
 *
 * Description: Ticket edit form with page tree selectors and existing attachments
 *
 * Required: $ticket, $token
 * Optional: $users
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container" style="max-width:800px">
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('tickets') ?>">Tickets</a>
        <span class="sep">/</span>
        <a href="<?= site_url('tickets/' . $token) ?>"><?= esc($ticket['title'] ?? '') ?></a>
        <span class="sep">/</span>
        <span class="active">Edit</span>
    </div>

    <h1 class="mb-4">Edit Ticket</h1>

    <div class="sap-card">
        <div class="sap-card-body">
            <form id="ticketForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="sap-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="sap-input" required minlength="5" value="<?= esc($ticket['title'] ?? '') ?>" placeholder="Brief description of the issue or request">
                    <div class="sap-hint">Minimal 5 characters</div>
                </div>
                <div class="mb-3">
                    <label class="sap-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="sap-input" rows="6" required placeholder="Detailed description including steps to reproduce for bugs..." style="min-height:120px"><?= esc($ticket['description'] ?? '') ?></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="sap-label">Type</label>
                        <select name="type" class="sap-select" id="ticketType">
                            <option value="2" <?= ($ticket['type'] ?? '') == 2 ? 'selected' : '' ?>>Task</option>
                            <option value="1" <?= ($ticket['type'] ?? '') == 1 ? 'selected' : '' ?>>Issue</option>
                            <option value="0" <?= ($ticket['type'] ?? '') == 0 ? 'selected' : '' ?>>Bug</option>
                            <option value="3" <?= ($ticket['type'] ?? '') == 3 ? 'selected' : '' ?>>Change Request</option>
                            <option value="4" <?= ($ticket['type'] ?? '') == 4 ? 'selected' : '' ?>>Data Request</option>
                            <option value="5" <?= ($ticket['type'] ?? '') == 5 ? 'selected' : '' ?>>Change Data Request</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="sap-label">Priority</label>
                        <div class="segmented-control" id="prioritySegments">
                            <button type="button" class="seg-option <?= ($ticket['priority'] ?? 1) == 0 ? '' : '' ?>" data-value="0">Low</button>
                            <button type="button" class="seg-option <?= ($ticket['priority'] ?? 1) == 1 ? 'active' : '' ?>" data-value="1">Medium</button>
                            <button type="button" class="seg-option <?= ($ticket['priority'] ?? 1) == 2 ? 'active' : '' ?>" data-value="2">High</button>
                            <button type="button" class="seg-option <?= ($ticket['priority'] ?? 1) == 3 ? 'active' : '' ?>" data-value="3">Critical</button>
                        </div>
                        <input type="hidden" name="priority" id="priorityValue" value="<?= esc($ticket['priority'] ?? 1) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="sap-label">Due Date</label>
                        <input type="date" name="due_date" class="sap-input" value="<?= esc($ticket['due_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="sap-label">Assignee</label>
                        <select name="assignee_id" class="sap-select" id="assigneeSelect">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4" id="bugTraceSection" style="display:<?= in_array(($ticket['type'] ?? ''), [0, 3, 4, 5], true) ? 'block' : 'none' ?>">
                    <h5 class="mb-3" style="color:var(--sap-text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.05em">
                        <i class="fas fa-project-diagram me-1"></i> Affected Page
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
                            <input type="hidden" name="page_id" id="pageIdValue" value="<?= esc($ticket['page_id'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <?php if (!empty($ticket['needs_approval'])): ?>
                <div class="mb-3" style="padding:12px 16px;border-radius:var(--sap-radius-sm);border:1px solid var(--sap-border);background:var(--sap-surface)">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-shield-alt" style="color:var(--sap-info);font-size:16px"></i>
                        <div>
                            <strong style="color:var(--sap-text);font-size:13px">Approval Required</strong>
                            <span class="text-muted" style="font-size:12px;margin-left:8px">
                                Status: <?= esc($ticket['status_name'] ?? '') ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mb-3" style="padding:12px 16px;border-radius:var(--sap-radius-sm);border:1px solid var(--sap-info-border, #b3d4ff);background:var(--sap-surface)">
                    <label class="sap-label">Designated Approver <span style="font-weight:400;color:var(--sap-text-muted)">(optional)</span></label>
                    <select name="approver_id" class="sap-select" id="approverSelect">
                        <option value="">Role-based approval (default)</option>
                        <?php foreach ($users ?? [] as $u): ?>
                        <option value="<?= esc($u['id']) ?>" <?= ($ticket['approver_id'] ?? '') === $u['id'] ? 'selected' : '' ?>><?= esc($u['full_name']) ?> — <?= esc($u['role_name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="sap-hint">Leave empty for standard 2-stage approval. Select a specific user for single-stage approval.</div>
                </div>
                <?php endif; ?>

                <?php if (!empty($ticket['attachments'])): ?>
                <div class="mb-4">
                    <label class="sap-label">Current Attachments</label>
                    <div class="d-flex flex-wrap gap-2" id="existingAttachments">
                        <?php foreach ($ticket['attachments'] as $att): ?>
                        <div class="attachment-item" data-id="<?= esc($att['id']) ?>" style="position:relative;display:inline-block">
                            <a href="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                               class="glightbox edit-attachment-link"
                               data-gallery="ticket-edit"
                               data-description="<?= esc($att['filename']) ?>">
                                <img src="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                                     alt="<?= esc($att['filename']) ?>"
                                     style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border);cursor:pointer"
                                     class="sap-hover-lift">
                            </a>
                            <button type="button" class="btn-remove-attachment" onclick="TicketEdit.removeAttachment('<?= esc($att['id']) ?>', this)" title="Remove"
                                    style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:var(--sap-error);color:#fff;border:none;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label class="sap-label">Add Images <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 3, JPG/PNG, max 2MB each)</span></label>
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

                <div class="d-flex gap-2">
                    <button type="submit" class="sap-btn sap-btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="<?= site_url('tickets/' . $token) ?>" class="sap-btn sap-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/tickets/edit.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode([
    'token'             => $token,
    'currentPageId'     => $ticket['page_id'] ?? '',
    'currentAssigneeId' => $ticket['assignee_raw_id'] ?? $ticket['assignee_id'] ?? '',
]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/tickets/edit.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
