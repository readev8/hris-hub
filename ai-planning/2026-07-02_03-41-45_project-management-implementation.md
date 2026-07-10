# Project Management Implementation (Two-CI4 Apps + JS Global Reuse)

Timestamp: 2026-07-02 03:41:45
Status: in-progress

## Objective

Build a Project Management web application with two CodeIgniter 4 apps: `api/` (backend REST, sole DB access) and `web/` (frontend, renders views, proxies to api/ via service API key). Browser uses CI4 sessions + CSRF; web/ uses reusable global JS utilities from `.coding-standard/.js/global/`. 5 roles, separate tickets (bug tracker) + improvements (approval workflow), AES-256-GCM ID encryption, append-only audit logging.

## Plan

### Phase 0 — Scaffold & Config Both CI4 Apps
- [ ] Step: Create api/ and web/ CI4 app starters
  - Files: `api/composer.json`, `web/composer.json`, `api/.env`, `web/.env`
  - Change: `composer create-project codeigniter4/appstarter api`, then `web`. api/ deps: `firebase/php-jwt`, `ramsey/uuid`. Remove default welcome controllers/views. Set .env for each.
  - Outcome: Two runnable CI4 frameworks.

- [ ] Step: Configure .env files
  - Files: `api/.env`, `web/.env`
  - Change: api/ gets DB creds, encryption.key_hex (256-bit), api.service_key, CORS origin. web/ gets api.base_url, api.service_key, file-based sessions.
  - Outcome: Isolated configs.

- [ ] Step: Verify both apps respond
  - Files: `api/public/.htaccess`, `web/public/.htaccess`
  - Change: Start PHP built-in servers, test health endpoint on api/, test page on web/
  - Outcome: Both apps serve.

### Phase 1 — Database (api/)
- [ ] Step: Create migration for users table (5 roles)
  - Files: `api/app/Database/Migrations/..._CreateUsers.php`
  - Change: `id`, `full_name`, `email` (unique), `password`, `role` (1=Developer,2=Requester,3=DeptHead,4=ITManager,5=Admin), `is_active`, `created_at`, `updated_at`
  - Outcome: users table with role enum.

- [ ] Step: Create migration for projects (improvements) table
  - Files: `api/app/Database/Migrations/..._CreateProjects.php`
  - Change: `id`, `name`, `description`, `business_case`, `priority` (1-4), `status` (0=Draft,1=Pending,2=Approved,3=Rejected,4=OnHold,5=Completed), `approval_workflow` JSON, `dept_head_id`, `it_manager_id`, `estimated_start`, `estimated_end`, `created_by`, `created_at`, `updated_at`
  - Outcome: projects table.

- [ ] Step: Create migration for approval_requests table
  - Files: `api/app/Database/Migrations/..._CreateApprovalRequests.php`
  - Change: `id`, `project_id` FK, `requester_id` FK, `approver_id` FK, `stage_sequence`, `status` (0=Pending,1=Approved,2=Rejected,3=Skipped), `notes`, `reviewed_at`, `created_at`
  - Outcome: approval_requests table.

- [ ] Step: Create migration for project_comments table
  - Files: `api/app/Database/Migrations/..._CreateProjectComments.php`
  - Change: `id`, `project_id` FK, `user_id` FK, `content`, `created_at`
  - Outcome: project_comments table.

- [ ] Step: Create migration for tickets table (bug tracker)
  - Files: `api/app/Database/Migrations/..._CreateTickets.php`
  - Change: `id`, `title`, `description`, `type` (0=Bug,1=Issue,2=Task), `priority` (0=Low,1=Medium,2=High,3=Critical), `status` (0=Open,1=Approved,2=InProgress,3=Resolved,4=Closed,5=Rejected), `closed_at`, `assignee_id` FK, `resolution_note`, `rejection_note`, `creator_id` FK, `due_date`, `created_at`, `updated_at`
  - Outcome: tickets table with full lifecycle statuses.

- [ ] Step: Create migration for ticket_comments table
  - Files: `api/app/Database/Migrations/..._CreateTicketComments.php`
  - Change: `id`, `ticket_id` FK, `user_id` FK, `content`, `created_at`
  - Outcome: ticket_comments table.

