/**
 * Anonymous Ticket Detail Page
 * @package App\Views\public
 * @file    tickets_detail.js
 */
/* global $, site_url, toastr */

(function () {
    'use strict';

    var pageData = window.PageData || {};
    var baseUrl  = pageData.ajaxBaseUrl || site_url + '/public/tickets';
    var code     = pageData.trackingCode || '';

    $(function () {
        if (!code) {
            showError('Kode pelacakan tidak tersedia.');
            return;
        }
        loadTicket(code);
        bindCopyCode();
        bindCloseTicket();
    });

    function loadTicket(code) {
        $('#ticketLoading').show();
        $.get(baseUrl + '/ajax-lookup/' + code, function (res) {
            $('#ticketLoading').hide();
            if (res && res.tracking_code) {
                renderTicket(res);
            } else {
                showError('Ticket tidak ditemukan.');
            }
        }).fail(function (xhr) {
            $('#ticketLoading').hide();
            var msg = 'Kode pelacakan mungkin tidak valid atau tiket ini bukan tiket anonim.';
            if (xhr.status === 404) msg = 'Ticket tidak ditemukan.';
            showError(msg);
        });
    }

    function renderTicket(t) {
        $('#breadcrumbCode').text(t.tracking_code);
        $('#ticketTitle').text(t.title);
        $('#trackingCode').text(t.tracking_code);
        $('#createdAt').text(formatDateTime(t.created_at));

        $('#statusBadge').attr('class', 'anon-badge anon-badge--' + getStatusClass(t.status)).text(t.status_name);
        $('#typeBadge').text(t.type_name);

        var priorityClass = getPriorityClass(t.priority);
        $('#priorityBadge').attr('class', 'anon-badge ' + priorityClass).text(t.priority_name);

        renderSidebar(t);
        renderStatusBar(t);

        if (t.description) {
            $('#ticketDescription').html(nl2br(escHtml(t.description)));
        } else {
            $('#ticketDescription').text('No description provided.');
        }

        renderComments(t.comments || []);

        if (t.attachments && t.attachments.length) {
            $('#attachmentsSection').show();
            $('#attachmentsDivider').show();
            var $grid = $('#attachmentsList').empty();
            for (var i = 0; i < t.attachments.length; i++) {
                var att = t.attachments[i];
                var url = site_url + '/public/attachment/' + att.stored_name;
                if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                    $grid.append('<a href="' + url + '" target="_blank" class="anon-attachment-thumb"><img src="' + url + '" alt="' + escAttr(att.filename) + '" loading="lazy"></a>');
                } else {
                    var icon = getFileIcon(att.mime_type);
                    $grid.append('<a href="' + url + '" target="_blank" class="anon-attachment-file"><i class="fas ' + icon + '"></i> ' + escHtml(att.filename) + '</a>');
                }
            }
        }

        renderTimeline(t.timeline || []);

        $('#ticketError').hide();
        $('#ticketContent').show();
    }

    function renderSidebar(t) {
        $('#sidebarType').text(t.type_name);
        $('#sidebarPriority').text(t.priority_name);
        $('#sidebarStatus').text(t.status_name);
        $('#sidebarCreated').text(formatDateTime(t.created_at));
        $('#sidebarCode').text(t.tracking_code);

        if (t.resolved_at) {
            $('#sidebarResolvedLabel').show();
            $('#sidebarResolved').show().text(formatDateTime(t.resolved_at));
        }
        if (t.closed_at) {
            $('#sidebarClosedLabel').show();
            $('#sidebarClosed').show().text(formatDateTime(t.closed_at));
        }
    }

    function renderStatusBar(t) {
        var $bar = $('#statusBarSection');
        var $icon = $('#statusBarIcon');
        var $label = $('#statusBarLabel');
        var $by = $('#statusBarBy');
        var $date = $('#statusBarDate');
        var $note = $('#statusBarNote');
        var $actions = $('#statusBarActions');

        if (t.status === 3) {
            // Resolved
            $icon.attr('class', 'fas fa-check-circle anon-status-icon--resolved');
            $label.text('Resolved');
            $by.text('by ' + (t.resolver_name || 'Support Team'));
            $date.text(t.resolved_at ? formatDateTime(t.resolved_at) : '');
            $note.text(t.resolution_note || '');
            $actions.show();
            $bar.show();
        } else if (t.status === 4) {
            // Closed
            $icon.attr('class', 'fas fa-times-circle anon-status-icon--closed');
            $label.text('Closed');
            $by.text(t.closed_at ? formatDateTime(t.closed_at) : '');
            $date.text('');
            $note.text('');
            $actions.hide();
            $bar.show();
        } else if (t.status === 5 && t.rejection_note) {
            // Rejected
            $icon.attr('class', 'fas fa-times-circle anon-status-icon--rejected');
            $label.text('Rejected');
            $by.text('');
            $date.text('');
            $note.text(t.rejection_note);
            $actions.hide();
            $bar.show();
        } else {
            $bar.hide();
        }
    }

    function renderComments(comments) {
        if (!comments || !comments.length) {
            $('#commentsSection').show();
            $('#commentsDivider').show();
            $('#commentsList').html('<div class="anon-comments-empty"><i class="fas fa-comments"></i><p>Belum ada komentar dari tim developer.</p></div>');
            return;
        }
        $('#commentsSection').show();
        $('#commentsDivider').show();
        $('#commentCount').text(comments.length);
        var $list = $('#commentsList').empty();
        for (var i = 0; i < comments.length; i++) {
            var c = comments[i];
            var initials = getInitials(c.full_name);
            var $comment = $('<div class="anon-comment"></div>');
            var headHtml = '<div class="anon-comment-head">';
            headHtml += '<span class="anon-comment-author">' + escHtml(c.full_name || 'Unknown') + '</span>';
            headHtml += '<span class="anon-comment-time">' + formatDateTime(c.created_at) + '</span>';
            headHtml += '</div>';

            var bodyHtml = '<div class="anon-comment-body">' + nl2br(escHtml(c.content)) + '</div>';

            var attHtml = '';
            if (c.attachments && c.attachments.length) {
                attHtml = '<div class="anon-comment-attachments">';
                for (var j = 0; j < c.attachments.length; j++) {
                    var att = c.attachments[j];
                    var url = site_url + '/public/attachment/' + att.stored_name;
                    if (att.mime_type && att.mime_type.indexOf('image/') === 0) {
                        attHtml += '<a href="' + url + '" target="_blank" class="anon-attachment-thumb"><img src="' + url + '" alt="' + escAttr(att.filename) + '" loading="lazy"></a>';
                    } else {
                        var icon = getFileIcon(att.mime_type);
                        attHtml += '<a href="' + url + '" target="_blank" class="anon-attachment-file"><i class="fas ' + icon + '"></i> ' + escHtml(att.filename) + '</a>';
                    }
                }
                attHtml += '</div>';
            }

            $comment.append(
                '<div class="anon-comment-avatar">' + escHtml(initials) + '</div>' +
                '<div class="anon-comment-main">' + headHtml + bodyHtml + attHtml + '</div>'
            );
            $list.append($comment);
        }
    }

    function renderTimeline(timeline) {
        if (!timeline || !timeline.length) return;
        $('#timelineSection').show();
        var $tl = $('#timeline').empty();
        for (var j = 0; j < timeline.length; j++) {
            var item = timeline[j];
            var dotClass = getTimelineDotClass(item.action);
            var html = '<div class="anon-timeline-item">';
            html += '<div class="anon-timeline-dot ' + dotClass + '"></div>';
            html += '<div class="anon-timeline-action">' + escHtml(item.action) + '</div>';
            if (item.status) {
                html += '<div class="anon-timeline-status">Status: ' + escHtml(item.status) + '</div>';
            }
            html += '<div class="anon-timeline-time">' + formatDateTime(item.created_at) + '</div>';
            html += '</div>';
            $tl.append(html);
        }
    }

    function showError(msg) {
        $('#errorMessage').text(msg);
        $('#ticketContent').hide();
        $('#ticketLoading').hide();
        $('#ticketError').show();
    }

    function bindCopyCode() {
        $('#copyCodeBtn, #copyCodeBtn2').on('click', function () {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(code).then(function () {
                    toastr.success('Tracking code copied!');
                });
            } else {
                var $temp = $('<input>');
                $('body').append($temp);
                $temp.val(code).trigger('select');
                document.execCommand('copy');
                $temp.remove();
                toastr.success('Tracking code copied!');
            }
        });
    }

    function bindCloseTicket() {
        $('#closeTicketBtn').on('click', function () {
            if (!confirm('Apakah Anda yakin ingin menutup tiket ini?')) return;
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Closing...');
            $.ajax({
                url: baseUrl + '/' + code + '/close',
                type: 'POST',
                success: function (res) {
                    if (res.status) {
                        toastr.success(res.message || 'Ticket berhasil ditutup');
                        setTimeout(function () { location.reload(); }, 1000);
                    } else {
                        toastr.error(res.message || 'Gagal menutup ticket');
                        $btn.prop('disabled', false).html('<i class="fas fa-times"></i> Close Ticket');
                    }
                },
                error: function () {
                    toastr.error('Gagal menutup ticket');
                    $btn.prop('disabled', false).html('<i class="fas fa-times"></i> Close Ticket');
                }
            });
        });
    }

    function getStatusClass(status) {
        var map = { 0: 'open', 1: 'approved', 2: 'in-progress', 3: 'resolved', 4: 'closed', 5: 'rejected' };
        return map[status] || 'open';
    }

    function getPriorityClass(priority) {
        var map = {
            0: 'anon-badge--priority-low',
            1: 'anon-badge--priority-medium',
            2: 'anon-badge--priority-high',
            3: 'anon-badge--priority-critical'
        };
        return map[priority] || 'anon-badge--priority-medium';
    }

    function getTimelineDotClass(action) {
        if (action.indexOf('submit') !== -1 || action.indexOf('create') !== -1) return 'anon-timeline-dot--create';
        if (action.indexOf('resolve') !== -1) return 'anon-timeline-dot--resolve';
        if (action.indexOf('close') !== -1) return 'anon-timeline-dot--close';
        if (action.indexOf('reject') !== -1) return 'anon-timeline-dot--reject';
        return '';
    }

    function getFileIcon(mime) {
        if (!mime) return 'fa-file';
        if (mime.indexOf('pdf') !== -1) return 'fa-file-pdf';
        if (mime.indexOf('word') !== -1 || mime.indexOf('document') !== -1) return 'fa-file-word';
        if (mime.indexOf('sheet') !== -1 || mime.indexOf('excel') !== -1) return 'fa-file-excel';
        if (mime.indexOf('presentation') !== -1 || mime.indexOf('powerpoint') !== -1) return 'fa-file-powerpoint';
        return 'fa-file';
    }

    function getInitials(name) {
        if (!name) return '?';
        var parts = name.trim().split(/\s+/);
        if (parts.length >= 2) {
            return parts[0].charAt(0) + parts[parts.length - 1].charAt(0);
        }
        return parts[0].charAt(0);
    }

    function formatDateTime(str) {
        if (!str) return '';
        var d = new Date(str);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear() + ' ' +
            pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function pad(n) { return n < 10 ? '0' + n : String(n); }

    function nl2br(s) {
        return String(s).replace(/\n/g, '<br>');
    }

    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function escAttr(s) {
        return String(s || '').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
    }
})();
