<?= $this->extend('template/anonymous') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/_shared/anonymous-layout.css') ?>?v=<?= config('App')->assetVersion ?>">
<link rel="stylesheet" href="<?= base_url('public/assets/css/page/public/tickets_detail.css') ?>?v=<?= config('App')->assetVersion ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="anon-container anon-container--wide">
    <div class="anon-breadcrumb">
        <a href="<?= site_url('public/tickets') ?>">Public Tickets</a>
        <span class="sep">/</span>
        <span class="active" id="breadcrumbCode">Loading...</span>
    </div>

    <a href="<?= site_url('public/tickets') ?>" class="anon-back-link">
        <i class="fas fa-arrow-left"></i> Back to Public Tickets
    </a>

    <div id="ticketError" class="anon-alert anon-alert--error" style="display:none">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Ticket not found</strong>
            <p id="errorMessage">Kode pelacakan mungkin tidak valid atau tiket ini bukan tiket anonim.</p>
        </div>
    </div>

    <div id="ticketLoading" class="anon-loading-skeleton">
        <div class="anon-skeleton-line anon-skeleton-line--lg"></div>
        <div class="anon-skeleton-line anon-skeleton-line--md"></div>
        <div class="anon-skeleton-line anon-skeleton-line--sm"></div>
        <div class="anon-skeleton-line anon-skeleton-line--lg"></div>
        <div class="anon-skeleton-line anon-skeleton-line--md"></div>
    </div>

    <div id="ticketContent" style="display:none">
        <div class="anon-detail-card">
            <div class="anon-detail-header">
                <div class="anon-detail-badges">
                    <span id="statusBadge" class="anon-badge"></span>
                    <span id="typeBadge" class="anon-badge anon-badge--outline"></span>
                    <span id="priorityBadge" class="anon-badge"></span>
                </div>
                <h1 id="ticketTitle" class="anon-detail-title"></h1>
                <div class="anon-detail-meta">
                    <span id="trackingCode" class="anon-tracking-code"></span>
                    <button type="button" id="copyCodeBtn" class="anon-btn-icon" title="Copy tracking code">
                        <i class="fas fa-copy"></i>
                    </button>
                    <span class="anon-meta-sep">&middot;</span>
                    <span id="createdAt" class="anon-meta-text"></span>
                </div>
            </div>

            <div class="anon-detail-layout">
                <aside class="anon-detail-sidebar">
                    <div class="anon-sidebar-block">
                        <h4 class="anon-sidebar-heading">Details</h4>
                        <dl class="anon-sidebar-list">
                            <dt>Type</dt>
                            <dd id="sidebarType"></dd>
                            <dt>Priority</dt>
                            <dd id="sidebarPriority"></dd>
                            <dt>Status</dt>
                            <dd id="sidebarStatus"></dd>
                            <dt>Created</dt>
                            <dd id="sidebarCreated"></dd>
                            <dt id="sidebarResolvedLabel" style="display:none">Resolved</dt>
                            <dd id="sidebarResolved" style="display:none"></dd>
                            <dt id="sidebarClosedLabel" style="display:none">Closed</dt>
                            <dd id="sidebarClosed" style="display:none"></dd>
                        </dl>
                    </div>
                    <div class="anon-sidebar-block">
                        <h4 class="anon-sidebar-heading">Tracking Code</h4>
                        <div class="anon-sidebar-code">
                            <code id="sidebarCode"></code>
                            <button type="button" id="copyCodeBtn2" class="anon-btn-icon" title="Copy">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </aside>

                <main class="anon-detail-main">
                    <section class="anon-detail-section">
                        <h3 class="anon-section-heading"><i class="fas fa-align-left"></i> Description</h3>
                        <div id="ticketDescription" class="anon-description"></div>
                    </section>

                    <div class="anon-section-divider"></div>

                    <section id="commentsSection" class="anon-detail-section" style="display:none">
                        <h3 class="anon-section-heading">
                            <i class="fas fa-comments"></i> Discussion <span id="commentCount" class="anon-comment-count"></span>
                        </h3>
                        <div id="commentsList" class="anon-comments"></div>
                    </section>

                    <div id="commentsDivider" class="anon-section-divider" style="display:none"></div>

                    <section id="attachmentsSection" class="anon-detail-section" style="display:none">
                        <h3 class="anon-section-heading"><i class="fas fa-paperclip"></i> Attachments</h3>
                        <div id="attachmentsList" class="anon-attachments-grid"></div>
                    </section>

                    <div id="attachmentsDivider" class="anon-section-divider" style="display:none"></div>

                    <section id="timelineSection" class="anon-detail-section" style="display:none">
                        <h3 class="anon-section-heading"><i class="fas fa-clock"></i> Timeline</h3>
                        <div id="timeline" class="anon-timeline"></div>
                    </section>

                    <div id="statusBarSection" class="anon-status-bar" style="display:none">
                        <div class="anon-status-info">
                            <i id="statusBarIcon" class="fas"></i>
                            <div class="anon-status-text">
                                <span id="statusBarLabel"></span>
                                <span id="statusBarBy" class="anon-status-by"></span>
                                <span id="statusBarDate" class="anon-status-date"></span>
                            </div>
                            <span id="statusBarNote" class="anon-status-note"></span>
                        </div>
                        <div id="statusBarActions">
                            <button type="button" id="closeTicketBtn" class="anon-btn anon-btn-close-flat">
                                <i class="fas fa-times"></i> Close Ticket
                            </button>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>window.PageData = <?= json_encode(['ajaxBaseUrl' => site_url('public/tickets'), 'trackingCode' => $tracking_code]) ?>;</script>
<script src="<?= base_url('public/assets/js/page/public/tickets_detail.js') ?>?v=<?= config('App')->assetVersion ?>"></script>
<?= $this->endSection() ?>
