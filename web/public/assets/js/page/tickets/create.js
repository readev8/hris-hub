/**
 * ============================================================================
 * Tickets Create
 * ============================================================================
 *
 * Description: Ticket creation form with page search modal, file upload,
 * segmented control, and referral search.
 *
 * Dependencies: jQuery, Bootstrap, Toastr
 * Date: 2026-08-18
 */

// ===========================
// STATE
// ===========================

var referralSearchTimer = null;
var pageSearchTimer = null;
var selectedPageId = null;

// ===========================
// CONSTANTS
// ===========================

var API_ENDPOINTS = {
    CREATE: site_url + '/tickets/create',
    PAGE_SEARCH: site_url + '/tickets/ajax/pages-search',
    TICKET_LIST: site_url + '/tickets/ajax-list'
};

// ===========================
// EVENTS
// ===========================

$(function() {
    // Segmented control
    $('#prioritySegments .seg-option').on('click', function() {
        $('#prioritySegments .seg-option').removeClass('active');
        $(this).addClass('active');
        $('#priorityValue').val($(this).data('value'));
    });

    // Approval checkbox - show/hide approver dropdown
    $('#needsApproval').on('change', function() {
        if ($(this).is(':checked')) {
            $('#approverSection').slideDown(200);
        } else {
            $('#approverSection').slideUp(200);
            $('#approverSelect').val('');
        }
    });

    // Type change - show/hide bug trace section
    $('#ticketType').on('change', function() {
        if (['0','3','4','5'].includes($(this).val())) {
            $('#bugTraceSection').slideDown(200);
        } else {
            $('#bugTraceSection').slideUp(200);
            $('#pageIdValue').val('');
            $('#pageSelectDisplay').val('');
            selectedPageId = null;
        }
    });

    // Page search
    $('#searchPageBtn, #pageSelectDisplay').on('click', function() {
        loadPageList(1, '');
        $('#pageSearch').val('');
        $('#pageModal').modal('show');
    });

    $('#pageSearch').on('keyup', function() {
        clearTimeout(pageSearchTimer);
        var searchVal = $(this).val();
        pageSearchTimer = setTimeout(function() {
            loadPageList(1, searchVal);
        }, 300);
    });

    $(document).on('click', '.btn-select-page', function() {
        selectedPageId = $(this).data('id') || $(this).attr('data-id');
        $('#pageIdValue').val(selectedPageId);
        $('#pageSelectDisplay').val($(this).data('name') + ' — ' + $(this).data('module') + ' / ' + $(this).data('project'));
        $('#pageSelectDisplay').css('border-color', 'var(--sap-success)');
        $('#pageModal').modal('hide');
        toastr.success('Page selected: ' + $(this).data('name'));
    });

    // Referral search
    $('#searchReferralBtn, #referralInput').on('click', function() {
        loadReferralList(1, '');
        $('#referralSearch').val('');
        $('#referralModal').modal('show');
    });

    $('#referralSearch').on('keyup', function() {
        clearTimeout(referralSearchTimer);
        var searchVal = $(this).val();
        referralSearchTimer = setTimeout(function() {
            loadReferralList(1, searchVal);
        }, 300);
    });

    $(document).on('click', '.btn-select-referral', function() {
        $('#referralInput').val($(this).data('code'));
        $('#referralModal').modal('hide');
    });

    // File dropzone
    $('#dropzone').on('dragover', function(e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--sap-brand)').css('background', 'var(--sap-brand-hover)');
    }).on('dragleave', function() {
        $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--sap-border)').css('background', 'transparent');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            var input = $(this).find('input[type="file"]')[0];
            input.files = files;
            $(input).trigger('change');
        }
    });

    $('#ticketForm input[name="images[]"]').on('change', function() {
        var preview = $('#imagePreview');
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
                preview.append('<img src="' + e.target.result + '" style="max-width:120px;max-height:90px;object-fit:cover;border-radius:6px;border:1px solid var(--sap-border)">');
            };
            reader.readAsDataURL(files[i]);
        }
    });

    // Form submission
    $('#ticketForm').on('submit', function(e) {
        e.preventDefault();
        var type = $('#ticketType').val();
        var pageId = selectedPageId || $('#pageIdValue').val();
        if (['0','3','4','5'].includes(type) && !pageId) {
            toastr.warning('Untuk tipe ini, wajib memilih halaman di bagian Affected Page');
            $('#bugTraceSection').slideDown(200);
            return;
        }
        var btn = $(this).find('[type="submit"]');
        btn.prop('disabled', true).html('<span class="sap-spinner sap-spinner-sm"></span> Submitting...');
        var formData = new FormData(this);
        $.ajax({
            url: API_ENDPOINTS.CREATE,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status && res.redirect) {
                    var msg = 'Ticket created successfully';
                    if (res.tracking_code) {
                        msg += '\nTracking code: ' + res.tracking_code;
                    }
                    toastr.success(msg, '', { timeOut: 5000 });
                    setTimeout(function() { window.location.href = res.redirect; }, 1500);
                } else {
                    var msg = res.message || 'Failed to create ticket';
                    if (res.errors && typeof res.errors === 'object') {
                        var details = Object.values(res.errors).join(', ');
                        msg += ': ' + details;
                    }
                    toastr.error(msg);
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
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
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit');
            }
        });
    });
});

// ===========================
// HELPERS
// ===========================

