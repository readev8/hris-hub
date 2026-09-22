# Monitoring Dashboard — Design System Specification

> **Purpose:** Design system tokens dan component patterns untuk monitoring dashboard.

---

## 1. Color System

### Brand Colors
```css
:root {
  /* Primary (Teal) */
  --sap-brand:          #0D9488;
  --sap-brand-dark:     #0F766E;
  --sap-brand-light:    #CCFBF1;
  --sap-brand-hover:    #F0FDFA;

  /* Shell (Navbar) */
  --sap-shell:          #0F172A;
  --sap-shell-text:     #FFFFFF;
  --sap-shell-hover:    rgba(255,255,255,0.1);

  /* Accent (Orange) */
  --sap-accent:         #EA580C;
  --sap-accent-dark:    #C2410C;
  --sap-accent-light:   #FFF7ED;
}
```

### Semantic Colors
```css
:root {
  /* Success */
  --sap-success:        #059669;
  --sap-success-bg:     #ECFDF5;
  --sap-success-border: #A7F3D0;

  /* Warning */
  --sap-warning:        #D97706;
  --sap-warning-bg:     #FFFBEB;
  --sap-warning-border: #FDE68A;

  /* Error / Danger */
  --sap-error:          #DC2626;
  --sap-error-bg:       #FEF2F2;
  --sap-error-border:   #FECACA;

  /* Info */
  --sap-info:           #0D9488;
  --sap-info-bg:        #F0FDFA;
  --sap-info-border:    #99F6E4;

  /* Priority */
  --sap-critical:       #DC2626;
  --sap-high:           #EA580C;
  --sap-medium:         #0D9488;
  --sap-low:            #059669;
}
```

### Chart Palette
```css
:root {
  --sap-chart-1:        #0D9488;  /* Teal - Primary data */
  --sap-chart-2:        #EA580C;  /* Orange - Secondary */
  --sap-chart-3:        #059669;  /* Green - Success */
  --sap-chart-4:        #7C3AED;  /* Purple - Accent */
  --sap-chart-5:        #2563EB;  /* Blue - Info */
  --sap-chart-6:        #DB2777;  /* Pink - Highlight */
}
```

### Surface Colors
```css
:root {
  /* Light Mode */
  --sap-background:     #F8FAFC;
  --sap-surface:        #FFFFFF;
  --sap-card-bg:        #FFFFFF;
  --sap-sidebar:        #FFFFFF;

  /* Borders */
  --sap-border:         #E2E8F0;
  --sap-border-light:   #F1F5F9;
  --sap-border-input:   #CBD5E1;
  --sap-divider:        #E2E8F0;
}
```

### Text Colors
```css
:root {
  --sap-text:           #1E293B;
  --sap-text-secondary: #475569;
  --sap-text-muted:     #94A3B8;
  --sap-text-placeholder:#94A3B8;
  --sap-text-inverse:   #FFFFFF;
  --sap-text-tertiary:  #94A3B8;
}
```

---

## 2. Dark Mode

### Class-Based Toggle
```css
html.dark-mode,
html[data-bs-theme="dark"],
.dark-mode {
  --sap-background:     #0F172A;
  --sap-surface:        #1E293B;
  --sap-card-bg:        #1E293B;
  --sap-sidebar:        #1E293B;

  --sap-text:           #F1F5F9;
  --sap-text-secondary: #CBD5E1;
  --sap-text-muted:     #94A3B8;
  --sap-text-tertiary:  #94A3B8;

  --sap-border:         #334155;
  --sap-border-light:   #475569;
  --sap-border-input:   #475569;
  --sap-divider:        #334155;

  --sap-shell:          #0F172A;

  /* Semantic remaps */
  --sap-brand-hover:    rgba(13,148,136,0.1);
  --sap-success-bg:     rgba(5,150,105,0.15);
  --sap-success-border: rgba(5,150,105,0.3);
  --sap-warning-bg:     rgba(217,119,6,0.15);
  --sap-warning-border: rgba(217,119,6,0.3);
  --sap-error-bg:       rgba(220,38,38,0.15);
  --sap-error-border:   rgba(220,38,38,0.3);
  --sap-info-bg:        rgba(13,148,136,0.15);
  --sap-info-border:    rgba(13,148,136,0.3);
  --sap-accent-light:   rgba(234,88,12,0.15);
  --sap-brand-light:    rgba(13,148,136,0.15);

  /* Shadows */
  --sap-shadow-sm:      0 1px 2px rgba(0,0,0,0.2);
  --sap-shadow:         0 1px 3px rgba(0,0,0,0.25), 0 4px 12px rgba(0,0,0,0.3);
  --sap-shadow-lg:      0 4px 12px rgba(0,0,0,0.3), 0 12px 32px rgba(0,0,0,0.4);
  --sap-shadow-xl:      0 8px 24px rgba(0,0,0,0.4), 0 24px 48px rgba(0,0,0,0.5);

  color-scheme: dark;
}
```

