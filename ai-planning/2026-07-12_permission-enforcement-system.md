# Permission Enforcement System

Timestamp: 2026-07-12
Status: completed (ALL phases completed)

## Objective

Implement comprehensive, defense-in-depth permission enforcement across the entire Project Management system. Replace all hardcoded `in_array($role, ...)` checks with a dynamic role-permission matrix that controls access at both the web controller layer and API layer.

## Architecture Overview

```
User Request
    │
    ▼
┌─────────────────────────────────────────────────┐
│  Web Layer (web/app/Controllers/)               │
│  guard('can_view' | 'can_create' | ...)         │
│  → Redirect or JSON 403 if unauthorized          │
└────────────────────┬────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────┐
│  API Layer (api/app/Controllers/)               │
│  checkPermission('module', 'action')             │
│  → JSON 403 if unauthorized                      │
│                                                  │
│  checkTicketOwnership($id)                       │
│  → JSON 403 if not creator/assignee/admin        │
└────────────────────┬────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────┐
│  Business Rules Layer                            │
│  Status transitions, workflow stage checks       │
│  Role-based approval gates (IT Manager, Dept)    │
└─────────────────────────────────────────────────┘
```

### Data Model

**Tables:**
- `roles` — 5 system roles (Developer, Requester, Dept Head, IT Manager, Admin)
- `access_modules` — 7 modules (dashboard, tickets, improvements, approvals, master_projects, users, roles)
- `role_permissions` — Junction table with 5 boolean flags per module: `can_view`, `can_create`, `can_update`, `can_delete`, `can_approve`
- `users.role_id` — FK to roles table

**Permission Matrix (default seed):**

| Role | dashboard | tickets | improvements | approvals | master_projects | users | roles |
|------|-----------|---------|--------------|-----------|-----------------|-------|-------|
| Developer | V | V+C+U | V+C+U | — | V | — | — |
| Requester | V | V+C | V+C | — | — | — | — |
| Dept Head | V | V+C+A | V+C+A | V+A | — | — | — |
| IT Manager | V | V+C+U+A | V+C+U+A | V+A | V | V | V |
| Admin | V+C+U+D+A | V+C+U+D+A | V+C+U+D+A | V+C+U+D+A | V+C+U+D+A | V+C+U+D+A | V+C+U+D+A |

*(V=View, C=Create, U=Update, D=Delete, A=Approve)*

---

## Completed Work

### Fase 1a — BaseApi Foundation (COMPLETED)

**File:** `api/app/Controllers/BaseApi.php`

Added 4 reusable methods:

```php
protected function getCurrentUserRole(): ?int
// Returns role_id from users table (falls back to legacy 'role' column)

protected function getCurrentUserRecord(): ?array
// Returns full user row

protected function checkPermission(string $module, string $action = 'can_view'): bool
// Delegates to PermissionCheck_model::hasPermission()

protected function checkTicketOwnership(int $ticketId): bool
// True if: admin OR creator OR assignee
```

**Dependencies:**
- `PermissionCheck_model` (`api/app/Models/Roles/PermissionCheck_model.php`) — already existed but was unused; now wired in
- `App\Config\Enums` — for ADMIN constant

### Fase 1b — API Permission Checks (COMPLETED)

Every write endpoint in the API now has a `checkPermission()` guard before business logic.

**Tickets API** (`api/app/Controllers/Tickets/Action/Tickets.php`):

| Method | Permission Check | Ownership Check |
|--------|-----------------|-----------------|
| `create_ticket()` | `tickets.can_create` | — |
| `update_ticket()` | — | `checkTicketOwnership($id)` |
| `approve_ticket()` | `tickets.can_approve` | — |
| `move_ticket()` | — | `checkTicketOwnership($id)` |
| `add_comment()` | — | `checkTicketOwnership($id)` |

**Tickets Attachments** (`api/app/Controllers/Tickets/Action/Attachments.php`):

| Method | Permission Check | Ownership Check |
|--------|-----------------|-----------------|
| `add()` | — | `checkTicketOwnership($ticketId)` |
| `delete()` | — | uploader OR ticket creator OR admin |

**Improvements API** (`api/app/Controllers/Improvements/Action/Projects.php`):

| Method | Permission Check | Ownership Check |
|--------|-----------------|-----------------|
| `create_improvement()` | `improvements.can_create` | — |
| `update_improvement()` | `improvements.can_update` | creator OR admin |
| `delete_improvement()` | `improvements.can_delete` | creator OR admin |
| `approve_it()` | `improvements.can_approve` | IT Manager OR admin |
| `approve_dept()` | `improvements.can_approve` | Dept Head OR admin |
| `reject()` | `improvements.can_approve` | IT Manager / Dept Head / admin |
| `add_comment()` | `improvements.can_update` | — |

