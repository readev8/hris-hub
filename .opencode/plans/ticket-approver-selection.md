# Plan: Add Per-User Approver Selection to Ticket Create

## Goal
When "Requires Approval" is checked on `/tickets/create`, reveal an approver dropdown (same pattern as `/improvements/create`). When a specific approver is selected, approval becomes **single-stage** — only that user (or Admin) can approve, skipping the IT Manager/Dept Head role checks entirely. When no approver is selected ("Role-based approval (default)"), the existing 2-stage role-based flow applies.

## Key Behavior Changes
- **With specific approver**: `OPEN(0) → approve → IN_PROGRESS(2)` — one step, per-user check only
- **Without specific approver (default)**: `OPEN(0) → approve_it → APPROVED(1) → approve_dept → IN_PROGRESS(2)` — current 2-stage role-based

---

## 1. Database Migration (NEW)

**File**: `api/app/Database/Migrations/2026-07-15-000002_AddApproverIdToTickets.php`

```php
$this->forge->addColumn('tickets', [
    'approver_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'creator_id'],
]);
$this->forge->addForeignKey('approver_id', 'users', 'id', 'SET NULL', 'CASCADE');
```

---

## 2. API — `Tickets/Action/Tickets.php`

### 2a. `create_ticket()` — Read + store `approver_id`
After the existing input parsing (line ~37):
```php
$approverId = !empty($input['approver_id']) ? $this->resolveId($input['approver_id']) : null;
```
Add to the INSERT array:
```php
'approver_id' => $approverId,
```

### 2b. `approvalAction()` — Conditional per-user vs role-based

Replace the role-check block (lines 426-434) with conditional logic:

```php
$role = $this->getCurrentUserRole();
if (!$role) return $this->JSONResponse('User tidak ditemukan', null, 404);

$hasSpecificApprover = !empty($ticket['approver_id']);

if ($hasSpecificApprover) {
    // Single-stage: only the designated approver (or admin) can approve
    if ((int) $ticket['approver_id'] !== $userId && $role !== Enums::ADMIN) {
        return $this->JSONResponse('Hanya approver yang ditunjuk yang dapat menyetujui ticket ini', null, 403);
    }
    // Expected status must be OPEN (single stage starts and ends here)
    if ((int) $ticket['status'] !== Enums::TICKET_STATUS_OPEN) {
        return $this->JSONResponse('Status ticket tidak sesuai untuk approval', null, 400);
    }
} else {
    // 2-stage role-based: current behavior unchanged
    if ($expectedStage === Enums::STAGE_PENDING_IT && $role !== Enums::IT_MANAGER && $role !== Enums::ADMIN) { ... }
    if ($expectedStage === Enums::STAGE_PENDING_DEPT && $role !== Enums::DEPT_HEAD && $role !== Enums::ADMIN) { ... }
    // Expected status check: OPEN for stage 1, APPROVED for stage 2
    ...
}
```

### 2c. `approve_it()` callback — Conditional target status

In the `approve_it()` callback, when `approver_id` is set, go directly to `IN_PROGRESS` instead of `APPROVED`:

```php
return $this->approvalAction($encryptedId, Enums::STAGE_PENDING_IT, function ($ticket, $userId) {
    $hasSpecificApprover = !empty($ticket['approver_id']);
    $newStatus = $hasSpecificApprover
        ? Enums::TICKET_STATUS_IN_PROGRESS    // single-stage: skip APPROVED
        : Enums::TICKET_STATUS_APPROVED;      // 2-stage: first stage done

    $this->db()->table('tickets')->update([
        'status'     => $newStatus,
        'updated_at' => date('Y-m-d H:i:s'),
    ], ['id' => $ticket['id']]);

    $this->db()->table('approval_requests')->insert([
        'ticket_id'      => $ticket['id'],
        'requester_id'   => $ticket['creator_id'],
        'approver_id'    => $userId,
        'stage_sequence' => Enums::STAGE_PENDING_IT,
        'status'         => Enums::APPROVAL_APPROVED,
        'reviewed_at'    => date('Y-m-d H:i:s'),
        'created_at'     => date('Y-m-d H:i:s'),
    ]);

    $this->audit->log($userId, 'ticket', $ticket['id'], 'approve_it',
        ['status' => Enums::TICKET_STATUS_OPEN],
        ['status' => $newStatus]
    );
    return $hasSpecificApprover ? 'Approval berhasil' : 'IT Manager approval berhasil';
});
```

### 2d. `reject_approval()` — Conditional per-user check

Add before the existing role check (after line 316):
```php
$hasSpecificApprover = !empty($ticket['approver_id']);
if ($hasSpecificApprover) {
    if ((int) $ticket['approver_id'] !== $userId && $role !== Enums::ADMIN) {
        return $this->JSONResponse('Hanya approver yang ditunjuk yang dapat menolak ticket ini', null, 403);
    }
} else {
    // existing role check (IT_MANAGER / DEPT_HEAD / ADMIN)
}
```

---

## 3. API — `TicketDetail.php`

Add approver info to the response (join users table, encrypt the ID):
```php
->select('tickets.*, ..., approver.full_name as approver_name')
->join('users as approver', 'approver.id = tickets.approver_id', 'left')
```
And:
```php
if (!empty($ticket['approver_id'])) {
    $ticket['approver_id'] = $this->api->encryptId($ticket['approver_id']);
}
```

---

## 4. Web Controller — `Tickets.php`

### 4a. `create()` GET branch — Load users for dropdown

Add before the view render (line 148):
```php
$usersResult = $this->api->get_data('users');
$usersList = $usersResult['data']['result'] ?? [];
return $this->view('tickets/create', [
    'title' => 'Create Ticket',
    'users' => $usersList,
]);
```

