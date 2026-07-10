# Project Management System

Two-app CI4 architecture: **API** (`:8080`) + **Web** (`:8081`).

## Requirements

- PHP 8.2+ (ext: intl, mbstring, mysqli, curl, json, openssl)
- MySQL 8+ (or MariaDB)
- Composer 2+

## Quick Start

### 1. Clone & Install

```bash
cd api && composer install
cd ../web && composer install
cd ..
```

### 2. Database Setup

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS project_management"

# Run migrations (creates all tables)
cd api && php spark migrate && cd ..

# Run seeder (creates test users + API key)
cd api && php spark db:seed ProjectManagementSeeder && cd ..
```

### 3. Configure Environment

Both `api/.env` and `web/.env` are pre-configured for localhost development:

| File | Key | Value |
|------|-----|-------|
| `api/.env` | `app.baseURL` | `http://localhost:8080/` |
| `api/.env` | `database.default.database` | `project_management` |
| `api/.env` | `database.default.username` | `root` |
| `api/.env` | `database.default.password` | *(empty)* |
| `web/.env` | `app.baseURL` | `http://localhost:8081/` |
| `web/.env` | `api.base_url` | `http://localhost:8080/` |
| `web/.env` | `api.service_key` | `0a99ba4084fbfd7c59188477d177f8daaf45b742a8dfadf47aecb114aace8341` |

### 4. Start Servers

```bash
# Terminal 1 – API Server
cd api && php spark serve --port=8080

# Terminal 2 – Web Server
cd web && php spark serve --port=8081
```

Open **http://localhost:8081** in your browser.

## Test Accounts

| Role | Email | Password |
|------|-------|----------|
| 🟢 Developer (1) | `developer@pm.test` | `password123` |
| 🔵 Requester (2) | `requester@pm.test` | `password123` |
| 🟡 Dept Head (3) | `depthead@pm.test` | `password123` |
| 🟠 IT Manager (4) | `itmanager@pm.test` | `password123` |
| 🔴 Admin (5) | `admin@pm.test` | `password123` |

All users share password `password123`.

## Feature Testing by Role

### Dashboard (`/`)
- [ ] Metric cards display ticket/improvement counts
- [ ] Chart.js renders ticket breakdown by status
- [ ] Recent activity table shows logs with avatars + badges
- [ ] Date range filter works
- All roles have access

### Tickets (`/tickets`)
- [ ] List page shows DataTable with search, pagination, sorting
- [ ] Status badges render with correct colors (Open=yellow, Approved=green, InProgress=blue, Resolved=purple, Closed=gray, Rejected=red)
- [ ] Priority dots render (Low=green, Medium=blue, High=orange, Critical=red)
- [ ] Avatar initials show in Creator column

| Action | Dev | Req | Dept Head | IT Mgr | Admin |
|--------|-----|-----|-----------|--------|-------|
| View list | ✓ | ✓ | ✓ | ✓ | ✓ |
| View detail | ✓ | ✓ | ✓ | ✓ | ✓ |
| Create ticket | ✓ | ✓ | ✓ | ✓ | ✓ |
| Upload images | ✓ | ✓ | ✓ | ✓ | ✓ |
| Approve ticket | — | — | ✓ | ✓ | ✓ |
| Reject ticket | — | — | ✓ | ✓ | ✓ |
| Take ticket | ✓ | — | — | — | — |
| Resolve ticket | ✓ | — | — | — | — |
| Close ticket | — | — | — | ✓ | ✓ |
| Reopen ticket | — | — | — | ✓ | ✓ |

### Ticket Detail (`/tickets/{id}`)
- [ ] Header shows monospace ID, status badge, priority dot
- [ ] Avatar + name for creator
- [ ] Gallery grid for images (if attached)
- [ ] Comment timeline with avatars + timestamps
- [ ] Sidebar shows Details + Actions cards
- [ ] SweetAlert2 confirmations for Approve/Reject/Resolve/Close/Reopen

### Improvements (`/improvements`)
- [ ] List page with DataTable, same badge/dot conventions
- [ ] Detail page shows approval stepper (horizontal nodes: Draft → IT Manager → Dept Head → Approved)
- [ ] Timeline for approval history with dot connectors
- [ ] Comments section with avatars

| Action | Dev | Req | Dept Head | IT Mgr | Admin |
|--------|-----|-----|-----------|--------|-------|
| View list | ✓ | ✓ | ✓ | ✓ | ✓ |
| View detail | ✓ | ✓ | ✓ | ✓ | ✓ |
| Create | ✓ | ✓ | ✓ | ✓ | ✓ |
| Approve (IT) | — | — | — | ✓ | ✓ |
| Approve (Dept) | — | — | ✓ | — | — |
| Reject | — | — | ✓ | ✓ | ✓ |
| Resubmit | ✓ | ✓ | — | — | — |