- [ ] Step: Create migration for audit_logs table
  - Files: `api/app/Database/Migrations/..._CreateAuditLogs.php`
  - Change: `id`, `user_id` FK, `entity_type`, `entity_id`, `action`, `old_values` JSON, `new_values` JSON, `created_at`
  - Outcome: audit_logs table (append-only, no update/delete).

- [ ] Step: Create migration for api_keys table
  - Files: `api/app/Database/Migrations/..._CreateApiKeys.php`
  - Change: `id`, `api_key`, `name`, `is_active`, `created_at`
  - Outcome: api_keys table for service key validation.

- [ ] Step: Create seeders for sample data
  - Files: `api/app/Database/Seeds/*.php`
  - Change: 5 sample users (one per role), default admin, an API key, sample departments
  - Outcome: Seed data ready.

### Phase 2 — Core Infrastructure (api/)
- [ ] Step: Create IdEncryption library (AES-256-GCM)
  - Files: `api/app/Libraries/IdEncryption.php`
  - Change: encryptId (base64url of iv+tag+ct), decryptId (reverse), mass_encrypt(array) helper. Random IV per call.
  - Outcome: ID encryption ready.

- [ ] Step: Create Enums config
  - Files: `api/app/Config/Enums.php`
  - Change: Abstract class with constants: Role (DEVELOPER=1, REQUESTER=2, DEPT_HEAD=3, IT_MANAGER=4, ADMIN=5), TicketType (BUG=0, ISSUE=1, TASK=2), Priority (LOW=0, MEDIUM=1, HIGH=2, CRITICAL=3), TicketStatus (OPEN=0, APPROVED=1, IN_PROGRESS=2, RESOLVED=3, CLOSED=4, REJECTED=5), ProjectStatus (DRAFT=0, PENDING=1, APPROVED=2, REJECTED=3, ON_HOLD=4, COMPLETED=5), ApprovalStatus (PENDING=0, APPROVED=1, REJECTED=2, SKIPPED=3)
  - Outcome: No magic numbers.

- [ ] Step: Create BaseApi controller
  - Files: `api/app/Controllers/BaseApi.php`
  - Change: extends ResourceController. JSONResponse(message, data, code). cleanInput(mixed). get_parameter(field_map, post). __get lazy-load via $model_map. initController sets $this->req, $this->api (IdEncryption).
  - Outcome: All API controllers extend this.

- [ ] Step: Create AuditLogger library
  - Files: `api/app/Libraries/AuditLogger.php`
  - Change: log(user_id, entity_type, entity_id, action, old_values, new_values) — INSERT into audit_logs. Called within transactions.
  - Outcome: Append-only audit trail.

- [ ] Step: Create ApiKeyAuth filter
  - Files: `api/app/Filters/ApiKeyAuthFilter.php`
  - Change: Read X-API-Key header, X-User-Id header. Validate key against api_keys table. Set $request->user_id from X-User-Id. Return 401 if invalid.
  - Outcome: All API calls authenticated via service key.

- [ ] Step: Create Cors filter
  - Files: `api/app/Filters/CorsFilter.php`
  - Change: Set Access-Control-* headers. Handle OPTIONS preflight. Whitelist origin from env.
  - Outcome: CORS handled.

- [ ] Step: Create Throttle filter
  - Files: `api/app/Filters/ThrottleFilter.php`
  - Change: 60 req/min default, 10 req/min on auth/login. Return 429 if exceeded.
  - Outcome: Rate limiting in place.

- [ ] Step: Register routes for api/
  - Files: `api/app/Config/Routes.php`
  - Change: Group routes under `api/` prefix. Apply filters: cors, throttle, apikeyauth (except login). Resource routes for tickets, improvements, dashboard, users.
  - Outcome: All API routes defined.

### Phase 3 — Auth (api/ + web/)
- [ ] Step: Auth endpoints on api/
  - Files: `api/app/Controllers/Auth/Action/Auth.php`
  - Change: `login(email, password)` — verify hash, return user data with encrypted ID. `me()` — return current user from request user_id. Logout not needed (stateless).
  - Outcome: Login API ready.