function escHtml(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function escAttr(s) {
    return String(s || '').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
}

// ===========================
// PAGE LIST MODAL
// ===========================

function loadPageList(page, search) {
    var $list = $('#pageList');
    $list.html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

    $.get(API_ENDPOINTS.PAGE_SEARCH, {
        page: page,
        per_page: 15,
        search: search
    }, function(res) {
        var rows = res.data || [];
        if (!rows.length) {
            $list.html('<div class="text-center py-3 text-muted">No pages found</div>');
            $('#pagePagination').html('');
            return;
        }
        var html = '<table class="table table-sm table-hover mb-0">';
        html += '<thead><tr>';
        html += '<th>Page</th>';
        html += '<th>Module</th>';
        html += '<th>Project</th>';
        html += '<th style="width:100px"></th>';
        html += '</tr></thead><tbody>';
        for (var i = 0; i < rows.length; i++) {
            var p = rows[i];
            var pageId = p.page_id || p.id || '';
            html += '<tr>';
            html += '<td style="font-size:13px;font-weight:500">' + escHtml(p.page_name || '') + '</td>';
            html += '<td style="font-size:12px;color:var(--sap-text-secondary)">' + escHtml(p.module_name || '') + '</td>';
            html += '<td style="font-size:12px;color:var(--sap-text-secondary)">' + escHtml(p.project_name || '') + '</td>';
            html += '<td><button type="button" class="sap-btn sap-btn-primary sap-btn-sm btn-select-page" ';
            html += 'data-id="' + escAttr(pageId) + '" data-name="' + escAttr(p.page_name) + '" ';
            html += 'data-module="' + escAttr(p.module_name) + '" data-project="' + escAttr(p.project_name) + '">Select</button></td>';
            html += '</tr>';
        }
        html += '</tbody></table>';
        $list.html(html);

        var totalPages = Math.ceil((res.total || 0) / 15);
        var pagHtml = '';
        if (totalPages > 1) {
            pagHtml += '<button type="button" class="sap-btn sap-btn-secondary sap-btn-sm btn-page-page" data-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '><i class="fas fa-chevron-left"></i> Prev</button>';
            pagHtml += '<span style="font-size:13px;color:var(--sap-text-secondary);padding:6px 12px">' + page + ' / ' + totalPages + '</span>';
            pagHtml += '<button type="button" class="sap-btn sap-btn-secondary sap-btn-sm btn-page-page" data-page="' + (page + 1) + '"' + (page >= totalPages ? ' disabled' : '') + '>Next <i class="fas fa-chevron-right"></i></button>';
        }
        $('#pagePagination').html(pagHtml);
    }).fail(function() {
        $list.html('<div class="text-center py-3 text-danger">Failed to load pages</div>');
    });
}

$(document).on('click', '.btn-page-page', function() {
    if ($(this).prop('disabled')) return;
    loadPageList(parseInt($(this).data('page')), $('#pageSearch').val());
});

// ===========================
// REFERRAL LIST MODAL
// ===========================

function loadReferralList(page, search) {
    var $list = $('#referralTicketList');
    $list.html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

    $.get(API_ENDPOINTS.TICKET_LIST, {
        page: page,
        per_page: 10,
        search: search
    }, function(res) {
        var rows = res.data || [];
        if (!rows.length) {
            $list.html('<div class="text-center py-3 text-muted">No tickets found</div>');
            $('#referralPagination').html('');
            return;
        }
        var html = '<table class="table table-sm table-hover mb-0">';
        html += '<thead><tr>';
        html += '<th style="width:140px">Code</th>';
        html += '<th>Title</th>';
        html += '<th style="width:100px">Status</th>';
        html += '<th style="width:80px"></th>';
        html += '</tr></thead><tbody>';
        for (var i = 0; i < rows.length; i++) {
            var t = rows[i];
            html += '<tr>';
            html += '<td><code style="font-size:12px;background:var(--sap-background);padding:2px 6px;border-radius:4px">' + escHtml(t.tracking_code || '') + '</code></td>';
            html += '<td style="font-size:13px">' + escHtml(t.title || '') + '</td>';
            html += '<td><span class="sap-badge info" style="font-size:11px;padding:2px 8px">' + escHtml(t.status_name || '') + '</span></td>';
            html += '<td><button type="button" class="sap-btn sap-btn-primary sap-btn-sm btn-select-referral" data-code="' + escAttr(t.tracking_code) + '" style="font-size:11px;padding:4px 10px">Select</button></td>';
            html += '</tr>';
        }
        html += '</tbody></table>';
        $list.html(html);

        var totalPages = Math.ceil((res.total || 0) / 10);
        var pagHtml = '';
        if (totalPages > 1) {
            pagHtml += '<button type="button" class="sap-btn sap-btn-secondary sap-btn-sm btn-referral-page" data-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '><i class="fas fa-chevron-left"></i> Prev</button>';
            pagHtml += '<span style="font-size:13px;color:var(--sap-text-secondary);padding:6px 12px">' + page + ' / ' + totalPages + '</span>';
            pagHtml += '<button type="button" class="sap-btn sap-btn-secondary sap-btn-sm btn-referral-page" data-page="' + (page + 1) + '"' + (page >= totalPages ? ' disabled' : '') + '>Next <i class="fas fa-chevron-right"></i></button>';
        }
        $('#referralPagination').html(pagHtml);
    }).fail(function() {
        $list.html('<div class="text-center py-3 text-danger">Failed to load tickets</div>');
    });
}

$(document).on('click', '.btn-referral-page', function() {
    if ($(this).prop('disabled')) return;
    loadReferralList(parseInt($(this).data('page')), $('#referralSearch').val());
});