### 4b. `edit()` GET branch — Load users for dropdown (same pattern)

```php
$usersResult = $this->api->get_data('users');
$usersList = $usersResult['data']['result'] ?? [];
return $this->view('tickets/edit', [..., 'users' => $usersList]);
```

### 4c. `update()` — Forward `approver_id` to API

The `$post` already forwards everything, so `approver_id` will be included automatically if present in the form.

---

## 5. Create View — `tickets/create.php`

### 5a. Add approver dropdown (hidden by default)

After the `needs_approval` checkbox div (line ~101), add:
```html
<div id="approverSection" style="display:none;margin-top:12px">
    <label class="sap-label">Approver <span style="font-weight:400;color:var(--sap-text-muted)">(optional)</span></label>
    <select name="approver_id" class="sap-select" id="approverSelect">
        <option value="">Role-based approval (default)</option>
        <?php foreach ($users as $u): ?>
            <option value="<?= esc($u['id']) ?>"><?= esc($u['full_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <small class="text-muted" style="font-size:11px">If set, only this user can approve (single-stage). Otherwise, 2-stage IT Manager → Dept Head.</small>
</div>
```

### 5b. JS — Show/hide dropdown on checkbox toggle

```js
$('#needsApproval').on('change', function() {
    if ($(this).is(':checked')) {
        $('#approverSection').slideDown(200);
    } else {
        $('#approverSection').slideUp(200);
        $('#approverSelect').val('');
    }
});
```

---

## 6. Edit View — `tickets/edit.php`

Add the same approver dropdown (pre-selected with existing value), in the approval status section (after line ~99). Pre-select:
```php
<option value="">Role-based approval (default)</option>
<?php foreach ($users as $u): ?>
    <option value="<?= esc($u['id']) ?>" <?= ($ticket['approver_id'] ?? '') === $u['id'] ? 'selected' : '' ?>><?= esc($u['full_name']) ?></option>
<?php endforeach; ?>
```

---

## 7. Detail View — `tickets/detail.php`

### 7a. Show designated approver in sidebar metadata

In the `<dl>` metadata section, if `approver_id` is set:
```php
<?php if (!empty($ticket['approver_name'])): ?>
<dt class="col-5 text-secondary" style="font-weight:500;font-size:13px">Approver</dt>
<dd class="col-7"><?= esc($ticket['approver_name']) ?></dd>
<?php endif; ?>
```

### 7b. Adaptive stepper

When `approver_id` is set, show 3-step stepper (Open → Approver → In Progress) instead of 4-step:
```php
<?php if (!empty($ticket['approver_id'])): ?>
    // 3 steps: Open → Approver → In Progress
<?php else: ?>
    // 4 steps: Open → IT Manager → Dept Head → In Progress (current)
<?php endif; ?>
```

### 7c. Conditional approve buttons

Update the action buttons section:
```php
<?php if ($needsApproval === 1 && $status === 0): ?>
    <?php if (!empty($ticket['approver_id'])): ?>
        <button onclick="doApproval('approve-it')"><i class="fas fa-check"></i> Approve</button>
    <?php else: ?>
        <button onclick="doApproval('approve-it')"><i class="fas fa-check"></i> Approve (IT)</button>
    <?php endif; ?>
    <button onclick="promptRejectApproval()">Reject</button>
<?php endif; ?>
<?php if ($needsApproval === 1 && $status === 1 && empty($ticket['approver_id'])): ?>
    <button onclick="doApproval('approve-dept')"><i class="fas fa-check"></i> Approve (Dept)</button>
    <button onclick="promptRejectApproval()">Reject</button>
<?php endif; ?>
```

---

## 8. Files Summary

| File | Action | Changes |
|---|---|---|
| `api/app/Database/Migrations/2026-07-15-000002_AddApproverIdToTickets.php` | CREATE | Add `approver_id` column to `tickets` |
| `api/app/Controllers/Tickets/Action/Tickets.php` | EDIT | `create_ticket()`: read+store approver_id; `approvalAction()`: conditional per-user vs role; `approve_it()`: conditional target status; `reject_approval()`: conditional check |
| `api/app/Controllers/Tickets/Data/TicketDetail.php` | EDIT | Join approver name, encrypt approver_id |
| `web/app/Controllers/Tickets.php` | EDIT | `create()` + `edit()` GET: load users list |
| `web/app/Views/tickets/create.php` | EDIT | Add approver dropdown (hidden), JS toggle |
| `web/app/Views/tickets/edit.php` | EDIT | Add approver dropdown (pre-selected) |
| `web/app/Views/tickets/detail.php` | EDIT | Show approver name, adaptive stepper, conditional buttons |

---

## 9. Approval Flow Summary

```
needs_approval = 1, approver_id = NULL (default):
  OPEN → approve_it (IT Manager) → APPROVED(1) → approve_dept (Dept Head) → IN_PROGRESS(2)
  OPEN/APPROVED → reject_approval → REJECTED(5)

needs_approval = 1, approver_id = <specific user>:
  OPEN → approve_it (designated user only) → IN_PROGRESS(2)   [single stage]
  OPEN → reject_approval (designated user only) → REJECTED(5)
```

## 10. Edge Cases
- If approver is set but the designated user doesn't have `can_approve` permission → blocked at the `checkPermission('tickets', 'can_approve')` gate (line 415). This is correct behavior.
- If checkbox is unchecked after being checked → approver dropdown hides and value clears → `approver_id` sent as empty → stored as NULL → falls back to role-based.
- Admin can always approve regardless of whether approver_id is set.
