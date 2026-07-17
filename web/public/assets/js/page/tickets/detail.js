/**
 * ============================================================================
 * Tickets Detail
 * ============================================================================
 *
 * Description: Ticket detail page with actions, comments, approval workflow
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// PUBLIC API (called from onclick in view)
// ===========================

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
    }).fail(function(xhr) {
        toastr.error('Gagal melakukan aksi (HTTP ' + xhr.status + ')');
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
            if (action === 'reopen') data.rejection_note = result.value;
            if (action === 'reject') data.rejection_note = result.value;
            $.post(site_url + '/tickets/' + token + '/' + action, data, function(res) {
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
                }).fail(function(xhr) {
                    toastr.error('Gagal menugaskan ticket (HTTP ' + xhr.status + ')');
                });
            }
        });
    }).fail(function(xhr) {
        toastr.error('Gagal memuat daftar user (HTTP ' + xhr.status + ')');
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
            }).fail(function(xhr) {
                toastr.error('Gagal menghapus ticket (HTTP ' + xhr.status + ')');
            });
        }
    });
}

function doApproval(action) {
    var btn = event && event.target ? $(event.target).closest('button') : null;
    $.post(site_url + '/tickets/' + token + '/' + action, {}, function(res) {
        if (res.status) {
            toastr.success(res.data.message);
            setTimeout(function() { location.reload(); }, 800);
        } else {
            toastr.error(res.data.message || 'Action failed');
        }
    }).fail(function(xhr) {
        toastr.error('Gagal melakukan aksi (HTTP ' + xhr.status + ')');
    }, btn);
}

function promptRejectApproval() {
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
            $.post(site_url + '/tickets/' + token + '/reject-approval', { notes: result.value }, function(res) {
                if (res.status) {
                    toastr.success(res.data.message);
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    toastr.error(res.data.message || 'Failed to reject');
                }
            }).fail(function(xhr) {
                toastr.error('Gagal melakukan aksi (HTTP ' + xhr.status + ')');
            });
        }
    });
}

function submitResolve() {
    var note = $('#resolveNote').val();
    if (!note || !note.trim()) {
        toastr.warning('Resolution summary wajib diisi');
        $('#resolveNote').focus();
        return;
    }

    var fileInput = $('#resolveFileInput')[0];
    var files = fileInput.files;
    if (files.length > 5) {
        toastr.warning('Maksimal 5 file');
        return;
    }
    for (var i = 0; i < files.length; i++) {
        if (files[i].size > 5 * 1024 * 1024) {
            toastr.warning(files[i].name + ' melebihi batas 5MB');
            return;
        }
    }

    var btn = $('#resolveSubmitBtn');
    btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Resolving...');

    var formData = new FormData($('#resolveForm')[0]);
    $.ajax({
        url: site_url + '/tickets/' + token + '/resolve',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status) {
                $('#resolveModal').modal('hide');
                toastr.success(res.data.message);
                setTimeout(function() { location.reload(); }, 800);
            } else {
                toastr.error(res.data.message || 'Gagal meresolusi ticket');
                btn.prop('disabled', false).html('<i class="fas fa-check-double"></i> Resolve Ticket');
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
            btn.prop('disabled', false).html('<i class="fas fa-check-double"></i> Resolve Ticket');
        }
    });
}

// ===========================
// STATE
// ===========================

var token = window.PageData ? window.PageData.token : '';

// ===========================
// INITIALIZATION
// ===========================

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

    // Resolve modal — file preview
    $('#resolveFileInput').on('change', function() {
        var preview = $('#resolvePreview');
        preview.empty();
        var files = this.files;
        if (files.length > 5) {
            toastr.warning('Maximum 5 files');
            $(this).val('');
            return;
        }
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (file.size > 5 * 1024 * 1024) {
                toastr.warning(file.name + ' exceeds 5MB limit');
                continue;
            }
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = (function(f) {
                    return function(e) {
                        preview.append(
                            '<div class="resolve-file-item" style="position:relative;display:inline-block">' +
                            '<img src="' + e.target.result + '" style="max-width:100px;max-height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)">' +
                            '<div style="font-size:10px;color:var(--sap-text-secondary);text-align:center;margin-top:2px;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + f.name + '</div>' +
                            '</div>'
                        );
                    };
                })(file);
                reader.readAsDataURL(file);
            } else {
                var icon = 'fa-file';
                if (file.type.includes('pdf')) icon = 'fa-file-pdf';
                else if (file.type.includes('word') || file.type.includes('document')) icon = 'fa-file-word';
                else if (file.type.includes('excel') || file.type.includes('spreadsheet')) icon = 'fa-file-excel';
                else if (file.type.includes('powerpoint') || file.type.includes('presentation')) icon = 'fa-file-powerpoint';
                preview.append(
                    '<div class="resolve-file-item" style="display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:6px;border:1px solid var(--sap-border);background:var(--sap-surface);max-width:180px">' +
                    '<i class="fas ' + icon + '" style="font-size:20px;color:var(--sap-text-secondary);flex-shrink:0"></i>' +
                    '<div style="min-width:0"><div style="font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + file.name + '</div>' +
                    '<div style="font-size:10px;color:var(--sap-text-muted)">' + (file.type || 'file') + '</div></div>' +
                    '</div>'
                );
            }
        }
    });

    // Resolve modal — drag & drop
    $('#resolveDropzone').on('dragover', function(e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--sap-success)').css('background', 'rgba(16,136,62,0.04)');
    }).on('dragleave', function() {
        $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            var input = $('#resolveFileInput')[0];
            input.files = files;
            $(input).trigger('change');
        }
    });

    // Resolve modal — reset on close
    $('#resolveModal').on('hidden.bs.modal', function() {
        $('#resolveNote').val('');
        $('#resolvePreview').empty();
        $('#resolveFileInput').val('');
        $('#resolveSubmitBtn').prop('disabled', false).html('<i class="fas fa-check-double"></i> Resolve Ticket');
    });

    // GLightbox initialization
    if (typeof GLightbox !== 'undefined') {
        GLightbox({
            selector: '.ticket-attachment-link',
            touchNavigation: true,
            keyboardNavigation: true,
            loop: false,
            preload: true
        });

        GLightbox({
            selector: '.comment-attachment-link',
            touchNavigation: true,
            keyboardNavigation: true,
            loop: true,
            preload: true
        });
    }
});
