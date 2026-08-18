<div class="modal fade sap-modal" id="specModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="specModalTitle"><i class="fas fa-list-alt me-2"></i>Add Page Specification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="specForm">
                <input type="hidden" name="blueprint_token" value="<?= esc($designPage['blueprint_id_encrypted'] ?? '', 'attr') ?>">
                <input type="hidden" name="spec_id" id="specFormId">
                <input type="hidden" name="existing_ux_att_id" id="specFormExistingAttId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="sap-label">Field Name <span class="text-danger">*</span></label>
                            <input type="text" name="field_name" class="sap-input" id="specFieldNameInput" required placeholder="e.g., Username">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Data</label>
                            <input type="text" name="data" class="sap-input" id="specDataInput" placeholder="e.g., varchar(100)">
                        </div>
                        <div class="col-md-12">
                            <label class="sap-label">Objective</label>
                            <input type="text" name="objective" class="sap-input" id="specObjectiveInput" placeholder="e.g., User identification">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Initial Data</label>
                            <input type="text" name="initial_data" class="sap-input" id="specInitialDataInput" placeholder="e.g., Empty">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Condition</label>
                            <input type="text" name="condition" class="sap-input" id="specConditionInput" placeholder="e.g., Required for login">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Validation</label>
                            <input type="text" name="validation" class="sap-input" id="specValidationInput" placeholder="e.g., Min 6 chars">
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Input/Display</label>
                            <select name="input_display" class="sap-select" id="specInputDisplayInput">
                                <option value="Input">Input</option>
                                <option value="Display">Display</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Datatype</label>
                            <select name="datatype" class="sap-select" id="specDatatypeInput">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="datetime">DateTime</option>
                                <option value="time">Time</option>
                                <option value="image">Image</option>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="sap-label">Control Type</label>
                            <select name="control_type" class="sap-select" id="specControlTypeInput">
                                <option value="text">Text</option>
                                <option value="password">Password</option>
                                <option value="date">Date</option>
                                <option value="datetime">DateTime</option>
                                <option value="combobox">Combobox</option>
                                <option value="radiobutton">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="multipleselect">Multiple Select</option>
                                <option value="uploadfile">Upload File</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="sap-label">UX Image <span class="text-secondary" style="font-weight:400;font-size:12px">(optional, max 500KB, JPG/PNG/WebP)</span></label>
                            <div class="spec-ux-dropzone" id="specUxDropzone">
                                <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:var(--sap-text-muted);display:block;margin-bottom:4px"></i>
                                <p class="mb-0 text-secondary" style="font-size:12px">Drop image here or click to browse</p>
                                <button type="button" class="sap-btn sap-btn-secondary sap-btn-sm mt-2" onclick="event.stopPropagation(); $('#specUxFile').click();">
                                    <i class="fas fa-image"></i> Pilih Gambar
                                </button>
                                <input type="file" name="ux_image[]" accept="image/jpeg,image/png,image/webp" hidden id="specUxFile">
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2" id="specUxPreview"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--sap-border)">
                    <button type="button" class="sap-btn sap-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sap-btn sap-btn-primary"><i class="fas fa-save"></i> <span id="specSubmitText">Save</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
