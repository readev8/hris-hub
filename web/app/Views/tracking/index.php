<!--
============================================================================
Track Ticket
============================================================================
Description: Halaman standalone untuk melacak status ticket via tracking code.
Standalone: yes (bukan extends template/index)
-->
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
    <link rel="stylesheet" href="<?= base_url('public/assets/css/page/tracking/index.css?v=' . config('App')->assetVersion) ?>">
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
