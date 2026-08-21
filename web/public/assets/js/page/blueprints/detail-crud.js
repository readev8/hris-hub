/**
 * ============================================================================
 * Blueprint Detail CRUD Operations
 * ============================================================================
 *
 * Module, scenario, design page, and blueprint CRUD operations.
 * All methods operate on the BlueprintDetail context (passed as `ctx`).
 *
 * Dependencies: jQuery, Toastr, SweetAlert2, BlueprintDetailUI
 * Date: 2026-08-18
 */

/* global $, site_url, toastr, Swal, BlueprintDetailUI */

const BlueprintDetailCRUD = {

    // ===========================
    // MODULE CRUD
    // ===========================

    showAddModule: function () {
        $('#moduleFormId').val('');
        $('#moduleNameInput').val('');
        $('#moduleModal').modal('show');
    },

    editModule: function (moduleId, ctx) {
        var mod = ctx.blueprintModules.find(function (m) { return (m.id_encrypted || m.id) == moduleId; });
        if (!mod) return;
        $('#moduleFormId').val(moduleId);
        $('#moduleNameInput').val(mod.name);
        $('#moduleModal').modal('show');
    },

    bindModuleForm: function (ctx) {
        $('#moduleForm').on('submit', function (e) {
            e.preventDefault();
            var id = $('#moduleFormId').val();
            var url = id ? ctx.API_ENDPOINTS.MODULE_UPDATE(id) : ctx.API_ENDPOINTS.MODULES(ctx.token);
            $.post(url, $(this).serialize(), function (res) {
                if (res.status) {
                    toastr.success(id ? 'Module updated' : 'Module added');
                    $('#moduleModal').modal('hide');
                    ctx.refreshBlueprintDetail();
                } else {
                    toastr.error(res.message || 'Failed');
                }
            }).fail(function () {
                toastr.error('Request failed');
            });
        });
    },

    deleteModule: function (moduleId, ctx) {
        Swal.fire({
            title: 'Delete Module?',
            text: 'This will remove all scenarios, pages, and specifications.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post(ctx.API_ENDPOINTS.MODULE_DELETE(moduleId), {}, function (res) {
                    if (res.status) {
                        toastr.success('Module deleted');
                        ctx.currentModuleId = null;
                        ctx.refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                });
            }
        });
    },

    // ===========================
    // SCENARIO CRUD
    // ===========================

    showAddScenario: function (ctx) {
        if (!ctx.currentModuleId) { toastr.warning('Select a module first'); return; }
        $('#scenarioFormId').val('');
        $('#scenarioTitleInput').val('');
        ctx.initSummernote('#scenarioDescInput', '');
        $('[name="actors"]').val('');
        $('[name="pre_condition"]').val('');
        $('[name="post_condition"]').val('');
        $('[name="normal_course"]').val('');
        $('[name="exception"]').val('');
        $('[name="frequency"]').val('');
        $('[name="notes"]').val('');
        $('[name="issue"]').val('');
        ctx.scenarioExistingAttachments = [];
        ctx.scenarioDeletedAttachments = [];
        ctx.scenarioFiles = [];
        $('#scenarioModal .modal-title').html('<i class="fas fa-briefcase me-2"></i>Add Business Scenario');
        BlueprintDetailUI.renderScenarioFiles(ctx.scenarioFiles, ctx.scenarioExistingAttachments, ctx.scenarioDeletedAttachments);
        $('#scenarioModal').modal('show');
    },

    editScenario: function (scenarioId, ctx) {
        var mod = ctx.blueprintModules.find(function (m) { return m.id == ctx.currentModuleId; });
        if (!mod) return;
        var scenarios = mod.business_scenarios || [];
        var s = scenarios.find(function (sc) { return (sc.id_encrypted || sc.id) == scenarioId; });
        if (!s) return;
        $('#scenarioFormId').val(scenarioId);
        $('#scenarioTitleInput').val(s.title);
        ctx.initSummernote('#scenarioDescInput', s.description);
        $('[name="actors"]').val(s.actors || '');
        $('[name="pre_condition"]').val(s.pre_condition || '');
        $('[name="post_condition"]').val(s.post_condition || '');
        $('[name="normal_course"]').val(s.normal_course || '');
        $('[name="exception"]').val(s.exception || '');
        $('[name="frequency"]').val(s.frequency || '');
        $('[name="notes"]').val(s.notes || '');
        $('[name="issue"]').val(s.issue || '');
        ctx.scenarioExistingAttachments = (s.attachments || []).map(function (a) { return Object.assign({}, a); });
        ctx.scenarioDeletedAttachments = [];
        ctx.scenarioFiles = [];
        $('#scenarioModal .modal-title').html('<i class="fas fa-briefcase me-2"></i>Edit Business Scenario');
        BlueprintDetailUI.renderScenarioFiles(ctx.scenarioFiles, ctx.scenarioExistingAttachments, ctx.scenarioDeletedAttachments);
        $('#scenarioModal').modal('show');
    },

    deleteScenario: function (scenarioId, ctx) {
        Swal.fire({
            title: 'Delete Scenario?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post(ctx.API_ENDPOINTS.SCENARIO_DELETE(scenarioId), {}, function (res) {
                    if (res.status) {
                        toastr.success('Scenario deleted');
                        ctx.refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                });
            }
        });
    },

    // ===========================
    // DESIGN PAGE CRUD
    // ===========================

    showAddDesignPage: function (ctx) {
        if (!ctx.currentModuleId) { toastr.warning('Select a module first'); return; }
        $('#designPageFormId').val('');
        $('#designPageTitleInput').val('');
        ctx.initSummernote('#designPageDescInput', '');
        ctx.designPageExistingAttachments = [];
        ctx.designPageDeletedAttachments = [];
        ctx.designPageFiles = [];
        $('#designPageModal .modal-title').html('<i class="fas fa-palette me-2"></i>Add Design Page');
        BlueprintDetailUI.renderDesignPageFiles(ctx.designPageFiles, ctx.designPageExistingAttachments, ctx.designPageDeletedAttachments);
        $('#designPageModal').modal('show');
    },

    editDesignPage: function (pageId, ctx) {
        var mod = ctx.blueprintModules.find(function (m) { return m.id == ctx.currentModuleId; });
        if (!mod) return;
        var pages = mod.design_pages || [];
        var p = pages.find(function (dp) { return (dp.id_encrypted || dp.id) == pageId; });
        if (!p) return;
        $('#designPageFormId').val(pageId);
        $('#designPageTitleInput').val(p.title);
        ctx.initSummernote('#designPageDescInput', p.description);
        ctx.designPageExistingAttachments = (p.attachments || []).map(function (a) { return Object.assign({}, a); });
        ctx.designPageDeletedAttachments = [];
        ctx.designPageFiles = [];
        $('#designPageModal .modal-title').html('<i class="fas fa-palette me-2"></i>Edit Design Page');
        BlueprintDetailUI.renderDesignPageFiles(ctx.designPageFiles, ctx.designPageExistingAttachments, ctx.designPageDeletedAttachments);
        $('#designPageModal').modal('show');
    },

    deleteDesignPage: function (pageId, ctx) {
        Swal.fire({
            title: 'Delete Design Page?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post(ctx.API_ENDPOINTS.DESIGN_PAGE_DELETE(pageId), {}, function (res) {
                    if (res.status) {
                        toastr.success('Design page deleted');
                        ctx.refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                });
            }
        });
    },

    // ===========================
    // BLUEPRINT DELETE
    // ===========================

    confirmDelete: function (ctx) {
        Swal.fire({
            title: 'Delete Blueprint?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#AA0808',
            cancelButtonColor: '#758CA4'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post(ctx.API_ENDPOINTS.DELETE(ctx.token), {}, function (res) {
                    if (res.status) {
                        toastr.success('Blueprint deleted');
                        setTimeout(function () { window.location.href = site_url + '/blueprints'; }, 800);
                    } else {
                        toastr.error(res.data.message || 'Failed to delete');
                    }
                }).fail(function (xhr) {
                    toastr.error('Delete failed (HTTP ' + xhr.status + ')');
                });
            }
        });
    }
};

window.BlueprintDetailCRUD = BlueprintDetailCRUD;
