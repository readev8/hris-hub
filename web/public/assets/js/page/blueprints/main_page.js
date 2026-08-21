/**
 * ============================================================================
 * Blueprints Main Page
 * ============================================================================
 *
 * DataTable list for blueprints with column search and export buttons
 *
 * Dependencies: jQuery, DataTables
 * Date: 2026-08-18
 */

/* global $, site_url, sapBadge */

const BlueprintMainPage = {

    // ===========================
    // API_ENDPOINTS
    // ===========================

    API_ENDPOINTS: {
        LIST: function () { return site_url + '/blueprints/ajax-list'; }
    },

    // ===========================
    // INITIALIZATION
    // ===========================

    init: function () {
        var self = this;
        var table = self.buildDataTable();
        self.bindColumnSearch(table);
        self.bindRowClick(table);
    },

    // ===========================
    // DATA LOADING
    // ===========================

    buildDataTable: function () {
        var self = this;

        return $('#blueprints-table').DataTable({
            destroy: true,
            processing: true,
            serverSide: false,
            autoWidth: false,
            scrollX: true,
            ajax: {
                url: self.API_ENDPOINTS.LIST(),
                dataSrc: 'data'
            },
            columns: [
                {
                    data: 'id',
                    render: function (d) {
                        return '<span class="text-muted mono">' + (d ? d.slice(0, 8) : '') + '..</span>';
                    }
                },
                { data: 'name', render: function (d) { return '<span class="fw-medium">' + d + '</span>'; } },
                { data: 'improvement_name', defaultContent: '-', render: function (d) { return d || '-'; } },
                { data: 'module_count', render: function (d) { return '<span class="sap-badge info">' + (d || 0) + '</span>'; } },
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
                    render: function (d) { return '<span class="text-muted" style="font-size:13px">' + d + '</span>'; }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function (d) {
                        return '<a href="' + site_url + '/blueprints/' + d + '" class="sap-btn sap-btn-secondary sap-btn-sm" onclick="event.stopPropagation();"><i class="fas fa-eye"></i></a>';
                    }
                }
            ],
            order: [[6, 'desc']],
            language: {
                searchPlaceholder: 'Search blueprints...',
                emptyTable: '<div class="sap-empty" style="padding:48px 20px"><i class="fas fa-drafting-compass"></i><h4>No blueprints found</h4><p>Create a new blueprint from an improvement to get started.</p></div>'
            },
            dom: '<"row mb-3"<"col-sm-12"B>>rt<"row mt-3"<"col-sm-4"l><"col-sm-4"i><"col-sm-4"p>>',
            buttons: [
                { extend: 'copy', text: '<i class="fas fa-copy"></i> Copy', className: 'btn-sm' },
                { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn-sm' },
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-sm' },
                { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-sm' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn-sm' },
                { extend: 'colvis', text: '<i class="fas fa-columns"></i> Columns', className: 'btn-sm' }
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
    },

    // ===========================
    // EVENT HANDLERS
    // ===========================

    bindColumnSearch: function (table) {
        $('#blueprints-table thead tr').clone(true).appendTo('#blueprints-table thead');
        $('#blueprints-table thead tr:last th').each(function (i) {
            if (i === 7) {
                $(this).html('');
                return;
            }
            $(this).html('<input type="text" class="column-search" placeholder="Search ' + $('#blueprints-table thead tr:first th:eq(' + i + ')').text() + '..." data-col="' + i + '">');
        });

        $('#blueprints-table').on('keyup change', '.column-search', function () {
            table.column($(this).data('col')).search(this.value).draw();
        });
    },

    bindRowClick: function (table) {
        $('#blueprints-table tbody').on('click', 'tr', function () {
            var data = table.row(this).data();
            if (data && data.id) {
                window.location.href = site_url + '/blueprints/' + data.id;
            }
        });
    }
};

window.BlueprintMainPage = BlueprintMainPage;

$(function () {
    BlueprintMainPage.init();
});
