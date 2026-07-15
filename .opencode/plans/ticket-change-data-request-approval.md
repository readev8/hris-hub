# Plan: Add "Change Data Request" Type + Optional Approval Workflow for Tickets

## Overview

1. **New ticket type**: "Change Data Request" (type=5) — requires page_id, like Bug/Change Request/Data Request
2. **Optional 2-stage approval**: Checkbox on create, reuses `approval_requests` table (add `ticket_id`), follows Improvements pattern (IT Manager → Dept Head)

---

## 1. Database Migration

**File**: `api/app/Database/Migrations/2026-07-15-000001_AddApprovalToTickets.php` (NEW)

```php
// Add ticket_id to approval_requests (nullable FK)
$this->forge->addColumn('approval_requests', [
    'ticket_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'project_id'],
]);
$this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'CASCADE', 'SET NULL');
$this->forge->addKey('ticket_id');

// Add needs_approval to tickets
$this->forge->addColumn('tickets', [
    'needs_approval' => ['type' => 'TINYINT', 'default' => 0, 'after' => 'type'],
]);
```

**Run**: `php spark migrate` on API

---

## 2. Enums (both API + Web)

**Files**: `api/app/Config/Enums.php`, `web/app/Config/Enums.php`

- Add `const TICKET_TYPE_CHANGE_DATA_REQUEST = 5;`
- Update `ticketTypeName()`: `5 => 'Change Data Request'`

---

## 3. API Changes

### 3a. Tickets Action Controller (`api/app/Controllers/Tickets/Action/Tickets.php`)

**`create_ticket()`**:
- Update `needsPage` to include `TICKET_TYPE_CHANGE_DATA_REQUEST` (type 5)
- Save `needs_approval` field from input (checkbox value 0/1)

**New methods** (modeled after Improvements `approve_it`/`approve_dept`/`reject`/`resubmit`):

```php
public function approve_it(string $encryptedId): ResponseInterface
{
    // Permission: tickets.can_approve
    // Stage 1: OPEN(0) → PENDING(1)
    // Insert into approval_requests (ticket_id, stage=1, status=APPROVED)
    // Audit log
}

public function approve_dept(string $encryptedId): ResponseInterface
{
    // Permission: tickets.can_approve
    // Stage 2: PENDING(1) → APPROVED(1) → then auto-transition to IN_PROGRESS(2)
    // Insert into approval_requests (ticket_id, stage=2, status=APPROVED)
    // Audit log
}

public function reject_approval(string $encryptedId): ResponseInterface
{
    // Permission: tickets.can_approve
    // From OPEN or PENDING → REJECTED(5)
    // Insert into approval_requests (ticket_id, stage based on current status, status=REJECTED, notes)
    // Audit log
}

public function resubmit(string $encryptedId): ResponseInterface
{
    // Permission: tickets.can_create
    // From REJECTED(5) → OPEN(0) (re-enters approval cycle)
    // Only creator can resubmit
    // Insert into approval_requests (ticket_id, stage=1, status=PENDING)
    // Audit log
}
```

**Transition logic**:
- Tickets with `needs_approval=1`:
  - OPEN(0) → PENDING(1) via `approve_it` (IT Manager)
  - PENDING(1) → APPROVED(1) via `approve_dept` (Dept Head) → then auto to IN_PROGRESS(2)
  - OPEN(0) or PENDING(1) → REJECTED(5) via `reject_approval`
  - REJECTED(5) → OPEN(0) via `resubmit` (creator only)
- Tickets with `needs_approval=0`:
  - Current workflow unchanged (OPEN → IN_PROGRESS via take, etc.)

### 3b. TicketsCheck_model (`api/app/Models/Tickets/check/TicketsCheck_model.php`)

- Add `TICKET_STATUS_PENDING = 1` (already exists as APPROVED, reuse for pending approval state)
- Update `canTransitionStatus()` to handle approval transitions:
  - OPEN → PENDING (via approve_it, can_approve role)
  - PENDING → APPROVED (via approve_dept, can_approve role)  
  - OPEN/PENDING → REJECTED (via reject_approval, can_approve role)
  - REJECTED → OPEN (via resubmit, creator only)

**Note**: TICKET_STATUS_APPROVED (1) already exists. For the approval workflow:
- Status 0 = OPEN (waiting for first approval)
- Status 1 = PENDING (IT approved, waiting for Dept approval) — reuse APPROVED constant
- After Dept approves → auto-transition to IN_PROGRESS (2)

### 3c. Ticket Detail API (`api/app/Controllers/Tickets/Data/TicketDetail.php`)

- Load `approval_requests` for the ticket (same as Improvements ProjectDetail)
- Add `approval_history` to the ticket response
- Add `needs_approval` to the response

### 3d. API Routes (`api/app/Config/Routes.php`)

Add new routes (inside auth group, BEFORE existing ticket routes):

```php
$routes->post('tickets/(:any)/approve-it',    'Tickets\Action\Tickets::approve_it/$1');
$routes->post('tickets/(:any)/approve-dept',  'Tickets\Action\Tickets::approve_dept/$1');
$routes->post('tickets/(:any)/reject-approval','Tickets\Action\Tickets::reject_approval/$1');
$routes->post('tickets/(:any)/resubmit',      'Tickets\Action\Tickets::resubmit/$1');
```

---

## 4. Web Controller Changes (`web/app/Controllers/Tickets.php`)

### 4a. Update validation

- `create()`: type `in_list[0,1,2,3,4,5]`
- `update()`: type `in_list[0,1,2,3,4,5]`
- Save `needs_approval` field to API

### 4b. New proxy methods

