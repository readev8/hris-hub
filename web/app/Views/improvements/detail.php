<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <?php if (!$improvement): ?>
        <div class="sap-empty">
            <i class="fas fa-exclamation-triangle" style="color:var(--sap-error)"></i>
            <h4>Improvement not found</h4>
            <p>The improvement you're looking for doesn't exist or has been removed.</p>
            <a href="<?= site_url('improvements') ?>" class="sap-btn sap-btn-secondary mt-3">Back</a>
        </div>
    <?php else: ?>
    <div class="sap-breadcrumb mb-4">
        <a href="<?= site_url('improvements') ?>">Improvements</a>
        <span class="sep">/</span>
        <span class="active"><?= esc($improvement['name']) ?></span>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 style="font-size:22px" class="mb-1"><?= esc($improvement['name']) ?></h1>
            <div class="d-flex align-items-center gap-2">
                <?= status_badge($improvement['status_name'] ?? '') ?>
                <span class="text-secondary" style="font-size:13px"><?= esc($improvement['priority_name']) ?></span>
            </div>
        </div>
        <a href="<?= site_url('improvements') ?>" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="sap-card mb-4">
        <div class="sap-card-body">
            <div class="approval-stepper">
                <?php
                $st = (int) ($improvement['status'] ?? -1);
                $steps = [
                    ['label' => 'Draft',      'key' => 'draft'],
                    ['label' => 'IT Manager',  'key' => 'it'],
                    ['label' => 'Dept Head',   'key' => 'dept'],
                    ['label' => 'Approved',    'key' => 'final'],
                ];
                $stepStates = ['draft' => 'completed'];
                if ($st === 0) { $stepStates['it'] = 'active'; $stepStates['dept'] = ''; $stepStates['final'] = ''; }
                elseif ($st === 1) { $stepStates['it'] = 'completed'; $stepStates['dept'] = 'active'; $stepStates['final'] = ''; }
                elseif ($st === 2 || $st === 4) { $stepStates['it'] = 'completed'; $stepStates['dept'] = 'completed'; $stepStates['final'] = 'active'; }
                elseif ($st === 3) {
                    $history = $improvement['approval_history'] ?? [];
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
                $icons = ['draft' => 'fa-pencil-alt', 'it' => 'fa-laptop', 'dept' => 'fa-users', 'final' => 'fa-check-double'];
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

    <div class="row g-3">
        <div class="col-md-8">
            <div class="sap-card mb-3">
                <div class="sap-card-header">
                    <i class="fas fa-file-alt"></i> Description
                </div>
                <div class="sap-card-body">
                    <p style="line-height:1.7"><?= nl2br(esc($improvement['description'] ?? '')) ?></p>
                    <?php if (!empty($improvement['business_case'])): ?>
                    <h5 style="font-size:14px;font-weight:600;color:var(--sap-text);margin-top:20px">Business Case</h5>
                    <p style="line-height:1.7"><?= nl2br(esc($improvement['business_case'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sap-card mb-3">
                <div class="sap-card-header">
                    <i class="fas fa-history"></i> Approval History
                </div>
                <div class="sap-card-body">
                    <?php if (empty($improvement['approval_history'])): ?>
                        <div class="sap-empty" style="padding:20px">
                            <i class="fas fa-history" style="font-size:36px"></i>
                            <h4>No approval history</h4>
                        </div>
                    <?php else: ?>
                        <div class="sap-timeline">
                            <?php foreach ($improvement['approval_history'] as $a): ?>
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

            <div class="sap-card mb-3">
                <div class="sap-card-header">
                    <i class="fas fa-comment-dots"></i> Comments
                    <span class="sap-badge closed" style="font-size:11px;margin-left:4px"><?= count($improvement['comments'] ?? []) ?></span>
                </div>
                <div class="sap-card-body">
                    <?php if (empty($improvement['comments'])): ?>
                        <div class="sap-empty" style="padding:20px">
                            <i class="fas fa-comment-dots" style="font-size:36px"></i>
                            <h4>No comments</h4>
                        </div>
                    <?php else: ?>
                        <?php foreach ($improvement['comments'] as $c): ?>
                        <div class="sap-comment">
                            <div class="sap-comment-header">
                                <?= avatar_initials($c['full_name'] ?? '?', 'sm', '#758CA4') ?>
                                <span class="sap-comment-author"><?= esc($c['full_name'] ?? '') ?></span>
                                <span class="sap-comment-time"><?= esc($c['created_at']) ?></span>
                            </div>
                            <div class="sap-comment-body"><?= nl2br(esc($c['content'])) ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <form id="commentForm" class="mt-3" style="border-top:1px solid var(--sap-border-light);padding-top:16px">
                        <div class="mb-2">
                            <textarea class="sap-input" id="commentText" rows="2" placeholder="Write a comment..." style="min-height:60px"></textarea>
                        </div>
                        <button class="sap-btn sap-btn-primary sap-btn-sm" type="submit"><i class="fas fa-paper-plane"></i> Send</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <?php
            $impPerms = (session('permissions') ?? [])['improvements'] ?? [];
            $impCanApprove = !empty($impPerms['can_approve']);
            $impCanCreate  = !empty($impPerms['can_create']);
            $impCanUpdate  = !empty($impPerms['can_update']);
            $impCanDelete  = !empty($impPerms['can_delete']);
            ?>
            <div class="sap-card mb-3">
                <div class="sap-card-header">
                    <i class="fas fa-info-circle"></i> Details
                </div>
                <div class="sap-card-body" style="font-size:14px">
                    <dl class="row mb-0" style="gap:4px 0">
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Creator</dt>
                        <dd class="col-7"><?= esc($improvement['creator_name'] ?? '') ?></dd>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Created</dt>
                        <dd class="col-7"><?= esc($improvement['created_at']) ?></dd>
                        <?php if (!empty($improvement['category'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Category</dt>
                        <dd class="col-7"><span class="sap-badge info"><?= esc($improvement['category']) ?></span></dd>
                        <?php endif; ?>
                        <?php if (!empty($improvement['department_name'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Department</dt>
                        <dd class="col-7"><?= esc($improvement['department_name']) ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($improvement['approver_name'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Approver</dt>
                        <dd class="col-7"><i class="fas fa-user-check text-success me-1" style="font-size:11px"></i> <?= esc($improvement['approver_name']) ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($improvement['target_date'])): ?>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Target Date</dt>
                        <dd class="col-7"><?= esc($improvement['target_date']) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="sap-card mb-3">
                <div class="sap-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-paperclip"></i> Attachments</span>
                    <?php if ($impCanUpdate): ?>
                    <label class="sap-btn sap-btn-secondary sap-btn-sm mb-0" style="cursor:pointer">
                        <i class="fas fa-plus"></i> Add Attachment
                        <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,.xlsx,.xls,.doc,.docx" multiple hidden id="attachmentInput">
                    </label>
                    <?php endif; ?>
                </div>
                <div class="sap-card-body">
                    <?php if (!empty($improvement['attachments'])): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($improvement['attachments'] as $att): ?>
                            <?php if (strpos($att['mime_type'] ?? '', 'image/') === 0): ?>
                        <a href="<?= site_url('uploads/improvements/' . $att['stored_name']) ?>"
                           class="glightbox improvement-attachment-link"
                           data-gallery="improvement-<?= $improvement['id'] ?>"
                           data-description="<?= esc($att['filename']) ?>">
                            <img src="<?= site_url('uploads/improvements/' . $att['stored_name']) ?>"
                                 alt="<?= esc($att['filename']) ?>"
                                 style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border);cursor:pointer"
                                 class="sap-hover-lift">
                        </a>
                            <?php else: ?>
                        <?php
                        $mime = $att['mime_type'] ?? '';
                        $iconClass = 'fas fa-file';
                        $iconColor = 'var(--sap-text-muted)';
                        if ($mime === 'application/pdf') { $iconClass = 'fas fa-file-pdf'; $iconColor = 'var(--sap-error)'; }
                        elseif (in_array($mime, ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])) { $iconClass = 'fas fa-file-excel'; $iconColor = '#217346'; }
                        elseif (in_array($mime, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) { $iconClass = 'fas fa-file-word'; $iconColor = '#2B579A'; }
                        ?>
                        <a href="<?= site_url('uploads/improvements/' . $att['stored_name']) ?>" target="_blank">
                            <div style="padding:12px 16px;background:var(--sap-background);border-radius:6px;border:1px solid var(--sap-border);font-size:13px">
                                <i class="<?= $iconClass ?>" style="color:<?= $iconColor ?>;margin-right:6px"></i>
                                <?= esc($att['filename']) ?>
                            </div>
                        </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0" style="font-size:13px">No attachments yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sap-card">
                <div class="sap-card-header">
                    <i class="fas fa-bolt"></i> Actions
                </div>
                <div class="sap-card-body d-flex flex-column gap-2">
                    <?php $st = (int)($improvement['status'] ?? -1); ?>
                    <?php if ($st === 0 && $impCanApprove): ?>
                        <button class="sap-btn sap-btn-success sap-btn-sm" onclick="doAction('approve-it')"><i class="fas fa-check"></i> Approve (IT)</button>
                        <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptReject()"><i class="fas fa-times"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($st === 1 && $impCanApprove): ?>
                        <button class="sap-btn sap-btn-success sap-btn-sm" onclick="doAction('approve-dept')"><i class="fas fa-check"></i> Approve (Dept)</button>
                        <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptReject()"><i class="fas fa-times"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($st === 3 && $impCanCreate): ?>
                        <button class="sap-btn sap-btn-warning sap-btn-sm" onclick="doAction('resubmit')"><i class="fas fa-undo"></i> Resubmit</button>
                    <?php endif; ?>
                    <hr class="my-1">
                    <?php if (($st === -1 || $st === 0) && ($impCanUpdate || $impCanDelete)): ?>
                    <?php if ($impCanUpdate): ?>
                    <a href="<?= site_url('improvements/' . $token . '/edit') ?>" class="sap-btn sap-btn-secondary sap-btn-sm"><i class="fas fa-edit"></i> Edit</a>
                    <?php endif; ?>
                    <?php if ($impCanDelete): ?>
                    <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="confirmDelete()"><i class="fas fa-trash"></i> Delete</button>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var token = '<?= esc($token) ?>';

function doAction(action) {
    $.post(site_url + '/improvements/' + token + '/' + action, {}, function(res) {
        if (res.status) {
            toastr.success(res.data.message);
            setTimeout(function() { location.reload(); }, 800);
        } else {
            toastr.error(res.data.message || 'Action failed');
        }
    }).fail(function(xhr) {
        toastr.error('Gagal melakukan aksi (HTTP ' + xhr.status + ')');
    });
}

function promptReject() {
    Swal.fire({
        title: 'Rejection Notes',
        input: 'textarea',
        inputPlaceholder: 'Enter reason for rejection...',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed && result.value) {
            $.post(site_url + '/improvements/' + token + '/reject', { notes: result.value }, function(res) {
                if (res.status) {
                    toastr.success(res.data.message);
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    toastr.error(res.data.message || 'Failed');
                }
            }).fail(function(xhr) {
                toastr.error('Gagal reject improvement (HTTP ' + xhr.status + ')');
            });
        }
    });
}

function confirmDelete() {
    Swal.fire({
        title: 'Delete Improvement?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(site_url + '/improvements/' + token + '/delete', {}, function(res) {
                if (res.status) {
                    toastr.success('Improvement deleted');
                    setTimeout(function() { window.location.href = site_url + '/improvements'; }, 800);
                } else {
                    toastr.error(res.data.message || 'Failed to delete');
                }
            }).fail(function(xhr) {
                toastr.error('Gagal menghapus improvement (HTTP ' + xhr.status + ')');
            });
        }
    });
}

$('#commentForm').on('submit', function(e) {
    e.preventDefault();
    var text = $('#commentText').val();
    if (!text.trim()) return;
    $.post(site_url + '/improvements/' + token + '/comments', { content: text }, function(res) {
        if (res.status) {
            toastr.success('Comment added');
            $('#commentText').val('');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            toastr.error(res.data.message || 'Failed');
        }
    }).fail(function(xhr) {
        toastr.error('Gagal menambahkan comment (HTTP ' + xhr.status + ')');
    });
});

$('#attachmentInput').on('change', function() {
    var files = this.files;
    if (!files.length) return;

    var maxSize = 500 * 1024;
    var fd = new FormData();
    var skipped = 0;
    for (var i = 0; i < files.length; i++) {
        if (files[i].size > maxSize) {
            toastr.warning(files[i].name + ' exceeds 500KB limit');
            skipped++;
            continue;
        }
        fd.append('images[]', files[i]);
    }

    if (skipped >= files.length) {
        $(this).val('');
        return;
    }

    var btn = $(this).closest('label');
    btn.css('pointer-events', 'none').css('opacity', '0.6');

    $.ajax({
        url: site_url + '/improvements/' + token + '/attachments',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status) {
                toastr.success('Attachment(s) uploaded');
                setTimeout(function() { location.reload(); }, 800);
            } else {
                toastr.error(res.message || 'Failed to upload attachment');
                btn.css('pointer-events', '').css('opacity', '');
                $('#attachmentInput').val('');
            }
        },
        error: function(xhr) {
            var res = null;
            try { res = JSON.parse(xhr.responseText); } catch(e) {}
            if (res && res.redirect) {
                window.location.href = res.redirect;
                return;
            }
            var msg = res && res.message ? res.message : 'Failed to upload (HTTP ' + xhr.status + ')';
            toastr.error(msg);
            btn.css('pointer-events', '').css('opacity', '');
            $('#attachmentInput').val('');
        }
    });

    $(this).val('');
});

// GLightbox init for improvements
var improvementLightbox = GLightbox({
    selector: '.improvement-attachment-link',
    touchNavigation: true,
    keyboardNavigation: true,
    loop: false,
    preload: true
});
</script>
<?= $this->endSection() ?>