---

## 3. Typography

### Font Families
```css
:root {
  --sap-font:           'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --sap-mono:           'SF Mono', Monaco, 'Cascadia Code', 'Courier New', monospace;
}
```

### Type Scale
```css
:root {
  --sap-h1:             2.25rem;    /* 36px - Page title */
  --sap-h2:             1.5rem;     /* 24px - Section title */
  --sap-h3:             1.25rem;    /* 20px - Card title */
  --sap-h4:             1.125rem;   /* 18px - Subsection */
  --sap-h5:             1rem;       /* 16px - Label */
  --sap-h6:             0.875rem;   /* 14px - Small label */
  --sap-body:           0.875rem;   /* 14px - Body text */
  --sap-small:          0.75rem;    /* 12px - Meta, badges */
}
```

### Base Typography
```css
body {
  font-family: var(--sap-font);
  font-size: var(--sap-body);
  line-height: 1.5;
  color: var(--sap-text);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

h1 { font-size: var(--sap-h1); font-weight: 800; line-height: 1.15; letter-spacing: -0.025em; }
h2 { font-size: var(--sap-h2); font-weight: 700; line-height: 1.25; }
h3 { font-size: var(--sap-h3); font-weight: 700; line-height: 1.35; }
h4 { font-size: var(--sap-h4); font-weight: 600; }
h5 { font-size: var(--sap-h5); font-weight: 600; color: var(--sap-text-secondary); text-transform: uppercase; letter-spacing: 0.05em; }
h6 { font-size: var(--sap-h6); font-weight: 600; color: var(--sap-text-muted); }
```

---

## 4. Spacing

```css
:root {
  --sap-space-xs:       0.25rem;    /* 4px */
  --sap-space-sm:       0.5rem;     /* 8px */
  --sap-space-md:       1rem;       /* 16px */
  --sap-space-lg:       1.5rem;     /* 24px */
  --sap-space-xl:       2rem;       /* 32px */
  --sap-space-xxl:      3rem;       /* 48px */
}
```

---

## 5. Border Radius

```css
:root {
  --sap-radius-sm:      6px;
  --sap-radius:         10px;
  --sap-radius-lg:      16px;
  --sap-radius-pill:    100px;
}
```

---

## 6. Shadows

```css
:root {
  --sap-shadow-sm:      0 1px 2px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.06);
  --sap-shadow:         0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.08);
  --sap-shadow-lg:      0 4px 12px rgba(0,0,0,0.08), 0 12px 32px rgba(0,0,0,0.12);
  --sap-shadow-xl:      0 8px 24px rgba(0,0,0,0.12), 0 24px 48px rgba(0,0,0,0.16);
}
```

---

## 7. Transitions

```css
:root {
  --sap-transition:     200ms cubic-bezier(0.4, 0, 0.2, 1);
  --sap-transition-slow:350ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

---

## 8. Z-Index Scale

```css
:root {
  --sap-z-dropdown:     1000;
  --sap-z-sticky:       1020;
  --sap-z-modal:        1050;
  --sap-z-drawer-scrim: 1055;
  --sap-z-drawer:       1056;
  --sap-z-toast:        1080;
  --sap-z-loader:       2000;
}
```

---

## 9. Component Specifications

### 9.1 KPI Card

```css
.metric-card {
  background: var(--sap-surface);
  border: 1px solid var(--sap-border);
  border-radius: var(--sap-radius-lg);
  padding: var(--sap-space-md);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 4px;
  transition: transform var(--sap-transition), box-shadow var(--sap-transition);
}

.metric-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--sap-shadow-lg);
}

.metric-value {
  font-size: 26px;
  font-weight: 700;
  color: var(--sap-brand);
}

.metric-label {
  font-size: 12px;
  color: var(--sap-text-secondary);
}

.metric-delta {
  font-size: 11px;
  font-weight: 600;
  min-height: 14px;
}

/* Variants */
.metric-card.icon-brand .metric-icon { color: var(--sap-brand); }
.metric-card.icon-success .metric-icon { color: var(--sap-success); }
.metric-card.icon-warning .metric-icon { color: var(--sap-warning); }
.metric-card.icon-danger .metric-icon { color: var(--sap-error); }
.metric-card.icon-info .metric-icon { color: var(--sap-info); }
```

### 9.2 Data Table

```css
.sap-table {
  width: 100%;
  border-collapse: collapse;
  font-size: var(--sap-body);
}

.sap-table thead th {
  font-weight: 600;
  color: var(--sap-text-secondary);
  text-align: left;
  padding: var(--sap-space-sm) var(--sap-space-md);
  border-bottom: 2px solid var(--sap-border);
  white-space: nowrap;
}

