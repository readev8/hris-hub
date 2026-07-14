<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Track Ticket' ?></title>
    <link rel="stylesheet" href="<?= base_url('public/vendor/fonts/plus-jakarta-sans.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/bootstrap/5.3.3/css/bootstrap.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/font-awesome/6.6.0/css/all.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/vendor/toastr/2.1.4/css/toastr.min.css?v=' . config('App')->assetVersion) ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/global/style.css?v=' . config('App')->assetVersion) ?>">
    <style>
        body {
            background: var(--sap-bg, #f5f6f7);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: '72', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .track-container {
            width: 100%;
            max-width: 600px;
            padding: 24px;
        }
        .track-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .track-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--sap-text, #32363a);
            margin-bottom: 8px;
        }
        .track-header p {
            font-size: 14px;
            color: var(--sap-text-secondary, #6a6d70);
        }
        .track-search {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }
        .track-search input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--sap-border, #d9d9d9);
            border-radius: var(--sap-radius, 8px);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .track-search input:focus {
            border-color: var(--sap-brand, #0070f2);
        }
        .track-search button {
            padding: 12px 24px;
            background: var(--sap-brand, #0070f2);
            color: #fff;
            border: none;
            border-radius: var(--sap-radius, 8px);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .track-search button:hover {
            background: var(--sap-brand-hover, #0058b3);
        }
        .track-search button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .track-result {
            display: none;
        }
        .track-ticket-card {
            background: #fff;
            border-radius: var(--sap-radius, 8px);
            border: 1px solid var(--sap-border, #d9d9d9);
            padding: 24px;
            margin-bottom: 24px;
        }
        .track-ticket-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .track-ticket-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--sap-text, #32363a);
        }
        .track-ticket-meta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .track-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .track-badge .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .track-badge.open { background: #e8f4fd; color: #0070f2; }
        .track-badge.open .badge-dot { background: #0070f2; }
        .track-badge.approved { background: #e6f9ee; color: #256f3a; }
        .track-badge.approved .badge-dot { background: #256f3a; }
        .track-badge.in-progress { background: #fef3e2; color: #e76500; }
        .track-badge.in-progress .badge-dot { background: #e76500; }
        .track-badge.resolved { background: #e8f4fd; color: #0070f2; }
        .track-badge.resolved .badge-dot { background: #0070f2; }
        .track-badge.closed { background: #f0f0f0; color: #6a6d70; }
        .track-badge.closed .badge-dot { background: #6a6d70; }
        .track-badge.rejected { background: #fde7e7; color: #aa0808; }
        .track-badge.rejected .badge-dot { background: #aa0808; }
        .track-meta-item {
            font-size: 13px;
            color: var(--sap-text-secondary, #6a6d70);
        }
        .track-meta-item strong {
            color: var(--sap-text, #32363a);
        }
        .track-timeline {
            background: #fff;
            border-radius: var(--sap-radius, 8px);
            border: 1px solid var(--sap-border, #d9d9d9);
            padding: 24px;
        }
        .track-timeline h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--sap-text, #32363a);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .timeline-list {
            position: relative;
            padding-left: 24px;
        }
        .timeline-list::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: var(--sap-border, #d9d9d9);
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-dot {
            position: absolute;
            left: -24px;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--sap-brand, #0070f2);
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px var(--sap-brand, #0070f2);
        }
        .timeline-item:first-child .timeline-dot {
            background: var(--sap-success, #256f3a);
            box-shadow: 0 0 0 2px var(--sap-success, #256f3a);
        }
        .timeline-action {
            font-size: 14px;
            font-weight: 500;
            color: var(--sap-text, #32363a);
            margin-bottom: 2px;
        }
        .timeline-status {
            font-size: 12px;
            color: var(--sap-text-secondary, #6a6d70);
        }
        .timeline-time {
            font-size: 12px;
            color: var(--sap-text-muted, #999);
        }
        .track-empty {
            text-align: center;
            padding: 48px 24px;
            color: var(--sap-text-secondary, #6a6d70);
        }
        .track-empty i {
            font-size: 48px;
            color: var(--sap-text-muted, #d9d9d9);
            margin-bottom: 16px;
            display: block;
        }
        .track-error {
            text-align: center;
            padding: 32px 24px;
            color: var(--sap-error, #aa0808);
            display: none;
        }
        .track-error i {
            font-size: 32px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="track-container">
        <div class="track-header">
            <div style="margin-bottom:16px">
                <i class="fas fa-search" style="font-size:32px;color:var(--sap-brand,#0070f2)"></i>
            </div>
            <h1>Track Your Ticket</h1>
            <p>Enter your tracking code to see the current status and progress</p>
        </div>

        <form class="track-search" id="trackForm" onsubmit="return false;">
            <input type="text" id="trackCode" placeholder="e.g. TKT-20260712-A3F9" value="<?= esc($code ?? '') ?>" autocomplete="off">
            <button type="submit" id="trackBtn" onclick="doTrack()">
                <i class="fas fa-search"></i> Track
            </button>
        </form>

        <div class="track-error" id="trackError">
            <i class="fas fa-exclamation-circle"></i>
            <p id="trackErrorMsg">Ticket not found</p>
        </div>

        <div class="track-result" id="trackResult">
            <div class="track-ticket-card">
                <div class="track-ticket-header">
                    <div class="track-ticket-title" id="ticketTitle"></div>
                </div>
                <div class="track-ticket-meta">
                    <span class="track-badge" id="ticketStatus"></span>
                    <span class="track-meta-item">Type: <strong id="ticketType"></strong></span>
                    <span class="track-meta-item">Priority: <strong id="ticketPriority"></strong></span>
                </div>
                <div class="track-ticket-meta">
                    <span class="track-meta-item"><i class="fas fa-calendar"></i> Created: <strong id="ticketCreated"></strong></span>
                    <span class="track-meta-item" id="ticketDueWrap" style="display:none"><i class="fas fa-clock"></i> Due: <strong id="ticketDue"></strong></span>
                </div>
            </div>

            <div class="track-timeline">
                <h3><i class="fas fa-stream"></i> Status History</h3>
                <div class="timeline-list" id="timelineList"></div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('public/vendor/jquery/3.7.1/jquery.min.js?v=' . config('App')->assetVersion) ?>"></script>
    <script>
    var site_url = '<?= site_url() ?>';

    function escHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function statusClass(statusId) {
        var map = { 0: 'open', 1: 'approved', 2: 'in-progress', 3: 'resolved', 4: 'closed', 5: 'rejected' };
        return map[statusId] || 'open';
    }

    function doTrack() {
        var code = $('#trackCode').val().trim();
        if (!code) {
            toastr.warning('Please enter a tracking code');
            return;
        }

        $('#trackBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Searching...');
        $('#trackResult').hide();
        $('#trackError').hide();

        $.ajax({
            url: site_url + '/track/' + encodeURIComponent(code),
            type: 'GET',
            timeout: 10000,
        })
        .done(function(res) {
            if (res && res.status && res.data && res.data.result) {
                renderTicket(res.data.result);
            } else {
                showError(res.data?.message || 'Ticket not found');
            }
        })
        .fail(function(xhr, status, error) {
            var msg = 'Ticket not found';
            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                msg = xhr.responseJSON.data.message;
            }
            showError(msg);
        })
        .always(function() {
            $('#trackBtn').prop('disabled', false).html('<i class="fas fa-search"></i> Track');
        });
    }

    function renderTicket(data) {
        $('#ticketTitle').text(data.title);
        $('#ticketType').text(data.type_name);
        $('#ticketPriority').text(data.priority_name);
        $('#ticketCreated').text(data.created_at);

        var $status = $('#ticketStatus');
        $status.attr('class', 'track-badge ' + statusClass(data.status));
        $status.html('<span class="badge-dot"></span>' + escHtml(data.status_name));

        if (data.due_date) {
            $('#ticketDue').text(data.due_date);
            $('#ticketDueWrap').show();
        } else {
            $('#ticketDueWrap').hide();
        }

        var timeline = data.timeline || [];
        var html = '';
        if (timeline.length === 0) {
            html = '<div style="color:var(--sap-text-secondary);font-size:13px">No status changes recorded yet.</div>';
        } else {
            for (var i = timeline.length - 1; i >= 0; i--) {
                var t = timeline[i];
                html += '<div class="timeline-item">';
                html += '<div class="timeline-dot"></div>';
                html += '<div class="timeline-action">' + escHtml(t.action) + '</div>';
                if (t.status) {
                    html += '<div class="timeline-status">Status: ' + escHtml(t.status) + '</div>';
                }
                html += '<div class="timeline-time">' + escHtml(t.created_at) + '</div>';
                html += '</div>';
            }
        }
        $('#timelineList').html(html);
        $('#trackResult').show();
    }

    function showError(msg) {
        $('#trackErrorMsg').text(msg);
        $('#trackError').show();
    }

    $(function() {
        $('#trackCode').on('keypress', function(e) {
            if (e.which === 13) doTrack();
        });
        if ($('#trackCode').val().trim()) {
            doTrack();
        }
    });
    </script>
</body>
</html>