- [ ] Step: Login page on web/
  - Files: `web/app/Controllers/Auth.php`, `web/app/Filters/SessionAuthFilter.php`, `web/app/Views/auth/login.php`
  - Change: Login form → POST to Auth::login → call $this->api->post_data('auth/login', ...) → on success store user in CI4 session → redirect to /dashboard. SessionAuthFilter checks session on protected routes.
  - Outcome: Users can log in via web/.

### Phase 4 — Base Models + Users (api/)
- [ ] Step: Create base model classes
  - Files: `api/app/Models/{BaseAction,BaseData,BaseReport,BaseCheck}.php`
  - Change: Each extends CI4 Model with lazy-loading __get. BaseCheck has MasterDataCheck-like pattern.
  - Outcome: Model hierarchy ready.

- [ ] Step: Create MasterDataCheck_model
  - Files: `api/app/Models/Shared/MasterDataCheck_model.php`
  - Change: checkUserId, checkUserActive, checkTicketId, checkProjectId — all use selectCount + Query Builder.
  - Outcome: Reusable data validation.

- [ ] Step: Users CRUD (Admin only)
  - Files: `api/app/Controllers/Users/Action/Users.php`, `api/app/Models/Users/{action,data,report,check}/*.php`
  - Change: list, create, update, activate/deactivate. All IDs encrypted.
  - Outcome: User management API.

### Phase 5 — Tickets (Bug Tracker) Module (api/)
- [ ] Step: Tickets Action controller
  - Files: `api/app/Controllers/Tickets/Action/Tickets.php`
  - Change: create_ticket (validate title min 5, description, priority, type), take_ticket (set assignee, status→InProgress), resolve_ticket (require resolution_note, status→Resolved), close_ticket (status→Closed), reopen_ticket (require rejection_note, status→Open), add_comment. All in transactions with audit log.
  - Outcome: Full ticket lifecycle API.

- [ ] Step: Tickets Data controller
  - Files: `api/app/Controllers/Tickets/Data/TicketDetail.php`
  - Change: get_detail (ticket + comments + audit logs), get_status_options, get_type_options
  - Outcome: Ticket detail retrieval.

- [ ] Step: Tickets Report controller
  - Files: `api/app/Controllers/Tickets/Report/TicketList.php`
  - Change: get_list (paginated, filterable by status/type/priority/assignee, sortable), get_my_tickets, get_pending_approval
  - Outcome: Ticket list API with filters.

- [ ] Step: Tickets Check model
  - Files: `api/app/Models/Tickets/check/TicketsCheck_model.php`
  - Change: canTransitionStatus(current, target, userRole, isCreator) — state machine enforcement. canTakeTicket. canCloseTicket.
  - Outcome: Role-based state transitions enforced.

### Phase 6 — Improvements (Projects + Approval) Module (api/)
- [ ] Step: Improvements Action controller
  - Files: `api/app/Controllers/Improvements/Action/Projects.php`
  - Change: create_improvement (name, description, business_case, priority, estimated_start/end → status=Pending IT Manager), approve_it (IT Manager → status=Pending Dept Head, create approval_request), approve_dept (Dept Head → status=Approved, record approval), reject (require notes → status=Rejected), resubmit, add_comment. Workflow reads approval_workflow JSON.
  - Outcome: Full improvement lifecycle with multi-stage approval.

- [ ] Step: Improvements Data controller
  - Files: `api/app/Controllers/Improvements/Data/ProjectDetail.php`
  - Change: get_detail (project + approval_history from approval_requests), get_workflow_stages
  - Outcome: Project detail with approval history.

- [ ] Step: Improvements Report controller
  - Files: `api/app/Controllers/Improvements/Report/ProjectList.php`
  - Change: get_list (paginated, filterable by status/priority/department), get_pending_approvals (for IT Manager and Dept Head)
  - Outcome: Improvement list API.

### Phase 7 — Dashboard + Audit (api/)
- [ ] Step: Dashboard Report controller
  - Files: `api/app/Controllers/Dashboard/Report/Stats.php`
  - Change: get_stats(date_start, date_end) — aggregate counts by status, type. approval_metrics — avg approval time, pending count. bug_metrics — resolved vs closed count.
  - Outcome: Dashboard data API.

- [ ] Step: Audit Log Report controller
  - Files: `api/app/Controllers/AuditLog/Report/LogList.php`
  - Change: get_recent_logs (latest 50, with user info), get_entity_logs(entity_type, entity_id)
  - Outcome: Activity feed API.

