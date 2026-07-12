# Dashboard, Tickets & Improvements Enhancement

Timestamp: 2026-07-11
Status: planned

## Objective

Enhance Dashboard, Tickets, and Improvements pages based on codebase analysis. Focus on missing CRUD operations, UX improvements, field additions, and master data loading fixes.

## Plan

### Phase 1 — Error Handling & Validation (All Pages)
- [ ] **Step: Guard API null responses in all controllers**
  - Files: `web/app/Controllers/Dashboard.php`, `Improvements.php`, `Tickets.php`
  - Change: Before accessing `$result['data']['result']`, check `if (!$result || !($result['status'] ?? false))` and return user-friendly fallback. Currently Dashboard silently passes empty array; Tickets/Improvements already have partial guards but inconsistent.
  - Outcome: No more silent errors or PHP warnings when API is down.

- [ ] **Step: Add server-side validation for all POST endpoints**
  - Files: `web/app/Controllers/Improvements.php`, `Tickets.php`
  - Change: Use CodeIgniter `$this->validate()` with rule sets for title (min:5), name (required), description (required), priority (in_list), type (in_list), file uploads (max_size, mime_in). Return JSON with per-field error messages.
  - Outcome: Validation not solely reliant on client-side JS.

### Phase 2 — Dashboard Enhancement
- [ ] **Step: Parse date filters in controller**
  - Files: `web/app/Controllers/Dashboard.php`
  - Change: Read `start_date` and `end_date` from GET params (default: month-to-date). Pass to `dashboard/stats` API endpoint. Pass to `audit-log/recent` as well.
  - Outcome: Date filter works server-side, not just JS redirect.

- [ ] **Step: Add trend chart and average resolution time**
  - Files: `web/app/Views/dashboard/main_page.php`
  - Change: Add line chart (Chart.js) for tickets created per week. Display average resolution time metric card.
  - Outcome: Richer dashboard analytics.

- [ ] **Step: Add dashboard filter by project and type**
  - Files: `web/app/Views/dashboard/main_page.php`, `web/app/Controllers/Dashboard.php`
  - Change: Add dropdown filters for `project_id` and `type`. Pass to stats API.
  - Outcome: Filterable dashboard metrics.

- [ ] **Step: Add auto-refresh toggle**
  - Files: `web/app/Views/dashboard/main_page.php`
  - Change: Add toggle button with 30s/60s interval. Use `setInterval` to reload stats via AJAX without full page reload.
  - Outcome: Real-time monitoring capability.

### Phase 3 — Tickets CRUD & Workflow Enhancement
- [ ] **Step: Add update/edit ticket functionality**
  - Files: `web/app/Controllers/Tickets.php` (add `update` method), `web/app/Views/tickets/edit.php` (new file)
  - Change: GET renders edit form pre-populated with ticket data. POST updates title, description, priority, type via API. Route: `/tickets/{id}/edit`.
  - Outcome: Users can edit existing tickets.

- [ ] **Step: Add delete ticket functionality**
  - Files: `web/app/Controllers/Tickets.php` (add `delete` method)
  - Change: POST to `/tickets/{id}/delete`. Only for status=Open/Rejected. API call to soft-delete or hard-delete. Confirm dialog via SweetAlert.
  - Outcome: Clean up invalid/spam tickets.

- [ ] **Step: Add assign by admin (not just self-take)**
  - Files: `web/app/Controllers/Tickets.php` (add `assign` method), `web/app/Views/tickets/detail.php`
  - Change: Admin/Manager can assign ticket to any user via user dropdown. POST to `/tickets/{id}/assign` with `assignee_id`.
  - Outcome: Flexible assignment workflow.

- [ ] **Step: Add due date field**
  - Files: `web/app/Views/tickets/create.php`, `web/app/Views/tickets/detail.php`, `web/app/Controllers/Tickets.php`
  - Change: Add `due_date` date input to create form. Display in detail page with SLA breach warning (red text if overdue).
  - Outcome: Track ticket deadlines.

- [ ] **Step: Add time tracking (started_at / resolved_at)**
  - Files: `web/app/Views/tickets/detail.php`
  - Change: Display "Started" time when ticket moved to In Progress. Display "Resolution time" (X hours) when resolved. Data from API.
  - Outcome: Measure resolution efficiency.

- [ ] **Step: Relax file upload restrictions**
  - Files: `web/app/Controllers/Tickets.php`
  - Change: Allow PDF and DOCX in addition to JPG/PNG. Increase max files from 3 to 5. Increase max size from 2MB to 5MB. Make limits configurable.
  - Outcome: More flexible attachment support.

- [ ] **Step: Migrate DataTable to server-side processing**
  - Files: `web/app/Controllers/Tickets.php` (ajaxList), `web/app/Views/tickets/main_page.php`
  - Change: Set `serverSide: true` in DataTable config. ajaxList now reads `start`, `length`, `order`, `search[value]` from GET params and passes to API. API response must include `recordsFiltered` for proper pagination.
  - Outcome: Scalable for 10,000+ tickets.

### Phase 4 — Improvements CRUD & Workflow Enhancement
- [ ] **Step: Add edit/update improvement**
  - Files: `web/app/Controllers/Improvements.php` (add `update` method), `web/app/Views/improvements/edit.php` (new)
  - Change: Pre-populated edit form. Only creator can edit when status=Draft. POST to API with updated fields.
  - Outcome: Improvement proposals can be revised.

- [ ] **Step: Add delete improvement**
  - Files: `web/app/Controllers/Improvements.php` (add `delete` method)
  - Change: POST to `/improvements/{id}/delete`. Only creator when status=Draft. Confirm dialog.
  - Outcome: Remove unnecessary proposals.