.sap-table tbody td {
  padding: var(--sap-space-sm) var(--sap-space-md);
  border-bottom: 1px solid var(--sap-border-light);
  vertical-align: middle;
}

.sap-table tbody tr {
  transition: background var(--sap-transition);
}

.sap-table tbody tr:hover {
  background: var(--sap-border-light);
}

.sap-table tbody tr.row-clickable {
  cursor: pointer;
}

/* Striped variant */
.sap-table-striped tbody tr:nth-child(even) {
  background: var(--sap-border-light);
}
```

### 9.3 Chart Card

```css
.mon-card {
  background: var(--sap-surface);
  border: 1px solid var(--sap-border);
  border-radius: var(--sap-radius-lg);
  padding: var(--sap-space-md);
}

.mon-card h3 {
  font-size: 14px;
  margin: 0 0 var(--sap-space-sm);
  color: var(--sap-text);
}

.mon-card canvas {
  max-height: 260px;
}

/* Chart grid */
.mon-chart-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: var(--sap-space-md);
  margin-bottom: var(--sap-space-lg);
}
```

### 9.4 Filter Bar

```css
.filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: var(--sap-space-sm);
  align-items: center;
}

.filter-bar input[type="date"] {
  border: 1px solid var(--sap-border-input);
  border-radius: var(--sap-radius-sm);
  padding: var(--sap-space-xs) var(--sap-space-sm);
  font-size: var(--sap-small);
  background: var(--sap-surface);
  color: var(--sap-text);
}

.filter-bar select {
  border: 1px solid var(--sap-border-input);
  border-radius: var(--sap-radius-sm);
  padding: var(--sap-space-xs) var(--sap-space-sm);
  font-size: var(--sap-small);
  background: var(--sap-surface);
  color: var(--sap-text);
  max-width: 190px;
}
```

### 9.5 Buttons

```css
.sap-btn {
  display: inline-flex;
  align-items: center;
  gap: var(--sap-space-xs);
  padding: var(--sap-space-xs) var(--sap-space-sm);
  font-size: var(--sap-small);
  font-weight: 500;
  border-radius: var(--sap-radius-sm);
  border: 1px solid transparent;
  cursor: pointer;
  transition: all var(--sap-transition);
  text-decoration: none;
}

.sap-btn-sm {
  padding: 4px 10px;
  font-size: 12px;
}

/* Primary */
.sap-btn-primary {
  background: var(--sap-brand);
  color: white;
  border-color: var(--sap-brand);
}

.sap-btn-primary:hover {
  background: var(--sap-brand-dark);
  border-color: var(--sap-brand-dark);
}

/* Secondary */
.sap-btn-secondary {
  background: var(--sap-surface);
  color: var(--sap-text);
  border-color: var(--sap-border);
}

.sap-btn-secondary:hover {
  background: var(--sap-border-light);
  border-color: var(--sap-brand);
}

/* Accent */
.sap-btn-accent {
  background: var(--sap-accent);
  color: white;
  border-color: var(--sap-accent);
}

.sap-btn-accent:hover {
  background: var(--sap-accent-dark);
  border-color: var(--sap-accent-dark);
}
```

### 9.6 Badges

```css
.sap-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  font-size: 12px;
  font-weight: 500;
  border-radius: var(--sap-radius-pill);
  line-height: 1.4;
}

.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* Status variants */
.sap-badge.approved,
.sap-badge.success {
  background: var(--sap-success-bg);
  color: var(--sap-success);
}
.sap-badge.approved .badge-dot,
.sap-badge.success .badge-dot {
  background: var(--sap-success);
}

.sap-badge.rejected,
.sap-badge.error {
  background: var(--sap-error-bg);
  color: var(--sap-error);
}
.sap-badge.rejected .badge-dot,
.sap-badge.error .badge-dot {
  background: var(--sap-error);
}

.sap-badge.warning {
  background: var(--sap-warning-bg);
  color: var(--sap-warning);
}
.sap-badge.warning .badge-dot {
  background: var(--sap-warning);
}

.sap-badge.info,
.sap-badge.in-progress {
  background: var(--sap-info-bg);
  color: var(--sap-info);
}
.sap-badge.info .badge-dot,
.sap-badge.in-progress .badge-dot {
  background: var(--sap-info);
}

/* Small variant */
.sap-badge-sm {
  font-size: 11px;
  padding: 2px 8px;
}
```

### 9.7 Modal

```css
.mon-modal-glass {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.mon-modal-glass .modal-content {
  background: var(--sap-surface);
  border: 1px solid var(--sap-border);
  border-radius: var(--sap-radius-lg);
}

.mon-modal-glass .modal-header {
  border-bottom: 1px solid var(--sap-border);
}

.mon-modal-glass .modal-footer {
  border-top: 1px solid var(--sap-border);
}
```

### 9.8 Tabs

```css
.domain-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: var(--sap-space-sm);
  margin-bottom: var(--sap-space-sm);
}