### Approval Center (`/approvals`)
- [ ] Tab filter: Pending Tickets / Pending Improvements
- [ ] DataTable loads per-tab with AJAX
- [ ] Status badges + avatar initials
- [ ] Eye icon links to detail page
- Only Dept Head (3), IT Manager (4), Admin (5) have access

### Users (`/users`)
- [ ] DataTable with avatar initials, email, role badges, active/inactive status
- [ ] Admin only — other roles see empty state or redirect
- Only Admin (5) has access

### Sidebar
- [ ] Icons render correctly for each menu item
- [ ] Active page is highlighted (blue left border + tint)
- [ ] "Management" section only shows for roles 3-5 (Approvals) and role 5 (Users)
- [ ] On viewports <768px, sidebar collapses to off-canvas with overlay
- [ ] Hamburger button toggles sidebar visibility

### UI Components
- [ ] All pages use **Inter** font
- [ ] **Bootstrap Icons** display on all buttons and sidebar items
- [ ] **Toastr** notifications appear on top-right on success/error
- [ ] **SweetAlert2** dialogs for destructive actions
- [ ] **Breadcrumbs** on create/detail pages
- [ ] Empty states show when no data (e.g., no tickets, no activity)
- [ ] Loading spinners during AJAX requests

## Architecture

- **API** (`/api`) – CodeIgniter 4 REST backend on port 8080
  - Controllers organized by domain: `Tickets/Action/`, `Tickets/Data/`, `Tickets/Report/`, `Improvements/Action/`, `Improvements/Data/`, `Improvements/Report/`, `Auth/Action/`, `Users/Action/`, `Dashboard/Report/`, `AuditLog/Report/`
  - Migrations create 8 tables: `users`, `api_keys`, `projects`, `approval_requests`, `project_comments`, `tickets`, `ticket_comments`, `ticket_attachments`, `audit_logs`
  - All API endpoints require `X-API-Key` header (service key from `api_keys` table)

- **Web** (`/web`) – CodeIgniter 4 frontend on port 8081
  - Consumes API via `api.base_url` config
  - Views use Bootstrap 5.3, DataTables, Chart.js, SweetAlert2, Toastr, Select2
  - Custom design system in `public/assets/css/global/style.css`

## URL Map

### API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/auth/login` | Login |
| GET | `/auth/me` | Current user info |
| GET | `/dashboard/stats` | Dashboard stats |
| GET | `/audit-log/recent` | Recent activity |
| GET | `/audit-log/entity` | Entity-specific logs |
| GET | `/tickets` | Ticket list |
| GET | `/tickets/{id}` | Ticket detail |
| POST | `/tickets/create` | Create ticket |
| POST | `/tickets/{id}/approve` | Approve ticket |
| POST | `/tickets/{id}/reject` | Reject ticket |
| POST | `/tickets/{id}/take` | Assign ticket to self |
| POST | `/tickets/{id}/resolve` | Mark resolved |
| POST | `/tickets/{id}/close` | Close ticket |
| POST | `/tickets/{id}/reopen` | Reopen ticket |
| POST | `/tickets/{id}/comments` | Add comment |
| POST | `/tickets/{id}/attachments` | Upload images |
| DELETE | `/attachments/{id}` | Delete attachment |
| GET | `/tickets/pending-approval` | Pending approval list |
| GET | `/tickets/my-tickets` | User's tickets |
| GET | `/improvements` | Improvement list |
| GET | `/improvements/{id}` | Improvement detail |
| POST | `/improvements/create` | Create improvement |
| POST | `/improvements/{id}/approve-it` | IT Manager approve |
| POST | `/improvements/{id}/approve-dept` | Dept Head approve |
| POST | `/improvements/{id}/reject` | Reject |
| POST | `/improvements/{id}/resubmit` | Resubmit after reject |
| POST | `/improvements/{id}/comments` | Add comment |
| GET | `/improvements/pending-approvals` | Pending approvals |
| GET | `/users` | User list |
| POST | `/users/create` | Create user |
| POST | `/users/{id}/toggle` | Toggle active status |

### Web Routes

| Path | Page |
|------|------|
| `/` or `/dashboard` | Dashboard |
| `/login` | Login |
| `/logout` | Logout |
| `/tickets` | Ticket list |
| `/tickets/create` | Create ticket |
| `/tickets/{id}` | Ticket detail |
| `/improvements` | Improvement list |
| `/improvements/create` | Create improvement |
| `/improvements/{id}` | Improvement detail |
| `/approvals` | Approval Center |
| `/users` | User management |
