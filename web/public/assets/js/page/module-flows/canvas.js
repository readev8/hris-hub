/**
 * ============================================================================
 * MODULE FLOWS CANVAS
 * ============================================================================
 *
 * Kanvas interaktif visualisasi flow antar modul lintas project.
 * Menggunakan jsPlumb CE 6.2.10 (vanilla JS).
 *
 * Fitur:
 * - Add module dari daftar existing, drag-drop posisi
 * - Connect modules dengan panah arah (many-to-many)
 * - Remove connector (klik → konfirmasi SweetAlert2)
 * - Remove node (toolbar → konfirmasi cascade)
 * - Autosave posisi saat drag (debounce 800ms)
 * - Filter project (dim non-selected)
 * - Double-click rename label
 * - Keyboard Delete → konfirmasi
 *
 * State:
 *   jsp             — jsPlumb instance
 *   _connMap        — { 'encFrom>encTo' → encConnId }
 *   _selectedNodeId — currently selected node element ID (string|null)
 *   _selectedConnId — currently selected jsPlumb connection ID (string|null)
 *
 * Dependencies: jsPlumb CE 6.2.10, jQuery, SweetAlert2, Toastr, Select2
 * Date: 2026-08-19
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
        UPDATE_LABEL:      site_url + '/module-flows/ajax-update-module/',
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
    var jsp = null;
    var _canvasNodes = [];
    var _canvasConnections = [];
    var _availableModules = [];
    var _projectIndex = {};
    var _projectColorMap = {};
    var _activeFilter = null;
    var _select2Instance = null;
    var _connMap = {};
    var _selectedNodeId = null;
    var _selectedConnId = null;
    var _isRendering = false;

    // ===========================
    // INITIALIZATION
    // ===========================
    function init() {
        _initJsPlumb();
        _loadCanvas();
        _bindToolbarEvents();
        _bindModalEvents();
        _bindProjectFilter();
    }

    function _initJsPlumb() {
        var container = document.getElementById('flow-canvas');
        if (!container) return;

        jsp = jsPlumb.newInstance({
            container: container,
            connector: {
                type: 'Flowchart',
                options: { cornerRadius: 5, alwaysRespectStubs: true }
            },
            paintStyle: { strokeWidth: 2, stroke: '#0D9488' },
            hoverPaintStyle: { strokeWidth: 3, stroke: '#0f766e' },
            endpoint: { type: 'Dot', options: { radius: 5 } },
            endpointStyle: { fill: '#fff', stroke: '#0D9488', strokeWidth: 2 },
            endpointHoverStyle: { fill: '#0D9488', stroke: '#0f766e', strokeWidth: 2 },
            connectionOverlays: [
                { type: 'Arrow', options: { width: 10, length: 10, location: 1, foldback: 0.8 } }
            ],
            maxConnections: -1,
        });

        // Register source selector for interactive drag-to-connect
        // In jsPlumb CE 6, isSource:true on addEndpoint only allows programmatic
        // connections. addSourceSelector() is required for interactive drag.
        jsp.addSourceSelector('.flow-endpoint-out', { maxConnections: -1 });

        // Connection events
        jsp.bind('beforeDrop', _onBeforeDrop);
        jsp.bind('connection', _onConnection);
        jsp.bind('connectionDetached', _onConnectionDetached);
        jsp.bind('click', _onConnectionClick);

        // Drag stop → save positions
        jsp.bind('drag:stop', function () {
            _saveAllPositions();
        });

        // Click on canvas background → clear selection
        container.addEventListener('mousedown', function (e) {
            if (e.target === container || e.target.classList.contains('jtk-surface')) {
                _clearSelection();
            }
        });

        // Double-click node → rename
        container.addEventListener('dblclick', _onNodeDblClick);

        // Keyboard Delete
        document.addEventListener('keydown', _onKeyDown, true);
    }

    function _clearCanvas() {
        if (!jsp) return;
        jsp.deleteEveryConnection();
        var els = document.querySelectorAll('#flow-canvas .jtk-node');
        els.forEach(function (el) {
            jsp.removeAllEndpoints(el);
            el.parentNode && el.parentNode.removeChild(el);
        });
    }

    // ===========================
    // KEYBOARD DELETE INTERCEPT
    // ===========================
    function _onKeyDown(e) {
        if (e.key !== 'Delete' && e.key !== 'Backspace') return;

        var active = document.activeElement;
        if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) return;

        if (_selectedConnId) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            var conn = jsp.getConnections().find(function (c) { return c.id === _selectedConnId; });
            if (conn) _confirmDeleteConnection(conn);
            return;
        }

        if (_selectedNodeId) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            _confirmRemoveNode();
            return;
        }
    }

    // ===========================
    // SELECTION
    // ===========================
    function _clearSelection() {
        _selectedNodeId = null;
        _selectedConnId = null;
        document.querySelectorAll('.jtk-node.selected').forEach(function (el) {
            el.classList.remove('selected');
        });
        document.querySelectorAll('.jtk-connector.selected').forEach(function (el) {
            el.classList.remove('selected');
        });
    }

    function _selectNode(nodeEl) {
        _clearSelection();
        _selectedNodeId = nodeEl.id;
        nodeEl.classList.add('selected');
    }

    function _selectConnection(conn) {
        _clearSelection();
        _selectedConnId = conn.id;
        var overlayEl = conn.canvas;
        if (overlayEl) overlayEl.classList.add('selected');
    }

    // ===========================
    // DATA LOADING
    // ===========================
    function _loadCanvas() {
        _showStatus('loading');
        $.get(ENDPOINTS.LOAD_CANVAS, function (res) {
            if (!res || !res.status) {
                toastr.error(res?.data?.message || res?.message || 'Gagal memuat data canvas');
                _showStatus('error');
                return;
            }
            var data = res.data.result || res.result || res.data;
            _canvasNodes = data.nodes || [];
            _canvasConnections = data.connections || [];
            _availableModules = data.available || [];

            _buildProjectIndex();
            _clearCanvas();
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
    // RENDER CANVAS
    // ===========================
    function _renderCanvas() {
        _connMap = {};

        // Add nodes
        _canvasNodes.forEach(function (node) {
            _createNodeElement(node);
        });

        // Add connections
        _isRendering = true;
        _canvasConnections.forEach(function (conn) {
            var sourceEl = document.getElementById(conn.from);
            var targetEl = document.getElementById(conn.to);
            if (sourceEl && targetEl) {
                var jsConn = jsp.connect({
                    source: sourceEl,
                    target: targetEl,
                    anchors: ['Right', 'Left'],
                    cssClass: 'flow-connection',
                });
                if (jsConn) {
                    _connMap[conn.from + '>' + conn.to] = conn.id;
                }
            }
        });
        _isRendering = false;
    }

    function _createNodeElement(node) {
        var color = _projectColorMap[node.project_id] || '#94a3b8';
        var el = document.createElement('div');
        el.id = node.module_id;
        el.className = 'jtk-node flow-module';
        el.style.left = (node.pos_x || 0) + 'px';
        el.style.top = (node.pos_y || 0) + 'px';
        el.innerHTML = '<div class="flow-module-header">'
            + '<span class="flow-project-dot" style="background:' + color + '"></span>'
            + '<span class="flow-module-name">' + _escHtml(node.name) + '</span>'
            + '</div>'
            + '<div class="flow-module-project">' + _escHtml(node.project_name || '—') + '</div>';

        document.getElementById('flow-canvas').appendChild(el);

        // Add endpoints (jsPlumb CE 6 auto-makes managed elements draggable)
        jsp.addEndpoint(el, {
            anchor: 'Left',
            isTarget: true,
            maxConnections: -1,
            cssClass: 'flow-endpoint flow-endpoint-in',
            parameters: { role: 'input' },
        });
        jsp.addEndpoint(el, {
            anchor: 'Right',
            isSource: true,
            maxConnections: -1,
            cssClass: 'flow-endpoint flow-endpoint-out',
            parameters: { role: 'output' },
        });

        // Click → select
        el.addEventListener('click', function (e) {
            e.stopPropagation();
            _selectNode(el);
        });
    }

    // ===========================
    // CONNECTION EVENTS
    // ===========================
    function _onBeforeDrop(info) {
        var sourceId = info.sourceId;
        var targetId = info.targetId;

        // No self-loop
        if (sourceId === targetId) {
            toastr.warning('Tidak dapat menghubungkan modul ke dirinya sendiri');
            return false;
        }

        // No duplicate
        if (_connMap[sourceId + '>' + targetId]) {
            toastr.info('Koneksi ini sudah ada');
            return false;
        }

        return true;
    }

    function _onConnection(info) {
        var sourceId = info.source.id;
        var targetId = info.target.id;

        // During initial render, jsp.connect() fires 'connection' event for each
        // existing connection. Skip API calls — just populate _connMap.
        if (_isRendering) {
            _connMap[sourceId + '>' + targetId] = info.connection.id || '';
            return;
        }

        // Prevent duplicate (race condition guard)
        if (_connMap[sourceId + '>' + targetId]) {
            jsp.deleteConnection(info.connection, { fireEvent: false });
            return;
        }

        _showStatus('saving');
        $.post(ENDPOINTS.ADD_CONNECTION, { from: sourceId, to: targetId }, function (res) {
            if (!res || !res.status) {
                toastr.error(res?.data?.message || res?.message || 'Gagal membuat koneksi');
                jsp.deleteConnection(info.connection, { fireEvent: false });
                _showStatus('error');
                return;
            }
            _connMap[sourceId + '>' + targetId] = res.data?.result?.id || res.data?.id || '';
            _showSavedStatus();
        }).fail(function () {
            toastr.error('Gagal terhubung ke server');
            jsp.deleteConnection(info.connection, { fireEvent: false });
            _showStatus('error');
        });
    }

    function _onConnectionDetached(info) {
        var sourceId = info.source.id;
        var targetId = info.target.id;
        var key = sourceId + '>' + targetId;

        if (_connMap[key]) {
            // This was a user-initiated detach (from confirm dialog)
            // The confirm dialog already handles the API call
            delete _connMap[key];
        }
    }

    function _onConnectionClick(conn) {
        _selectConnection(conn);
    }

    // ===========================
    // CONNECTION DELETE
    // ===========================
    function _confirmDeleteConnection(conn) {
        var sourceName = _getNodeLabelById(conn.sourceId);
        var targetName = _getNodeLabelById(conn.targetId);
        var connEncId = _connMap[conn.sourceId + '>' + conn.targetId];

        if (!connEncId) return;

        Swal.fire({
            title: 'Hapus koneksi?',
            text: sourceName + ' → ' + targetName,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            jsp.deleteConnection(conn, { fireEvent: false });

            _showStatus('saving');
            $.post(ENDPOINTS.DELETE_CONNECTION + connEncId, {}, function (res) {
                if (res && res.status) {
                    delete _connMap[conn.sourceId + '>' + conn.targetId];
                    _showSavedStatus();
                    toastr.success('Koneksi dihapus');
                } else {
                    _refreshCanvas();
                    toastr.error(res?.data?.message || res?.message || 'Gagal menghapus koneksi');
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
    // NODE DELETE
    // ===========================
    function _confirmRemoveNode() {
        if (!_selectedNodeId) {
            toastr.info('Pilih modul terlebih dahulu (klik node di kanvas)');
            return;
        }

        var el = document.getElementById(_selectedNodeId);
        if (!el) return;

        var moduleName = _getNodeLabelById(_selectedNodeId);

        // Count affected connections
        var affected = Object.keys(_connMap).filter(function (key) {
            var parts = key.split('>');
            return parts[0] === _selectedNodeId || parts[1] === _selectedNodeId;
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
            $.post(ENDPOINTS.REMOVE_MODULE + _selectedNodeId, {}, function (res) {
                if (res && res.status) {
                    toastr.success('Modul dihapus dari kanvas');
                    _refreshCanvas();
                } else {
                    toastr.error(res?.data?.message || res?.message || 'Gagal menghapus modul');
                    _showStatus('error');
                }
            }).fail(function () {
                toastr.error('Gagal terhubung ke server');
                _showStatus('error');
            });
        });
    }

    // ===========================
    // RENAME MODULE (double-click → SweetAlert2 prompt → auto-save)
    // ===========================
    function _onNodeDblClick(e) {
        var nodeEl = e.target.closest('.jtk-node');
        if (!nodeEl) return;

        var encId = nodeEl.id;
        var currentName = _getNodeLabelById(encId);

        Swal.fire({
            title: 'Rename modul',
            input: 'text',
            inputValue: currentName,
            inputPlaceholder: 'Nama modul di kanvas',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            confirmButtonColor: '#0D9488',
            cancelButtonColor: '#6b7280',
            inputValidator: function (value) {
                if (!value || !value.trim()) return 'Nama tidak boleh kosong';
            },
        }).then(function (result) {
            if (!result.isConfirmed) return;
            var newName = result.value.trim();
            if (newName === currentName) return;

            _showStatus('saving');
            $.post(ENDPOINTS.UPDATE_LABEL + encId, { label: newName }, function (res) {
                if (!res || !res.status) {
                    toastr.error(res?.data?.message || res?.message || 'Gagal rename modul');
                    _showStatus('error');
                    return;
                }
                var nameEl = nodeEl.querySelector('.flow-module-name');
                if (nameEl) nameEl.textContent = newName;
                _showSavedStatus();
            }).fail(function () {
                toastr.error('Gagal terhubung ke server');
                _showStatus('error');
            });
        });
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

        var c = document.getElementById('flow-canvas');
        var centerX = c.scrollLeft + c.clientWidth / 2;
        var centerY = c.scrollTop + c.clientHeight / 2;

        _showStatus('saving');
        $('#btnAddModule').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

        $.post(ENDPOINTS.ADD_MODULE, {
            module_id: moduleId,
            pos_x: Math.round(centerX - 90),
            pos_y: Math.round(centerY - 40)
        }, function (res) {
            if (!res || !res.status) {
                toastr.error(res?.data?.message || res?.message || 'Gagal menambahkan modul');
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
        if (!jsp) return;

        clearTimeout(_positionSaveTimer);
        _positionSaveTimer = setTimeout(function () {
            var changed = [];
            var els = document.querySelectorAll('#flow-canvas .jtk-node');

            els.forEach(function (el) {
                var encId = el.id;
                var newX = Math.round(parseInt(el.style.left) || 0);
                var newY = Math.round(parseInt(el.style.top) || 0);
                var prev = _lastPositionState[encId];

                if (!prev || prev.x !== newX || prev.y !== newY) {
                    _lastPositionState[encId] = { x: newX, y: newY };
                    changed.push({ encId: encId, x: newX, y: newY });
                }
            });

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
            _clearCanvas();
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
        var filterId = _activeFilter;
        var els = document.querySelectorAll('#flow-canvas .jtk-node');

        els.forEach(function (el) {
            var encId = el.id;
            if (!filterId || encId === filterId || _getNodeProjectId(encId) === filterId) {
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

    function _getNodeLabelById(encId) {
        var el = document.getElementById(encId);
        if (!el) return '';
        var match = el.innerHTML.match(/flow-module-name[^>]*>([^<]+)/);
        return match ? match[1].trim() : '';
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