### Phase 8 — Frontend Core Infrastructure (web/)
- [ ] Step: Create ApiClient library
  - Files: `web/app/Libraries/ApiClient.php`
  - Change: get_data(endpoint, params) and post_data(endpoint, params). Wraps cURL with X-API-Key + X-User-Id headers. Returns parsed JSON. Timeout 30s.
  - Outcome: $this->api helper for all API calls.

- [ ] Step: Create BaseController for web/
  - Files: `web/app/Controllers/BaseController.php`
  - Change: initController creates $this->api (ApiClient). Sets $this->userId from session. Injects base_url/site_url into view data.
  - Outcome: All page controllers extend this.

- [ ] Step: Create CSRF infrastructure
  - Files: `web/app/Controllers/Request.php`, `web/app/Config/Security.php`
  - Change: request/get returns CSRF token map. Security config sets $CSRFHeaderName = 'X-CSRF-Token', enables regeneration.
  - Outcome: Browser AJAX CSRF works.

- [ ] Step: Create filter persistence routes
  - Files: `web/app/Controllers/Helper.php`
  - Change: helper/sf stores filter JSON in session. helper/gf retrieves it. Used by kendotable.js/h.js (minimal).
  - Outcome: Filter persistence available.

- [ ] Step: Create layout (template shell + partials)
  - Files: `web/app/Views/template/{index.php, partial/{css,navbar,sidebar,js}.php}`
  - Change: index.php — shell with head, navbar, sidebar, content, js includes. #i hidden input with csrf_token(). base_url/site_url JS vars. Sidebar renders menu based on user role from session.
  - Outcome: Layout with all required DOM elements.

- [ ] Step: Copy global JS utilities
  - Files: `web/public/assets/js/global/{gc,d,populate,sweetalert,toastr,custom,sanitize,secure-ajax,select2,c,e,f,h}.js`
  - Change: Copy from `.coding-standard/.js/global/` (skip kendotable.js — Kendo commercial license, skip fullcalendar-loader.js — not needed for MVP, skip highchart.js — Chart.js used instead, skip m.js — has bug).
  - Outcome: All validated global JS helpers available.

