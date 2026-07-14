# Module Card Fix — Add "View Pages" Button

## Problem
Module cards in project detail have `onclick` on the entire card div, but clicking doesn't navigate. The user wants an explicit "View Pages" button instead of relying on the card being clickable.

## Design Decision
Only the button should be clickable (not the whole card). Follows SAP Fiori pattern of explicit actions.

## Changes

### File: `web/app/Views/master-projects/detail.php`

#### 1. CSS — Replace `.module-card` styles (lines ~306-333)

**Remove:**
```css
.module-card {
    background: var(--sap-surface);
    border: 1px solid var(--sap-border-light);
    border-radius: var(--sap-radius-lg);
    cursor: pointer;
    transition: all var(--sap-transition);
    position: relative;
    overflow: hidden;
}
.module-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--sap-brand);
    opacity: 0;
    transition: opacity var(--sap-transition);
}
.module-card:hover {
    border-color: var(--sap-brand);
    box-shadow: var(--sap-shadow-md);
    transform: translateY(-2px);
}
.module-card:hover::before {
    opacity: 1;
}
```

**Replace with:**
```css
.module-card {
    background: var(--sap-surface);
    border: 1px solid var(--sap-border-light);
    border-radius: var(--sap-radius-lg);
    transition: border-color var(--sap-transition), box-shadow var(--sap-transition);
}
.module-card:hover {
    border-color: var(--sap-border);
}

.module-card-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--sap-border-light);
    background: var(--sap-background);
    border-radius: 0 0 var(--sap-radius-lg) var(--sap-radius-lg);
}

.module-card-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: var(--sap-brand);
    text-decoration: none;
    transition: color 150ms ease, gap 150ms ease;
}
.module-card-link:hover {
    color: var(--sap-brand-hover);
    gap: 10px;
    text-decoration: none;
}
.module-card-link i {
    font-size: 11px;
    transition: transform 150ms ease;
}
.module-card-link:hover i {
    transform: translateX(2px);
}
```

#### 2. CSS — Add dark mode overrides (after existing dark-mode block, ~line 281)

```css
.dark-mode .module-card-footer {
    background: var(--sap-dark-surface);
    border-color: #475569;
}
.dark-mode .module-card-link:hover {
    color: #5EEAD4;
}
```

#### 3. PHP — Replace card HTML (lines ~426-453)

**Remove:**
```php
<div class="module-grid">
    <?php foreach ($project['modules'] as $mod): ?>
    <div class="module-card" onclick="window.location.href='<?= site_url('master-projects/' . $project['id'] . '/modules/' . $mod['id']) ?>'">
        <div class="module-card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="module-card-icon"><i class="fas fa-puzzle-piece"></i></span>
                    <h6 class="mb-0 fw-semibold"><?= esc($mod['name']) ?></h6>
                </div>
                <div class="sap-btn-group">
                    <button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="event.stopPropagation();editModule('<?= $mod['id'] ?>','<?= esc(addslashes($mod['name'])) ?>','<?= esc(addslashes($mod['description'] ?? '')) ?>')" title="Edit module"><i class="fas fa-pencil-alt"></i></button>
                    <button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="event.stopPropagation();deleteModule('<?= $mod['id'] ?>')" title="Delete module"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>
            <p class="text-secondary mb-3" style="font-size:12px;line-height:1.5"><?= esc($mod['description'] ?? 'No description') ?></p>
            <div class="d-flex align-items-center gap-3">
                <span class="module-card-stat"><i class="fas fa-file-alt"></i> <?= count($mod['pages']) ?> pages</span>
                <?php $totalBugs = 0; $openBugs = 0; foreach ($mod['pages'] as $pg) { $totalBugs += (int)($pg['bug_total'] ?? 0); $openBugs += (int)($pg['bug_open'] ?? 0); } ?>
                <?php if ($totalBugs > 0): ?>
                <span class="module-card-stat"><i class="fas fa-bug"></i> <?= $totalBugs ?> bugs</span>
                <?php endif; ?>
                <?php if ($openBugs > 0): ?>
                <span class="module-card-stat module-card-stat--open"><i class="fas fa-exclamation-circle"></i> <?= $openBugs ?> open</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
```

**Replace with:**
```php
<div class="module-grid">
    <?php foreach ($project['modules'] as $mod): ?>
    <div class="module-card">
        <div class="module-card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="module-card-icon"><i class="fas fa-puzzle-piece"></i></span>
                    <h6 class="mb-0 fw-semibold"><?= esc($mod['name']) ?></h6>
                </div>
                <div class="sap-btn-group">
                    <button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="editModule('<?= $mod['id'] ?>','<?= esc(addslashes($mod['name'])) ?>','<?= esc(addslashes($mod['description'] ?? '')) ?>')" title="Edit module"><i class="fas fa-pencil-alt"></i></button>
                    <button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="deleteModule('<?= $mod['id'] ?>')" title="Delete module"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>
            <p class="text-secondary mb-3" style="font-size:12px;line-height:1.5"><?= esc($mod['description'] ?? 'No description') ?></p>
            <div class="d-flex align-items-center gap-3">
                <span class="module-card-stat"><i class="fas fa-file-alt"></i> <?= count($mod['pages']) ?> pages</span>
                <?php $totalBugs = 0; $openBugs = 0; foreach ($mod['pages'] as $pg) { $totalBugs += (int)($pg['bug_total'] ?? 0); $openBugs += (int)($pg['bug_open'] ?? 0); } ?>
                <?php if ($totalBugs > 0): ?>
                <span class="module-card-stat"><i class="fas fa-bug"></i> <?= $totalBugs ?> bugs</span>
                <?php endif; ?>
                <?php if ($openBugs > 0): ?>
                <span class="module-card-stat module-card-stat--open"><i class="fas fa-exclamation-circle"></i> <?= $openBugs ?> open</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="module-card-footer">
            <a href="<?= site_url('master-projects/' . $project['id'] . '/modules/' . $mod['id']) ?>" class="module-card-link">
                <i class="fas fa-arrow-right"></i> View Pages
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
```

