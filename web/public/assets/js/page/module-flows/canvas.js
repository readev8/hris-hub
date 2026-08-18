/**
 * ============================================================================
 * MODULE FLOWS CANVAS
 * ============================================================================
 *
 * Kanvas interaktif visualisasi flow antar modul lintas project.
 * Menggunakan Drawflow 0.0.59 (vanilla JS).
 *
 * Fitur:
 * - Add module dari daftar existing, drag-drop posisi
 * - Connect modules dengan panah arah
 * - Remove connector (klik → konfirmasi SweetAlert2)
 * - Remove node (toolbar → konfirmasi cascade)
 * - Autosave posisi saat drag (debounce 800ms)
 * - Filter project (dim non-selected)
 * - Zoom +/−/fit/reset
 * - Intercept keyboard Delete → konfirmasi (bypass native delete Drawflow)
 *
 * Arsitekstur state: mapping sendiri, tidak akses internal Drawflow
 *   _encIdToDfId: { encryptedModuleId → numericDfId }
 *   _dfIdToEncId: { numericDfId → encryptedModuleId }
 *   _connKeyToEncId: { 'fromDfId>toDfId' → encryptedConnectionId }
 *
 * Dependencies: Drawflow 0.0.59, jQuery, SweetAlert2, Toastr, Select2, GlobalSanitize
 * Date: 2026-08-18
 */

