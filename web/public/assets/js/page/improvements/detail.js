/**
 * ============================================================================
 * Improvements Detail
 * ============================================================================
 *
 * Description: Improvement detail with approval actions, comments, attachments
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// STATE
// ===========================

var IMPROVEMENT_TOKEN = '';

// ===========================
// PUBLIC API (called from onclick in view)
// ===========================

function doAction(action) {
    $.post(site_url + '/improvements/' + IMPROVEMENT_TOKEN + '/' + action, {}, function(res) {
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
            $.post(site_url + '/improvements/' + IMPROVEMENT_TOKEN + '/reject', { notes: result.value }, function(res) {
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
            $.post(site_url + '/improvements/' + IMPROVEMENT_TOKEN + '/delete', {}, function(res) {
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

// ===========================
// INITIALIZATION
// ===========================

$(function() {
    var data = window.PageData || {};
    IMPROVEMENT_TOKEN = data.token || '';

    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        var text = $('#commentText').val();
        if (!text.trim()) return;
        $.post(site_url + '/improvements/' + IMPROVEMENT_TOKEN + '/comments', { content: text }, function(res) {
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
            url: site_url + '/improvements/' + IMPROVEMENT_TOKEN + '/attachments',
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

    GLightbox({
        selector: '.improvement-attachment-link',
        touchNavigation: true,
        keyboardNavigation: true,
        loop: false,
        preload: true
    });
});
