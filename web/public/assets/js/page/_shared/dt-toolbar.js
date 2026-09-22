/**
 * DataTables External Toolbar Helper
 *
 * Relocates DataTables Buttons container to an external toolbar div.
 * Usage: DtToolbar.relocate(table, '#page-toolbar')
 */
var DtToolbar = {
    relocate: function (table, selector) {
        if (!table || typeof table.buttons !== 'function') return;
        var $target = $(selector);
        if (!$target.length) return;
        table.buttons().container().appendTo($target);
        $target.find('.dt-buttons').attr('role', 'toolbar').attr('aria-label', 'Alat ekspor');
    }
};
window.DtToolbar = DtToolbar;
