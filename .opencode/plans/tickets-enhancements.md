# Tickets Enhancements — 3 Features

## Overview
1. **Change Request (type=3)** requires project/module/page selection
2. **New type: Data Request (type=4)**
3. **Comment file upload** supports non-image files (xlsx, pdf, docx)

---

## Feature 1: Change Request needs project/module/page

Currently only Bug (type=0) shows the "Bug Location" cascade and requires `page_id`. Change Request (type=3) and the new Data Request (type=4) should also require it.

### Files to modify:

#### 1A. `api/app/Controllers/Tickets/Action/Tickets.php`
- **Line 46-48**: Expand Bug-only page_id check to also include type 3 and 4
  ```php
  // Before:
  if ($type === Enums::TICKET_TYPE_BUG && !$pageId) {
  // After:
  $needsPage = in_array($type, [Enums::TICKET_TYPE_BUG, Enums::TICKET_TYPE_CHANGE_REQUEST, Enums::TICKET_TYPE_DATA_REQUEST], true);
  if ($needsPage && !$pageId) {
  ```
- **Line ~343-345**: Same change in update validation
  ```php
  // Before:
  if ($type === Enums::TICKET_TYPE_BUG && empty($input['page_id']) && empty($ticket['page_id'])) {
  // After:
  $needsPage = in_array($type, [Enums::TICKET_TYPE_BUG, Enums::TICKET_TYPE_CHANGE_REQUEST, Enums::TICKET_TYPE_DATA_REQUEST], true);
  if ($needsPage && empty($input['page_id']) && empty($ticket['page_id'])) {
  ```

#### 1B. `web/app/Controllers/Tickets.php`
- **Line 70-72**: Expand page_id required check
  ```php
  // Before:
  if (($post['type'] ?? '') === '0') {
      $rules['page_id'] = 'required';
  }
  // After:
  if (in_array($post['type'] ?? '', ['0', '3', '4'], true)) {
      $rules['page_id'] = 'required';
  }
  ```

#### 1C. `web/app/Views/tickets/create.php`
- **Line 53-56**: Change heading from "Bug Location" to "Affected Page" (generic)
- **Line 57**: Change description text
- **Lines 129-140**: Change JS to show cascade for types 0, 3, 4
  ```javascript
  // Before:
  if ($(this).val() === '0') {
  // After:
  var needsPage = ['0', '3', '4'].indexOf($(this).val()) !== -1;
  if (needsPage) {
  ```
- **Lines 274-282**: Change client-side validation to check types 0, 3, 4

#### 1D. `web/app/Views/tickets/edit.php`
- **Line 61**: Change initial visibility to show for types 0, 3, 4
  ```php
  style="display:<?= in_array($ticket['type'] ?? '', [0, 3, 4], true) ? 'block' : 'none' ?>"
  ```
- **Lines 184-192**: Change JS type check to include 3, 4
- **Lines 225-257**: Change `resolveBugLocation` trigger to include types 3, 4

---

## Feature 2: Add Data Request type (type=4)

### Files to modify:

#### 2A. `api/app/Config/Enums.php`
- **Line 18**: Add `const TICKET_TYPE_DATA_REQUEST = 4;` after CHANGE_REQUEST
- **Lines 77-86**: Add `self::TICKET_TYPE_DATA_REQUEST => 'Data Request'` to match

#### 2B. `web/app/Config/Enums.php`
- Same changes as API Enums (copy)

#### 2C. `web/app/Controllers/Tickets.php`
- **Line 67**: Change `in_list[0,1,2,3]` to `in_list[0,1,2,3,4]`
- **Line 168**: Same change in update validation

#### 2D. `web/app/Views/tickets/create.php`
- **Lines 27-32**: Add `<option value="4">Data Request</option>` to type dropdown

#### 2E. `web/app/Views/tickets/edit.php`
- **Lines 29-34**: Add `<option value="4">Data Request</option>` with selected logic

#### 2F. Kanban card CSS (in `detail.php` for master-projects)
- The `buildKanbanCard()` function already handles type dynamically via `t.type_name`, so no change needed there.

---

## Feature 3: Comment file upload supports non-image files

### Files to modify:

#### 3A. `web/app/Controllers/Tickets.php`
- **Lines 507-536**: Update `validateUploadedFiles()` to accept office documents
  ```php
  // Before:
  $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
  // After:
  $allowedMimes = [
      'image/jpeg', 'image/png', 'image/gif', 'image/webp',
      'application/pdf',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
      'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
      'application/vnd.ms-excel', // xls
      'application/msword', // doc
      'application/vnd.ms-powerpoint', // ppt
      'text/csv',
  ];
  ```
- **Line 527**: Update error message to list new allowed types

#### 3B. `web/app/Views/tickets/detail.php`
- **Line 115**: Update `accept` attribute on comment file input
  ```html
  accept="image/*,.pdf,.xlsx,.xls,.docx,.doc,.pptx,.ppt,.csv"
  ```
- **Lines 44-61**: Update ticket attachment display — non-image files show as download link with file icon instead of `<img>` thumbnail
  ```php
  <?php foreach ($ticket['attachments'] as $att): ?>
  <?php if (str_starts_with($att['mime_type'] ?? '', 'image/')): ?>
      <a href="..." class="glightbox ..." ...><img ...></a>
  <?php else: ?>
      <a href="<?= site_url('uploads/tickets/' . $att['stored_name']) ?>"
         class="sap-btn sap-btn-secondary sap-btn-sm" download>
          <i class="fas fa-file"></i> <?= esc($att['filename']) ?>
      </a>
  <?php endif; ?>
  <?php endforeach; ?>
  ```
- **Lines 88-101**: Same change for comment attachments — show file icon for non-images
- **Line 85**: Update `accept` attribute on ticket attachment file input (if it exists)

#### 3C. `web/app/Views/tickets/create.php`
- **Line 85**: Update `accept` attribute
  ```html
  accept="image/*,.pdf,.xlsx,.xls,.docx,.doc,.pptx,.ppt,.csv"
  ```
- **Line 79**: Update helper text to mention allowed file types
- **Line 87**: Update helper text below dropzone

#### 3D. `web/app/Views/tickets/edit.php`
- **Line 118**: Update `accept` attribute
  ```html
  accept="image/*,.pdf,.xlsx,.xls,.docx,.doc,.pptx,.ppt,.csv"
  ```

---

## Summary of all files to modify

| # | File | Changes |
|---|------|---------|
| 1 | `api/app/Config/Enums.php` | Add TICKET_TYPE_DATA_REQUEST, update ticketTypeName() |
| 2 | `web/app/Config/Enums.php` | Same as above |
| 3 | `api/app/Controllers/Tickets/Action/Tickets.php` | Expand page_id validation for types 3, 4 |
| 4 | `web/app/Controllers/Tickets.php` | Expand type in_list, expand page_id required, expand allowedMimes |
| 5 | `web/app/Views/tickets/create.php` | Add Data Request option, expand cascade to 0/3/4, update accept attr |
| 6 | `web/app/Views/tickets/edit.php` | Same as create |
| 7 | `web/app/Views/tickets/detail.php` | Non-image attachment display, update accept attr on comment input |

**Total: 7 files, ~15 specific edits**
