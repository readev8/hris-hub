# Monitoring Dashboard — Screen Specification

> **Purpose:** Dashboard untuk monitoring penggunaan aplikasi (log login) dan log akses aplikasi HRIS.

---

## 1. Overview

### Target Users
- IT Manager, Admin, Dept Head
- User dengan permission `monitoring.can_view`

### Key Metrics
| Metric | Description | Source |
|--------|-------------|--------|
| Total Login | Jumlah login hari ini | `session` table |
| User Aktif | User unik yang login hari ini | `session` table |
| Login Gagal | Login dengan status gagal | `session` table |
| Sesi Aktif | Sesi yang masih aktif | `session` table |
| Total Akses | Total akses aplikasi hari ini | Activity logs |
| Top Page | Halaman paling banyak diakses | Activity logs |

---

## 2. Screen Layout (ASCII Wireframe)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  HEADER                                                                     │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Monitoring Penggunaan Aplikasi                                     │   │
│  │  Halo [User], pantau penggunaan aplikasi dan akses di sini.         │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  [Date Start] to [Date End]  [Apply] [Export CSV] [Export XLSX]    │   │
│  │  [Auto Refresh] [Preset Dropdown] [Save Preset]                    │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────────────────┤
│  KPI CARDS (6 cards, 3x2 grid)                                              │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐│
│  │  128     │ │  45      │ │  3       │ │  12      │ │  1,234   │ │  /dash  ││
│  │  Total   │ │  User    │ │  Login   │ │  Sesi    │ │  Total   │ │  Top    ││
│  │  Login   │ │  Aktif   │ │  Gagal   │ │  Aktif   │ │  Akses   │ │  Page   ││
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘│
├─────────────────────────────────────────────────────────────────────────────┤
│  CHARTS (2x2 grid)                                                          │
│  ┌─────────────────────────────┐ ┌─────────────────────────────┐           │
│  │  Login Trend (14 hari)      │ │  Login by Hour              │           │
│  │  [Line Chart]               │ │  [Bar Chart]                │           │
│  └─────────────────────────────┘ └─────────────────────────────┘           │
│  ┌─────────────────────────────┐ ┌─────────────────────────────┐           │
│  │  Top Pages (10 teratas)     │ │  Login Status               │           │
│  │  [Horizontal Bar Chart]     │ │  [Doughnut Chart]           │           │
│  └─────────────────────────────┘ └─────────────────────────────┘           │
├─────────────────────────────────────────────────────────────────────────────┤
│  TABS: [Login Logs] [Access Logs]                                           │
├─────────────────────────────────────────────────────────────────────────────┤
│  DATA TABLE (based on active tab)                                            │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Search: [________]  Filter: [Status ▼] [Aplikasi ▼]              │   │
│  ├─────────────────────────────────────────────────────────────────────┤   │
│  │  Waktu    │ UserID │ IP Address    │ Device/Browser │ Status       │   │
│  │  ─────────┼────────┼───────────────┼────────────────┼──────────────│   │
│  │  09:45:12 │ user1  │ 192.168.1.10  │ Chrome/Windows │ ● Berhasil  │   │
│  │  09:42:33 │ user2  │ 10.0.0.5      │ Safari/macOS   │ ● Gagal     │   │
│  │  09:40:01 │ user3  │ 172.16.0.1    │ Firefox/Linux  │ ● Berhasil  │   │
│  │  ...      │ ...    │ ...           │ ...            │ ...          │   │
│  ├─────────────────────────────────────────────────────────────────────┤   │
│  │  ◄ 1 2 3 ... 10 ►                    Showing 1-20 of 189          │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────────────────┤
│  MODAL DETAIL (on row click)                                                │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  Detail Login                                               [×]    │   │
│  ├─────────────────────────────────────────────────────────────────────┤   │
│  │  Waktu          : 2026-09-11 09:45:12                              │   │
│  │  UserID         : user1                                             │   │
│  │  IP Address     : 192.168.1.10                                      │   │
│  │  Device         : Chrome 120 / Windows 11                           │   │
│  │  Status         : Berhasil                                          │   │
│  │  Aplikasi       : HR Self-Service                                   │   │
│  │  Duration       : 2h 30m                                            │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Section Specifications

### Section 1: Header + Filters

**Purpose:** Judul halaman, greeting, dan filter controls

**Components:**
- Page title: "Monitoring Penggunaan Aplikasi"
- Subtitle: Greeting dengan nama user
- Date range picker: `start_date`, `end_date`
- Apply button: Trigger filter apply
- Export buttons: CSV, XLSX
- Auto-refresh toggle: 60s interval
- Preset dropdown: Save/load filter presets