**MasterProjects API** (`api/app/Controllers/MasterProjects/Action/Projects.php`):

| Method | Permission Check |
|--------|-----------------|
| `create_project()` | `master_projects.can_create` |
| `update_project()` | `master_projects.can_update` |
| `delete_project()` | `master_projects.can_delete` |

**Users API** (`api/app/Controllers/Users/Action/Users.php`):

| Method | Permission Check |
|--------|-----------------|
| `get_list()` | `users.can_view` |
| `create()` | `users.can_create` |
| `toggle_active()` | `users.can_update` |

**Roles API** (`api/app/Controllers/Roles/Action/Roles.php`):

| Method | Permission Check |
|--------|-----------------|
| `create_role()` | `roles.can_create` |
| `update_role()` | `roles.can_update` |
| `delete_role()` | `roles.can_delete` |
| `save_permissions()` | `roles.can_update` |
| `toggle_active()` | `roles.can_update` |

### Fase 2 — Web Controller Guards (COMPLETED)

All web controllers now have a `guard(string $action = 'can_view'): bool` method that delegates to the `has_permission()` helper. Action methods call `$this->guard('can_xxx')` and return a 403 JSON or redirect on failure.

**Pattern:**
```php
private function guard(string $action = 'can_view'): bool
{
    return has_permission('tickets', $action);
}

private function denyResponse()
{
    return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki izin']);
}

public function create()
{
    if (!$this->guard('can_create')) {
        return $this->denyResponse();
    }
    // ... business logic
}
```

**Controllers updated:**

| Controller | Methods Guarded | Notes |
|-----------|----------------|-------|
| `Tickets.php` | 17 methods | index, ajaxList, create, update, delete, detail, edit, assign, take, resolve, close, reopen, approve, reject, addComment, uploadAttachment, deleteAttachment, move |
| `Improvements.php` | 13 methods | index, ajaxList, create, detail, edit, update, delete, uploadAttachment, approveIt, approveDept, reject, resubmit, addComment |
| `Users.php` | 2 methods | index, ajaxList (NEW — had no guard before) |
| `Approvals.php` | 2 methods | index, ajaxList (NEW — had no guard before) |
| `Roles.php` | 8 methods | FIXED — was `can_view` only for all; now action-specific |
| `MasterProjects.php` | 15 methods | FIXED — was `can_view` only for all; now action-specific |

### Test Results (Fase 1-2)

**Test 1: Developer cannot approve ticket**
```
POST /api/public/tickets/{id}/approve
Headers: X-User-Id: 1 (Developer)
Expected: 403 (can_approve=0)
Result:   403 "Anda tidak memiliki izin untuk approve ticket" ✅
```

**Test 2: Admin can approve ticket**
```
POST /api/public/tickets/{id}/approve
Headers: X-User-Id: 5 (Admin)
Expected: 200 (can_approve=1)
Result:   200 "Ticket berhasil di-approve" ✅
```

**Test 3: Login returns permissions map**
```
POST /api/public/auth/login
Body: { email: "developer@pm.test", password: "..." }
Result: permissions map returned with all 7 modules ✅
```

---

## Completed Work (Fase 3-4, 6)

### Fase 3 — Workflow Logic & Data Integrity (COMPLETED)

- [x] **Sync approve/reject with TicketsCheck_model**
  - `approve_ticket()` and `reject_ticket()` now use `transition()` method, delegating status validation to `TicketsCheck_model::validateTransition()`. Single source of truth.
  - Removed inline `in_array($user['role'], ...)` checks — model handles role-based transition rules.
  - Ownership check: `checkTicketOwnership()` (creator/admin only for approve/reject).

- [x] **Fix improvement reject stage validation**
  - DRAFT status: only IT Manager or Admin can reject (not yet passed IT stage)
  - PENDING status: only Dept Head or Admin can reject (passed IT, waiting for Dept)
  - Eliminates scenario where Dept Head could reject before IT review.

- [x] **Add CLOSED → OPEN and REJECTED → OPEN rules in TicketsCheck_model**
  - CLOSED → OPEN: creator, requester, or admin (reopen closed ticket)
  - REJECTED → OPEN: creator or admin (resubmit rejected ticket)

### Fase 4 — Permission Refresh Mechanism (COMPLETED)