```php
public function approveIt(string $encryptedId)   // → POST tickets/{id}/approve-it
public function approveDept(string $encryptedId)  // → POST tickets/{id}/approve-dept
public function rejectApproval(string $encryptedId) // → POST tickets/{id}/reject-approval
public function resubmit(string $encryptedId)     // → POST tickets/{id}/resubmit
```

All use `guard('can_approve')` except `resubmit` which uses `guard('can_create')`.

### 4c. Web Routes (`web/app/Config/Routes.php`)

```php
$routes->post('/tickets/(:any)/approve-it',     'Tickets::approveIt/$1');
$routes->post('/tickets/(:any)/approve-dept',   'Tickets::approveDept/$1');
$routes->post('/tickets/(:any)/reject-approval', 'Tickets::rejectApproval/$1');
$routes->post('/tickets/(:any)/resubmit',       'Tickets::resubmit/$1');
```

---

## 5. View Changes

### 5a. Create View (`web/app/Views/tickets/create.php`)

- Add `<option value="5">Change Data Request</option>` to type dropdown
- Update type change handler: show Affected Page section for types `['0','3','4','5']`
- Update client validation: types `['0','3','4','5']` need page_id
- Add "Requires Approval" checkbox below the form fields:
  ```html
  <div class="form-check mt-3">
      <input type="checkbox" name="needs_approval" value="1" id="needsApproval" class="form-check-input">
      <label for="needsApproval" class="form-check-label">Requires Approval (2-stage: IT Manager → Dept Head)</label>
  </div>
  ```

### 5b. Edit View (`web/app/Views/tickets/edit.php`)

- Add `<option value="5">Change Data Request</option>`
- Update type change handler + initial section visibility for type 5
- Show approval status if `needs_approval=1`

### 5c. Detail View (`web/app/Views/tickets/detail.php`)

**Approval stepper** (same pattern as Improvements, shown only if `needs_approval=1`):
- Steps: Open → IT Manager → Dept Head → Approved
- Status-based step states (active/completed/rejected)

**Approval history card** (shown only if `needs_approval=1`):
- Timeline of approval_requests entries
- Same UI as Improvements approval history

**Action buttons** (conditional on `needs_approval` and status):
- Status OPEN + `needs_approval=1` + `can_approve`: "Approve (IT)" + "Reject"
- Status PENDING + `needs_approval=1` + `can_approve`: "Approve (Dept)" + "Reject"
- Status REJECTED + `needs_approval=1` + `can_create` + isCreator: "Resubmit"
- Status APPROVED (auto → IN_PROGRESS): Show normal ticket workflow buttons

**Existing buttons**: Keep current workflow for `needs_approval=0` tickets unchanged.

---

## 6. Files Summary

| File | Action | Changes |
|---|---|---|
| `api/app/Database/Migrations/2026-07-15-000001_AddApprovalToTickets.php` | CREATE | Migration: ticket_id on approval_requests, needs_approval on tickets |
| `api/app/Config/Enums.php` | EDIT | Add TICKET_TYPE_CHANGE_DATA_REQUEST = 5, update ticketTypeName() |
| `web/app/Config/Enums.php` | EDIT | Same as API Enums |
| `api/app/Controllers/Tickets/Action/Tickets.php` | EDIT | Update needsPage, save needs_approval, add approve_it/approve_dept/reject_approval/resubmit methods |
| `api/app/Models/Tickets/check/TicketsCheck_model.php` | EDIT | Add approval transition rules |
| `api/app/Controllers/Tickets/Data/TicketDetail.php` | EDIT | Load approval_history, include needs_approval |
| `api/app/Config/Routes.php` | EDIT | Add 4 new ticket approval routes |
| `web/app/Controllers/Tickets.php` | EDIT | Update type validation, add 4 proxy methods |
| `web/app/Config/Routes.php` | EDIT | Add 4 new web approval routes |
| `web/app/Views/tickets/create.php` | EDIT | Type 5 option, needs_approval checkbox, cascade for type 5 |
| `web/app/Views/tickets/edit.php` | EDIT | Type 5 option, cascade for type 5, approval status display |
| `web/app/Views/tickets/detail.php` | EDIT | Approval stepper, approval history, conditional approve/reject/resubmit buttons |
| `web/app/Config/App.php` | EDIT | Asset version bump |

---

## 7. Approval Workflow Diagram

```
needs_approval = 0 (current workflow, unchanged):
  OPEN → take → IN_PROGRESS → resolve → RESOLVED → close → CLOSED
  OPEN → reject → REJECTED → reopen → OPEN

needs_approval = 1 (new approval workflow):
  OPEN → approve_it → PENDING(1) → approve_dept → APPROVED → auto IN_PROGRESS
  OPEN/PENDING → reject_approval → REJECTED → resubmit → OPEN
  After approval complete → normal ticket workflow (take/resolve/close)
```

---

## 8. Key Decisions

- **TICKET_STATUS_APPROVED (1) reused as PENDING**: In the existing system, status 1 = APPROVED. For the approval workflow, this represents "IT approved, waiting for Dept". After Dept approves, it auto-transitions to IN_PROGRESS (2).
- **approval_requests.ticket_id is nullable**: Existing improvement approval records have NULL ticket_id; existing ticket records have NULL project_id.
- **Checkbox is one-time**: Once ticket is created with `needs_approval=1`, the approval workflow is mandatory. Cannot be toggled off after creation.
- **Existing approve/reject endpoints preserved**: `approve_ticket` and `reject_ticket` remain for backward compatibility. New endpoints are `approve_it`, `approve_dept`, `reject_approval`, `resubmit`.
- **Auto-transition after dept approval**: When Dept Head approves, ticket automatically moves to IN_PROGRESS (assignee_id = creator or stays unassigned).