**Interactions:**
- Apply: Reload all data with new date range
- Export: Download file via `monitoring/export` endpoint
- Auto-refresh: Toggle 60s interval for live data
- Preset: Save current filters to localStorage

**Data Source:**
- Initial: Controller passes `$start_date`, `$end_date` from query params
- Apply: AJAX call to `monitoring/ajax-stats`, `monitoring/ajax-list`, etc.

---

### Section 2: KPI Summary Cards

**Purpose:** Ringkasan metrik utama dalam card format

**Components (6 cards):**

| Card | Value | Label | Icon | Color |
|------|-------|-------|------|-------|
| Total Login | `stats.login.total` | Total Login | `fa-sign-in-alt` | `var(--sap-brand)` |
| User Aktif | `stats.login.unique_users` | User Aktif | `fa-users` | `var(--sap-info)` |
| Login Gagal | `stats.login.failed` | Login Gagal | `fa-exclamation-triangle` | `var(--sap-error)` |
| Sesi Aktif | `stats.sessions.active` | Sesi Aktif | `fa-circle` | `var(--sap-success)` |
| Total Akses | `stats.access.total` | Total Akses | `fa-desktop` | `var(--sap-accent)` |
| Top Page | `stats.access.top_page` | Top Page | `fa-chart-bar` | `var(--sap-warning)` |

**Layout:**
```css
.mon-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
}
```

**Interactions:**
- Click card: Scroll to relevant table section
- Hover: Subtle lift effect (`transform: translateY(-2px)`)

**Data Source:**
- Endpoint: `monitoring/stats` with `start_date`, `end_date` params
- Response: `stats.login`, `stats.sessions`, `stats.access` objects

---

### Section 3: Charts Grid (2x2)

**Purpose:** Visualisasi data login dan akses

**Chart 1: Login Trend (14 hari)**
- Type: Line chart
- X-axis: Date (last 14 days)
- Y-axis: Count
- Series: Total login, Login gagal
- Colors: `var(--sap-chart-1)` (teal), `var(--sap-error)` (red)

**Chart 2: Login by Hour**
- Type: Bar chart
- X-axis: Hour (0-23)
- Y-axis: Count
- Color: `var(--sap-chart-2)` (orange)

**Chart 3: Top Pages (10 teratas)**
- Type: Horizontal bar chart
- Y-axis: Page name
- X-axis: Access count
- Color: `var(--sap-chart-3)` (green)

**Chart 4: Login Status**
- Type: Doughnut chart
- Segments: Berhasil, Gagal
- Colors: `var(--sap-success)`, `var(--sap-error)`

**Data Source:**
- Endpoint: `monitoring/trend` with `start_date`, `end_date` params
- Response: `items` array with daily counts

---

### Section 4: Tabs (Login Logs / Access Logs)

**Purpose:** Switch between two data views

**Components:**
- Tab bar with two tabs
- Active tab: `background: var(--sap-brand)`, `color: white`
- Inactive tab: `background: var(--sap-surface)`, `border: 1px solid var(--sap-border)`

**Interactions:**
- Click tab: Switch table content without page reload
- URL hash: `#login-logs` or `#access-logs`

---

### Section 5: Login Logs Table

**Purpose:** Detail log setiap login attempt

**Columns:**

| Column | Field | Width | Sortable | Description |
|--------|-------|-------|----------|-------------|
| Waktu | `created_at` | 120px | Yes | Timestamp login |
| UserID | `userid` | 100px | Yes | Username |
| IP Address | `ip_address` | 140px | Yes | IP address |
| Device/Browser | `user_agent` | auto | No | Device info |
| Status | `status` | 100px | Yes | Berhasil/Gagal |
| Aplikasi | `apps` | 120px | Yes | Nama aplikasi |

**Features:**
- Search: Filter by UserID, IP, Device
- Sort: Click column header
- Pagination: 20 items per page
- Row click: Open detail modal

**Data Source:**
- Endpoint: `monitoring/list` with `table=session`, `start_date`, `end_date`, `take`, `skip`, `sort`, `dir`, `q`
- Response: `items` array, `pagination` object

---

### Section 6: Access Logs Table

**Purpose:** Detail log setiap akses halaman/aplikasi

**Columns:**

| Column | Field | Width | Sortable | Description |
|--------|-------|-------|----------|-------------|
| Waktu | `created_at` | 120px | Yes | Timestamp akses |
| UserID | `userid` | 100px | Yes | Username |
| Halaman | `page` | auto | Yes | Nama halaman |
| Endpoint | `endpoint` | 200px | No | URL endpoint |
| Method | `method` | 80px | Yes | GET/POST/PUT/DELETE |
| Durasi | `duration` | 100px | Yes | Response time |

**Features:**
- Search: Filter by UserID, Halaman, Endpoint
- Sort: Click column header
- Pagination: 20 items per page
- Row click: Open detail modal

