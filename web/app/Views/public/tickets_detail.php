<?= $this->extend('template/anonymous') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/_shared/anonymous-layout.css') ?>?v=<?= config('App')->assetVersion ?>">
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/tickets_detail.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="anon-container">
    <div class="anon-breadcrumb">
        <a href="<?= site_url('public/tickets') ?>">My Tickets</a>
        <span class="sep">/</span>
        <span class="active" id="breadcrumbCode">Loading...</span>
    </div>

    <a href="<?= site_url('public/tickets') ?>" class="anon-back-link">
        <i class="fas fa-arrow-left"></i> Back to My Tickets
    </a>

    <div id="ticketError" class="anon-alert anon-alert--error" style="display:none">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Ticket not found</strong>
            <p id="errorMessage">Kode pelacakan mungkin tidak valid atau tiket ini bukan tiket anonim.</p>
        </div>
    </div>

    <div id="ticketContent" style="display:none">
        <div class="anon-ticket-header">
            <div class="anon-ticket-badges">
                <span id="statusBadge" class="anon-badge"></span>
                <span id="typeBadge" class="anon-badge anon-badge--outline"></span>
                <span id="priorityBadge" class="anon-badge anon-badge--outline"></span>
            </div>
            <h1 id="ticketTitle"></h1>
            <div class="anon-ticket-meta">
                <span id="trackingCode" class="anon-tracking-code"></span>
                <button type="button" id="copyCodeBtn" class="anon-btn-icon" title="Copy tracking code">
                    <i class="fas fa-copy"></i>
                </button>
                <span class="anon-meta-sep">&middot;</span>
                <span id="createdAt" class="anon-meta-text"></span>
            </div>
        </div>

        <div id="resolutionCallout" class="anon-resolution-callout" style="display:none">
            <div class="anon-resolution-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="anon-resolution-body">
                <div class="anon-resolution-meta">
                    <span class="anon-resolution-label">Resolved</span>
                    <span class="anon-resolution-by" id="resolvedBy"></span>
                    <span class="anon-resolution-date" id="resolvedDate"></span>
                </div>
                <div class="anon-resolution-text" id="resolutionText"></div>
            </div>
        </div>

        <div id="ticketLoading" class="anon-loading-skeleton">
            <div class="anon-skeleton-line anon-skeleton-line--lg"></div>
            <div class="anon-skeleton-line anon-skeleton-line--md"></div>
            <div class="anon-skeleton-line anon-skeleton-line--sm"></div>
            <div class="anon-skeleton-line anon-skeleton-line--lg"></div>
            <div class="anon-skeleton-line anon-skeleton-line--md"></div>
        </div>

        <div class="anon-ticket-body">
            <div class="anon-section">
                <h3 class="anon-section-title">Description</h3>
                <div id="ticketDescription" class="anon-description"></div>
            </div>

            <div id="commentsSection" class="anon-section" style="display:none">
                <h3 class="anon-section-title">
                    Discussion <span id="commentCount" class="anon-comment-count"></span>
                </h3>
                <div id="commentsList" class="anon-comments"></div>
            </div>

            <div id="attachmentsSection" class="anon-section" style="display:none">
                <h3 class="anon-section-title">Attachments</h3>
                <div id="attachmentsList" class="anon-attachments-grid"></div>
            </div>

            <div id="timelineSection" class="anon-section" style="display:none">
                <h3 class="anon-section-title">Timeline</h3>
                <div id="timeline" class="anon-timeline"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['ajaxBaseUrl' => site_url('public/tickets'), 'trackingCode' => $tracking_code]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/public/_shared/tracking-store.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<script src="<?= base_url('public/assets/js/page/public/tickets_detail.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