- [x] **API `auth/me` now returns permissions**
  - `PermissionCheck_model::getUserPermissions()` called in `me()` endpoint
  - Web can fetch fresh permissions without re-authentication

- [x] **Web `Auth::refreshPermissions()` endpoint**
  - Calls API `auth/me`, updates session permissions
  - Route: `POST /auth/refresh-permissions`

- [x] **Global AJAX 403 handler** (in `template/index.php`)
  - Detects 403 from any AJAX call
  - Auto-calls `refreshPermissions()` 
  - Shows toast + reloads page to update UI
  - Debounced via `window._permRefreshing` flag

- [x] **Cleanup: stopped storing legacy `role` in session**
  - Login now stores only `role_id` (not `role`)
  - `has_any_permission()` removed from helper (dead code)

### Fase 6 — UX Polish (COMPLETED)

- [x] **Fix sidebar permission checks**
  - Main Menu items (Dashboard, Tickets, Improvements) now check `can_view`
  - Previously shown to all users regardless of permissions

- [x] **Hide action buttons based on permissions**
  - Tickets main_page: "New Ticket" button wrapped in `can_create`
  - Tickets detail: all action buttons (Approve, Reject, Take, Resolve, Close, Reopen, Assign, Edit, Delete) now check appropriate permissions
  - Improvements main_page: "New Improvement" button wrapped in `can_create`
  - Improvements detail: Approve/Reject → `can_approve`, Resubmit → `can_create`, Edit → `can_update`, Delete → `can_delete`

- [x] **Rate limiting for public tracking endpoint**
  - New `RateLimitFilter` — 30 requests/minute per IP
  - Applied to `tickets/track/(:any)` route
  - Returns 429 with JSON when exceeded

- [x] **Cleanup dead code**
  - Removed `has_any_permission()` from helper
  - Stopped storing `role` (legacy int) in session

### Fase 5 — Database Cleanup (COMPLETED)

- [x] **Converted all `$user['role']` references in controllers to use `getCurrentUserRole()` or `checkPermission()`**
  - `Improvements/Action/Projects.php` — 4 references converted
  - `Improvements/Report/ProjectList.php` — 1 reference converted
  - `Tickets/Action/Tickets.php` — 1 reference converted

- [x] **Converted hardcoded `in_array($user['role'], ...)` to `checkPermission()`**
  - `MasterProjects/Action/Modules.php` — 3 methods converted
  - `MasterProjects/Action/Pages.php` — 3 methods converted

- [x] **Converted Users API to use `role_id`**
  - `get_list()` — SELECT and roleName() now use `role_id`
  - `create()` — accepts `role_id` input, inserts into `role_id` column

- [x] **Converted Auth to use `role_id`**
  - `login()` — roleName() uses `role_id`
  - `me()` — roleName() uses `role_id`

- [x] **Dropped legacy `users.role` column**
  - Created migration `2026-07-12-000006_DropLegacyRoleColumn.php`
  - Updated seeder to use `role_id`
  - Verified all data intact

---

## Key Design Decisions

1. **Defense in depth**: Both web and API layers check permissions independently. Even if web guard is bypassed (direct API call), API layer still enforces.

2. **Permission check via model, not session**: API layer always queries `PermissionCheck_model` fresh from DB. This ensures consistency even if session data is stale. Performance impact is minimal (single cached query).

3. **Ownership separate from permission**: `checkTicketOwnership()` is a separate check from `checkPermission()`. A user may have `can_update` permission but still can't edit a ticket they don't own (unless admin).

4. **Role-based approval gates retained**: For improvements workflow, `approve_it` still requires IT Manager role, `approve_dept` still requires Dept Head role. The `can_approve` permission is a prerequisite, role check is a second gate. This preserves the two-stage approval workflow.

5. **Graceful degradation**: Login endpoint tries to load permissions but falls back to empty map if tables don't exist. This allows the system to boot even before migrations run.

6. **Admin override**: Admin role (id=5) bypasses ownership checks via `checkTicketOwnership()`. Admin has all permissions by default in the seed data.

## Files Modified (Fase 1-2)

### API Layer
- `api/app/Controllers/BaseApi.php` — Added 4 methods + PermissionCheck_model dependency
- `api/app/Controllers/Tickets/Action/Tickets.php` — 5 permission/ownership checks
- `api/app/Controllers/Tickets/Action/Attachments.php` — 2 ownership checks
- `api/app/Controllers/Improvements/Action/Projects.php` — 7 permission checks
- `api/app/Controllers/MasterProjects/Action/Projects.php` — 3 permission checks
- `api/app/Controllers/Users/Action/Users.php` — 3 permission checks
- `api/app/Controllers/Roles/Action/Roles.php` — 5 permission checks