.domain-tab {
  cursor: pointer;
  border: 1px solid var(--sap-border);
  background: var(--sap-surface);
  border-radius: var(--sap-radius);
  padding: 6px 14px;
  font-size: 13px;
  color: var(--sap-text);
  transition: background var(--sap-transition), color var(--sap-transition);
}

.domain-tab:hover {
  background: var(--sap-border-light);
}

.domain-tab.active {
  background: var(--sap-brand);
  border-color: var(--sap-brand);
  color: white;
}
```

### 9.9 Alert Badge

```css
.mon-alert-badge {
  background: rgba(220, 38, 38, 0.12);
  border: 1px solid rgba(220, 38, 38, 0.35);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border-radius: var(--sap-radius-pill);
  padding: 2px 10px;
  font-size: 12px;
  font-weight: 600;
  color: var(--sap-error);
}
```

---

## 10. Animations & Transitions

### Skeleton Loading
```css
.skel {
  background: linear-gradient(
    90deg,
    var(--sap-border-light) 25%,
    var(--sap-surface) 50%,
    var(--sap-border-light) 75%
  );
  background-size: 200% 100%;
  animation: mon-skel 1.2s ease-in-out infinite;
  border-radius: 6px;
  min-height: 14px;
}

@keyframes mon-skel {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
```

### Stagger Animation
```css
.stagger-in {
  animation: appr-stagger 0.3s ease-out both;
}

@keyframes appr-stagger {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
```

### Pulse Animation
```css
.metric-pulse {
  animation: metricPulse 2s ease-in-out infinite;
}

@keyframes metricPulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.3); }
  50% { box-shadow: 0 0 0 8px rgba(217, 119, 6, 0); }
}

@media (prefers-reduced-motion: reduce) {
  .metric-card, .domain-tab, .sap-table tbody tr { transition: none; }
  .metric-card:hover { transform: none; }
  .skel, .metric-pulse, .stagger-in { animation: none; }
}
```

---

## 11. Responsive Breakpoints

### Mobile (< 768px)
```css
@media (max-width: 768px) {
  .mon-kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .mon-chart-grid {
    grid-template-columns: 1fr;
  }

  .metric-value {
    font-size: 21px;
  }

  .chart-grid {
    grid-template-columns: 1fr;
  }

  .workload-row {
    grid-template-columns: 1fr;
    gap: 4px;
  }

  .workload-count {
    flex-wrap: wrap;
  }
}
```

### Tablet (768px - 1024px)
```css
@media (min-width: 768px) and (max-width: 1024px) {
  .mon-kpi-grid {
    grid-template-columns: repeat(3, 1fr);
  }

  .mon-chart-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
```

### Desktop (> 1024px)
```css
@media (min-width: 1024px) {
  .mon-kpi-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  }

  .mon-chart-grid {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  }
}
```

---

## 12. Layout Tokens

```css
:root {
  /* Shell */
  --sap-shell-height:   56px;
  --sap-sidebar-width:  260px;
  --sap-sidebar-collapsed: 72px;
}
```

---

## 13. Accessibility

### Focus States
```css
*:focus-visible {
  outline: 2px solid var(--sap-brand);
  outline-offset: 2px;
}
```

### Reduced Motion
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

### Color Contrast
- Text on background: Minimum 4.5:1 ratio
- Large text (18px+): Minimum 3:1 ratio
- Interactive elements: Minimum 3:1 ratio

---

## 14. Dark Mode Toggle Implementation

```javascript
// Theme toggle
function toggleTheme() {
  const html = document.documentElement;
  const isDark = html.classList.toggle('dark-mode');
  html.dataset.bsTheme = isDark ? 'dark' : 'light';
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

// Initialize
(function() {
  const stored = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (stored === 'dark' || (!stored && prefersDark)) {
    document.documentElement.classList.add('dark-mode');
    document.documentElement.dataset.bsTheme = 'dark';
  }
})();
```

---

## 15. Component Matrix

| Component | Light | Dark | Animation | Responsive |
|-----------|-------|------|-----------|------------|
| KPI Card | ✓ | ✓ | Hover lift | 2-6 cols |
| Data Table | ✓ | ✓ | Row hover | Scroll |
| Chart Card | ✓ | ✓ | - | 1-2 cols |
| Filter Bar | ✓ | ✓ | - | Wrap |
| Button | ✓ | ✓ | Transition | Auto |
| Badge | ✓ | ✓ | - | Auto |
| Modal | ✓ | ✓ | Backdrop blur | Auto |
| Tabs | ✓ | ✓ | Transition | Wrap |
| Skeleton | ✓ | ✓ | Shimmer | Auto |
