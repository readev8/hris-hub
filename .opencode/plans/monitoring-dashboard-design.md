# Monitoring Dashboard Design — screen.md & design.md

> **For agentic workers:** REQUIRED SUB-SKILL: Use subagent-driven-development (recommended) or executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create `screen.md` and `design.md` specification files for a new monitoring dashboard page that displays login logs and application access logs with UX alternative design.

**Architecture:** Two markdown specification files that define the screen layout and design system for the new monitoring dashboard. These files will serve as blueprints for implementation.

**Tech Stack:** Markdown specification files, CSS design tokens, CodeIgniter 4 views

**Spec:** This plan creates the specification files themselves — no code implementation yet.

---

## File Structure

| File | Purpose |
|------|---------|
| `screen.md` | Screen specification: layout, sections, wireframe, data flow |
| `design.md` | Design system: colors, typography, components, patterns |

---

## Task 1: Create `screen.md` — Screen Specification

**Files:**
- Create: `screen.md` (root of project or `docs/` directory)

**Content Structure:**
1. **Overview** — Purpose, target users, key metrics
2. **Screen Layout** — ASCII wireframe showing sections
3. **Section Specifications** — Each section with:
   - Purpose
   - Data source (existing API endpoints)
   - UI components
   - Interaction patterns
4. **Data Flow** — How data moves from API to screen
5. **Responsive Behavior** — Mobile/tablet/desktop breakpoints

**Sections to define:**

### Section 1: Header + Filters
- Date range picker (start_date, end_date)
- Filter buttons: Apply, Export CSV, Export XLSX
- Auto-refresh toggle
- Preset filter dropdown

### Section 2: KPI Summary Cards
- Total Login (hari ini)
- User Aktif (unik)
- Login Gagal
- Sesi Aktif
- Total Akses Aplikasi
- Top Page Diakses

### Section 3: Charts Grid
- **Login Trend Chart** — Line chart, 14 hari terakhir
- **Login by Hour Chart** — Bar chart, distribusi per jam
- **Top Pages Chart** — Horizontal bar, halaman paling banyak diakses
- **Login Status Chart** — Doughnut, berhasil vs gagal

### Section 4: Login Logs Table
- Columns: Waktu, UserID, IP Address, Device/Browser, Status, Aplikasi
- Features: Search, sort, pagination, row click for detail
- Filter: status (berhasil/gagal), aplikasi

### Section 5: Application Access Logs Table
- Columns: Waktu, UserID, Halaman, Endpoint, Method, Durasi
- Features: Search, sort, pagination, row click for detail
- Filter: halaman, method (GET/POST)

### Section 6: Detail Modal
- Key-value display for selected row
- Related records links

**API Endpoints (existing):**
- `monitoring/stats` — KPI data
- `monitoring/list` — Table data with pagination
- `monitoring/trend` — Trend chart data
- `monitoring/activity` — Activity feed
- `monitoring/detail` — Row detail
- `monitoring/export` — CSV/XLSX export

---

## Task 2: Create `design.md` — Design System Specification

**Files:**
- Create: `design.md` (root of project or `docs/` directory)

**Content Structure:**

### 1. Color System
Based on existing `--sap-*` tokens:

```css
/* Brand */
--sap-brand: #0D9488;        /* Teal primary */
--sap-brand-dark: #0F766E;
--sap-brand-light: #CCFBF1;

/* Surfaces */
--sap-background: #F8FAFC;
--sap-surface: #FFFFFF;
--sap-border: #E2E8F0;

/* Semantic */
--sap-success: #059669;      /* Login berhasil */
--sap-error: #DC2626;        /* Login gagal */
--sap-warning: #D97706;      /* Warning states */
--sap-info: #0D9488;         /* Info states */

/* Chart Palette */
--sap-chart-1: #0D9488;      /* Primary data */
--sap-chart-2: #EA580C;      /* Secondary data */
--sap-chart-3: #059669;      /* Success */
--sap-chart-4: #7C3AED;      /* Accent */
--sap-chart-5: #2563EB;      /* Info */
--sap-chart-6: #DB2777;      /* Pink */
```