### Web Layer
- `web/app/Controllers/Tickets.php` — guard() + 17 method guards
- `web/app/Controllers/Improvements.php` — guard() + 13 method guards
- `web/app/Controllers/Users.php` — guard() + 2 method guards (NEW)
- `web/app/Controllers/Approvals.php` — guard() + 2 method guards (NEW)
- `web/app/Controllers/Roles.php` — guard() FIXED to action-specific
- `web/app/Controllers/MasterProjects.php` — guard() FIXED to action-specific + 4 helper methods guarded

## Files Modified (Fase 3-5, 6)

### API Layer
- `api/app/Models/Tickets/check/TicketsCheck_model.php` — Added CLOSED→OPEN, REJECTED→OPEN transitions
- `api/app/Controllers/Tickets/Action/Tickets.php` — Refactored approve/reject to use transition(); ownership check; getCurrentUserRole() for transition
- `api/app/Controllers/Improvements/Action/Projects.php` — Stage-gated reject validation; getCurrentUserRole() for all role checks
- `api/app/Controllers/Improvements/Report/ProjectList.php` — getCurrentUserRole() for pending approvals
- `api/app/Controllers/MasterProjects/Action/Modules.php` — Converted in_array to checkPermission()
- `api/app/Controllers/MasterProjects/Action/Pages.php` — Converted in_array to checkPermission()
- `api/app/Controllers/Users/Action/Users.php` — Converted to use role_id for get_list and create
- `api/app/Controllers/Auth/Action/Auth.php` — `me()` returns permissions; login/me use role_id for roleName()
- `api/app/Database/Migrations/2026-07-12-000006_DropLegacyRoleColumn.php` — NEW: drops legacy role column
- `api/app/Database/Seeds/ProjectManagementSeeder.php` — Updated to use role_id
- `api/app/Filters/RateLimitFilter.php` — NEW: 30 req/min rate limiting for tracking endpoint
- `api/app/Config/Filters.php` — Registered ratelimit filter alias
- `api/app/Config/Routes.php` — Applied ratelimit filter to tracking route

### Web Layer
- `web/app/Controllers/Auth.php` — Added `refreshPermissions()` method; removed legacy `role` from session
- `web/app/Controllers/Tickets.php` — Changed approve/reject guard from `can_approve` to `can_update`
- `web/app/Config/Routes.php` — Added `POST /auth/refresh-permissions` route
- `web/app/Config/App.php` — Asset version bumped to 1.0.5
- `web/app/Helpers/permission_helper.php` — Removed dead `has_any_permission()` function

### Views
- `web/app/Views/template/partial/sidebar.php` — All menu items now check `can_view`
- `web/app/Views/template/index.php` — Added global AJAX 403 handler with auto-refresh
- `web/app/Views/tickets/main_page.php` — "New Ticket" button wrapped in `can_create`
- `web/app/Views/tickets/detail.php` — All action buttons wrapped in permission checks
- `web/app/Views/improvements/main_page.php` — "New Improvement" button wrapped in `can_create`
- `web/app/Views/improvements/detail.php` — All action buttons wrapped in permission checks

## Summary

The Permission Enforcement System is now **fully implemented** across all layers:

### Fase 1-2 (Foundation)
- **25+ permission checks** in API controllers (checkPermission() + checkTicketOwnership())
- **60+ guard checks** in web controllers (action-specific permissions)
- **Defense-in-depth**: Web layer → API layer → Business rules

### Fase 3 (Workflow Logic)
- Tickets: approve/reject use `TicketsCheck_model::validateTransition()` as single source of truth
- Improvements: stage-gated reject (IT Mgr for DRAFT, Dept Head for PENDING)
- TicketsCheck_model: added CLOSED→OPEN and REJECTED→OPEN transitions

### Fase 4 (Permission Refresh)
- `auth/me` API returns permissions
- Web `refreshPermissions()` endpoint for session update
- Global AJAX 403 handler: auto-refresh + page reload

### Fase 5 (Database Cleanup)
- Converted all `$user['role']` references to `getCurrentUserRole()` or `checkPermission()`
- Converted Users API and Auth to use `role_id` column
- Dropped legacy `users.role` column via migration
- Updated seeder to use `role_id`

### Fase 6 (UX Polish)
- Sidebar: all menu items check `can_view`
- Action buttons: wrapped in permission checks
- Rate limiting: 30 req/min for tracking endpoint
- Cleanup: removed dead `has_any_permission()` function