const ModuleFlowsCanvas = (function () {
    'use strict';

    // ===========================
    // API ENDPOINTS
    // ===========================
    var ENDPOINTS = {
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
    var PROJECT_COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];

    // ===========================
    // STATE
    // ===========================
    var editor = null;
    var _canvasNodes = [];
    var _canvasConnections = [];
    var _availableModules = [];
    var _projectIndex = {};
    var _projectColorMap = {};
    var _activeFilter = null;
    var _select2Instance = null;

    // Mappings (Drawflow numeric IDs ↔ encrypted IDs)
    var _dfIdToEncId = {};
    var _encIdToDfId = {};
    var _connKeyToEncId = {};

    // Connection pending selection (for keyboard Delete intercept)
    var _selectedConnInfo = null;

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
        editor.curvature = 0.5;
        editor.editor_mode = 'edit';
        editor.start();

        editor.on('connectionCreated', _onConnectionCreated);

        // Inject SVG marker defs for arrowheads
        _injectArrowMarker(container);

        // Mouseup on canvas → save positions
        container.addEventListener('mouseup', _saveAllPositions);
        container.addEventListener('touchend', _saveAllPositions);

        // Intercept Delete key in CAPTURE phase → bypass native Drawflow delete
        document.addEventListener('keydown', _onKeyDownCapture, true);
    }

    // ===========================
    // KEYBOARD DELETE INTERCEPT
    // ===========================
    function _onKeyDownCapture(e) {
        if (e.key !== 'Delete' && e.key !== 'Backspace') return;
        if (!editor || editor.editor_mode !== 'edit') return;

        var active = document.activeElement;
        if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) return;

        // Check connection selected
        var connEl = container().querySelector('.connection.selected');
        if (connEl) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            _confirmDeleteConnectionByElement(connEl);
            return;
        }

        // Check node selected
        var nodeEl = editor.node_selected;
        if (nodeEl) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            _confirmRemoveNode();
            return;
        }
    }

    function container() {
        return document.getElementById('drawflow-container');
    }

    // ===========================
    // DATA LOADING
    // ===========================
    function _loadCanvas() {
        _showStatus('loading');
        $.get(ENDPOINTS.LOAD_CANVAS, function (res) {
            if (!res || !res.status) {
                toastr.error(res?.message || 'Gagal memuat data canvas');
                _showStatus('error');
                return;
            }
            var data = res.data.result || res.result || res.data;
            _canvasNodes = data.nodes || [];
            _canvasConnections = data.connections || [];
            _availableModules = data.available || [];

            _buildProjectIndex();
            editor.clear();
            _renderCanvas();
            _populateProjectFilter();
            _renderLegend();
            _showSavedStatus();
        }).fail(function () {
            toastr.error('Gagal terhubung ke server');
            _showStatus('error');
        });
    }

    function _buildProjectIndex() {
        _projectIndex = {};
        _projectColorMap = {};
        var colorIdx = 0;
        var all = _canvasNodes.concat(_availableModules.map(function (m) {
            return { project_id: m.project_id, project_name: m.project_name };
        }));
        all.forEach(function (item) {
            if (item.project_id && !_projectIndex[item.project_id]) {
                _projectIndex[item.project_id] = item.project_name;
                _projectColorMap[item.project_id] = PROJECT_COLORS[colorIdx % PROJECT_COLORS.length];
                colorIdx++;
            }
        });
    }

    // ===========================
    // RENDER CANVAS (correct addNode signature)
    // ===========================
    function _renderCanvas() {
        // Reset mappings
        _dfIdToEncId = {};
        _encIdToDfId = {};
        _connKeyToEncId = {};

        // Add nodes — correct 8-param signature: name, inputs, outputs, x, y, class, data, html
        _canvasNodes.forEach(function (node) {
            var color = _projectColorMap[node.project_id] || '#94a3b8';
            var html = '<div class="flow-module" style="border-left:4px solid ' + color + '">'
                + '<div class="flow-module-header">'
                + '<span class="flow-project-dot" style="background:' + color + '"></span>'
                + '<span class="flow-module-name">' + _escHtml(node.name) + '</span>'
                + '</div>'
                + '<div class="flow-module-project">' + _escHtml(node.project_name || '—') + '</div>'
                + '</div>';

            var dfId = editor.addNode(
                node.module_id,   // name: store encrypted id
                1, 1,             // inputs, outputs
                node.pos_x || 0,  // pos_x
                node.pos_y || 0,  // pos_y
                'module',         // CSS class
                {},               // data
                html,             // html (string)
                false             // typenode: false = render as HTML string
            );

            // Map bidirectional
            _dfIdToEncId[dfId] = node.module_id;
            _encIdToDfId[node.module_id] = dfId;
        });

        // Add connections
        _canvasConnections.forEach(function (conn) {
            var fromDfId = _encIdToDfId[conn.from];
            var toDfId = _encIdToDfId[conn.to];
            if (fromDfId && toDfId) {
                editor.addConnection(fromDfId, toDfId, 'output_1', 'input_1');
                _connKeyToEncId[fromDfId + '>' + toDfId] = conn.id;
            }
        });

        // Reset position state
        _lastPositionState = {};
    }

    function _getProjectColorClass(projectId) {
        if (!projectId) return '';
        var idx = Object.keys(_projectColorMap).indexOf(projectId);
        if (idx < 0) return '';
        return 'project-color-' + ((idx % 6) + 1);
    }

    // ===========================
    // CONNECTION CREATED (user drew a line)
    // ===========================
    function _onConnectionCreated(info) {
        // info: { output_id (fromDfId string), input_id (toDfId string),
        //         output_class ('output_1'), input_class ('input_1') }
        var fromDfId = parseInt(info.output_id);
        var toDfId = parseInt(info.input_id);

        var fromEnc = _dfIdToEncId[fromDfId];
        var toEnc = _dfIdToEncId[toDfId];

        // Validation
        if (!fromEnc || !toEnc) {
            editor.removeSingleConnection(fromDfId, toDfId, 'output_1', 'input_1');
            return;
        }
        if (fromEnc === toEnc) {
            editor.removeSingleConnection(fromDfId, toDfId, 'output_1', 'input_1');
            toastr.warning('Tidak dapat menghubungkan modul ke dirinya sendiri');
            return;
        }
        if (_connKeyToEncId[fromDfId + '>' + toDfId]) {
            toastr.info('Koneksi ini sudah ada');
            return;
        }

        _showStatus('saving');
        $.post(ENDPOINTS.ADD_CONNECTION, { from: fromEnc, to: toEnc }, function (res) {
            if (!res || !res.status) {
                toastr.error(res?.message || 'Gagal membuat koneksi');
                editor.removeSingleConnection(fromDfId, toDfId, 'output_1', 'input_1');
                _showStatus('error');
                return;
            }
            _connKeyToEncId[fromDfId + '>' + toDfId] = res.data?.result?.id || res.data?.id || '';
            _showSavedStatus();
        }).fail(function () {
            toastr.error('Gagal terhubung ke server');
            editor.removeSingleConnection(fromDfId, toDfId, 'output_1', 'input_1');
            _showStatus('error');
        });
    }

    // ===========================
    // CONNECTION DELETE (click → confirm)
    // ===========================
    function _confirmDeleteConnectionByElement(connEl) {
        if (!connEl) return;

        // Get node IDs from class attribute: "connection node_in_node-X node_out_node-Y ..."
        var classes = connEl.className.baseVal || connEl.className;
        var fromMatch = classes.match(/node_out_node-(\d+)/);
        var toMatch = classes.match(/node_in_node-(\d+)/);
        if (!fromMatch || !toMatch) return;

        var fromDfId = parseInt(fromMatch[1]);
        var toDfId = parseInt(toMatch[1]);
        var fromEnc = _dfIdToEncId[fromDfId];
        var toEnc = _dfIdToEncId[toDfId];
        var connEncId = _connKeyToEncId[fromDfId + '>' + toDfId];

        if (!fromEnc || !toEnc || !connEncId) return;

        var fromName = _getNodeLabel(fromDfId);
        var toName = _getNodeLabel(toDfId);

        Swal.fire({
            title: 'Hapus koneksi?',
            text: fromName + ' → ' + toName,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            editor.removeSingleConnection(fromDfId, toDfId, 'output_1', 'input_1');

            _showStatus('saving');
            $.post(ENDPOINTS.DELETE_CONNECTION + connEncId, {}, function (res) {
                if (res && res.status) {
                    delete _connKeyToEncId[fromDfId + '>' + toDfId];
                    _showSavedStatus();
                    toastr.success('Koneksi dihapus');
                } else {
                    _refreshCanvas();
                    toastr.error(res?.message || 'Gagal menghapus koneksi');
                    _showStatus('error');
                }
            }).fail(function () {
                _refreshCanvas();
                toastr.error('Gagal terhubung ke server');
                _showStatus('error');
            });
        });
    }

    // ===========================
    // NODE DELETE (toolbar button → confirm)
    // ===========================
    function _confirmRemoveNode() {
        var nodeEl = editor.node_selected;
        if (!nodeEl) {
            toastr.info('Pilih modul terlebih dahulu (klik node di kanvas)');
            return;
        }

        var dfId = parseInt(nodeEl.id.replace('node-', ''));
        var encId = _dfIdToEncId[dfId];
        if (!encId) return;

        var moduleName = _getNodeLabel(dfId);

        // Count affected connections
        var affected = [];
        Object.keys(_connKeyToEncId).forEach(function (key) {
            var parts = key.split('>');
            if (parseInt(parts[0]) === dfId || parseInt(parts[1]) === dfId) {
                affected.push(_connKeyToEncId[key]);
            }
        });

        var text = 'Menghapus "' + moduleName + '"';
        if (affected.length) {
            text += ' akan menghapus ' + affected.length + ' koneksi terkait.';
        }

        Swal.fire({
            title: 'Hapus modul dari kanvas?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            _showStatus('saving');
            $.post(ENDPOINTS.REMOVE_MODULE + encId, {}, function (res) {
                if (res && res.status) {
                    toastr.success('Modul dihapus dari kanvas');
                    _refreshCanvas();
                } else {
                    toastr.error(res?.message || 'Gagal menghapus modul');
                    _showStatus('error');
                }
            }).fail(function () {
                toastr.error('Gagal terhubung ke server');
                _showStatus('error');
            });
        });
    }

    function _getNodeLabel(dfId) {
        var nodeData = editor.getNodeFromId(dfId);
        if (!nodeData) return '';
        var match = (nodeData.html || '').match(/flow-module-name[^>]*>([^<]+)/);
        return match ? match[1].trim() : '';
    }

    // ===========================
    // ADD MODULE (via modal Select2)
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
        var grouped = {};
        _availableModules.forEach(function (m) {
            var projName = m.project_name || 'Unknown Project';
            if (!grouped[projName]) grouped[projName] = [];
            grouped[projName].push(m);
        });

        var opts = '<option value="">Search and select a module...</option>';
        for (var projName in grouped) {
            opts += '<optgroup label="' + _escHtml(projName) + '">';
            grouped[projName].forEach(function (m) {
                opts += '<option value="' + m.module_id + '">' + _escHtml(m.name) + '</option>';
            });
            opts += '</optgroup>';
        }

        if (_select2Instance) {
            _select2Instance.select2('destroy');
        }
        $('#moduleSelect').html(opts);
        _select2Instance = $('#moduleSelect').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Search...' });

        _select2Instance.on('select2:select', function (e) {
            var module = _availableModules.find(function (m) { return m.module_id === e.params.data.id; });
            if (module) {
                $('#selectedModuleName').text(module.name);
                $('#selectedModuleProject').text(module.project_name || '');
                $('#selectedModuleInfo').show();
                $('#btnAddModule').prop('disabled', false);
            }
        });
    }

    function _addModule() {
        var moduleId = $('#moduleSelect').val();
        if (!moduleId) return;

        var c = container();
        var centerX = (c.scrollLeft + c.clientWidth / 2) / editor.zoom;
        var centerY = (c.scrollTop + c.clientHeight / 2) / editor.zoom;

        _showStatus('saving');
        $('#btnAddModule').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

        $.post(ENDPOINTS.ADD_MODULE, {
            module_id: moduleId,
            pos_x: Math.round(centerX - 90),
            pos_y: Math.round(centerY - 40)
        }, function (res) {
            if (!res || !res.status) {
                toastr.error(res?.message || 'Gagal menambahkan modul');
                _showStatus('error');
                $('#btnAddModule').prop('disabled', false).html('<i class="fas fa-plus"></i> Add to Canvas');
                return;
            }
            toastr.success('Modul ditambahkan ke kanvas');
            $('#addModuleModal').modal('hide');
            _refreshCanvas();
        }).fail(function () {
            toastr.error('Gagal terhubung ke server');
            _showStatus('error');
            $('#btnAddModule').prop('disabled', false).html('<i class="fas fa-plus"></i> Add to Canvas');
        });
    }

    // ===========================
    // AUTOSAVE POSITIONS
    // ===========================
    var _lastPositionState = {};
    var _positionSaveTimer = null;

    function _saveAllPositions() {
        if (!editor) return;

        // Debounce: wait 800ms after last mouseup
        clearTimeout(_positionSaveTimer);
        _positionSaveTimer = setTimeout(function () {
            var exportData = editor.export();
            var homeData = (exportData.drawflow && exportData.drawflow.Home)
                ? exportData.drawflow.Home.data : {};
            var changed = [];

            Object.keys(homeData).forEach(function (dfId) {
                var node = homeData[dfId];
                var encId = node.name; // encrypted module id stored as 'name'
                if (!encId) return;

                var prev = _lastPositionState[encId];
                var newX = Math.round(node.pos_x);
                var newY = Math.round(node.pos_y);

                if (!prev || prev.x !== newX || prev.y !== newY) {
                    _lastPositionState[encId] = { x: newX, y: newY };
                    changed.push({ encId: encId, x: newX, y: newY });
                }
            });

            // Send all position updates
            changed.forEach(function (item) {
                $.post(ENDPOINTS.UPDATE_POSITION + item.encId, {
                    pos_x: item.x,
                    pos_y: item.y
                });
            });

            if (changed.length) _showSavedStatus();
        }, 800);
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
            editor.clear();
            _renderCanvas();
            _applyFilter();
            _populateProjectFilter();
            _renderLegend();
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
        $filter.select2({ theme: 'bootstrap-5', width: '200', minimumResultsForSearch: Infinity });
    }

    function _applyFilter() {
        if (!editor) return;
        var filterId = _activeFilter;

        // Dim/show nodes
        Object.keys(_dfIdToEncId).forEach(function (dfId) {
            var el = document.getElementById('node-' + dfId);
            if (!el) return;

            if (!filterId || _dfIdToEncId[dfId] === filterId || _getNodeProjectId(_dfIdToEncId[dfId]) === filterId) {
                el.classList.remove('dimmed');
            } else {
                el.classList.add('dimmed');
            }
        });
    }

    function _getNodeProjectId(encId) {
        for (var i = 0; i < _canvasNodes.length; i++) {
            if (_canvasNodes[i].module_id === encId) return _canvasNodes[i].project_id;
        }
        return null;
    }

    // ===========================
    // TOOLBAR EVENTS
    // ===========================
    function _bindToolbarEvents() {
        $('#btnZoomIn').on('click', function () { editor.zoom_in(); });
        $('#btnZoomOut').on('click', function () { editor.zoom_out(); });
        $('#btnFitView').on('click', function () { _fitView(); });
        $('#btnZoomReset').on('click', function () { editor.zoom_refresh(); });
        $('#btnRefresh').on('click', function () { _refreshCanvas(); });
        $('#btnRemoveNode').on('click', function () { _confirmRemoveNode(); });
    }

    function _renderLegend() {
        var $legend = $('#projectLegend');
        if (!$legend.length) return;
        $legend.empty();
        for (var pid in _projectIndex) {
            var color = _projectColorMap[pid] || '#94a3b8';
            $legend.append(
                '<span class="project-legend-item">'
                + '<span class="project-legend-dot" style="background:' + color + '"></span>'
                + _escHtml(_projectIndex[pid])
                + '</span>'
            );
        }
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
    function _showStatus(state) {
        var $el = $('#saveStatus');
        if (!$el.length) return;
        if (state === 'saving' || state === 'loading') {
            $el.removeClass('saved error').addClass('saving')
                .text(state === 'loading' ? 'Memuat...' : 'Menyimpan...').show();
        } else if (state === 'error') {
            $el.removeClass('saving saved').addClass('error').text('Error').show();
        }
    }

    function _showSavedStatus() {
        var $el = $('#saveStatus');
        if (!$el.length) return;
        $el.removeClass('saving error').addClass('saved').text('\u2713 Tersimpan').show();
        setTimeout(function () { $el.fadeOut(300); }, 2000);
    }

    // ===========================
    // UTILITIES
    // ===========================
    function _escHtml(str) {
        return $('<div>').text(str || '').html();
    }

    function _injectArrowMarker(rootEl) {
        var svgNS = 'http://www.w3.org/2000/svg';
        var defs = document.createElementNS(svgNS, 'defs');
        var marker = document.createElementNS(svgNS, 'marker');
        marker.setAttribute('id', 'flow-arrow');
        marker.setAttribute('viewBox', '0 0 10 10');
        marker.setAttribute('refX', '9');
        marker.setAttribute('refY', '5');
        marker.setAttribute('markerWidth', '5');
        marker.setAttribute('markerHeight', '5');
        marker.setAttribute('orient', 'auto');
        var path = document.createElementNS(svgNS, 'path');
        path.setAttribute('d', 'M 0 0 L 10 5 L 0 10 z');
        path.setAttribute('fill', getComputedStyle(document.documentElement).getPropertyValue('--sap-brand').trim() || '#0D9488');
        marker.appendChild(path);
        defs.appendChild(marker);
        rootEl.appendChild(defs);
    }

    function _fitView() {
        if (!editor) return;
        var exportData = editor.export();
        var homeData = exportData.drawflow ? exportData.drawflow.Home.data : {};
        var nodes = Object.keys(homeData);
        if (!nodes.length) { editor.zoom_refresh(); return; }

        var minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
        nodes.forEach(function (id) {
            var n = homeData[id];
            var nodeEl = document.getElementById('node-' + id);
            var w = nodeEl ? nodeEl.offsetWidth : 200;
            var h = nodeEl ? nodeEl.offsetHeight : 80;
            minX = Math.min(minX, n.pos_x);
            maxX = Math.max(maxX, n.pos_x + w);
            minY = Math.min(minY, n.pos_y);
            maxY = Math.max(maxY, n.pos_y + h);
        });

        var containerEl = container();
        var canvasW = containerEl.clientWidth;
        var canvasH = containerEl.clientHeight;
        var padding = 80;
        var scaleX = (canvasW - padding * 2) / (maxX - minX || 1);
        var scaleY = (canvasH - padding * 2) / (maxY - minY || 1);
        var zoom = Math.min(scaleX, scaleY, 1.5);
        zoom = Math.max(zoom, 0.3);

        editor.zoom = zoom;
        var tx = (canvasW / 2) - ((minX + maxX) / 2) * zoom;
        var ty = (canvasH / 2) - ((minY + maxY) / 2) * zoom;
        editor.precanvas.style.transform = 'translate(' + tx + 'px,' + ty + 'px) scale(' + zoom + ')';
        editor.zoom_refresh = editor.zoom_refresh || function () {};
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
