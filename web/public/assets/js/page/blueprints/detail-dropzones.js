/**
 * ============================================================================
 * Blueprint Detail Dropzone & File Form Bindings
 * ============================================================================
 *
 * Handles drag-and-drop file upload zones, file preview rendering triggers,
 * and form submission logic for scenario and design page modals.
 *
 * Dependencies: jQuery, BlueprintDetailUI
 * Date: 2026-08-18
 */

/* global $, site_url, toastr, BlueprintDetailUI */

const BlueprintDropzones = {

    // ===========================
    // SCENARIO DROPZONE
    // ===========================

    bindScenarioDropzone: function (ctx) {
        $('#scenarioDropzone').on('click', function (e) {
            if (e.target !== this) return;
            $(this).find('input[type="file"]').click();
        })
        .on('dragover', function (e) { e.preventDefault(); $(this).css('border-color', 'var(--sap-brand)'); })
        .on('dragleave', function () { $(this).css('border-color', 'var(--sap-border)'); })
        .on('drop', function (e) {
            e.preventDefault();
            $(this).css('border-color', 'var(--sap-border)');
            var files = e.originalEvent.dataTransfer.files;
            for (var i = 0; i < files.length; i++) { ctx.scenarioFiles.push(files[i]); }
            BlueprintDetailUI.renderScenarioFiles(ctx.scenarioFiles, ctx.scenarioExistingAttachments, ctx.scenarioDeletedAttachments);
        });

        $('#scenarioForm input[name="images[]"]').on('change', function () {
            for (var i = 0; i < this.files.length; i++) { ctx.scenarioFiles.push(this.files[i]); }
            BlueprintDetailUI.renderScenarioFiles(ctx.scenarioFiles, ctx.scenarioExistingAttachments, ctx.scenarioDeletedAttachments);
            $(this).val('');
        });

        $('#scenarioFilePreview').on('click', '.btn-remove-file', function () {
            ctx.scenarioFiles.splice($(this).data('idx'), 1);
            BlueprintDetailUI.renderScenarioFiles(ctx.scenarioFiles, ctx.scenarioExistingAttachments, ctx.scenarioDeletedAttachments);
        });

        $('#scenarioFilePreview').on('click', '.btn-remove-existing-attachment', function () {
            var attId = $(this).data('att-id');
            if (ctx.scenarioDeletedAttachments.indexOf(attId) === -1) {
                ctx.scenarioDeletedAttachments.push(attId);
            }
            BlueprintDetailUI.renderScenarioFiles(ctx.scenarioFiles, ctx.scenarioExistingAttachments, ctx.scenarioDeletedAttachments);
        });

        $('#scenarioForm').on('submit', function (e) {
            e.preventDefault();
            ctx.syncSummernote('#scenarioDescInput');
            var id = $('#scenarioFormId').val();
            var encModId = ctx.getEncryptedModuleId(ctx.currentModuleId);
            var url = id
                ? ctx.API_ENDPOINTS.SCENARIO_UPDATE(id)
                : ctx.API_ENDPOINTS.SCENARIO_CREATE(encModId);
            var fd = new FormData(this);
            fd.delete('images[]');
            ctx.scenarioFiles.forEach(function (f) { fd.append('images[]', f); });
            if (ctx.scenarioDeletedAttachments.length) {
                fd.append('deleted_attachments', JSON.stringify(ctx.scenarioDeletedAttachments));
            }
            $.ajax({
                url: url,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status) {
                        toastr.success(id ? 'Scenario updated' : 'Scenario added');
                        $('#scenarioModal').modal('hide');
                        ctx.refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                },
                error: function () { toastr.error('Request failed'); }
            });
        });
    },

    // ===========================
    // DESIGN PAGE DROPZONE
    // ===========================

    bindDesignPageDropzone: function (ctx) {
        $('#designPageDropzone').on('click', function (e) {
            if (e.target !== this) return;
            $(this).find('input[type="file"]').click();
        })
        .on('dragover', function (e) { e.preventDefault(); $(this).css('border-color', 'var(--sap-brand)'); })
        .on('dragleave', function () { $(this).css('border-color', 'var(--sap-border)'); })
        .on('drop', function (e) {
            e.preventDefault();
            $(this).css('border-color', 'var(--sap-border)');
            var files = e.originalEvent.dataTransfer.files;
            for (var i = 0; i < files.length; i++) { ctx.designPageFiles.push(files[i]); }
            BlueprintDetailUI.renderDesignPageFiles(ctx.designPageFiles, ctx.designPageExistingAttachments, ctx.designPageDeletedAttachments);
        });

        $('#designPageForm input[name="images[]"]').on('change', function () {
            for (var i = 0; i < this.files.length; i++) { ctx.designPageFiles.push(this.files[i]); }
            BlueprintDetailUI.renderDesignPageFiles(ctx.designPageFiles, ctx.designPageExistingAttachments, ctx.designPageDeletedAttachments);
            $(this).val('');
        });

        $('#designPageFilePreview').on('click', '.btn-remove-file', function () {
            ctx.designPageFiles.splice($(this).data('idx'), 1);
            BlueprintDetailUI.renderDesignPageFiles(ctx.designPageFiles, ctx.designPageExistingAttachments, ctx.designPageDeletedAttachments);
        });

        $('#designPageFilePreview').on('click', '.btn-remove-existing-attachment', function () {
            var attId = $(this).data('att-id');
            if (ctx.designPageDeletedAttachments.indexOf(attId) === -1) {
                ctx.designPageDeletedAttachments.push(attId);
            }
            BlueprintDetailUI.renderDesignPageFiles(ctx.designPageFiles, ctx.designPageExistingAttachments, ctx.designPageDeletedAttachments);
        });

        $('#designPageForm').on('submit', function (e) {
            e.preventDefault();
            ctx.syncSummernote('#designPageDescInput');
            var id = $('#designPageFormId').val();
            var encModId = ctx.getEncryptedModuleId(ctx.currentModuleId);
            var url = id
                ? ctx.API_ENDPOINTS.DESIGN_PAGE_UPDATE(id)
                : ctx.API_ENDPOINTS.DESIGN_PAGE_CREATE(encModId);
            var fd = new FormData(this);
            fd.delete('images[]');
            ctx.designPageFiles.forEach(function (f) { fd.append('images[]', f); });
            if (ctx.designPageDeletedAttachments.length) {
                fd.append('deleted_attachments', JSON.stringify(ctx.designPageDeletedAttachments));
            }
            $.ajax({
                url: url,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.status) {
                        toastr.success(id ? 'Design page updated' : 'Design page added');
                        $('#designPageModal').modal('hide');
                        ctx.refreshBlueprintDetail();
                    } else {
                        toastr.error(res.message || 'Failed');
                    }
                },
                error: function () { toastr.error('Request failed'); }
            });
        });
    }
};

window.BlueprintDropzones = BlueprintDropzones;
