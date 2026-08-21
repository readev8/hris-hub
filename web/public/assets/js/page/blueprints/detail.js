/**
 * ============================================================================
 * Blueprint Detail Page (Orchestrator)
 * ============================================================================
 *
 * Main orchestration, state, selection, refresh, and event wiring for blueprint detail.
 * CRUD operations are in detail-crud.js (BlueprintDetailCRUD).
 * UI rendering functions are in detail-ui.js (BlueprintDetailUI).
 * Dropzone bindings are in detail-dropzones.js (BlueprintDropzones).
 *
 * Dependencies: jQuery, Bootstrap, Toastr, GLightbox, BlueprintDetailUI, BlueprintDetailCRUD, BlueprintDropzones
 * Date: 2026-08-18
 */

/* global $, site_url, toastr, GLightbox, BlueprintDetailUI, BlueprintDetailCRUD, BlueprintDropzones */

const BlueprintDetail = {

    // ===========================
    // API_ENDPOINTS
    // ===========================

    API_ENDPOINTS: {
        REFRESH: function (token) { return site_url + '/blueprints/' + token + '/refresh'; },
        MODULES: function (token) { return site_url + '/blueprints/' + token + '/modules'; },
        MODULE_UPDATE: function (id) { return site_url + '/blueprints/modules/' + id + '/update'; },
        MODULE_DELETE: function (id) { return site_url + '/blueprints/modules/' + id + '/delete'; },
        SCENARIO_UPDATE: function (id) { return site_url + '/blueprints/business-scenarios/' + id + '/update'; },
        SCENARIO_CREATE: function (encModId) { return site_url + '/blueprints/modules/' + encModId + '/business-scenarios'; },
        SCENARIO_DELETE: function (id) { return site_url + '/blueprints/business-scenarios/' + id + '/delete'; },
        DESIGN_PAGE_UPDATE: function (id) { return site_url + '/blueprints/design-pages/' + id + '/update'; },
        DESIGN_PAGE_CREATE: function (encModId) { return site_url + '/blueprints/modules/' + encModId + '/design-pages'; },
        DESIGN_PAGE_DELETE: function (id) { return site_url + '/blueprints/design-pages/' + id + '/delete'; },
        DELETE: function (token) { return site_url + '/blueprints/' + token + '/delete'; },
        ATTACHMENTS: function (token) { return site_url + '/blueprints/' + token + '/attachments'; },
        COMMENTS: function (token) { return site_url + '/blueprints/' + token + '/comments'; }
    },

    // ===========================
    // PAGE DATA
    // ===========================

    pageData: window.PageData || {},

    get token() {
        return this.pageData.token || '';
    },

    get userPerms() {
        return this.pageData.userPermissions || {};
    },

    blueprintModules: [],

    // ===========================
    // STATE
    // ===========================

    currentModuleId: null,
    currentTab: 'scenarios',

    scenarioFiles: [],
    scenarioExistingAttachments: [],
    scenarioDeletedAttachments: [],

    designPageFiles: [],
    designPageExistingAttachments: [],
    designPageDeletedAttachments: [],

    // ===========================
    // INITIALIZATION
    // ===========================

    init: function () {
        var self = this;
        self.blueprintModules = self.pageData.modules || [];
        self.currentModuleId = self.pageData.currentModuleId || null;

        if (self.currentModuleId) {
            var firstModule = $('.module-item[data-module-id="' + self.currentModuleId + '"]');
            if (firstModule.length) {
                self.selectModule(self.currentModuleId, firstModule[0]);
            }
        }

        BlueprintDropzones.bindScenarioDropzone(self);
        BlueprintDropzones.bindDesignPageDropzone(self);
        self.bindAttachmentInput();
        self.initLightbox();
        self.bindModalDismiss();
        BlueprintDetailCRUD.bindModuleForm(self);
    },

    // ===========================
    // HELPERS
    // ===========================

    escHtml: function (str) {
        return $('<div>').text(str || '').html();
    },

    getEncryptedModuleId: function (rawId) {
        var mod = this.blueprintModules.find(function (m) { return m.id == rawId; });
        return mod ? (mod.id_encrypted || mod.id) : null;
    },

    // ===========================
    // SUMMERNOTE WYSIWYG
    // ===========================

    SUMMERNOTE_CONFIG: {
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'strike']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['links', ['link']],
            ['misc', ['undo', 'redo', 'clear']]
        ],
        height: 150,
        placeholder: 'Describe...',
        disableDragAndDrop: true,
        shortcuts: false
    },

    initSummernote: function (selector, content) {
        if (typeof $.fn.summernote !== 'function') return;
        try { $(selector).summernote('destroy'); } catch (e) { /* not initialised */ }
        $(selector).summernote(this.SUMMERNOTE_CONFIG);
        if (content) {
            $(selector).summernote('code', content);
        }
    },

    destroySummernote: function (selector) {
        if (typeof $.fn.summernote !== 'function') return;
        try { $(selector).summernote('destroy'); } catch (e) { /* not initialised */ }
    },

    syncSummernote: function (selector) {
        if (typeof $.fn.summernote !== 'function') return;
        $(selector).val($(selector).summernote('code'));
    },

    // ===========================
    // MODULE SELECTION
    // ===========================

    selectModule: function (moduleId, el) {
        this.currentModuleId = moduleId;
        $('.module-item').removeClass('active');
        $(el).addClass('active');
        var name = $(el).find('.fw-medium').text();
        $('#currentModuleName').text(name);
        this.loadModuleContent(moduleId);
    },

    loadModuleContent: function (moduleId) {
        if (!moduleId) return;
        var mod = this.blueprintModules.find(function (m) { return m.id == moduleId; });
        if (!mod) return;
        BlueprintDetailUI.renderScenarios(mod.business_scenarios || [], {
            userPerms: this.userPerms,
            token: this.token
        });
        BlueprintDetailUI.renderDesignPages(mod.design_pages || [], {
            userPerms: this.userPerms
        });
    },

    // ===========================
    // AJAX REFRESH SYSTEM
    // ===========================

    refreshBlueprintDetail: function (callback) {
        var self = this;
        $.ajax({
            url: self.API_ENDPOINTS.REFRESH(self.token),
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                $('#modulesList').css('opacity', '0.6');
            },
            success: function (res) {
                $('#modulesList').css('opacity', '');
                if (res.status) {
                    self.blueprintModules = res.data.modules || [];
                    BlueprintDetailUI.updateModulesSidebar(res.data.modules, {
                        currentModuleId: self.currentModuleId,
                        userPerms: self.userPerms
                    });
                    if (!self.currentModuleId && res.data.modules && res.data.modules.length) {
                        self.currentModuleId = res.data.modules[0].id;
                    }
                    if (self.currentModuleId) {
                        self.loadModuleContent(self.currentModuleId);
                    }
                    BlueprintDetailUI.updateBlueprintHeader(res.data);
                    BlueprintDetailUI.updateCommentsSection(res.data.comments);
                    self.bindCommentForm();
                    BlueprintDetailUI.updateAttachmentsSection(res.data.attachments);
                    if (callback) callback(res.data);
                } else {
                    toastr.error(res.message || 'Failed to refresh data');
                }
            },
            error: function () {
                $('#modulesList').css('opacity', '');
                toastr.error('Failed to refresh data');
            }
        });
    },

    // ===========================
    // TAB SWITCHING
    // ===========================

    switchTab: function (tab) {
        this.currentTab = tab;
        $('#moduleTabs .nav-link').removeClass('active');
        $('#moduleTabs .nav-link[data-tab="' + tab + '"]').addClass('active');
        $('.tab-content-section').hide();
        $('#tab-content-' + tab).show();
    },

    // ===========================
    // CRUD DELEGATION
    // ===========================

    showAddModule: function () { BlueprintDetailCRUD.showAddModule(); },
    editModule: function (moduleId) { BlueprintDetailCRUD.editModule(moduleId, this); },
    deleteModule: function (moduleId) { BlueprintDetailCRUD.deleteModule(moduleId, this); },
    showAddScenario: function () { BlueprintDetailCRUD.showAddScenario(this); },
    editScenario: function (scenarioId) { BlueprintDetailCRUD.editScenario(scenarioId, this); },
    deleteScenario: function (scenarioId) { BlueprintDetailCRUD.deleteScenario(scenarioId, this); },
    showAddDesignPage: function () { BlueprintDetailCRUD.showAddDesignPage(this); },
    editDesignPage: function (pageId) { BlueprintDetailCRUD.editDesignPage(pageId, this); },
    deleteDesignPage: function (pageId) { BlueprintDetailCRUD.deleteDesignPage(pageId, this); },
    confirmDelete: function () { BlueprintDetailCRUD.confirmDelete(this); },

    // ===========================
    // ATTACHMENT UPLOAD
    // ===========================

    bindAttachmentInput: function () {
        var self = this;
        $('#attachmentInput').on('change', function () {
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

            if (skipped >= files.length) { $(this).val(''); return; }

            var btn = $(this).closest('label');
            btn.css('pointer-events', 'none').css('opacity', '0.6');

            $.ajax({
                url: self.API_ENDPOINTS.ATTACHMENTS(self.token),
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status) {
                        toastr.success('Attachment(s) uploaded');
                        self.refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed to upload');
                        btn.css('pointer-events', '').css('opacity', '');
                        $('#attachmentInput').val('');
                    }
                },
                error: function (xhr) {
                    var res = null;
                    try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
                    if (res && res.redirect) { window.location.href = res.redirect; return; }
                    toastr.error(res && res.message ? res.message : 'Upload failed (HTTP ' + xhr.status + ')');
                    btn.css('pointer-events', '').css('opacity', '');
                    $('#attachmentInput').val('');
                }
            });
            $(this).val('');
        });
    },

    // ===========================
    // COMMENTS
    // ===========================

    bindCommentForm: function () {
        var self = this;
        $('#commentForm').off('submit').on('submit', function (e) {
            e.preventDefault();
            var text = $('#commentText').val();
            if (!text.trim()) return;
            $.post(self.API_ENDPOINTS.COMMENTS(self.token), { content: text }, function (res) {
                if (res.status) {
                    toastr.success('Comment added');
                    $('#commentText').val('');
                    self.refreshBlueprintDetail();
                } else {
                    toastr.error(res.data.message || 'Failed');
                }
            }).fail(function (xhr) {
                toastr.error('Failed to add comment (HTTP ' + xhr.status + ')');
            });
        });
    },

    // ===========================
    // LIGHTBOX
    // ===========================

    initLightbox: function () {
        GLightbox({
            selector: '.blueprint-attachment-link',
            touchNavigation: true,
            keyboardNavigation: true,
            loop: false,
            preload: true
        });
    },

    // ===========================
    // MODAL CLEANUP
    // ===========================

    bindModalDismiss: function () {
        var self = this;
        $('#scenarioModal, #designPageModal').on('hidden.bs.modal', function () {
            self.destroySummernote('#scenarioDescInput');
            self.destroySummernote('#designPageDescInput');
        });
    }
};

window.BlueprintDetail = BlueprintDetail;

$(function () {
    BlueprintDetail.init();
});