### 2. Typography
```css
--sap-font: 'Plus Jakarta Sans', sans-serif;
--sap-mono: 'SF Mono', monospace;

/* Scale */
--sap-h1: 2.25rem;   /* Page title */
--sap-h2: 1.5rem;    /* Section title */
--sap-h3: 1.25rem;   /* Card title */
--sap-body: 0.875rem; /* Body text */
--sap-small: 0.75rem; /* Labels, meta */
```

### 3. Spacing & Radius
```css
/* Spacing */
--sap-space-xs: 0.25rem;
--sap-space-sm: 0.5rem;
--sap-space-md: 1rem;
--sap-space-lg: 1.5rem;
--sap-space-xl: 2rem;

/* Radius */
--sap-radius-sm: 6px;
--sap-radius: 10px;
--sap-radius-lg: 16px;
```

### 4. Component Specifications

#### KPI Card
- Background: `var(--sap-surface)`
- Border: `1px solid var(--sap-border)`
- Border-radius: `var(--sap-radius-lg)`
- Padding: `16px`
- Value: `26px`, `font-weight: 700`, `color: var(--sap-brand)`
- Label: `12px`, `color: var(--sap-text-secondary)`

#### Data Table
- Header: `font-weight: 600`, `color: var(--sap-text-secondary)`
- Row hover: `background: var(--sap-border-light)`
- Row clickable: `cursor: pointer`
- Striped: Alternating `var(--sap-surface)` and `var(--sap-border-light)`

#### Chart Card
- Background: `var(--sap-surface)`
- Border: `1px solid var(--sap-border)`
- Border-radius: `var(--sap-radius-lg)`
- Padding: `16px`
- Title: `14px`, `font-weight: 600`

#### Filter Bar
- Input: `border: 1px solid var(--sap-border-input)`
- Button primary: `background: var(--sap-brand)`, `color: white`
- Button secondary: `background: var(--sap-surface)`, `border: 1px solid var(--sap-border)`

#### Badge/Status
- Success: `background: var(--sap-success-bg)`, `color: var(--sap-success)`
- Error: `background: var(--sap-error-bg)`, `color: var(--sap-error)`
- Warning: `background: var(--sap-warning-bg)`, `color: var(--sap-warning)`

### 5. Dark Mode
All tokens remap via `html.dark-mode` class:
```css
html.dark-mode {
  --sap-background: #0F172A;
  --sap-surface: #1E293B;
  --sap-text: #F1F5F9;
  --sap-border: #334155;
  /* ... */
}
```

### 6. Responsive Breakpoints
```css
/* Mobile: < 768px */
/* Tablet: 768px - 1024px */
/* Desktop: > 1024px */

.mon-kpi-grid {
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
}

@media (max-width: 768px) {
  .mon-kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .mon-chart-grid { grid-template-columns: 1fr; }
}
```

### 7. Animations & Transitions
```css
--sap-transition: 200ms cubic-bezier(0.4, 0, 0.2, 1);

/* Skeleton loading shimmer */
.skel {
  background: linear-gradient(90deg, var(--sap-border-light) 25%, var(--sap-surface) 50%, var(--sap-border-light) 75%);
  background-size: 200% 100%;
  animation: mon-skel 1.2s ease-in-out infinite;
}
```

---

## Task 3: Review & Finalize

**Verification:**
- [ ] `screen.md` covers all 6 sections
- [ ] `design.md` includes all component specs
- [ ] Both files reference existing `--sap-*` tokens
- [ ] Dark mode specifications included
- [ ] Responsive breakpoints defined
- [ ] API endpoints documented

---

## Execution Handoff

Plan complete and saved to `.opencode/plans/monitoring-dashboard-design.md`. Two execution options:

**1. Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints

Which approach?
