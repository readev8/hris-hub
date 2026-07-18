/**
 * ============================================================================
 * Tickets Main Page
 * ============================================================================
 *
 * Description: DataTable with column search, tracking modal for ticket codes
 * Date: 2026-07-16
 * Standard: Mini (<400 lines)
 */

// ===========================
// CONSTANTS
// ===========================

var statusMap = { 0: 'Open', 1: 'Approved', 2: 'In Progress', 3: 'Resolved', 4: 'Closed', 5: 'Rejected' };
var priorityMap = { 0: 'Low', 1: 'Medium', 2: 'High', 3: 'Critical' };

// ===========================
// TRACKING MODAL
// ===========================

var currentTrackingCode = '';

function showTrackingModal(code) {
    currentTrackingCode = code;
    $('#trackingCodeDisplay').text(code);
    $('#trackingModal').css('display', 'flex');
}

function closeTrackingModal() {
    $('#trackingModal').css('display', 'none');
    currentTrackingCode = '';
}

function copyTrackingCode() {
    navigator.clipboard.writeText(currentTrackingCode).then(function() {
        toastr.success('Tracking code copied!');
    });
}

function copyTrackingLink() {
    var link = site_url + '/track/' + currentTrackingCode;
    navigator.clipboard.writeText(link).then(function() {
        toastr.success('Tracking link copied!');
    });
}

// ===========================
// INITIALIZATION
// ===========================

$(function() {
    $('#trackingModal').on('click', function(e) {
        if (e.target === this) closeTrackingModal();
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') closeTrackingModal();
    });

    var table = $('#tickets-table').DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({ header: function(row) { return 'Details: ' + row.data().title; }}),
                renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'sap-table mb-0' })
            }
        },
        ajax: {
            url: site_url + '/tickets/ajax-list',
            data: function (d) {
                var status = $('#statusFilter').val();
                var overdue = $('#overdueFilter').is(':checked') ? 1 : '';
                if (status !== null && status !== '') d.status = status;
                if (overdue) d.overdue = overdue;
            },
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                render: function(d) {
                    return '<span class="text-muted mono">' + (d ? d.slice(0,8) : '') + '..</span>';
                }
            },
            {
                data: null,
                render: function(d) {
                    return '<div class="d-flex align-items-center gap-2"><span class="fw-medium" style="color:var(--sap-text)">' + d.title + '</span></div>';
                }
            },
            {
                data: 'tracking_code',
                orderable: false,
                render: function(d) {
                    if (!d) return '<span class="text-muted">-</span>';
                    return '<span class="d-inline-flex align-items-center gap-1" style="cursor:pointer" onclick="event.stopPropagation();showTrackingModal(\'' + d + '\')" title="Click to view & copy">' +
                        '<code style="font-size:12px;background:var(--sap-background);padding:1px 6px;border-radius:3px;font-family:monospace">' + d + '</code>' +
                        '<i class="fas fa-copy" style="font-size:10px;color:var(--sap-text-muted);opacity:0.6"></i>' +
                        '</span>';
                }
            },
            {
                data: 'status_name',
                render: function(d) { return sapBadge(d); }
            },
            { data: 'type_name' },
            {
                data: null,
                render: function(d) {
                    return '<span class="d-flex align-items-center gap-1">' + priorityDot(d.priority_name) + '<span class="text-secondary">' + d.priority_name + '</span></span>';
                }
            },
            {
                data: 'creator_name',
                defaultContent: '-',
                render: function(d) {
                    if (!d) return '-';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:var(--sap-brand);color:#fff">' + d.charAt(0).toUpperCase() + '</div><span>' + d + '</span></div>';
                }
            },
            {
                data: 'assignee_name',
                defaultContent: '-',
                render: function(d) {
                    if (!d) return '<span class="text-muted">-</span>';
                    return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:var(--sap-text-muted);color:#fff">' + d.charAt(0).toUpperCase() + '</div><span>' + d + '</span></div>';
                }
            },
            {
                data: 'created_at',
                render: function(d) { return '<span class="text-muted" style="font-size:13px">' + d + '</span>'; }
            },
            {
                data: 'id',
                orderable: false,
                render: function(d) {
                    return '<a href="' + site_url + '/tickets/' + d + '" class="sap-btn sap-btn-secondary sap-btn-sm" onclick="event.stopPropagation();"><i class="fas fa-eye"></i></a>';
                }
            }
        ],
        order: [[8, 'desc']],
        language: {
            search: '<i class="fas fa-search"></i>',
            searchPlaceholder: 'Search tickets...',
            emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-ticket-alt"></i><h4>No tickets found</h4><p>Create a new ticket to get started.</p></div>'
        },
        dom: '<"row mb-3"<"col-sm-12"B>>rt<"row mt-3"<"col-sm-4"l><"col-sm-4"i><"col-sm-4"p>>',
        buttons: [
            { extend: 'copy', text: '<i class="fas fa-copy"></i> Copy', className: 'btn-sm' },
            { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn-sm' },
            { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-sm' },
            { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-sm' },
            { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn-sm' },
            { extend: 'colvis', text: '<i class="fas fa-columns"></i> Columns', className: 'btn-sm' },
        ],
        drawCallback: function() {
            var api = this.api();
            api.rows().every(function() {
                var status = this.data().status_name;
                if (status) {
                    var cls = status.toLowerCase().replace(/\s+/g, '-');
                    $(this.node()).addClass('row-status-' + cls);
                }
            });
        }
    });

    $('#tickets-table thead tr').clone(true).appendTo('#tickets-table thead');
    $('#tickets-table thead tr:last th').each(function(i) {
        if (i === 9) {
            $(this).html('');
            return;
        }
        $(this).html('<input type="text" class="column-search" placeholder="Search ' + $('#tickets-table thead tr:first th:eq(' + i + ')').text() + '..." data-col="' + i + '">');
    });

    $('#tickets-table').on('keyup change', '.column-search', function() {
        table.column($(this).data('col')).search(this.value).draw();
    });

    $('#tickets-table tbody').on('click', 'tr', function() {
        var data = table.row(this).data();
        if (data && data.id) {
            window.location.href = site_url + '/tickets/' + data.id;
        }
    });

    // ── Status/Overdue filter initialization from URL params ────
    var urlParams = new URLSearchParams(window.location.search);
    var initialStatus  = urlParams.get('status');
    var initialOverdue = urlParams.get('overdue');

    if (initialStatus !== null && initialStatus !== '') {
        $('#statusFilter').val(initialStatus);
    }
    if (initialOverdue === '1') {
        $('#overdueFilter').prop('checked', true);
    }

    $('#statusFilter, #overdueFilter').on('change', function () {
        table.ajax.reload();
        updateFilterUrl();
    });

    $('#clearFiltersBtn').on('click', function () {
        $('#statusFilter').val('');
        $('#overdueFilter').prop('checked', false);
        table.ajax.reload();
        updateFilterUrl();
    });

    function updateFilterUrl() {
        var params = new URLSearchParams();
        var s = $('#statusFilter').val();
        var o = $('#overdueFilter').is(':checked') ? 1 : '';
        if (s !== null && s !== '') params.set('status', s);
        if (o) params.set('overdue', o);
        var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
});