**Data Source:**
- Endpoint: `monitoring/list` with `table=access_log`, `start_date`, `end_date`, `take`, `skip`, `sort`, `dir`, `q`
- Response: `items` array, `pagination` object

---

### Section 7: Detail Modal

**Purpose:** Tampilan detail satu baris data

**Components:**
- Modal header: "Detail Login" / "Detail Akses"
- Close button: `[×]`
- Key-value table: Field name on left, value on right
- Related links: If applicable

**Interactions:**
- Open: Click table row
- Close: Click `[×]` or press `Escape`
- Backdrop: Click outside modal

**Data Source:**
- Endpoint: `monitoring/detail` with `table`, `id` params
- Response: `row` object with all fields

---

## 4. Data Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Browser   │────▶│  Web Controller │────▶│  API Server │
│  (View)     │◀────│  (Monitoring)  │◀────│  (Monitoring)│
└─────────────┘     └─────────────┘     └─────────────┘
       │                   │                   │
       │  1. Page Load     │                   │
       │──────────────────▶│                   │
       │                   │  2. GET /monitoring/stats
       │                   │──────────────────▶│
       │                   │◀──────────────────│
       │  3. Render HTML   │                   │
       │◀──────────────────│                   │
       │                   │                   │
       │  4. AJAX Request  │                   │
       │──────────────────▶│                   │
       │                   │  5. GET /monitoring/list
       │                   │──────────────────▶│
       │                   │◀──────────────────│
       │  6. Update Table  │                   │
       │◀──────────────────│                   │
       │                   │                   │
       │  7. Click Row     │                   │
       │──────────────────▶│                   │
       │                   │  8. GET /monitoring/detail
       │                   │──────────────────▶│
       │                   │◀──────────────────│
       │  9. Show Modal    │                   │
       │◀──────────────────│                   │
```

---

## 5. Responsive Behavior

### Desktop (> 1024px)
- Full layout as wireframe
- 6 KPI cards in 3x2 grid
- 4 charts in 2x2 grid
- Full table with all columns

### Tablet (768px - 1024px)
- 4 KPI cards in 2x2 grid
- 2 charts in 1x2 grid (stacked)
- Table with essential columns

### Mobile (< 768px)
- 2 KPI cards in 1x2 grid
- Charts stacked vertically
- Table horizontal scroll
- Filter bar wrapped

---

## 6. File Structure (Implementation)

```
web/app/
├── Controllers/
│   └── MonitoringDashboard.php          # New controller
├── Views/
│   └── monitoring-dashboard/
│       ├── main_page.php                # Main view
│       ├── _section_kpi.php             # KPI cards partial
│       ├── _section_charts.php          # Charts partial
│       ├── _section_login_logs.php      # Login logs table partial
│       ├── _section_access_logs.php     # Access logs table partial
│       └── _modal_detail.php            # Detail modal partial
└── Config/
    └── Routes.php                       # Add new routes

web/public/assets/
├── css/page/monitoring-dashboard/
│   └── main_page.css                    # Page-specific styles
└── js/page/monitoring-dashboard/
    ├── main_page.js                     # Main JS
    ├── charts.js                        # Chart configurations
    └── tables.js                        # Table logic
```

---

## 7. API Endpoints (Existing)

| Endpoint | Method | Params | Response |
|----------|--------|--------|----------|
| `/monitoring/stats` | GET | `start_date`, `end_date` | `stats` object |
| `/monitoring/list` | GET | `table`, `start_date`, `end_date`, `take`, `skip`, `sort`, `dir`, `q` | `items`, `pagination` |
| `/monitoring/trend` | GET | `start_date`, `end_date` | `items` array |
| `/monitoring/activity` | GET | `limit`, `domain`, `start_date`, `end_date` | `items`, `skipped` |
| `/monitoring/detail` | GET | `table`, `id` | `row` object |
| `/monitoring/export` | GET | `table`, `format`, `start_date`, `end_date` | CSV/XLSX file |

---

## 8. Permission

- Required: `monitoring.can_view = true`
- Check: `has_permission('monitoring', 'can_view')`
- Guard: `session()->has('user')`

---

## 9. UX Alternatives

### Alternative A: Tabbed Layout (Default)
- Single page with tabs for Login Logs / Access Logs
- Pros: Clean, less scrolling
- Cons: Only one table visible at a time

### Alternative B: Split Layout
- Two tables side by side (50/50 width)
- Pros: Both visible at once
- Cons: Cramped on smaller screens

### Alternative C: Accordion Layout
- Collapsible sections for each table
- Pros: User controls what's visible
- Cons: Extra click to view

**Recommendation:** Alternative A (Tabbed) — matches existing monitoring patterns and provides clean UX.
