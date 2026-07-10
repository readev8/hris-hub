<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>
<div class="container">
    <?php if (!$improvement): ?>
        <div class="empty-state">
            <i class="bi bi-exclamation-triangle" style="color:var(--danger)"></i>
            <h4>Improvement not found</h4>
            <p>The improvement you're looking for doesn't exist or has been removed.</p>
            <a href="<?= site_url('improvements') ?>" class="btn btn-outline-secondary mt-3">Back</a>
        </div>
    <?php else: ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('improvements') ?>">Improvements</a></li>
            <li class="breadcrumb-item active"><?= esc($improvement['name']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1" style="font-size:22px"><?= esc($improvement['name']) ?></h1>
            <div class="d-flex align-items-center gap-2">
                <?= status_badge($improvement['status_name'] ?? '') ?>
                <span class="text-meta" style="font-size:13px"><?= esc($improvement['priority_name']) ?></span>
            </div>
        </div>
        <a href="<?= site_url('improvements') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
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
                    // Determine which step rejected
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
                $icons = ['draft' => 'bi-pencil', 'it' => 'bi-laptop', 'dept' => 'bi-people', 'final' => 'bi-check2-all'];
                foreach ($steps as $i => $s):
                    $state = $stepStates[$s['key']] ?? '';
                    $icon = $icons[$s['key']];
                ?>
                <div class="stepper-step <?= esc($state, 'attr') ?>">
                    <div class="stepper-node">
                        <?php if ($state === 'completed'): ?><i class="bi bi-check"></i>
                        <?php elseif ($state === 'rejected'): ?><i class="bi bi-x"></i>
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
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-file-text"></i> Description
                </div>
                <div class="card-body">
                    <p style="line-height:1.7"><?= nl2br(esc($improvement['description'] ?? '')) ?></p>
                    <?php if (!empty($improvement['business_case'])): ?>
                    <h5>Business Case</h5>
                    <p style="line-height:1.7"><?= nl2br(esc($improvement['business_case'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history"></i> Approval History
                </div>
                <div class="card-body">
                    <?php if (empty($improvement['approval_history'])): ?>
                        <div class="empty-state" style="padding:20px">
                            <i class="bi bi-clock-history" style="font-size:36px"></i>
                            <h4>No approval history</h4>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($improvement['approval_history'] as $a): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot <?= (int)($a['status'] ?? 0) === 1 ? 'approved' : ((int)($a['status'] ?? 0) === 2 ? 'rejected' : '') ?>"></div>
                                <div class="timeline-content">
                                    <div class="d-flex align-items-center gap-2">
                                        <?= status_badge($a['status_name'] ?? '') ?>
                                        <span class="fw-medium">Stage <?= esc($a['stage_sequence']) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <?= avatar_initials($a['approver_name'] ?? '?', 'sm', '#64748B') ?>
                                        <span class="text-meta"><?= esc($a['approver_name'] ?? '') ?></span>
                                        <span class="text-muted" style="font-size:12px"><?= esc($a['reviewed_at'] ?? '') ?></span>
                                    </div>
                                    <?php if (!empty($a['notes'])): ?>
                                    <p class="mt-1 mb-0 text-meta" style="font-size:13px;font-style:italic">"<?= esc($a['notes']) ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-chat-dots"></i> Comments
                    <span class="status-badge closed" style="font-size:11px"><?= count($improvement['comments'] ?? []) ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($improvement['comments'])): ?>
                        <div class="empty-state" style="padding:20px">
                            <i class="bi bi-chat-dots" style="font-size:36px"></i>
                            <h4>No comments</h4>
                        </div>
                    <?php else: ?>
                        <?php foreach ($improvement['comments'] as $c): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <?= avatar_initials($c['full_name'] ?? '?', 'sm', '#64748B') ?>
                                <span class="comment-author"><?= esc($c['full_name'] ?? '') ?></span>
                                <span class="comment-time"><?= esc($c['created_at']) ?></span>
                            </div>
                            <div class="comment-body"><?= nl2br(esc($c['content'])) ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <form id="commentForm" class="mt-3" style="border-top:1px solid var(--border);padding-top:16px">
                        <div class="mb-2">
                            <textarea class="form-control" id="commentText" rows="2" placeholder="Write a comment..."></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-send"></i> Send</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle"></i> Details
                </div>
                <div class="card-body" style="font-size:14px">
                    <dl class="row mb-0" style="gap:4px 0">
                        <dt class="col-5 text-meta" style="font-weight:500;font-size:13px">Creator</dt>
                        <dd class="col-7"><?= esc($improvement['creator_name'] ?? '') ?></dd>
                        <dt class="col-5 text-meta" style="font-weight:500;font-size:13px">Status</dt>
                        <dd class="col-7"><?= status_badge($improvement['status_name'] ?? '') ?></dd>
                        <dt class="col-5 text-meta" style="font-weight:500;font-size:13px">Created</dt>
                        <dd class="col-7"><?= esc($improvement['created_at']) ?></dd>
                        <?php if (!empty($improvement['department_name'])): ?>
                        <dt class="col-5 text-meta" style="font-weight:500;font-size:13px">Department</dt>
                        <dd class="col-7"><?= esc($improvement['department_name']) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-lightning"></i> Actions
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <?php $st = (int)($improvement['status'] ?? -1); ?>
                    <?php if ($st === 0): ?>
                        <button class="btn btn-success btn-sm" onclick="doAction('approve-it')"><i class="bi bi-check-lg"></i> Approve (IT)</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="promptReject()"><i class="bi bi-x-lg"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($st === 1): ?>
                        <button class="btn btn-success btn-sm" onclick="doAction('approve-dept')"><i class="bi bi-check-lg"></i> Approve (Dept)</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="promptReject()"><i class="bi bi-x-lg"></i> Reject</button>
                    <?php endif; ?>
                    <?php if ($st === 3): ?>
                        <button class="btn btn-warning btn-sm" onclick="doAction('resubmit')"><i class="bi bi-arrow-counterclockwise"></i> Resubmit</button>
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
    });
}

function promptReject() {
    Swal.fire({
        title: 'Rejection Notes',
        input: 'textarea',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#E11D48',
    }).then(function(result) {
        if (result.isConfirmed && result.value) {
            $.post(site_url + '/improvements/' + token + '/reject', { notes: result.value }, function(res) {
                if (res.status) {
                    toastr.success(res.data.message);
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    toastr.error(res.data.message || 'Failed');
                }
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
            toastr_success('Comment added');
            $('#commentText').val('');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            toastr_error(res.data.message || 'Failed');
        }
    });
});
</script>
<?= $this->endSection() ?>