- [ ] **Step: Add file attachment support**
  - Files: `web/app/Controllers/Improvements.php`, `web/app/Views/improvements/create.php`, `web/app/Views/improvements/detail.php`
  - Change: Add drag-and-drop file upload (reuse ticket's dropzone pattern). Max 3 files, 5MB each. Store via API similar to tickets. Display attachments in detail page.
  - Outcome: Support documents (PDF specs, screenshots) for improvement proposals.

- [ ] **Step: Add project/module/page cascade to improvements**
  - Files: `web/app/Views/improvements/create.php`
  - Change: Reuse the Project→Module→Page cascading dropdown from Tickets create form. Link improvement to a specific project scope.
  - Outcome: Improvements can be scoped to specific projects.

- [ ] **Step: Add assignee/PIC and target date**
  - Files: `web/app/Views/improvements/create.php`, `web/app/Views/improvements/detail.php`
  - Change: Add `assigned_to` (select user) and `target_date` (date picker) fields. Display in detail sidebar.
  - Outcome: Clear ownership and deadline for each improvement.

- [ ] **Step: Add category field**
  - Files: `web/app/Views/improvements/create.php`
  - Change: Add select dropdown: UI/UX, Performance, Security, New Feature, Integration, Other.
  - Outcome: Better categorization and filtering.

- [ ] **Step: Migrate DataTable to server-side processing**
  - Files: `web/app/Controllers/Improvements.php` (ajaxList), `web/app/Views/improvements/main_page.php`
  - Change: Same as tickets — set `serverSide: true`, pass DataTable params to API.
  - Outcome: Scalable improvement list.

### Phase 5 — Master Data Loading Fix (Ticket Creation Cascade)
- [ ] **Step: Fix cascading dropdown race condition**
  - Files: `web/app/Views/tickets/create.php`
  - Change: Abort previous AJAX request when new selection is made using `var activeRequest = $.ajax(...)` and `activeRequest.abort()` before firing new one. Prevents out-of-order responses.
  - Outcome: No stale/incorrect module/page lists when changing project quickly.

- [ ] **Step: Add error handling for cascade AJAX calls**
  - Files: `web/app/Views/tickets/create.php`
  - Change: Add `.fail()` handler on all cascade AJAX calls (`loadProjects`, project change, module change). Show toastr error message on failure. Re-enable dropdown with "Select..." placeholder.
  - Outcome: Users see error message instead of stuck "Loading..." state when API fails.

- [ ] **Step: Add loading spinner for cascading dropdowns**
  - Files: `web/app/Views/tickets/create.php`
  - Change: Add small spinner icon next to dropdown label or inside dropdown during loading. Remove spinner on success/error.
  - Outcome: Visual feedback during data loading.

- [ ] **Step: Reset cascading dropdowns when ticket type changes FROM Bug**
  - Files: `web/app/Views/tickets/create.php`
  - Change: When type changes from Bug to Task/Issue, also visually reset Project, Module, Page selects to placeholder state (currently only hides section and clears pageIdValue).
  - Outcome: Clean state when switching ticket types.

- [ ] **Step: Add cache for master project data**
  - Files: `web/app/Views/tickets/create.php`
  - Change: Cache `loadProjects()` result in a JavaScript variable. Only re-fetch if `refetch=true` param or if cache is empty. Prevents unnecessary API calls when user toggles Bug type on/off.
  - Outcome: Reduced API load and faster cascade rendering.

- [ ] **Step: Verify getActive() endpoint works without auth guard**
  - Files: `web/app/Controllers/MasterProjects.php`
  - Change: `getActive()` method has no `guard()` call (good for public access). But verify it works correctly when API returns empty array. Currently passes `[]` as fallback.
  - Outcome: Non-admin users can still see project list when creating tickets.

- [ ] **Step: Validate page_id server-side on ticket creation**
  - Files: `web/app/Controllers/Tickets.php`
  - Change: Add validation in `create()` method: if type=Bug (0), verify `page_id` is present. Optionally call API to validate page_id exists before proceeding.
  - Outcome: No orphan page_id references.

### Phase 6 — Cross-Cutting Improvements
- [ ] **Step: Standardize API response handling**
  - Files: All controllers
  - Change: Create helper/trait to consistently handle API responses: extract data, handle errors, log failures.
  - Outcome: DRY error handling across all controllers.

- [ ] **Step: Add CSRF verification to all POST forms**
  - Files: All view files with forms
  - Change: Verify hidden CSRF input (`<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">`) exists in all forms. Verify JS POSTs include header.
  - Outcome: No CSRF vulnerabilities.

- [ ] **Step: Log API failures for debugging**
  - Files: All controllers
  - Change: When API returns null or status=false, log to `log_message('error', ...)` with endpoint and params (excluding passwords).
  - Outcome: Easier debugging of API issues.

## Technical Details

### Current Cascade Flow (to be fixed)
```
Type=Bug selected
  → loadProjects() GET /master-projects/active
    → populate #projectSelect
      → user selects project → GET /master-projects/{pid}/modules
        → populate #moduleSelect
          → user selects module → GET /modules/{mid}/pages
            → populate #pageSelect
              → user selects page → #pageIdValue set
```

### Known Issues in Current Cascade
1. No request abort → race condition on fast clicks
2. No error handling → infinite "Loading..." state
3. No loading spinner → poor UX feedback
4. No cache → repeated API calls when toggling type
5. Selects not fully reset on type change (only hidden)
6. No server-side validation that selected page_id matches type=Bug
