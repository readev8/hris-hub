/**
 * ============================================================================
 * Approvals Main Page
 * ============================================================================
 *
 * Approval center with tab switching between tickets and improvements.
 *
 * Dependencies: jQuery, Bootstrap, Toastr, DataTables
 * Date: 2026-08-18
 */

// ===========================
// INITIALIZATION
// ===========================

const ApprovalMainPage = {

    // ===========================
    // STATE
    // ===========================

    _currentType: 'tickets',
    _table: null,

    // ===========================
    // DATA LOADING
    // ===========================

    loadApprovals: function (type) {
        var self = ApprovalMainPage;
        self._currentType = type;
        if (self._table) { self._table.destroy(); self._table = null; }

        self._table = $('#approval-table').DataTable({
            processing: true,
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({ header: function () { return 'Details'; } }),
                    renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'sap-table mb-0' })
                }
            },
            ajax: {
                url: site_url + '/approvals/ajax-list',
                data: { type: type },
                dataSrc: 'data'
            },
            columns: [
                {
                    data: 'id',
                    render: function (d) {
                        return d ? '<span class="text-muted mono">' + d.slice(0, 8) + '..</span>' : '-';
                    }
                },
                {
                    data: type === 'tickets' ? 'title' : 'name',
                    render: function (d) { return d ? '<span class="fw-medium">' + d + '</span>' : '-'; }
                },
                { data: 'status_name', render: function (d) { return sapBadge(d); } },
                {
                    data: 'creator_name',
                    defaultContent: '-',
                    render: function (d) {
                        if (!d) return '-';
                        return '<div class="d-flex align-items-center gap-2"><div class="avatar-circle avatar-circle-sm" style="background:var(--sap-brand);color:#fff">' + d.charAt(0).toUpperCase() + '</div><span>' + d + '</span></div>';
                    }
                },
                {
                    data: 'created_at',
                    render: function (d) { return d ? '<span class="text-muted" style="font-size:13px">' + d + '</span>' : '-'; }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function (d) {
                        var prefix = self._currentType === 'tickets' ? 'tickets' : 'improvements';
                        return d ? '<a href="' + site_url + '/' + prefix + '/' + d + '" class="sap-btn sap-btn-secondary sap-btn-sm" onclick="event.stopPropagation();"><i class="fas fa-eye"></i></a>' : '-';
                    }
                }
            ],
            order: [[4, 'desc']],
            language: {
                emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-check-circle"></i><h4>No pending items</h4><p>All caught up!</p></div>'
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
            drawCallback: function () {
                var api = this.api();
                api.rows().every(function () {
                    var status = this.data().status_name;
                    if (status) {
                        var cls = status.toLowerCase().replace(/\s+/g, '-');
                        $(this.node()).addClass('row-status-' + cls);
                    }
                });
            }
        });

        $('#approval-table thead tr').clone(true).appendTo('#approval-table thead');
        $('#approval-table thead tr:last th').each(function (i) {
            if (i === 5) {
                $(this).html('');
                return;
            }
            $(this).html('<input type="text" class="column-search" placeholder="Search ' + $('#approval-table thead tr:first th:eq(' + i + ')').text() + '..." data-col="' + i + '">');
        });

        $('#approval-table').off('keyup change', '.column-search');
        $('#approval-table').off('click', 'tbody tr');

        $('#approval-table').on('keyup change', '.column-search', function () {
            self._table.column($(this).data('col')).search(this.value).draw();
        });

        $('#approval-table tbody').on('click', 'tr', function () {
            var rowData = self._table.row(this).data();
            if (rowData && rowData.id) {
                var prefix = self._currentType === 'tickets' ? 'tickets' : 'improvements';
                window.location.href = site_url + '/' + prefix + '/' + rowData.id;
            }
        });
    },

    // ===========================
    // EVENTS
    // ===========================

    init: function () {
        var self = ApprovalMainPage;
        self.loadApprovals('tickets');

        $('#approvalTabs a').on('click', function (e) {
            e.preventDefault();
            $('#approvalTabs a').removeClass('active');
            $(this).addClass('active');
            self.loadApprovals($(this).data('type'));
        });
    }
};

// ===========================
// AUTO-INITIALIZATION
// ===========================

$(function () {
    ApprovalMainPage.init();
});

// ===========================
// EXPORTS
// ===========================

window.ApprovalMainPage = ApprovalMainPage;

// Backward compatibility for onclick handlers in views
window.loadApprovals = function (type) { ApprovalMainPage.loadApprovals(type); };
