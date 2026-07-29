<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <?php if (!$ticket): ?>
        <div class="sap-empty">
            <i class="fas fa-exclamation-triangle" style="color:var(--sap-error)"></i>
            <h4>Ticket not found</h4>
            <p>The ticket you're looking for doesn't exist or has been removed.</p>
            <a href="<?= site_url('tickets') ?>" class="sap-btn sap-btn-secondary mt-3">Back to Tickets</a>
        </div>
    <?php else: ?>
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('tickets') ?>">Tickets</a>
        <span class="sep">/</span>
        <span class="active"><?= esc($ticket['title']) ?></span>
    </div>

    <div class="row g-3">
        <?php $needsApproval = (int)($ticket['needs_approval'] ?? 0); ?>
        <?php if ($needsApproval === 1): ?>
        <div class="col-12">
            <div class="sap-card mb-3">
                <div class="sap-card-body">
                    <div class="approval-stepper">
                        <?php
                        $st = (int) ($ticket['status'] ?? -1);
                        $hasSpecificApprover = !empty($ticket['approver_id']);
                        if ($hasSpecificApprover) {
                            $steps = [
                                ['label' => 'Open',       'key' => 'open'],
                                ['label' => 'Approved',    'key' => 'approved'],
                                ['label' => 'In Progress', 'key' => 'final'],
                            ];
                            $stepStates = ['open' => 'completed'];
                            if ($st === 0) { $stepStates['approved'] = 'active'; $stepStates['final'] = ''; }
                            elseif ($st === 2) { $stepStates['approved'] = 'completed'; $stepStates['final'] = 'active'; }
                            elseif ($st === 5) {
                                $history = $ticket['approval_history'] ?? [];
                                $rejectedStage = 0;
                                foreach ($history as $h) {
                                    if ((int)($h['status'] ?? 0) === 2) {
                                        $rejectedStage = (int)($h['stage_sequence'] ?? 0);
                                        break;
                                    }
                                }
                                if ($rejectedStage <= 1) { $stepStates['approved'] = 'rejected'; $stepStates['final'] = ''; }
                            }
                            $icons = ['open' => 'fa-door-open', 'approved' => 'fa-check-circle', 'final' => 'fa-play-circle'];
                        } else {
                            $steps = [
                                ['label' => 'Open',       'key' => 'open'],
                                ['label' => 'IT Manager',  'key' => 'it'],
                                ['label' => 'Dept Head',   'key' => 'dept'],
                                ['label' => 'In Progress', 'key' => 'final'],
                            ];
                            $stepStates = ['open' => 'completed'];
                            if ($st === 0) { $stepStates['it'] = 'active'; $stepStates['dept'] = ''; $stepStates['final'] = ''; }
                            elseif ($st === 1) { $stepStates['it'] = 'completed'; $stepStates['dept'] = 'active'; $stepStates['final'] = ''; }
                            elseif ($st === 2) { $stepStates['it'] = 'completed'; $stepStates['dept'] = 'completed'; $stepStates['final'] = 'active'; }
                            elseif ($st === 5) {
                                $history = $ticket['approval_history'] ?? [];
                                $rejectedStage = 0;
                                foreach ($history as $h) {
                                    if ((int)($h['status'] ?? 0) === 2) {
                                        $rejectedStage = (int)($h['stage_sequence'] ?? 0);
                                        break;
                                    }
                                }
                                if ($rejectedStage <= 1) { $stepStates['it'] = 'rejected'; $stepStates['dept'] = ''; $stepStates['final'] = ''; }
                                else { $stepStates['it'] = 'completed'; $stepStates['dept'] = 'rejected'; $stepStates['final'] = ''; }
                            }
                            $icons = ['open' => 'fa-door-open', 'it' => 'fa-laptop', 'dept' => 'fa-users', 'final' => 'fa-play-circle'];
                        }
                        foreach ($steps as $i => $s):
                            $state = $stepStates[$s['key']] ?? '';
                            $icon = $icons[$s['key']];
                        ?>
                        <div class="stepper-step <?= esc($state, 'attr') ?>">
                            <div class="stepper-node">
                                <?php if ($state === 'completed'): ?><i class="fas fa-check"></i>
                                <?php elseif ($state === 'rejected'): ?><i class="fas fa-times"></i>
                                <?php else: ?><?= $i + 1 ?>
                                <?php endif; ?>
                            </div>
                            <div class="stepper-label"><?= esc($s['label']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-md-8">
            <div class="sap-card mb-3">
                <div class="sap-card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <?= status_badge($ticket['status_name'] ?? '') ?>
                        <span class="priority-dot <?= strtolower($ticket['priority_name'] ?? 'medium') ?>"></span>
                        <span class="text-secondary" style="font-size:13px"><?= esc($ticket['priority_name']) ?></span>
                        <span class="text-muted" style="font-size:13px">|</span>
                        <span class="text-secondary" style="font-size:13px"><?= esc($ticket['type_name']) ?></span>
                    </div>
                    <h1 style="font-size:22px" class="mb-2"><?= esc($ticket['title']) ?></h1>

                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="d-flex align-items-center gap-1">
                            <?= avatar_initials($ticket['creator_name'] ?? '?', 'sm', '#0070F2') ?>
                            <span class="text-secondary" style="font-size:13px"><?= esc($ticket['creator_name'] ?? '') ?></span>
                        </div>
                        <span class="text-muted" style="font-size:12px">|</span>
                        <span class="text-muted" style="font-size:13px"><?= esc($ticket['created_at']) ?></span>
                    </div>

                    <div class="p-3" style="background:var(--sap-background);border-radius:var(--sap-radius-sm);line-height:1.7">
                        <?= nl2br(esc($ticket['description'] ?? '')) ?>
                    </div>

                    <?php if (!empty($ticket['attachments'])): ?>
                    <div class="mt-3">
                        <h5 style="font-size:13px;color:var(--sap-text-secondary);text-transform:uppercase;letter-spacing:0.04em">Attachments</h5>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <?php foreach ($ticket['attachments'] as $att): ?>
                            <?php $isImage = str_starts_with($att['mime_type'] ?? '', 'image/'); ?>
                            <?php if ($isImage): ?>
                            <a href="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                               class="glightbox ticket-attachment-link"
                               data-gallery="ticket-main"
                               data-description="<?= esc($att['filename']) ?>">
                                <img src="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                                     alt="<?= esc($att['filename']) ?>"
                                     style="max-width:160px;max-height:120px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border);cursor:pointer;transition:transform 0.2s ease,box-shadow 0.2s ease"
                                     class="sap-hover-lift"
                                     loading="lazy">
                            </a>
                            <?php else: ?>
                            <a href="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                               download="<?= esc($att['filename']) ?>"
                               class="sap-attachment-file"
                               style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:6px;border:1px solid var(--sap-border);background:var(--sap-surface);text-decoration:none;color:var(--sap-text-primary);transition:all 0.2s ease;max-width:220px"
                               onmouseover="this.style.borderColor='var(--sap-active)'"
                               onmouseout="this.style.borderColor='var(--sap-border)'">
                                <i class="fas fa-file" style="font-size:24px;color:var(--sap-text-secondary);flex-shrink:0"></i>
                                <div style="min-width:0">
                                    <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= esc($att['filename']) ?></div>
                                    <div style="font-size:11px;color:var(--sap-text-muted)"><?= esc($att['mime_type'] ?? 'file') ?></div>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($needsApproval === 1): ?>
            <div class="sap-card mb-3">
                <div class="sap-card-header">
                    <i class="fas fa-history"></i> Approval History
                </div>
                <div class="sap-card-body">
                    <?php if (empty($ticket['approval_history'])): ?>
                        <div class="sap-empty" style="padding:20px">
                            <i class="fas fa-history" style="font-size:36px"></i>
                            <h4>No approval history</h4>
                        </div>
                    <?php else: ?>
                        <div class="sap-timeline">
                            <?php foreach ($ticket['approval_history'] as $a): ?>
                            <div class="sap-timeline-item">
                                <div class="sap-timeline-dot <?= (int)($a['status'] ?? 0) === 1 ? 'approved' : ((int)($a['status'] ?? 0) === 2 ? 'rejected' : '') ?>"></div>
                                <div class="sap-timeline-content">
                                    <div class="d-flex align-items-center gap-2">
                                        <?= status_badge($a['status_name'] ?? '') ?>
                                        <span class="fw-medium">Stage <?= esc($a['stage_sequence']) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <?= avatar_initials($a['approver_name'] ?? '?', 'sm', '#758CA4') ?>
                                        <span class="text-secondary"><?= esc($a['approver_name'] ?? '') ?></span>
                                        <span class="text-muted" style="font-size:12px"><?= esc($a['reviewed_at'] ?? '') ?></span>
                                    </div>
                                    <?php if (!empty($a['notes'])): ?>
                                    <p class="mt-1 mb-0 text-secondary" style="font-size:13px;font-style:italic">"<?= esc($a['notes']) ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="sap-card">
                <div class="sap-card-header">
                    <i class="fas fa-comment-dots" style="color:var(--sap-text-muted)"></i>
                    Comments
                    <span class="sap-badge closed" style="font-size:11px;margin-left:4px"><?= count($ticket['comments'] ?? []) ?></span>
                </div>
                <div class="sap-card-body">
                    <?php if (empty($ticket['comments'])): ?>
                        <div class="sap-empty" style="padding:24px 20px">
                            <i class="fas fa-comment-dots" style="font-size:36px"></i>
                            <h4>No comments yet</h4>
                            <p>Start the discussion by adding a comment below.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ticket['comments'] as $comment): ?>
                        <div class="sap-comment">
                            <div class="sap-comment-header">
                                <?= avatar_initials($comment['full_name'] ?? '?', 'sm', '#758CA4') ?>
                                <span class="sap-comment-author"><?= esc($comment['full_name'] ?? '') ?></span>
                                <span class="sap-comment-time"><?= esc($comment['created_at']) ?></span>
                            </div>
                            <div class="sap-comment-body"><?= nl2br(esc($comment['content'])) ?></div>
                            <?php if (!empty($comment['attachments'])): ?>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <?php foreach ($comment['attachments'] as $att): ?>
                                <?php $isImage = str_starts_with($att['mime_type'] ?? '', 'image/'); ?>
                                <?php if ($isImage): ?>
                                <a href="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                                   class="glightbox comment-attachment-link"
                                   data-gallery="comment-<?= $comment['id'] ?>"
                                   data-description="<?= esc($att['filename']) ?>">
                                    <img src="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                                         alt="<?= esc($att['filename']) ?>"
                                         style="max-width:100px;max-height:80px;object-fit:cover;border-radius:4px;border:1px solid var(--sap-border);cursor:pointer;transition:transform 0.2s ease"
                                         class="sap-hover-lift"
                                         loading="lazy">
                                </a>
                                <?php else: ?>
                                <a href="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                                   download="<?= esc($att['filename']) ?>"
                                   style="display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:4px;border:1px solid var(--sap-border);background:var(--sap-surface);text-decoration:none;color:var(--sap-text-primary);font-size:12px;transition:all 0.2s ease"
                                   onmouseover="this.style.borderColor='var(--sap-active)'"
                                   onmouseout="this.style.borderColor='var(--sap-border)'">
                                    <i class="fas fa-file" style="color:var(--sap-text-secondary)"></i>
                                    <?= esc($att['filename']) ?>
                                </a>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <form id="commentForm" class="mt-3" enctype="multipart/form-data" style="border-top:1px solid var(--sap-border-light);padding-top:16px">
                        <div class="mb-2">
                            <textarea class="sap-input" id="commentText" name="content" rows="2" placeholder="Write a comment..." style="min-height:60px"></textarea>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <label class="sap-btn sap-btn-secondary sap-btn-sm" style="cursor:pointer">
                                <i class="fas fa-paperclip"></i>
                                <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,.xlsx,.xls,.docx,.doc,.pptx,.ppt,.csv" multiple hidden>
                            </label>
                            <button class="sap-btn sap-btn-primary sap-btn-sm" type="submit"><i class="fas fa-paper-plane"></i> Send</button>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2" id="commentImagePreview"></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="sap-card mb-3">
                <div class="sap-card-header">
                    <i class="fas fa-info-circle"></i>
                    Details
                </div>
                <div class="sap-card-body" style="font-size:14px">
                    <dl class="row mb-0" style="gap:4px 0">
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Tracking</dt>
                        <dd class="col-7">
                            <div class="d-flex align-items-center gap-1">
                                <code style="font-size:13px;background:var(--sap-background);padding:2px 8px;border-radius:4px;font-family:'SF Mono',Monaco,Consolas,monospace"><?= esc($ticket['tracking_code'] ?? '') ?></code>
                                <button class="sap-btn sap-btn-secondary sap-btn-sm" style="padding:2px 6px;font-size:11px" onclick="copyTrackingCode('<?= esc($ticket['tracking_code'] ?? '') ?>')" title="Copy tracking code"><i class="fas fa-copy"></i></button>
                            </div>
                        </dd>
                        <?php if (!empty($ticket['referral'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Referral</dt>
                        <dd class="col-7">
                            <a href="<?= site_url('tickets/' . ($referralToken ?? '')) ?>" style="text-decoration:none">
                                <code style="font-size:13px;background:var(--sap-background);padding:2px 8px;border-radius:4px;font-family:'SF Mono',Monaco,Consolas,monospace;color:var(--sap-brand)"><?= esc($ticket['referral']) ?></code>
                            </a>
                        </dd>
                        <?php endif; ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Creator</dt>
                        <dd class="col-7"><?= esc($ticket['creator_name'] ?? '') ?></dd>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Assignee</dt>
                        <dd class="col-7"><?= esc($ticket['assignee_name'] ?? '-') ?></dd>
                        <?php if (!empty($ticket['approver_name'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Approver</dt>
                        <dd class="col-7">
                            <span style="display:inline-flex;align-items:center;gap:4px">
                                <?= avatar_initials($ticket['approver_name'] ?? '?', 'sm', '#0070F2') ?>
                                <?= esc($ticket['approver_name']) ?>
                            </span>
                        </dd>
                        <?php endif; ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Created</dt>
                        <dd class="col-7"><?= esc($ticket['created_at']) ?></dd>
                        <?php $status = (int)($ticket['status'] ?? -1); ?>
                        <?php if (!empty($ticket['due_date'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Due Date</dt>
                        <dd class="col-7">
                            <span style="<?= strtotime($ticket['due_date']) < time() && !in_array($status, [4, 5]) ? 'color:var(--sap-error);font-weight:600' : '' ?>">
                                <?= esc($ticket['due_date']) ?>
                                <?php if (strtotime($ticket['due_date']) < time() && !in_array($status, [4, 5])): ?>
                                <i class="fas fa-exclamation-triangle" title="Overdue"></i>
                                <?php endif; ?>
                            </span>
                        </dd>
                        <?php endif; ?>
                        <?php if (!empty($ticket['started_at'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Started</dt>
                        <dd class="col-7"><?= esc($ticket['started_at']) ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($ticket['resolved_at'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Resolved</dt>
                        <dd class="col-7"><?= esc($ticket['resolved_at']) ?></dd>
                        <?php endif; ?>
                        <?php if ($ticket['closed_at']): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Closed</dt>
                        <dd class="col-7"><?= esc($ticket['closed_at']) ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($ticket['page_name'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Location</dt>
                        <dd class="col-7">
                            <span style="font-size:13px">
                                <i class="fas fa-project-diagram" style="color:var(--sap-brand)"></i>
                                <?php if ($ticket['project_id']): ?>
                                <a href="<?= site_url('master-projects/' . $ticket['project_id']) ?>" style="text-decoration:none;color:var(--sap-brand)">
                                <?php endif; ?>
                                <?= esc($ticket['project_name'] ?? '') ?>
                                <?php if ($ticket['project_id']): ?>
                                </a>
                                <?php endif; ?>
                                &rarr; <?= esc($ticket['module_name'] ?? '') ?>
                                &rarr; <?= esc($ticket['page_name'] ?? '') ?>
                            </span>
                        </dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="sap-card">
                <div class="sap-card-header">
                    <i class="fas fa-bolt"></i>
                    Actions
                </div>
                <div class="sap-card-body d-flex flex-column gap-2">
                    <?php
                    $userId = session('user_id');
                    $perms = session('permissions') ?? [];
                    $ticketPerms = $perms['tickets'] ?? [];
                    $canUpdate = !empty($ticketPerms['can_update']);
                    $canDelete = !empty($ticketPerms['can_delete']);
                    $canApprove = !empty($ticketPerms['can_approve']);
                    $isAssignee = isset($ticket['assignee_id']) && (string)$ticket['assignee_id'] === (string)$userId;
                    $isCreator = isset($ticket['creator_id']) && (string)$ticket['creator_id'] === (string)$userId;
                    $roleId = session('role_id');
                    $isAdmin = $roleId == 5;
                    $hasSpecificApprover = !empty($ticket['approver_id']);
                    $isApprover = $hasSpecificApprover && (string)($ticket['approver_id'] ?? '') === (string)$userId;
                    ?>
                    <?php if ($needsApproval === 1): ?>
                        <?php if ($status === 0 && (($hasSpecificApprover && ($isApprover || $isAdmin)) || (!$hasSpecificApprover && $canApprove))): ?>
                            <button class="sap-btn sap-btn-success sap-btn-sm" onclick="doApproval('approve-it')"><i class="fas fa-check"></i> <?= $hasSpecificApprover ? 'Approve' : 'Approve (IT)' ?></button>
                            <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptRejectApproval()"><i class="fas fa-times"></i> Reject</button>
                        <?php endif; ?>
                        <?php if ($status === 1 && !$hasSpecificApprover && $canApprove): ?>
                            <button class="sap-btn sap-btn-success sap-btn-sm" onclick="doApproval('approve-dept')"><i class="fas fa-check"></i> Approve (Dept)</button>
                            <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptRejectApproval()"><i class="fas fa-times"></i> Reject</button>
                        <?php endif; ?>
                        <?php if ($status === 5 && $isCreator): ?>
                            <button class="sap-btn sap-btn-warning sap-btn-sm" onclick="doApproval('resubmit')"><i class="fas fa-undo"></i> Resubmit</button>
                        <?php endif; ?>
                        <?php if ($status === 2): ?>
                            <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="doAction('take')"><i class="fas fa-hand-pointer"></i> Take Ticket</button>
                        <?php endif; ?>
                        <?php if ($status === 2 && $isAssignee): ?>
                            <button class="sap-btn sap-btn-success sap-btn-sm" data-bs-toggle="modal" data-bs-target="#resolveModal"><i class="fas fa-check-double"></i> Resolve</button>
                        <?php endif; ?>
                        <?php if ($status === 3 && $isCreator): ?>
                            <button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="doAction('close')"><i class="fas fa-lock"></i> Close</button>
                        <?php endif; ?>
                        <?php if ($status === 5 && ($isCreator || $isAdmin)): ?>
                            <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptAction('reopen','Reopen reason')"><i class="fas fa-undo"></i> Reopen</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($status === 0 && $canUpdate): ?>
                            <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="doAction('take')"><i class="fas fa-hand-pointer"></i> Take Ticket</button>
                            <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptAction('reject','Rejection note')"><i class="fas fa-times"></i> Reject</button>
                        <?php endif; ?>
                        <?php if ($status === 2 && $isAssignee): ?>
                            <button class="sap-btn sap-btn-success sap-btn-sm" data-bs-toggle="modal" data-bs-target="#resolveModal"><i class="fas fa-check-double"></i> Resolve</button>
                        <?php endif; ?>
                        <?php if ($status === 3 && $isCreator): ?>
                            <button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="doAction('close')"><i class="fas fa-lock"></i> Close</button>
                        <?php endif; ?>
                        <?php if ($status === 3 && ($isCreator || $roleId == 2)): ?>
                            <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptAction('reopen','Reopen reason')"><i class="fas fa-undo"></i> Reopen</button>
                        <?php endif; ?>
                        <?php if ($status === 4 && ($isCreator || $roleId == 2 || $isAdmin)): ?>
                            <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptAction('reopen','Reopen reason')"><i class="fas fa-undo"></i> Reopen</button>
                        <?php endif; ?>
                        <?php if ($status === 5 && ($isCreator || $isAdmin)): ?>
                            <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptAction('reopen','Reopen reason')"><i class="fas fa-undo"></i> Reopen</button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <hr class="my-1">
                    <?php if ($canUpdate): ?>
                    <a href="<?= site_url('tickets/' . $token . '/edit') ?>" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-edit"></i> Edit</a>
                    <?php endif; ?>
                    <?php if ($canDelete): ?>
                    <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="confirmDelete()"><i class="fas fa-trash"></i> Delete</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Resolve Modal -->
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
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/tickets/detail.css?v=' . config('App')->assetVersion) ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['token' => $token]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/tickets/detail.js?v=' . config('App')->assetVersion) ?>"></script>
<?= $this->endSection() ?>
