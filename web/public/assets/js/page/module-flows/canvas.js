/**
 * ============================================================================
 * MODULE FLOWS CANVAS
 * ============================================================================
 *
 * Kanvas interaktif visualisasi flow antar modul lintas project.
 * Menggunakan Drawflow (jerosoler/Drawflow) sebagai engine koneksi.
 *
 * Fitur:
 * - Add module dari daftar existing, drag-drop posisi
 * - Connect modules dengan panah arah
 * - Remove connector (klik + konfirmasi)
 * - Remove node (konfirmasi, cascade hapus koneksi)
 * - Autosave posisi saat drag (debounce 800ms)
 * - Filter project (dim/highlight)
 * - Zoom +/−/fit/reset
 *
 * Dependencies: Drawflow 0.0.59, jQuery, SweetAlert2, Toastr, Select2, GlobalSanitize
 * Date: 2026-08-18
 */

const ModuleFlowsCanvas = (function () {
    'use strict';

    // ===========================
    // API ENDPOINTS
    // ===========================
    const ENDPOINTS = {
        LOAD_CANVAS:       site_url + '/module-flows/ajax-canvas',
        ADD_MODULE:        site_url + '/module-flows/ajax-add-module',
        UPDATE_POSITION:   site_url + '/module-flows/ajax-update-position/',
        REMOVE_MODULE:     site_url + '/module-flows/ajax-remove-module/',
        ADD_CONNECTION:    site_url + '/module-flows/ajax-add-connection',
        DELETE_CONNECTION: site_url + '/module-flows/ajax-delete-connection/',
    };

    // ===========================
    // PROJECT COLORS
    // ===========================
    const PROJECT_COLORS = [
        '#3b82f6', // blue
        '#10b981', // green
        '#f59e0b', // amber
        '#ef4444', // red
        '#8b5cf6', // violet
        '#06b6d4', // cyan
    ];

    // ===========================
    // STATE
    // ===========================
    let editor = null;
    let _canvasNodes = [];
    let _canvasConnections = [];
    let _availableModules = [];
    let _projectIndex = {};
    let _projectColorMap = {};
    let _activeFilter = null;
    let _pendingSaves = new Set();
    let _selectedConnectionId = null;
    let _select2Instance = null;

    // ===========================
    // INITIALIZATION
    // ===========================
    function init() {
        _initDrawflow();
        _loadCanvas();
        _bindToolbarEvents();
        _bindModalEvents();
        _bindProjectFilter();
    }

    function _initDrawflow() {
        var container = document.getElementById('drawflow-container');
        if (!container) return;

        editor = new Drawflow(container);
        editor.reroute = false;
        editor.reroute_fix_curvature = false;
        editor.force_first_input = false;
        editor.line_path = 5;
        editor.editor_mode = 'edit';
        editor.start();

        // Connection events
        editor.on('connectionCreated', _onConnectionCreated);
        editor.on('connectionRemoved', _onConnectionRemoved);

        // Connection selection for delete
        _initConnectionSelect();

        // Position save: listen to mouseup on canvas
        container.addEventListener('mouseup', _onCanvasMouseUp);
        container.addEventListener('touchend', _onCanvasMouseUp);

        // Keyboard delete
        document.addEventListener('keydown', function (e) {
            if ((e.key === 'Delete' || e.key === 'Backspace') && _selectedConnectionId) {
                _confirmDeleteConnection(_selectedConnectionId);
            }
        });
    }

    function _onCanvasMouseUp() {
        _saveAllPositions();
    }

    // ===========================
    // DATA LOADING
    // ===========================
    function _loadCanvas() {
        _showSavingStatus('loading');
        $.get(ENDPOINTS.LOAD_CANVAS, function (res) {
            if (!res || !res.status) {
                toastr.error(res?.message || 'Gagal memuat data canvas');
                _showSavingStatus('error');
                return;
            }

            var data = res.data.result || res.result || res.data;
            _canvasNodes = data.nodes || [];
            _canvasConnections = data.connections || [];
            _availableModules = data.available || [];

            _buildProjectIndex();
            _renderCanvas();
            _populateProjectFilter();
            _showSavedStatus();
        }).fail(function () {
            toastr.error('Gagal terhubung ke server');
            _showSavingStatus('error');
        });
    }

    function _buildProjectIndex() {
        _projectIndex = {};
        _projectColorMap = {};
        var colorIdx = 0;

        // From nodes
        _canvasNodes.forEach(function (n) {
            if (n.project_id && !_projectIndex[n.project_id]) {
                _projectIndex[n.project_id] = n.project_name;
                _projectColorMap[n.project_id] = PROJECT_COLORS[colorIdx % PROJECT_COLORS.length];
                colorIdx++;
            }
        });

        // From available modules
        _availableModules.forEach(function (m) {
            if (m.project_id && !_projectIndex[m.project_id]) {
                _projectIndex[m.project_id] = m.project_name;
                _projectColorMap[m.project_id] = PROJECT_COLORS[colorIdx % PROJECT_COLORS.length];
                colorIdx++;
            }
        });
    }

    // ===========================
    // RENDER CANVAS
    // ===========================
    function _renderCanvas() {
        // Clear
        editor.clear();

        // Add nodes
        var nodeMap = {}; // encrypted_id → drawflow_node_id

        _canvasNodes.forEach(function (node) {
            var colorClass = _getProjectColorClass(node.project_id);
            var html = '<div class="flow-module ' + colorClass + '" title="' + _escHtml(node.project_name || '') + '">'
                + '<div class="flow-module-name">' + _escHtml(node.name) + '</div>'
                + '<div class="flow-module-project">' + _escHtml(node.project_name || '') + '</div>'
                + '</div>';

            var nodeId = editor.addNode(
                'module', 1, 1,
                node.pos_x, node.pos_y,
                node.module_id, // name/title
                html,
                {}, // data
                {}, // classes
                { min_inputs: 1, max_inputs: 1, min_outputs: 1, max_outputs: 1 }
            );

            nodeMap[node.module_id] = nodeId;
        });

        // Add connections
        _canvasConnections.forEach(function (conn) {
            var fromNodeId = nodeMap[conn.from];
            var toNodeId = nodeMap[conn.to];
            if (fromNodeId && toNodeId) {
                editor.addConnection(fromNodeId, toNodeId, 'output_1', 'input_1', conn.id);
            }
        });
    }

    function _getProjectColorClass(projectId) {
        if (!projectId) return '';
        var idx = Object.keys(_projectColorMap).indexOf(projectId);
        if (idx < 0) return '';
        return 'project-color-' + ((idx % 6) + 1);
    }

    // ===========================
    // CONNECTION EVENTS
    // ===========================
    function _onConnectionCreated(info) {
        var fromId = info.output_id;   // encrypted module_id stored in name
        var toId = info.input_id;      // encrypted module_id stored in name

        // Drawflow internal IDs — need to get from name attribute
        var fromName = editor.getNodeModule(info.output_id) || '';
        var toName = editor.getNodeModule(info.input_id) || '';

        // Actually fromName/toName are the module names in Drawflow (we stored module_id as name)
        // info.output_id and info.input_id are the DRAWFLOW numeric IDs
        // We need to get the 'name' (encrypted module_id) from the Drawflow node
        var fromData = editor.drawflow.MODULES.data[info.output_id];
        var toData = editor.drawflow.MODULES.data[info.input_id];
        var fromEncId = fromData ? fromData.name : null;
        var toEncId = toData ? toData.name : null;

        if (!fromEncId || !toEncId || fromEncId === toEncId) {
            editor.removeSingleConnection(info.output_id, info.input_id, 'output_1', 'input_1');
            if (fromEncId === toEncId) {
                toastr.warning('Tidak dapat menghubungkan modul ke dirinya sendiri');
            }
            return;
        }

        _showSavingStatus('saving');
        $.post(ENDPOINTS.ADD_CONNECTION, {
            from: fromEncId,
            to: toEncId
        }, function (res) {
            if (!res || !res.status) {
                toastr.error(res?.message || 'Gagal membuat koneksi');
                editor.removeSingleConnection(info.output_id, info.input_id, 'output_1', 'input_1');
                _showSavingStatus('error');
                return;
            }
            _showSavedStatus();
            _refreshCanvas();
        }).fail(function () {
            toastr.error('Gagal terhubung ke server');
            editor.removeSingleConnection(info.output_id, info.input_id, 'output_1', 'input_1');
            _showSavingStatus('error');
        });
    }

    function _onConnectionRemoved(info) {
        // Drawflow auto-removes on keyboard Delete or when removeSingleConnection is called
        // Don't trigger for connections we're removing ourselves (handled in confirm flow)
    }

    // ===========================
    // CONNECTION SELECTION + DELETE
    // ===========================
    function _initConnectionSelect() {
        editor.on('connectionSelected', function (info) {
            _selectedConnectionId = info.connection.id;
        });
        editor.on('connectionUnselected', function () {
            _selectedConnectionId = null;
        });
    }

    function _confirmDeleteConnection(connectionId) {
        if (!connectionId) return;

        // Find connection in our data
        var conn = _canvasConnections.find(function (c) {
            return connectionId.indexOf(c.id) !== -1 || connectionId === c.id;
        });

        // Also check if connectionId matches the Drawflow format
        // Drawflow connection IDs look like: "input_1-{toId}-output_1-{fromId}"
        if (!conn) {
            // Try to find by parsing Drawflow connection ID format
            var parts = connectionId.split('-');
            if (parts.length >= 4) {
                var toNodeId = parseInt(parts[1]);
                var fromNodeId = parseInt(parts[3]);
                var toNode = editor.drawflow.MODULES.data[toNodeId];
                var fromNode = editor.drawflow.MODULES.data[fromNodeId];
                if (toNode && fromNode) {
                    conn = { from: fromNode.name, to: toNode.name, id: connectionId };
                }
            }
        }

        if (!conn) return;

        Swal.fire({
            title: 'Hapus koneksi?',
            text: 'Koneksi ini akan dihapus permanen',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
        }).then(function (result) {
            if (result.isConfirmed) {
                // Remove from Drawflow
                var fromNode = editor.drawflow.MODULES.data[parts ? parseInt(parts[3]) : 0];
                var toNode = editor.drawflow.MODULES.data[parts ? parseInt(parts[1]) : 0];
                if (fromNode && toNode) {
                    editor.removeSingleConnection(
                        parts ? parseInt(parts[3]) : 0,
                        parts ? parseInt(parts[1]) : 0,
                        'output_1',
                        'input_1'
                    );
                }

                // Delete from API
                _showSavingStatus('saving');
                $.post(ENDPOINTS.DELETE_CONNECTION + conn.id, {}, function (res) {
                    if (res && res.status) {
                        _showSavedStatus();
                        _refreshCanvas();
                    } else {
                        toastr.error(res?.message || 'Gagal menghapus koneksi');
                        _showSavedStatus('error');
                    }
                }).fail(function () {
                    toastr.error('Gagal terhubung ke server');
                    _showSavingStatus('error');
                });
            }
            _selectedConnectionId = null;
        });
    }

    // ===========================
    // ADD MODULE
    // ===========================
    function _openAddModuleModal() {
        if (!_availableModules.length) {
            Swal.fire('Info', 'Semua modul sudah ada di kanvas', 'info');
            return;
        }

        _populateSelect2();
        $('#selectedModuleInfo').hide();
        $('#btnAddModule').prop('disabled', true);
        $('#addModuleModal').modal('show');
    }

    function _populateSelect2() {
        // Group by project
        var grouped = {};
        _availableModules.forEach(function (m) {
            var projName = m.project_name || 'Unknown Project';
            if (!grouped[projName]) grouped[projName] = [];
            grouped[projName].push(m);
        });

        var options = '<option value="">Search and select a module...</option>';
        for (var projName in grouped) {
            options += '<optgroup label="' + _escHtml(projName) + '">';
            grouped[projName].forEach(function (m) {
                options += '<option value="' + m.module_id + '">' + _escHtml(m.name) + '</option>';
            });
            options += '</optgroup>';
        }

        if (_select2Instance) {
            _select2Instance.select2('destroy');
        }

        $('#moduleSelect').html(options);
        _select2Instance = $('#moduleSelect').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search and select a module...',
        });

        _select2Instance.on('select2:select', function (e) {
            var module = _availableModules.find(function (m) { return m.module_id === e.params.data.id; });
            if (module) {
                $('#selectedModuleName').text(module.name);
                $('#selectedModuleProject').text(module.project_name || 'Unknown Project');
                $('#selectedModuleInfo').show();
                $('#btnAddModule').prop('disabled', false);
            }
        });
    }

    function _addModule() {
        var moduleId = $('#moduleSelect').val();
        if (!moduleId) return;

        // Calculate center position
        var container = document.getElementById('drawflow-container');
        var centerX = (container.scrollLeft + container.clientWidth / 2) / editor.zoom;
        var centerY = (container.scrollTop + container.clientHeight / 2) / editor.zoom;

        _showSavingStatus('saving');
        $('#btnAddModule').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

        $.post(ENDPOINTS.ADD_MODULE, {
            module_id: moduleId,
            pos_x: Math.round(centerX - 90),
            pos_y: Math.round(centerY - 40)
        }, function (res) {
            if (!res || !res.status) {
                toastr.error(res?.message || 'Gagal menambahkan modul');
                _showSavingStatus('error');
                $('#btnAddModule').prop('disabled', false).html('<i class="fas fa-plus"></i> Add to Canvas');
                return;
            }

            toastr.success('Modul ditambahkan ke kanvas');
            $('#addModuleModal').modal('hide');
            _showSavedStatus();
            _refreshCanvas();
        }).fail(function () {
            toastr.error('Gagal terhubung ke server');
            _showSavingStatus('error');
            $('#btnAddModule').prop('disabled', false).html('<i class="fas fa-plus"></i> Add to Canvas');
        });
    }

    // ===========================
    // REMOVE NODE
    // ===========================
    function _removeSelectedNode() {
        var selectedId = editor.node_selected;
        if (!selectedId) {
            toastr.info('Pilih modul terlebih dahulu');
            return;
        }

        var nodeData = editor.drawflow.MODULES.data[selectedId];
        if (!nodeData) return;

        var encryptedId = nodeData.name; // we stored encrypted module_id as name
        var moduleName = _getModuleName(selectedId);

        // Count connections
        var connCount = 0;
        Object.values(editor.drawflow.MODULES.data).forEach(function (n) {
            Object.values(n.outputs || {}).forEach(function (output) {
                Object.values(output.connections || {}).forEach(function (c) {
                    if (c.node === selectedId || n.id === selectedId) connCount++;
                });
            });
        });

        var confirmText = 'Menghapus modul "' + moduleName + '"';
        if (connCount > 0) {
            confirmText += ' akan menghapus ' + connCount + ' koneksi terkait';
        }
        confirmText += '.';

        Swal.fire({
            title: 'Hapus modul dari kanvas?',
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            _showSavingStatus('saving');
            $.post(ENDPOINTS.REMOVE_MODULE + encryptedId, {}, function (res) {
                if (res && res.status) {
                    toastr.success('Modul dihapus dari kanvas');
                    _showSavedStatus();
                    _refreshCanvas();
                } else {
                    toastr.error(res?.message || 'Gagal menghapus modul');
                    _showSavingStatus('error');
                }
            }).fail(function () {
                toastr.error('Gagal terhubung ke server');
                _showSavingStatus('error');
            });
        });
    }

    function _getModuleName(nodeId) {
        var nodeData = editor.drawflow.MODULES.data[nodeId];
        if (!nodeData) return '';
        var nameEl = nodeData.html ? nodeData.html.match(/flow-module-name[^>]*>([^<]+)/) : null;
        return nameEl ? nameEl[1].trim() : '';
    }

    // ===========================
    // AUTOSAVE POSITIONS
    // ===========================
    var _lastPositionState = {};

    function _saveAllPositions() {
        if (!editor) return;
        var data = editor.export();
        var moduleData = (data.drawflow && data.drawflow.MODULES) ? data.drawflow.MODULES.data : {};
        var changed = false;

        for (var dfId in moduleData) {
            var node = moduleData[dfId];
            var encId = node.name;
            if (!encId) continue;

            var prev = _lastPositionState[encId];
            var newX = Math.round(node.pos_x);
            var newY = Math.round(node.pos_y);

            if (!prev || prev.x !== newX || prev.y !== newY) {
                _lastPositionState[encId] = { x: newX, y: newY };
                changed = true;
                _sendPositionUpdate(encId, newX, newY);
            }
        }
    }

    function _sendPositionUpdate(encId, x, y) {
        $.post(ENDPOINTS.UPDATE_POSITION + encId, { pos_x: x, pos_y: y }, function () {
            _showSavedStatus();
        }).fail(function () {
            _showSavingStatus('error');
        });
    }

    // ===========================
    // CANVAS REFRESH
    // ===========================
    function _refreshCanvas() {
        $.get(ENDPOINTS.LOAD_CANVAS, function (res) {
            if (!res || !res.status) return;
            var data = res.data.result || res.result || res.data;
            _canvasNodes = data.nodes || [];
            _canvasConnections = data.connections || [];
            _availableModules = data.available || [];

            _buildProjectIndex();
            _renderCanvas();
            _applyFilter();
            _populateProjectFilter();
            _lastPositionState = {};
        });
    }

    // ===========================
    // PROJECT FILTER
    // ===========================
    function _populateProjectFilter() {
        var $filter = $('#filterProject');
        $filter.html('<option value="">All Projects</option>');
        for (var pid in _projectIndex) {
            $filter.append('<option value="' + pid + '">' + _escHtml(_projectIndex[pid]) + '</option>');
        }
        if (_select2Instance) {
            $filter.select2('destroy');
        }
        $filter.select2({ theme: 'bootstrap-5', width: 'auto', minimumResultsForSearch: Infinity });
    }

    function _applyFilter() {
        if (!editor) return;
        var moduleData = editor.drawflow.MODULES ? editor.drawflow.MODULES.data : {};
        var filterId = _activeFilter;

        for (var dfId in moduleData) {
            var node = moduleData[dfId];
            var el = document.querySelector('.drawflow-node[data-node-id="' + dfId + '"]');
            if (!el) continue;

            if (!filterId || node.name === filterId || _getNodeProjectId(node) === filterId) {
                el.classList.remove('dimmed');
            } else {
                el.classList.add('dimmed');
            }
        }
    }

    function _getNodeProjectId(nodeData) {
        // Find project_id from canvasNodes by matching encrypted name
        for (var i = 0; i < _canvasNodes.length; i++) {
            if (_canvasNodes[i].module_id === nodeData.name) {
                return _canvasNodes[i].project_id;
            }
        }
        return null;
    }

    // ===========================
    // TOOLBAR EVENTS
    // ===========================
    function _bindToolbarEvents() {
        $('#btnZoomIn').on('click', function () { editor.zoom_in(); });
        $('#btnZoomOut').on('click', function () { editor.zoom_out(); });
        $('#btnFitView').on('click', function () { editor.zoom_reset(); });
        $('#btnZoomReset').on('click', function () { editor.zoom_reset(); });
        $('#btnRefresh').on('click', function () { _refreshCanvas(); });
        $('#btnRemoveNode').on('click', function () { _removeSelectedNode(); });
    }

    function _bindModalEvents() {
        $('#btnOpenAddModule').on('click', function () { _openAddModuleModal(); });
        $('#btnAddModule').on('click', function () { _addModule(); });
    }

    function _bindProjectFilter() {
        $('#filterProject').on('change', function () {
            _activeFilter = $(this).val() || null;
            _applyFilter();
        });
    }

    // ===========================
    // SAVE STATUS INDICATOR
    // ===========================
    function _showSavingStatus(state) {
        var $el = $('#saveStatus');
        if (!$el.length) return;

        if (state === 'saving' || state === 'loading') {
            $el.removeClass('saved error').addClass('saving')
                .text(state === 'loading' ? 'Memuat...' : 'Menyimpan...');
        } else if (state === 'error') {
            $el.removeClass('saving saved').addClass('error')
                .text('Error').show();
        }
    }

    function _showSavedStatus() {
        var $el = $('#saveStatus');
        if (!$el.length) return;
        $el.removeClass('saving error').addClass('saved')
            .text('✓ Tersimpan').show();
        setTimeout(function () { $el.fadeOut(300); }, 2000);
    }

    // ===========================
    // UTILITIES
    // ===========================
    function _escHtml(str) {
        return $('<div>').text(str || '').html();
    }

    // ===========================
    // PUBLIC API
    // ===========================
    return {
        init: init,
        refreshCanvas: _refreshCanvas,
    };
})();

// ===========================
// AUTO-INIT
// ===========================
$(function () {
    ModuleFlowsCanvas.init();
});
