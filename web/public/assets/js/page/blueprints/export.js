/* Blueprint Export PDF — pdfmake 0.2.7 + html-to-pdfmake 2.5.20
 * Depends on: pdfMake, htmlToPdfmake (globals), PageData.token
 * Provides BlueprintDetail.exportPdf()
 * Design tokens: ui-ux-pro-max Trust & Authority (Slate + Teal brand)
 */
;(function () {
    'use strict';

    /* ── Design tokens (from app global style.css) ────────────── */
    var T = {
        brand:       '#0D9488',
        brandDark:   '#0F766E',
        brandLight:  '#CCFBF1',
        shell:       '#0F172A',
        ink:         '#1E293B',
        slate:       '#475569',
        muted:       '#94A3B8',
        surface:     '#F8FAFC',
        border:      '#E2E8F0',
        borderLight: '#F1F5F9',
        white:       '#FFFFFF'
    };

    var STATUS_COLORS = {
        'draft':    { bg: '#64748B', fg: '#FFFFFF' },
        'pending':  { bg: '#B45309', fg: '#FFFFFF' },
        'approved': { bg: '#15803D', fg: '#FFFFFF' },
        'rejected': { bg: '#B91C1C', fg: '#FFFFFF' },
        'on hold':  { bg: '#7C3AED', fg: '#FFFFFF' },
        'default':  { bg: '#64748B', fg: '#FFFFFF' }
    };

    var SITE_URL = (typeof site_url !== 'undefined') ? site_url : '';

    function _txt(v) { return v == null ? '' : String(v); }

    function _statusStyle(status) {
        var key = _txt(status).toLowerCase();
        return STATUS_COLORS[key] || STATUS_COLORS['default'];
    }

    /* ---- HTML helpers ---- */

    function _stripTags(html) {
        if (!html) return '';
        var s = String(html);
        s = s.replace(/<br\s*\/?>/gi, '\n');
        s = s.replace(/<\/p>/gi, '\n');
        s = s.replace(/<\/div>/gi, '\n');
        s = s.replace(/<\/li>/gi, '\n');
        s = s.replace(/<li[^>]*>/gi, '\u2022 ');
        s = s.replace(/<[^>]+>/g, '');
        s = s.replace(/&nbsp;/gi, ' ');
        s = s.replace(/&lt;/gi, '<');
        s = s.replace(/&gt;/gi, '>');
        s = s.replace(/&quot;/gi, '"');
        s = s.replace(/&#39;/gi, "'");
        s = s.replace(/&amp;/gi, '&');
        s = s.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        s = s.replace(/[ \t]+\n/g, '\n');
        s = s.replace(/\n{3,}/g, '\n\n');
        return s.trim();
    }

    function _preClean(html) {
        var s = String(html || '');
        s = s.replace(/<script[\s\S]*?<\/script>/gi, '');
        s = s.replace(/<style[\s\S]*?<\/style>/gi, '');
        s = s.replace(/<!--[\s\S]*?-->/g, '');
        s = s.replace(/<img[^>]*>/gi, '');
        return s;
    }

    function _htmlToContent(html) {
        if (!html) return null;
        var clean = _preClean(html);
        if (typeof window.htmlToPdfmake === 'undefined') {
            return { text: _stripTags(clean), fontSize: 9, margin: [0, 0, 0, 6] };
        }
        var conv = window.htmlToPdfmake(clean, {
            tableAutoSize: true,
            removeExtraBlanks: true
        });
        var stack = Array.isArray(conv) ? conv : [conv];
        if (!stack.length) return null;
        return { stack: stack, margin: [0, 0, 0, 6] };
    }

    /* ---- Image helpers ---- */

    var _IMAGE_EXTS = /\.(png|jpe?g|gif|webp|bmp|tiff?)$/i;

    function _isImage(att) {
        if (!att) return false;
        var mime = att.mime_type || '';
        if (mime.indexOf('image/') === 0) return true;
        if (att.stored_name && _IMAGE_EXTS.test(att.stored_name)) return true;
        return false;
    }

    function _loadImage(att) {
        if (!_isImage(att)) return Promise.resolve(null);
        var url = SITE_URL + '/uploads/blueprints/' + att.stored_name;
        return fetch(url, { credentials: 'same-origin' })
            .then(function (res) {
                if (!res.ok) return null;
                return res.blob();
            })
            .then(function (blob) {
                if (!blob) return null;
                var mime = blob.type || '';
                if (mime === 'image/png' || mime === 'image/jpeg') {
                    return new Promise(function (resolve) {
                        var reader = new FileReader();
                        reader.onload = function () {
                            var img = new Image();
                            img.onload = function () {
                                resolve({ dataUrl: reader.result, w: img.naturalWidth || 1, h: img.naturalHeight || 1 });
                            };
                            img.onerror = function () { resolve(null); };
                            img.src = reader.result;
                        };
                        reader.onerror = function () { resolve(null); };
                        reader.readAsDataURL(blob);
                    });
                }
                return new Promise(function (resolve) {
                    var img = new Image();
                    img.onload = function () {
                        var w = img.naturalWidth || 1;
                        var h = img.naturalHeight || 1;
                        if (w <= 0 || h <= 0) return resolve(null);
                        var canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        canvas.getContext('2d').drawImage(img, 0, 0);
                        resolve({ dataUrl: canvas.toDataURL('image/png'), w: w, h: h });
                    };
                    img.onerror = function () { resolve(null); };
                    img.src = URL.createObjectURL(blob);
                });
            })
            .catch(function () { return null; });
    }

    function _collectImages(bp) {
        if (!bp) return Promise.resolve({});
        var atts = [];
        if (bp.attachments) atts = atts.concat(bp.attachments);
        (bp.modules || []).forEach(function (mod) {
            (mod.business_scenarios || []).forEach(function (sc) {
                if (sc.attachments) atts = atts.concat(sc.attachments);
            });
            (mod.design_pages || []).forEach(function (pg) {
                if (pg.attachments) atts = atts.concat(pg.attachments);
                (pg.page_specifications || []).forEach(function (sp) {
                    if (sp.ux_attachment) atts.push(sp.ux_attachment);
                });
            });
        });
        var filtered = atts.filter(_isImage);
        if (!filtered.length) return Promise.resolve({});
        var imgMap = {};
        var promises = filtered.map(function (att) {
            return _loadImage(att).then(function (r) { if (r) imgMap[att.stored_name] = r; });
        });
        return Promise.allSettled(promises).then(function () { return imgMap; });
    }

    function _fitImage(w, h, maxW, maxH) {
        var fw = Math.min(w, maxW);
        var fh = (h / w) * fw;
        if (fh > maxH) { fh = maxH; fw = (w / h) * fh; }
        return { w: Math.round(fw), h: Math.round(fh) };
    }

    /* ---- Composed image helpers ---- */

    function _framedImage(dataUrl, w, h, maxW, maxH) {
        var fit = _fitImage(w, h, maxW, maxH);
        return {
            table: {
                widths: [fit.w + 4],
                body: [[{ image: dataUrl, width: fit.w, height: fit.h }]]
            },
            layout: {
                hLineWidth: function () { return 0.5; },
                vLineWidth: function () { return 0.5; },
                hLineColor: function () { return T.border; },
                vLineColor: function () { return T.border; },
                paddingLeft: function () { return 2; },
                paddingRight: function () { return 2; },
                paddingTop: function () { return 2; },
                paddingBottom: function () { return 2; }
            },
            margin: [0, 0, 0, 4]
        };
    }

    function _imagesGrid(atts, imgMap, cols, maxW, maxH) {
        cols = cols || 2;
        maxW = maxW || 245;
        maxH = maxH || 680;
        if (!atts || !atts.length) return null;
        var images = [], nonImages = [];
        atts.forEach(function (att) {
            if (_isImage(att) && imgMap[att.stored_name]) images.push(att);
            else nonImages.push(att);
        });
        var content = [];
        for (var i = 0; i < images.length; i += cols) {
            var cells = [];
            for (var j = i; j < Math.min(i + cols, images.length); j++) {
                var img = imgMap[images[j].stored_name];
                var fit = _fitImage(img.w, img.h, maxW, maxH);
                cells.push({
                    stack: [
                        _framedImage(img.dataUrl, img.w, img.h, maxW, maxH),
                        { text: _txt(images[j].filename), fontSize: 6.5, color: T.muted, italics: true, margin: [2, 2, 0, 0] }
                    ]
                });
            }
            while (cells.length < cols) cells.push('');
            content.push({ columns: cells, columnGap: 8, margin: [0, 0, 0, 8] });
        }
        nonImages.forEach(function (att) {
            content.push({
                table: { widths: ['auto'], body: [[{ text: (_txt(att.filename) || 'File'), fontSize: 8, color: T.slate }]] },
                layout: {
                    hLineWidth: function () { return 0.5; }, vLineWidth: function () { return 0.5; },
                    hLineColor: function () { return T.border; }, vLineColor: function () { return T.border; },
                    fillColor: function () { return T.borderLight; },
                    paddingLeft: function () { return 6; }, paddingRight: function () { return 6; },
                    paddingTop: function () { return 3; }, paddingBottom: function () { return 3; }
                },
                margin: [0, 0, 0, 4], width: 'auto'
            });
        });
        return content.length ? content : null;
    }

    /* ---- Date / header / footer helpers ---- */

    function _today() {
        var d = new Date(), m = String(d.getMonth() + 1), day = String(d.getDate());
        return d.getFullYear() + '-' + (m.length < 2 ? '0' : '') + m + '-' + (day.length < 2 ? '0' : '') + day;
    }

    function _bpName(bp) { return _txt(bp.name) || 'Untitled'; }

    function _pageHeader(bp) {
        return function (currentPage, pageCount) {
            return {
                margin: [40, 16, 40, 0],
                columns: [
                    { text: _bpName(bp), fontSize: 7, color: T.muted, width: '*' },
                    { text: 'Page ' + currentPage + ' / ' + pageCount, fontSize: 7, color: T.muted, width: 'auto', alignment: 'right' }
                ],
                canvas: [{ type: 'line', x1: 0, y1: 12, x2: 515, y2: 12, lineWidth: 0.75, lineColor: T.brand }]
            };
        };
    }

    function _pageFooter() {
        return function () {
            return {
                margin: [40, 0, 40, 20],
                columns: [
                    { text: 'Generated ' + _today() + ' via HRIS Hub', fontSize: 7, color: T.muted, width: '*' }
                ]
            };
        };
    }

    /* ---- Section heading with bottom rule ---- */

    function _sectionHeading(text) {
        return {
            table: {
                widths: ['*'],
                body: [[{ text: text, fontSize: 12, bold: true, color: T.brand }]]
            },
            layout: {
                hLineWidth: function (i) { return i === 0 ? 0 : 0.75; },
                vLineWidth: function () { return 0; },
                hLineColor: function () { return T.brand; },
                paddingTop: function () { return 0; },
                paddingBottom: function () { return 4; },
                paddingLeft: function () { return 0; },
                paddingRight: function () { return 0; }
            },
            margin: [0, 0, 0, 8]
        };
    }

    /* ---- Cover page ---- */

    function _cover(bp, imgMap) {
        var content = [];

        /* Dark band header */
        content.push({
            table: {
                widths: ['*'],
                body: [[{
                    columns: [
                        { text: 'BLUEPRINT DOCUMENT', fontSize: 9, color: T.white, bold: true, width: '*', margin: [0, 8, 0, 8] },
                        { text: 'HRIS Hub', fontSize: 8, color: T.brandLight, width: 'auto', alignment: 'right', margin: [0, 8, 0, 8] }
                    ]
                }]]
            },
            layout: {
                hLineWidth: function () { return 0; },
                vLineWidth: function () { return 0; },
                fillColor: function () { return T.shell; },
                paddingLeft: function () { return 12; },
                paddingRight: function () { return 12; },
                paddingTop: function () { return 0; },
                paddingBottom: function () { return 0; }
            },
            margin: [-40, -50, -40, 0],
            width: 'stretch'
        });

        /* Brand rule */
        content.push({
            canvas: [{ type: 'line', x1: 0, y1: 0, x2: 515, y2: 0, lineWidth: 3, lineColor: T.brand }],
            margin: [0, 0, 0, 20]
        });

        /* Title */
        content.push({ text: _bpName(bp), fontSize: 24, bold: true, color: T.ink, margin: [0, 10, 0, 12] });

        /* Status pill */
        var st = _statusStyle(bp.status_name);
        content.push({
            table: { widths: ['auto'], body: [[{ text: _txt(bp.status_name) || 'Draft', fontSize: 9, bold: true, color: st.fg, margin: [10, 4, 10, 4] }]] },
            layout: {
                hLineWidth: function () { return 0; }, vLineWidth: function () { return 0; },
                fillColor: function () { return st.bg; },
                paddingLeft: function () { return 0; }, paddingRight: function () { return 0; },
                paddingTop: function () { return 0; }, paddingBottom: function () { return 0; }
            },
            margin: [0, 0, 0, 16], width: 'auto'
        });

        /* Description */
        if (bp.description) {
            content.push({ text: _txt(bp.description), fontSize: 10, color: T.slate, margin: [0, 0, 0, 16] });
        }

        /* Meta table */
        var metaRows = [];
        if (bp.creator_name) metaRows.push([
            { text: 'CREATOR', style: 'metaLabel', width: 80 },
            { text: _txt(bp.creator_name), style: 'metaValue', width: 360 }
        ]);
        if (bp.created_at) metaRows.push([
            { text: 'CREATED', style: 'metaLabel', width: 80 },
            { text: _txt(bp.created_at), style: 'metaValue', width: 360 }
        ]);
        if (bp.updated_at) metaRows.push([
            { text: 'UPDATED', style: 'metaLabel', width: 80 },
            { text: _txt(bp.updated_at), style: 'metaValue', width: 360 }
        ]);
        metaRows.push([
            { text: 'EXPORTED', style: 'metaLabel', width: 80 },
            { text: _today(), style: 'metaValue', width: 360 }
        ]);

        if (metaRows.length) {
            content.push({
                table: { widths: [80, 360], body: metaRows },
                layout: {
                    hLineWidth: function (i) { return i > 0 ? 0.5 : 0; },
                    vLineWidth: function () { return 0; },
                    hLineColor: function () { return T.border; },
                    paddingTop: function () { return 5; },
                    paddingBottom: function () { return 5; },
                    paddingLeft: function () { return 0; },
                    paddingRight: function () { return 0; }
                },
                margin: [0, 0, 0, 0]
            });
        }

        /* Blueprint-level attachments */
        var bpGrid = _imagesGrid(bp.attachments, imgMap);
        if (bpGrid) {
            content.push(_sectionHeading('Attachments'));
            content = content.concat(bpGrid);
        }

        return content;
    }

    /* ---- Module header block ---- */

    function _moduleHeader(mod, mi, scenarios, pages, specCount) {
        var metaStr = scenarios + ' Scenarios \u00B7 ' + pages + ' Pages \u00B7 ' + specCount + ' Specs';
        return {
            table: {
                widths: [4, '*'],
                body: [[
                    '',
                    {
                        stack: [
                            { text: 'MODULE ' + (mi + 1), fontSize: 7, bold: true, color: T.brand, characterSpacing: 1.5, margin: [0, 0, 0, 4] },
                            { text: _txt(mod.name), fontSize: 16, bold: true, color: T.ink, margin: [0, 0, 0, 4] },
                            { text: metaStr, fontSize: 8, color: T.muted, margin: [0, 0, 0, 0] }
                        ]
                    }
                ]]
            },
            layout: {
                hLineWidth: function () { return 0; },
                vLineWidth: function (i) { return i === 0 ? 3 : 0; },
                vLineColor: function () { return T.brand; },
                fillColor: function (col, row) { return col === 1 ? T.borderLight : null; },
                paddingLeft: function (col) { return col === 0 ? 0 : 12; },
                paddingRight: function () { return 8; },
                paddingTop: function () { return 8; },
                paddingBottom: function () { return 8; }
            },
            margin: [0, 0, 0, 16]
        };
    }

    /* ---- Scenario card ---- */

    function _scenarioCard(sc, mi, si) {
        var body = [];

        /* Title row */
        body.push([{
            columns: [
                { text: (mi + 1) + '.' + (si + 1), fontSize: 10, bold: true, color: T.brand, width: 30 },
                { text: _txt(sc.title), fontSize: 10, bold: true, color: T.ink, width: '*' }
            ],
            columnGap: 4, margin: [0, 0, 0, 4]
        }]);

        /* Description */
        var desc = _htmlToContent(sc.description);
        if (desc) body.push([desc]);

        /* Fields */
        var fieldRows = [];
        var fields = [
            ['Actors', sc.actors], ['Frequency', sc.frequency],
            ['Pre Condition', sc.pre_condition], ['Post Condition', sc.post_condition],
            ['Normal Course', sc.normal_course], ['Exception', sc.exception],
            ['Notes', sc.notes], ['Issue', sc.issue]
        ];
        fields.forEach(function (f) {
            if (f[1]) {
                fieldRows.push([{
                    columns: [
                        { text: f[0] + ':', fontSize: 8, bold: true, color: T.slate, width: 80 },
                        { text: _txt(f[1]), fontSize: 8, color: T.ink, width: '*' }
                    ],
                    columnGap: 4, margin: [0, 1, 0, 1]
                }]);
            }
        });
        if (fieldRows.length) body = body.concat(fieldRows);

        return {
            table: {
                widths: ['*'],
                body: [[{ stack: body.length ? body : [{ text: '(empty)', fontSize: 8, color: T.muted, italics: true }] }]]
            },
            layout: {
                hLineWidth: function () { return 0.5; },
                vLineWidth: function () { return 0.5; },
                hLineColor: function () { return T.border; },
                vLineColor: function () { return T.border; },
                paddingLeft: function () { return 10; },
                paddingRight: function () { return 10; },
                paddingTop: function () { return 8; },
                paddingBottom: function () { return 8; }
            },
            margin: [0, 0, 0, 10]
        };
    }

    /* ---- Specs table ---- */

    function _specsTable(specs) {
        if (!specs || !specs.length) return null;
        var headerStyle = { fontSize: 7, bold: true, color: T.white, margin: [0, 1, 0, 1] };
        var headerRow = [
            { text: 'Field', style: '' },
            { text: 'Data', style: '' },
            { text: 'Objective', style: '' },
            { text: 'Init', style: '' },
            { text: 'Condition', style: '' },
            { text: 'Validation', style: '' },
            { text: 'I/D', style: '' },
            { text: 'Datatype', style: '' },
            { text: 'Control', style: '' }
        ].map(function (h) { return { text: h.text, fontSize: 7, bold: true, color: T.white, margin: [0, 1, 0, 1] }; });

        var body = [headerRow];
        specs.forEach(function (sp, idx) {
            var isEven = idx % 2 === 0;
            body.push([
                { text: _txt(sp.field_name), fontSize: 7, bold: true, color: T.ink },
                { text: _txt(sp.data), fontSize: 7, color: T.ink },
                { text: _txt(sp.objective), fontSize: 7, color: T.ink },
                { text: _txt(sp.initial_data), fontSize: 7, color: T.ink },
                { text: _txt(sp.condition), fontSize: 7, color: T.ink },
                { text: _txt(sp.validation), fontSize: 7, color: T.ink },
                { text: _txt(sp.input_display), fontSize: 7, color: T.ink, alignment: 'center' },
                { text: _txt(sp.datatype), fontSize: 7, color: T.ink },
                { text: _txt(sp.control_type), fontSize: 7, color: T.ink }
            ]);
        });
        return {
            table: {
                widths: [62, 50, 60, 45, 55, 55, 25, 50, 55],
                body: body,
                headerRows: 1
            },
            layout: {
                hLineWidth: function (i) { return i === 1 ? 1 : 0.5; },
                vLineWidth: function () { return 0.5; },
                hLineColor: function () { return T.border; },
                vLineColor: function () { return T.border; },
                fillColor: function (rowIndex) {
                    if (rowIndex === 0) return T.shell;
                    return rowIndex % 2 === 0 ? T.borderLight : null;
                },
                paddingTop: function () { return 4; },
                paddingBottom: function () { return 4; },
                paddingLeft: function () { return 4; },
                paddingRight: function () { return 4; }
            },
            margin: [0, 0, 0, 8]
        };
    }

    /* ---- Document builder ---- */

    function _buildDocument(bp, imgMap) {
        var content = [];

        content = content.concat(_cover(bp, imgMap));

        var modules = bp.modules || [];
        if (!modules.length) {
            content.push({ text: 'No modules defined.', fontSize: 10, color: T.muted, margin: [0, 20, 0, 0] });
        }

        modules.forEach(function (mod, mi) {
            content.push({ text: '', pageBreak: 'before' });

            var scenarios = mod.business_scenarios || [];
            var pages = mod.design_pages || [];
            var specCount = 0;
            pages.forEach(function (pg) { specCount += (pg.page_specifications || []).length; });

            content.push(_moduleHeader(mod, mi, scenarios.length, pages.length, specCount));

            if (scenarios.length) {
                content.push(_sectionHeading('Business Scenarios'));
                scenarios.forEach(function (sc, si) {
                    content.push(_scenarioCard(sc, mi, si));
                    var scGrid = _imagesGrid(sc.attachments, imgMap);
                    if (scGrid) content = content.concat(scGrid);
                });
            }

            if (pages.length) {
                content.push(_sectionHeading('Design Pages'));
                pages.forEach(function (pg, pi) {
                    /* Page sub-card */
                    var pageBody = [];
                    pageBody.push({ text: (mi + 1) + '.' + (pi + 1) + '  ' + _txt(pg.title), fontSize: 10, bold: true, color: T.ink, margin: [0, 0, 0, 4] });
                    var pgDesc = _htmlToContent(pg.description);
                    if (pgDesc) pageBody.push(pgDesc);

                    var pgGrid = _imagesGrid(pg.attachments, imgMap);
                    if (pgGrid) pgGrid.forEach(function (g) { pageBody.push(g); });

                    var specsTable = _specsTable(pg.page_specifications);
                    if (specsTable) pageBody.push(specsTable);

                    /* UX thumbnails */
                    (pg.page_specifications || []).forEach(function (sp) {
                        if (sp.ux_attachment && _isImage(sp.ux_attachment)) {
                            var uxImg = imgMap[sp.ux_attachment.stored_name];
                            if (uxImg) {
                                pageBody.push({ text: 'UX \u2014 ' + _txt(sp.field_name), bold: true, fontSize: 7.5, color: T.muted, margin: [0, 4, 0, 2] });
                                pageBody.push(_framedImage(uxImg.dataUrl, uxImg.w, uxImg.h, 80, 80));
                            }
                        }
                    });

                    content.push({
                        table: {
                            widths: ['*'],
                            body: [[{ stack: pageBody }]]
                        },
                        layout: {
                            hLineWidth: function () { return 0.5; },
                            vLineWidth: function () { return 0.5; },
                            hLineColor: function () { return T.border; },
                            vLineColor: function () { return T.border; },
                            paddingLeft: function () { return 10; },
                            paddingRight: function () { return 10; },
                            paddingTop: function () { return 8; },
                            paddingBottom: function () { return 8; }
                        },
                        margin: [0, 0, 0, 10]
                    });
                });
            }

            if (!scenarios.length && !pages.length) {
                content.push({ text: 'No scenarios or design pages defined.', fontSize: 10, color: T.muted, margin: [0, 10, 0, 0] });
            }
        });

        return {
            pageSize: 'A4',
            pageMargins: [40, 50, 40, 50],
            header: _pageHeader(bp),
            footer: _pageFooter(),
            content: content,
            defaultStyle: { fontSize: 9, lineHeight: 1.4, color: T.ink },
            styles: {
                metaLabel: { fontSize: 7, bold: true, color: T.muted, characterSpacing: 0.8 },
                metaValue: { fontSize: 9, color: T.ink },
                'html-a': { color: T.brand, decoration: 'underline' }
            }
        };
    }

    /* ---- Export entry ---- */

    function exportPdf() {
        if (typeof pdfMake === 'undefined') {
            if (typeof toastr !== 'undefined') toastr.error('PDF library not loaded. Please refresh and try again.');
            return;
        }

        var btn = document.getElementById('btnExportPdf');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...'; }

        var token = window.PageData ? window.PageData.token : null;
        if (!token) {
            if (typeof toastr !== 'undefined') toastr.error('Blueprint token not found.');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-download"></i> Download PDF'; }
            return;
        }

        $.ajax({
            url: SITE_URL + '/blueprints/' + token + '/refresh',
            type: 'GET', dataType: 'json',
            success: function (res) {
                if (!res || !res.status || !res.data || !res.data.blueprint) {
                    if (typeof toastr !== 'undefined') toastr.error((res && res.message) || 'Failed to load blueprint data.');
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-download"></i> Download PDF'; }
                    return;
                }
                var bp = res.data.blueprint;
                _collectImages(bp).then(function (imgMap) {
                    try {
                        var doc = _buildDocument(bp, imgMap);
                        var name = (bp.name || 'blueprint').replace(/[^a-zA-Z0-9 _-]/g, '').replace(/\s+/g, '_');
                        pdfMake.createPdf(doc).download(name + '_' + _today() + '.pdf');
                        if (typeof toastr !== 'undefined') toastr.success('PDF downloaded successfully!');
                    } catch (e) {
                        console.error('BlueprintExport error:', e);
                        if (typeof toastr !== 'undefined') toastr.error('Error generating PDF: ' + e.message);
                    } finally {
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-download"></i> Download PDF'; }
                    }
                }).catch(function (e) {
                    console.error('BlueprintExport image error:', e);
                    if (typeof toastr !== 'undefined') toastr.error('Error loading images: ' + e.message);
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-download"></i> Download PDF'; }
                });
            },
            error: function () {
                if (typeof toastr !== 'undefined') toastr.error('Failed to fetch blueprint data.');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-download"></i> Download PDF'; }
            }
        });
    }

    if (window.BlueprintDetail) {
        window.BlueprintDetail.exportPdf = exportPdf;
    } else {
        window.BlueprintDetail = { exportPdf: exportPdf };
    }
})();
