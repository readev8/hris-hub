<div class="modal fade sap-modal" id="scenarioModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-briefcase me-2"></i>Add Business Scenario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scenarioForm">
                <div class="modal-body scenario-grid">
                    <input type="hidden" name="scenario_id" id="scenarioFormId">
                    <input type="hidden" name="blueprint_token" value="<?= esc($token, 'attr') ?>">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="sap-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="sap-input" id="scenarioTitleInput" required placeholder="e.g., User Login">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="sap-label">Description</label>
                            <textarea name="description" class="sap-input" rows="4" id="scenarioDescInput" placeholder="Describe the business scenario..." style="min-height:100px"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="sap-label">Actors</label>
                            <textarea name="actors" class="sap-input" rows="2" placeholder="e.g., System Admin, Department Head, User"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="sap-label">Frequency</label>
                            <select name="frequency" class="sap-select">
                                <option value="">-- Select --</option>
                                <option value="Daily">Daily</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="sap-label">Pre Condition</label>
                            <textarea name="pre_condition" class="sap-input" rows="2" placeholder="Conditions before this scenario starts..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="sap-label">Post Condition</label>
                            <textarea name="post_condition" class="sap-input" rows="2" placeholder="State after the scenario completes..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="sap-label">Normal Course</label>
                            <textarea name="normal_course" class="sap-input" rows="2" placeholder="Step-by-step description of the main flow..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="sap-label">Exception</label>
                            <textarea name="exception" class="sap-input" rows="2" placeholder="Error conditions and how they are handled..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="sap-label">Notes</label>
                            <textarea name="notes" class="sap-input" rows="2" placeholder="Additional notes..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="sap-label">Issue (Business Rules / Assumptions)</label>
                            <textarea name="issue" class="sap-input" rows="2" placeholder="Business rules, assumptions..."></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="sap-label">Attachments <span style="font-weight:400;color:var(--sap-text-muted)">(optional, max 5)</span></label>
                            <div style="border:2px dashed var(--sap-border);border-radius:var(--sap-radius);padding:16px;text-align:center;cursor:pointer" id="scenarioDropzone">
                                <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:var(--sap-text-muted);display:block;margin-bottom:4px"></i>
                                <p class="mb-0 text-secondary" style="font-size:12px">Drop files here or click to browse</p>
                                <button type="button" class="sap-btn sap-btn-secondary sap-btn-sm mt-2" onclick="event.stopPropagation(); $(this).closest('.mb-3').find('input[type=file]').click();">
                                    <i class="fas fa-upload"></i> Pilih File
                                </button>
                                <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf" multiple hidden>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2" id="scenarioFilePreview"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--sap-border)">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