- [ ] Step: Create base CSS
  - Files: `web/public/assets/css/global/style.css`
  - Change: Bootstrap overrides per UI/UX guidelines: color system (Primary #0F4C81, Slate #64748B, Background #F8FAFC, Emerald #10B981, Amber #F59E0B, Rose #E11D48, Sky #0EA5E9), Inter font, spacing scale (4px base), card styles (8px radius, border #E2E8F0), badge colors per status, skeleton animations, table styles.
  - Outcome: UI consistent with spec.

- [ ] Step: Configure web/ routes
  - Files: `web/app/Config/Routes.php`
  - Change: Route groups for /dashboard, /tickets, /improvements, /approvals, /auth. Apply SessionAuth filter to protected groups.
  - Outcome: All frontend routes defined.

### Phase 9 — Login + Session Management (web/)
- [ ] Step: Login page
  - Files: `web/app/Views/auth/login.php`, `web/app/Controllers/Auth.php`
  - Change: Clean login form (email + password), error handling, loading state. On success → redirect to /dashboard.
  - Outcome: Working login.

- [ ] Step: Logout
  - Files: `web/app/Controllers/Auth.php`
  - Change: Destroy session, redirect to /login.
  - Outcome: Working logout.

- [ ] Step: SessionAuthFilter
  - Files: `web/app/Filters/SessionAuthFilter.php`
  - Change: Check session for 'user' key. If missing, redirect to /login with return URL.
  - Outcome: Protected routes redirect to login.

- [ ] Step: Role-based menu
  - Files: `web/app/Views/template/partial/sidebar.php`
  - Change: Show/hide menu items based on user role (e.g. Admin sees Users, IT Manager sees Approval Center).
  - Outcome: Role-scoped navigation.

### Phase 10 — Dashboard Page (web/)
- [ ] Step: Dashboard page controller + view
  - Files: `web/app/Controllers/Dashboard.php`, `web/app/Views/dashboard/main_page.php`
  - Change: Page controller calls $this->api->get_data('dashboard/stats', {date_start, date_end}). View renders metric cards (total tickets, open, in progress, resolved, closed), Chart.js donut chart for type distribution, date range picker, recent activity table. Loading/Error/Empty states per spec.
  - Outcome: Dashboard with real-time metrics.

### Phase 11 — Tickets Pages (web/)
- [ ] Step: Ticket List page
  - Files: `web/app/Controllers/Tickets.php`, `web/app/Views/tickets/main_page.php`
  - Change: DataTable with filters (status, type, priority, assignee), search, pagination. Status badges, priority dots. "Create Ticket" button. Loading/Error/Empty states.
  - Outcome: Ticket list view.

- [ ] Step: Create Ticket page
  - Files: `web/app/Controllers/Tickets.php` (create action), `web/app/Views/tickets/create.php`
  - Change: Form with title, description (textarea), priority (radio), type (dropdown: Bug/Issue automatic). Client-side validation (title min 5). Submit → $this->api->post_data('tickets/create', ...). Success → redirect to ticket detail. Error → show validation messages per field.
  - Outcome: Ticket creation form.

- [ ] Step: Ticket Detail page
  - Files: `web/app/Controllers/Tickets.php` (detail action), `web/app/Views/tickets/detail.php`
  - Change: Header (title, ID, status badge, priority, creator info). Body (description). Comments thread (timestamped, sorted asc). Role-based action panel: Developer sees "Take Ticket" / "Mark Resolved", Requester sees "Close" / "Reopen", Manager sees "Approve" / "Reject". Action modals for resolution_note / rejection_note.
  - Outcome: Full ticket detail with actions.

### Phase 12 — Approval Center (web/)
- [ ] Step: Approval Center page
  - Files: `web/app/Controllers/Approvals.php`, `web/app/Views/approvals/main_page.php`
  - Change: Lists tickets with status=Pending Approval (for bug validation) and improvements with Pending status (for IT Manager / Dept Head). Inline Approve/Reject quick actions. Confirm modal for reject with notes field.
  - Outcome: Centralized approval hub.

### Phase 13 — Improvements Pages (web/)
- [ ] Step: Improvement List page
  - Files: `web/app/Controllers/Improvements.php`, `web/app/Views/improvements/main_page.php`
  - Change: DataTable with filters (status, department), search, "Create Improvement" button. Status badges with workflow colors. Loading/Error/Empty states.
  - Outcome: Improvement list view.

- [ ] Step: Create Improvement page
  - Files: `web/app/Controllers/Improvements.php` (create action), `web/app/Views/improvements/create.php`
  - Change: Form with name, description, business_case, priority, department, estimated_budget (Rp with masking via m.js pattern, using formatNumberWithDots), target_date. Submit → $this->api->post_data('improvements/create', ...). Success → redirect to detail.
  - Outcome: Improvement creation form.

- [ ] Step: Improvement Detail page
  - Files: `web/app/Controllers/Improvements.php` (detail action), `web/app/Views/improvements/detail.php`
  - Change: Header (name, status badge, metadata). Approval stepper (Draft → Pending IT → Pending Dept → Approved/Rejected). Approval history timeline. Role-based action panel: IT Manager sees Approve/Reject for Pending IT, Dept Head for Pending Dept. Modal for approval notes.
  - Outcome: Full improvement detail with approval flow.

### Phase 14 — Hardening
- [ ] Step: Service API key rotation plan doc
  - Files: `ai-planning/api-key-rotation.md`
  - Change: Document procedure for rotating api.service_key.
  - Outcome: Operational readiness.

- [ ] Step: CSRF audit
  - Files: `web/app/Views/template/index.php`, all form views
  - Change: Verify every POST form includes CSRF protection. Verify d.js override is loaded.
  - Outcome: No CSRF gaps.

- [ ] Step: XSS audit
  - Files: All .js and .php view files
  - Change: Verify .text() used over .html() for user content. Verify esc() used in PHP views.
  - Outcome: XSS prevented.

- [ ] Step: PHPCS PSR-12 on api/
  - Files: `api/composer.json` (add phpcs as dev-dependency)
  - Change: Run phpcs --standard=PSR12 app/ on api/. Fix violations.
  - Outcome: api/ PSR-12 compliant.

- [ ] Step: End-to-end smoke tests
  - Files: Manual test plan
  - Change: Test both lifecycles: (1) Bug: create → approve → take → resolve → close → reopen/reclose. (2) Improvement: create → IT approve → Dept approve → completed. Verify audit logs generated.
  - Outcome: Both workflows verified.

## Technical Details

### Architecture Diagram
```
Browser ──session+CSRF──▶ web/ (CI4) ──X-API-Key+X-User-Id──▶ api/ (CI4) ──SQL──▶ MySQL
   jQuery AJAX              Page + AJAX proxy                   REST = sole DB access
```

### Data Flow (Create Ticket Example)
1. Browser submits form → jQuery $.post (d.js override injects CSRF header) → web/ AJAX endpoint
2. web/ controller validates → calls $this->api->post_data('tickets/create', $data)
3. ApiClient adds X-API-Key + X-User-Id headers → cURL to api/
4. api/ ApiKeyAuthFilter validates key → controller calls TicketService
5. TicketService validates (state machine, role) → Ticket_Act_model inserts in transStart/transComplete
6. AuditLogger writes audit_logs within same transaction
7. API returns JSON with encrypted ID → web/ returns JSON → browser redirects

### Ticket State Machine
```
Open ──(Requester approves)──▶ Approved ──(Developer takes)──▶ In Progress
  │                                                                │
  └──(Requester rejects)──▶ Rejected                     resolution_note
                                                                    │
                                                            Resolved ◀┘
                                                              │
                                  ┌───────────────────────────┴───────────┐
                          (Requester closes)                      (Requester reopens + note)
                                  ▼                                       ▼
                              Closed                                   Open
```

### Improvement State Machine
```
Draft ──submit──▶ Pending IT Manager ──(IT approves)──▶ Pending Dept Head
                       │                                       │
                  (IT rejects)                           (Dept approves)
                       ▼                                       ▼
                   Rejected ◀───────────────────────────── Approved → Completed
```

### CSRF Flow (d.js / secure-ajax.js compatibility)
1. Layout includes: `<input type="hidden" id="i" value="<?= csrf_token() ?>">`
2. d.js $.post override calls `regenerate()` → GET site_url/request/get → returns `{csrf_test_name: "hash123", token: {csrf_test_name: "hash123"}}`
3. Sets header `X-CSRF-TOKEN: token[$("#i").val()]` (= token["csrf_test_name"] = "hash123")
4. CI4 Security config: `$CSRFHeaderName = 'X-CSRF-Token'`

### ID Encryption
- All IDs exposed in API responses use encrypted tokens (base64url of AES-256-GCM iv+tag+ct)
- All IDs received in API requests are decrypted before DB query
- encryptId/decryptId in IdEncryption library, injected via $this->api in BaseApi

## Draft Code

### api/app/Controllers/BaseApi.php
Provided in original plan — extends ResourceController, JSONResponse, cleanInput, get_parameter, __get lazy-load.

### api/app/Libraries/IdEncryption.php
Provided in original plan — AES-256-GCM with random IV, base64url, decrypt returns ?int.

### web/app/Libraries/ApiClient.php
Provided in original plan — cURL wrapper with X-API-Key + X-User-Id, get_data/post_data.

### web/app/Controllers/Request.php (CSRF endpoint)
```php
<?php namespace App\Controllers;
class Request extends BaseController {
    public function get() {
        $tokenName = csrf_token();
        $hash = csrf_hash();
        return $this->response->setJSON([
            $tokenName => $hash,
            'token' => [$tokenName => $hash],
        ]);
    }
}
```

### api/app/Filters/ApiKeyAuthFilter.php
```php
<?php namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;
class ApiKeyAuthFilter implements FilterInterface {
    public function before(\CodeIgniter\HTTP\RequestInterface $request, $arguments = null) {
        $apiKey = $request->getHeaderLine('X-API-Key');
        if (!$apiKey || $apiKey !== env('api.service_key')) {
            return \Config\Services::response()
                ->setStatusCode(401)
                ->setJSON(['data'=>['statuscode'=>401,'message'=>'Unauthorized','result'=>null],'status'=>false]);
        }
        $userId = $request->getHeaderLine('X-User-Id');
        $request->user_id = $userId ? (int)$userId : null;
    }
    public function after(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, $arguments = null) {}
}
```

## Revisions