#### 4. JS — Update `renderModules()` function

**Remove the old renderModules function and replace with:**
```javascript
function renderModules(modules) {
    if (!modules || !modules.length) {
        $('#modulesList').html('<div class="sap-empty" style="padding:32px 20px"><i class="fas fa-puzzle-piece"></i><h4>No modules yet</h4><p>Add modules to organize your project pages.</p></div>');
        return;
    }
    var html = '<div class="module-grid">';
    for (var i = 0; i < modules.length; i++) {
        var m = modules[i];
        var pages = m.pages || [];
        var totalBugs = 0, openBugs = 0;
        for (var j = 0; j < pages.length; j++) {
            totalBugs += parseInt(pages[j].bug_total || 0);
            openBugs += parseInt(pages[j].bug_open || 0);
        }
        var moduleUrl = site_url + '/master-projects/' + projectId + '/modules/' + m.id;
        html += '<div class="module-card">';
        html += '<div class="module-card-body">';
        html += '<div class="d-flex align-items-start justify-content-between mb-2">';
        html += '<div class="d-flex align-items-center gap-2">';
        html += '<span class="module-card-icon"><i class="fas fa-puzzle-piece"></i></span>';
        html += '<h6 class="mb-0 fw-semibold">' + escHtml(m.name) + '</h6>';
        html += '</div>';
        html += '<div class="sap-btn-group">';
        html += '<button class="sap-btn sap-btn-ghost sap-btn-xs" onclick="editModule(\'' + m.id + '\',\'' + escAttr(m.name) + '\',\'' + escAttr(m.description || '') + '\')" title="Edit module"><i class="fas fa-pencil-alt"></i></button>';
        html += '<button class="sap-btn sap-btn-ghost sap-btn-xs sap-btn-danger-ghost" onclick="deleteModule(\'' + m.id + '\')" title="Delete module"><i class="fas fa-trash-alt"></i></button>';
        html += '</div>';
        html += '</div>';
        html += '<p class="text-secondary mb-3" style="font-size:12px;line-height:1.5">' + escHtml(m.description || 'No description') + '</p>';
        html += '<div class="d-flex align-items-center gap-3">';
        html += '<span class="module-card-stat"><i class="fas fa-file-alt"></i> ' + pages.length + ' pages</span>';
        if (totalBugs > 0) html += '<span class="module-card-stat"><i class="fas fa-bug"></i> ' + totalBugs + ' bugs</span>';
        if (openBugs > 0) html += '<span class="module-card-stat module-card-stat--open"><i class="fas fa-exclamation-circle"></i> ' + openBugs + ' open</span>';
        html += '</div>';
        html += '</div>';
        html += '<div class="module-card-footer">';
        html += '<a href="' + moduleUrl + '" class="module-card-link"><i class="fas fa-arrow-right"></i> View Pages</a>';
        html += '</div>';
        html += '</div>';
    }
    html += '</div>';
    $('#modulesList').html(html);
}
```

#### 5. JS — Update `deleteModule()` function

Change `AppEvent.dispatch('module:changed')` to `window.location.reload()`:
```javascript
function deleteModule(id) {
    Swal.fire({
        title: 'Delete this module?',
        text: 'All pages within will also be deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#AA0808',
        cancelButtonColor: '#758CA4',
        confirmButtonText: 'Delete',
    }).then(function(r) {
        if (r.isConfirmed) {
            $.post(site_url + '/modules/' + id + '/delete', function(res) {
                if (res.status) { toastr.success('Module deleted'); window.location.reload(); }
                else { toastr.error(res.data?.message || 'Failed'); }
            }).fail(function(xhr) {
                toastr.error('Gagal menghapus module (HTTP ' + xhr.status + ')');
            });
        }
    });
}
```

#### 6. JS — Update `$('#moduleForm').on('submit')` handler

Change `AppEvent.dispatch('module:changed')` to `window.location.reload()`:
```javascript
$('#moduleForm').on('submit', function(e) {
    e.preventDefault();
    var editId = $('#moduleEditId').val();
    var url = editId
        ? site_url + '/modules/' + editId + '/update'
        : site_url + '/master-projects/' + projectId + '/modules';
    var data = $(this).serialize();
    $.post(url, data, function(res) {
        if (res.status) {
            toastr.success(editId ? 'Module updated' : 'Module created');
            bootstrap.Modal.getInstance(document.getElementById('moduleModal')).hide();
            window.location.reload();
        } else {
            toastr.error(res.data?.message || 'Failed');
        }
    }).fail(function(xhr) {
        toastr.error('Gagal menyimpan module (HTTP ' + xhr.status + ')');
    });
});
```

## Summary of Changes
- Remove card-level `onclick` handler
- Remove `cursor: pointer` from card
- Remove hover transform effect (keep subtle border change)
- Add `.module-card-footer` with `<a>` link styled as text button
- Update `renderModules()` JS to generate new card structure
- Update module create/edit/delete to use `window.location.reload()`
- Add dark mode overrides for footer
