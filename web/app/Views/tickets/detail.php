<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <?php if (!$ticket): ?>
        <div class="empty-state">
            <i class="bi bi-exclamation-triangle" style="color:var(--danger)"></i>
            <h4>Ticket not found</h4>
            <p>The ticket you're looking for doesn't exist or has been removed.</p>
            <a href="<?= site_url('tickets') ?>" class="btn btn-outline-secondary mt-3">Back to Tickets</a>
        </div>
    <?php else: ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('tickets') ?>">Tickets</a></li>
            <li class="breadcrumb-item active"><?= esc($ticket['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <?= status_badge($ticket['status_name'] ?? '') ?>
                        <span class="priority-dot <?= strtolower($ticket['priority_name'] ?? 'medium') ?>"></span>
                        <span class="text-meta" style="font-size:13px"><?= esc($ticket['priority_name']) ?></span>
                        <span class="text-meta" style="font-size:13px">·</span>
                        <span class="text-meta" style="font-size:13px"><?= esc($ticket['type_name']) ?></span>
                    </div>
                    <h1 class="mb-1" style="font-size:22px"><?= esc($ticket['title']) ?></h1>

                    <div class="d-flex align-items-center gap-2 mt-2 mb-3">
                        <div class="d-flex align-items-center gap-1">
                            <?= avatar_initials($ticket['creator_name'] ?? '?', 'sm', '#0F4C81') ?>
                            <span class="text-meta" style="font-size:13px"><?= esc($ticket['creator_name'] ?? '') ?></span>
                        </div>
                        <span class="text-muted" style="font-size:12px">·</span>
                        <span class="text-meta" style="font-size:13px"><?= esc($ticket['created_at']) ?></span>
                    </div>

                    <div class="p-3" style="background:var(--bg);border-radius:var(--radius-sm);line-height:1.7">
                        <?= nl2br(esc($ticket['description'] ?? '')) ?>
                    </div>

                    <?php if (!empty($ticket['attachments'])): ?>
                    <div class="mt-3">
                        <h5>Attachments</h5>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <?php foreach ($ticket['attachments'] as $att): ?>
                            <a href="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>" target="_blank" class="d-block">
                                <img src="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                                     alt="<?= esc($att['filename']) ?>"
                                     style="max-width:160px;max-height:120px;object-fit:cover;border-radius:6px;border:1px solid var(--border)"
                                     class="img-thumbnail">
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-chat-dots" style="color:var(--text-meta)"></i>
                    Comments
                    <span class="status-badge closed" style="font-size:11px"><?= count($ticket['comments'] ?? []) ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($ticket['comments'])): ?>
                        <div class="empty-state" style="padding:24px 20px">
                            <i class="bi bi-chat-dots" style="font-size:36px"></i>
                            <h4>No comments yet</h4>
                            <p>Start the discussion by adding a comment below.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ticket['comments'] as $comment): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <?= avatar_initials($comment['full_name'] ?? '?', 'sm', '#64748B') ?>
                                <span class="comment-author"><?= esc($comment['full_name'] ?? '') ?></span>
                                <span class="comment-time"><?= esc($comment['created_at']) ?></span>
                            </div>
                            <div class="comment-body"><?= nl2br(esc($comment['content'])) ?></div>
                            <?php if (!empty($comment['attachments'])): ?>
                            <div class="comment-images d-flex flex-wrap gap-1 mt-2">
                                <?php foreach ($comment['attachments'] as $att): ?>
                                <a href="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>" target="_blank">
                                    <img src="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
                                         alt="<?= esc($att['filename']) ?>"
                                         style="max-width:100px;max-height:80px;object-fit:cover;border-radius:4px;border:1px solid var(--border)">
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <form id="commentForm" class="mt-3" enctype="multipart/form-data" style="border-top:1px solid var(--border);padding-top:16px">
                        <div class="mb-2">
                            <textarea class="form-control" id="commentText" name="content" rows="2" placeholder="Write a comment..."></textarea>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <label class="btn btn-outline-secondary btn-sm" style="cursor:pointer">
                                <i class="bi bi-image"></i>
                                <input type="file" name="images[]" accept="image/jpeg,image/png" multiple hidden>
                            </label>
                            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-send"></i> Send</button>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2" id="commentImagePreview"></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle"></i>
                    Details
                </div>
                <div class="card-body" style="font-size:14px">
                    <dl class="row mb-0" style="gap:4px 0">
                        <dt class="col-5 text-meta" style="font-weight:500;font-size:13px">Creator</dt>
                        <dd class="col-7"><?= esc($ticket['creator_name'] ?? '') ?></dd>
                        <dt class="col-5 text-meta" style="font-weight:500;font-size:13px">Assignee</dt>
                        <dd class="col-7"><?= esc($ticket['assignee_name'] ?? '-') ?></dd>
                        <dt class="col-5 text-meta" style="font-weight:500;font-size:13px">Created</dt>
                        <dd class="col-7"><?= esc($ticket['created_at']) ?></dd>
                        <?php if ($ticket['closed_at']): ?>
                        <dt class="col-5 text-meta" style="font-weight:500;font-size:13px">Closed</dt>
                        <dd class="col-7"><?= esc($ticket['closed_at']) ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($ticket['page_name'])): ?>
                        <dt class="col-5 text-meta" style="font-weight:500;font-size:13px">Bug Location</dt>
                        <dd class="col-7">
                            <span style="font-size:13px">
                                <i class="bi bi-diagram-3" style="color:var(--primary)"></i>
                                <?php if ($ticket['project_id']): ?>
                                <a href="<?= site_url('master-projects/' . $ticket['project_id']) ?>" style="text-decoration:none">
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

            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-lightning"></i>
                    Actions
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <?php
                    $status = (int)($ticket['status'] ?? -1);
                    $userId = session('user_id');
                    ?>
                    <?php if ($status === 0): ?>
                        <button class="btn btn-success btn-sm" onclick="doAction('approve')"><i class="bi bi-check-lg"></i> Approve</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="promptAction('reject','Rejection note')"><i class="bi bi-x-lg"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($status === 1): ?>
                        <button class="btn btn-primary btn-sm" onclick="doAction('take')"><i class="bi bi-hand-index"></i> Take Ticket</button>
                    <?php endif; ?>
                    <?php if ($status === 2): ?>
                        <button class="btn btn-success btn-sm" onclick="promptAction('resolve','Resolution note')"><i class="bi bi-check2-all"></i> Resolve</button>
                    <?php endif; ?>
                    <?php if ($status === 3): ?>
                        <button class="btn btn-secondary btn-sm" onclick="doAction('close')"><i class="bi bi-lock"></i> Close</button>
                        <button class="btn btn-warning btn-sm" onclick="promptAction('reopen','Reopen reason')"><i class="bi bi-arrow-counterclockwise"></i> Reopen</button>
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
        confirmButtonColor: '#0F4C81',
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

$(function() {
    $('#commentForm input[name="images[]"]').on('change', function() {
        var preview = $('#commentImagePreview');
        preview.empty();
        var files = this.files;
        if (files.length > 3) {
            toastr.warning('Maximum 3 images');
            $(this).val('');
            return;
        }
        for (var i = 0; i < files.length && i < 3; i++) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.append('<img src="' + e.target.result + '" style="max-width:80px;max-height:60px;object-fit:cover;border-radius:4px;border:1px solid #ddd">');
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
                    toastr_success('Comment added');
                    $('#commentText').val('');
                    $('#commentImagePreview').empty();
                    $('#commentForm input[name="images[]"]').val('');
                    setTimeout(function() { location.reload(); }, 500);
                } else {
                    toastr_error(res.data.message || 'Failed');
                    btn.prop('disabled', false).html('Send');
                }
            },
            error: function() {
                toastr_error('Request failed');
                btn.prop('disabled', false).html('Send');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
