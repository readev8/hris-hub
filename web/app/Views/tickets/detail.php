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
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

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
                                <input type="file" name="images[]" accept="image/jpeg,image/png" multiple hidden>
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
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Creator</dt>
                        <dd class="col-7"><?= esc($ticket['creator_name'] ?? '') ?></dd>
                        <dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Assignee</dt>
                        <dd class="col-7"><?= esc($ticket['assignee_name'] ?? '-') ?></dd>
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
                    ?>
                    <?php if ($status === 0 && $canUpdate): ?>
                        <button class="sap-btn sap-btn-success sap-btn-sm" onclick="doAction('approve')"><i class="fas fa-check"></i> Approve</button>
                        <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptAction('reject','Rejection note')"><i class="fas fa-times"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($status === 1 && $canUpdate): ?>
                        <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="doAction('take')"><i class="fas fa-hand-pointer"></i> Take Ticket</button>
                    <?php endif; ?>
                    <?php if ($status === 2 && $canUpdate): ?>
                        <button class="sap-btn sap-btn-success sap-btn-sm" onclick="promptAction('resolve','Resolution note')"><i class="fas fa-check-double"></i> Resolve</button>
                    <?php endif; ?>
                    <?php if ($status === 3 && $canUpdate): ?>
                        <button class="sap-btn sap-btn-secondary sap-btn-sm" onclick="doAction('close')"><i class="fas fa-lock"></i> Close</button>
                        <button class="sap-btn sap-btn-danger sap-btn-sm" onclick="promptAction('reopen','Reopen reason')"><i class="fas fa-undo"></i> Reopen</button>
                    <?php endif; ?>
                    <hr class="my-1">
                    <?php if ($canUpdate): ?>
                    <button class="sap-btn sap-btn-primary sap-btn-sm" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> Assign</button>
                    <?php endif; ?>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var token = '<?= esc($token) ?>';

function copyTrackingCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        toastr.success('Tracking code copied!');
    });
}

function doAction(action) {
    var btn = event && event.target ? $(event.target).closest('button') : null;
    $.post(site_url + '/tickets/' + token + '/' + action, {}, function(res) {
        if (res.status) {
            toastr.success(res.data.message);
            setTimeout(function() { location.reload(); }, 800);
        } else {
            toastr.error(res.data.message || 'Action failed');
        }
    }, btn);
}

function promptAction(action, label) {
    Swal.fire({
        title: label,
        input: 'textarea',
        inputPlaceholder: 'Enter ' + label.toLowerCase() + '...',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        confirmButtonColor: '#0070F2',
        cancelButtonColor: '#758CA4',
        inputAttributes: { style: 'border-radius:6px;font-family:Inter' }
    }).then(function(result) {
        if (result.isConfirmed && result.value) {
            var data = {};
            if (action === 'resolve') data.resolution_note = result.value;
            if (action === 'reopen') data.rejection_note = result.value;
            if (action === 'reject') data.rejection_note = result.value;
            $.post(site_url + '/tickets/' + token + '/' + action, data, function(res) {
                if (res.status) {
                    toastr.success(res.data.message);
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    toastr.error(res.data.message || 'Action failed');
                }
            });
        }
    });
}

function showAssignModal() {
    $.get(site_url + '/users/ajax-list', function(res) {
        var users = res.data || [];
        var options = '<option value="">Select user...</option>';
        for (var i = 0; i < users.length; i++) {
            options += '<option value="' + users[i].id + '">' + (users[i].full_name || users[i].name) + '</option>';
        }
        Swal.fire({
            title: 'Assign Ticket',
            html: '<select id="swal-assign" class="sap-select" style="width:100%">' + options + '</select>',
            showCancelButton: true,
            confirmButtonText: 'Assign',
            confirmButtonColor: '#0070F2',
            cancelButtonColor: '#758CA4',
            preConfirm: function() {
                var val = $('#swal-assign').val();
                if (!val) {
                    Swal.showValidationMessage('Please select a user');
                    return false;
                }
                return val;
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                $.post(site_url + '/tickets/' + token + '/assign', {assignee_id: result.value}, function(res) {
                    if (res.status) {
                        toastr.success('Ticket assigned');
                        setTimeout(function() { location.reload(); }, 800);
                    } else {
                        toastr.error(res.data.message || 'Failed to assign');
                    }
                });
            }
        });
    });
}

function confirmDelete() {
    Swal.fire({
        title: 'Delete Ticket?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(site_url + '/tickets/' + token + '/delete', {}, function(res) {
                if (res.status) {
                    toastr.success('Ticket deleted');
                    setTimeout(function() { window.location.href = site_url + '/tickets'; }, 800);
                } else {
                    toastr.error(res.data.message || 'Failed to delete');
                }
            });
        }
    });
}

$(function() {
    $('#commentForm input[name="images[]"]').on('change', function() {
        var preview = $('#commentImagePreview');
        preview.empty();
        var files = this.files;
        if (files.length > 5) {
            toastr.warning('Maximum 5 files');
            $(this).val('');
            return;
        }
        for (var i = 0; i < files.length && i < 3; i++) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.append('<img src="' + e.target.result + '" style="max-width:80px;max-height:60px;object-fit:cover;border-radius:4px;border:1px solid var(--sap-border)">');
            };
            reader.readAsDataURL(files[i]);
        }
    });

    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        var text = $('#commentText').val();
        if (!text.trim()) return;
        var btn = $(this).find('[type="submit"]');
        var formData = new FormData(this);
        $.ajax({
            url: site_url + '/tickets/' + token + '/comments',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status) {
                    toastr.success('Comment added');
                    $('#commentText').val('');
                    $('#commentImagePreview').empty();
                    $('#commentForm input[name="images[]"]').val('');
                    setTimeout(function() { location.reload(); }, 500);
                } else {
                    toastr.error(res.data.message || 'Failed');
                    btn.prop('disabled', false).html('Send');
                }
            },
            error: function(xhr) {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch(e) {}
                if (res && res.redirect) {
                    window.location.href = res.redirect;
                    return;
                }
                var msg = res && res.message ? res.message : 'Request failed (HTTP ' + xhr.status + ')';
                toastr.error(msg);
                btn.prop('disabled', false).html('Send');
            }
        });
    });
});

// GLightbox initialization
var ticketLightbox = GLightbox({
    selector: '.ticket-attachment-link',
    touchNavigation: true,
    keyboardNavigation: true,
    loop: false,
    preload: true
});
</script>
<?= $this->endSection() ?>
