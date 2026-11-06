# Rahi (Intracode ERP) — UI Reference Specification (`ui.md`)

Call or reference `@ui.md` whenever creating or updating any page in **Rahi (Intracode ERP)**.
This specification replicates the **Octave / Linear / Attio** high-craft B2B SaaS & ERP aesthetic and strictly prevents generic "AI-generated" dashboard patterns.

---

## 0. Senior UI/UX Designer Persona & Philosophy (MANDATORY)

Always act as a **Staff / Senior Principal UI/UX Designer** specializing in high-craft enterprise B2B SaaS and ERP systems (Octave, Linear, Attio, Stripe):
1. **Senior Design Discretion**:
   - Never clutter screens with repetitive, noisy miniature badges, floating micro-stats, or nested card borders.
   - Maintain strict visual hierarchy: one prominent focus per viewport (e.g. clean executive performance graphs, spacious transaction ledgers).
2. **Data-Dense Elegance Over AI Boilerplate**:
   - When presenting analytics or metrics, prefer clean data visualizations (pure SVG hairline bar/spline charts, quota guide lines, tabular numbers, subtle tooltips) over sprawling rows of awkward status pills.
3. **Restraint & Craft**:
   - Use flat surfaces, 8px layout rhythm, crisp 1px borders (`#e5e7eb`), hairline dividers (`#f1f5f9`), solid colors (never gradients), and minimal purposeful icons.

---

## 1. Anti-AI-Generated UI Rules (CRITICAL)

To ensure pages look human-designed by a senior product designer and **never** look AI-generated, strictly enforce these prohibitions:

1. **NO Icon Circles in KPI / Metric Cards**:
   - Never place a colored circle/square with an icon inside KPI cards.
   - KPI cards must only use a small muted text label (`12px`, `#64748b`) with a tiny `↗` arrow if needed, a bold tabular number (`22px–24px`), and a tiny dot-status badge (`● Watch` / `● On track` / `● Off track`).
2. **NO Gradients Anywhere**:
   - Zero `linear-gradient` or `radial-gradient` on backgrounds, buttons, cards, badges, or text.
3. **NO Rainbow Cards or Left-Border Accents**:
   - Never use `border-left: 4px solid ...` on cards or tint whole cards blue/green/purple/orange.
   - All cards use pure `#ffffff` backgrounds with `1px solid #e5e7eb` borders. Color is reserved strictly for tiny `6px` status dots, compact semantic pills, and the single primary CTA button.
4. **NO Heavy Drop Shadows or Bloated Corners**:
   - Never use large diffuse shadows (`0 10px 25px...`) or pill-shaped cards (`border-radius: 16px+`).
   - Use crisp `1px solid #e5e7eb` hairline borders, `border-radius: 8px` to `10px` on panels (`6px` on buttons/inputs), and flat surfaces.
5. **NO Icon Clutter ("Avoid Too Many Icons")**:
   - Do not put icons on table column headers, status badges, form labels, secondary links, or card titles.
   - Only use thin `1.5px`-stroke Lucide icons (`15px–17px`) in the sidebar navigation, compact filter pills, and standalone icon-only utility buttons.
6. **NO Hover-Lift Animations**:
   - Never use `transform: translateY(-2px)` on cards or rows. Use instant or `120ms` background color shifts (`#ffffff` -> `#f8fafc`).

---

## 2. Color Tokens & Surface Hierarchy

```css
:root {
    /* Canvas & Surfaces */
    --bg-app: #f8f9fb;            /* Subtle cool-gray main workspace canvas */
    --bg-surface: #ffffff;        /* Card, sidebar, and table surface */
    --bg-subtle: #f3f4f6;         /* Subtle pill / muted control background */
    --bg-hover: #f8fafc;          /* Row and control hover state */
    --bg-active-nav: #eff6ff;     /* Soft blue-tinted active sidebar pill */

    /* Hairline Borders */
    --border-strong: #e5e7eb;     /* Outer card, sidebar, and input borders (1px) */
    --border-subtle: #f1f5f9;     /* Internal card dividers, table rows, KPI hairlines */

    /* Typography Colors */
    --text-primary: #0f172a;      /* Headings, metric values, active labels */
    --text-secondary: #475569;    /* Body text, inactive nav items, table cells */
    --text-muted: #64748b;        /* Subtitles, filter labels, column headers */
    --text-faint: #94a3b8;        /* Section overlines, timestamps, placeholders */

    /* Primary Brand Accent (Solid Blue — No Gradient) */
    --accent-primary: #2563eb;    /* Primary CTA button & active nav text */
    --accent-primary-hover: #1d4ed8;

    /* Semantic Status Pills (Soft Fill + Dark Text + Dot) */
    --status-success-bg: #ecfdf5;
    --status-success-text: #047857;
    --status-success-dot: #10b981;

    --status-warn-bg: #fffbeb;
    --status-warn-text: #b45309;
    --status-warn-dot: #f59e0b;

    --status-danger-bg: #fef2f2;
    --status-danger-text: #b91c1c;
    --status-danger-dot: #ef4444;
}
```

---

## 3. Typography & Information Density

- **Font Family**: `'Poppins', sans-serif` (or `'Inter'`, with `font-variant-numeric: tabular-nums` on all numbers, prices, quantities, and dates).
- **Compact Enterprise Scale**:
  - **Sidebar Section Overline**: `10.5px`, `font-weight: 600`, `text-transform: uppercase`, `letter-spacing: 0.06em`, color `#94a3b8`.
  - **Top Breadcrumb**: `12.5px`, parent `#64748b` / current page `#0f172a` (`font-weight: 500`).
  - **Page Title (`h1`)**: `20px–22px`, `font-weight: 600`, `letter-spacing: -0.02em`, color `#0f172a`, line-height `1.25`.
  - **Page Subtitle**: `13px`, `font-weight: 400`, color `#64748b`, margin-top `2px`.
  - **Card / Panel Title**: `14px`, `font-weight: 600`, color `#0f172a`.
  - **KPI Big Number**: `22px–24px`, `font-weight: 600`, `letter-spacing: -0.02em`, `font-variant-numeric: tabular-nums`, color `#0f172a`.
  - **Body / Table Cell / Control Text**: `12.5px–13px`, `font-weight: 400` or `500`, color `#334155`.
  - **Micro Badges & Captions**: `11.5px`, `font-weight: 500`.

---

## 4. Page Layout Blueprint (Octave / ERP Pattern)

Every ERP module page (Sales Order, Customer, Invoice, Production, Warehouse, Reports) must follow this structured 5-layer layout inside `.page-wrapper` (`margin-left: 245px; background: #f8f9fb; min-height: 100vh;`):

### Layer 1: Top Utility & Breadcrumb Bar (`height: 48px`)
- Full-width bar with `background: #ffffff`, `border-bottom: 1px solid #e5e7eb`, `padding: 0 24px`, `display: flex; align-items: center; justify-content: space-between;`.
- **Left**: Breadcrumb trail (`Configuration / ATC` or `Configuration / Bank Accounts`).
- **Right**: Clean search input box (`width: 240px`, `height: 30px`, `background: #f8fafc`, `border: 1px solid #e2e8f0`, `border-radius: 6px`, `font-size: 12px`).
  - **NO `<kbd>⌘K</kbd>` tag**: Do not include keyboard shortcut badges inside the search input.
  - **NO status pills**: Keep the top-right clean with only the search input.

### Layer 2: Executive Page Header & Action Bar
- `padding: 18px 24px 12px 24px`, `display: flex; align-items: center; justify-content: space-between;`.
- **Left**:
  - Page Title (`20px`, `font-weight: 600`, `#0f172a`).
  - **NO subtitle paragraphs**: Do not place descriptive text or paragraphs below the title; keep the header clean and compact.
- **Right (Action Cluster)**:
  - Secondary actions as compact white outline buttons (`height: 32px`, `padding: 0 11px`, `background: #ffffff`, `border: 1px solid #e2e8f0`, `border-radius: 6px`, `font-size: 12.5px`, `color: #334155`).
  - **Maximum ONE solid primary button** (`height: 32px`, `padding: 0 13px`, `background: #5b4fe9`, `color: #ffffff`, `border: none`, `border-radius: 6px`, `font-size: 12.5px`, `font-weight: 500`).

### Layer 3: Inline Segmented Filter Bar
- `padding: 0 24px 16px 24px`, `display: flex; align-items: center; gap: 8px; flex-wrap: wrap;`.
- Each filter pill is a compact button (`height: 30px`, `padding: 0 10px`, `background: #ffffff`, `border: 1px solid #e2e8f0`, `border-radius: 6px`, `font-size: 12px`, `display: inline-flex; align-items: center; gap: 5px;`):
  - Filter category in muted gray (`color: #64748b`) + active value in bold dark (`color: #0f172a; font-weight: 500`) + tiny `13px` chevron-down.
  - Example: `Period` **Year to date** `▾` | `Status` **All** `▾` | `Customer` **All** `▾` | plain text `Reset` button.

### Layer 4: Unified Hairline KPI Strip (Joined Container, NOT Separate Floating Cards)
- Instead of separate cards with gaps and shadows, wrap 4 to 6 KPI cells in **one single bordered container**:
  - Container: `margin: 0 24px 16px 24px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); overflow: hidden;`
  - Each KPI cell (`padding: 14px 16px; border-right: 1px solid #f1f5f9;`):
    1. **Top Label**: `font-size: 12px; color: #64748b; font-weight: 400;` (e.g., `Total Orders ↗`).
    2. **Primary Value**: `font-size: 22px; font-weight: 600; color: #0f172a; margin: 4px 0 6px 0; font-variant-numeric: tabular-nums;`.
    3. **Status + Context Line**: Inline flex with a compact status pill (`● Watch` or `● On track`) + muted comparison text (`98.0% of plan`, `11.5px`, `#64748b`).
    4. **Optional Micro-Subtext**: `font-size: 11px; color: #94a3b8; margin-top: 6px;`.

### Layer 5: Primary Workspace Split (`65% / 35%` or Full-Width Data Table Card)
- `padding: 0 24px 24px 24px; display: grid; grid-template-columns: 1fr 340px; gap: 16px;` (or `1fr` when displaying a wide ERP transaction table/form).
- **Main Panel (`.erp-panel`)**:
  - `background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px;`
  - Panel header has title (`14px`, `font-weight: 600`), subtitle (`12px`, `#64748b`), and right-aligned segmented switcher (`background: #f1f5f9; padding: 2px; border-radius: 6px;` with active tab `background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.06);`).
  - Data tables inside `.erp-panel`:
    - Header row: `border-bottom: 1px solid #e5e7eb; font-size: 11.5px; font-weight: 500; color: #64748b; padding: 8px 12px; background: transparent;` (avoid heavy gray/colored table headers).
    - Body rows: `border-bottom: 1px solid #f1f5f9; font-size: 12.5px; color: #1e293b; padding: 10px 12px;` with subtle `#f8fafc` hover.
    - **Table Row Actions (3-Dots Menu Dropdown)**:
      - Always use a single **3-dots button** (`<i data-lucide="more-horizontal"></i>`) in the actions column instead of exposing individual icons side-by-side.
      - Clicking the 3-dots button reveals a clean floating dropdown menu containing actions like `Print`, `Edit`, and `Delete`.
      - Delete option in the dropdown must be highlighted in danger red (`#dc2626`).
- **Right Insights / Activity Panel (`.erp-side-panel`)**:
  - `background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px;`
  - Header with title (`What changed` / `Recent Activity` / `Exceptions`) + subtle count pill (`3 insights` in `#f1f5f9`, `#475569`, `11px`).
  - Stacked feed items separated by `1px solid #f1f5f9`:
    - Bold concise headline (`12.5px`, `font-weight: 600`, `#0f172a`)
    - 1–2 line explanation (`12px`, `#64748b`, `line-height: 1.45`)
    - Footer row with soft semantic delta pill (`-13% vs plan` in `#fef2f2`/`#b91c1c`) on the left and subtle action link (`Pipeline →` in `#334155`, `font-weight: 500`) on the right.

---

## 5. Reusable Status Pill & Badge Patterns

Always use **dot + text pills** instead of solid neon badges:

```html
<!-- Watch / Warning Pill -->
<span class="status-pill status-warn">
    <span class="status-dot"></span> Watch
</span>

<!-- Off Track / Danger Pill -->
<span class="status-pill status-danger">
    <span class="status-dot"></span> Off track
</span>

<!-- On Track / Success Pill -->
<span class="status-pill status-success">
    <span class="status-dot"></span> On track
</span>
```

```css
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 2px 7px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 500;
    line-height: 1.4;
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.status-warn { background: #fffbeb; color: #b45309; }
.status-warn .status-dot { background: #f59e0b; }

.status-danger { background: #fef2f2; color: #b91c1c; }
.status-danger .status-dot { background: #ef4444; }

.status-success { background: #ecfdf5; color: #047857; }
.status-success .status-dot { background: #10b981; }
```

---

## 6. Table Row Actions — 3-Dots Dropdown Pattern

Never place action icons (print, edit, delete) side-by-side in data tables. Every table row must use a single **3-dots button** (`more-horizontal`) that opens a floating popup menu on click:

```html
<td class="text-center no-print">
    <div class="row-action-dropdown">
        <button type="button" class="row-menu-trigger" title="More Actions" onclick="toggleActionMenu(event, rowId)">
            <i data-lucide="more-horizontal"></i>
        </button>
        <div class="row-action-menu" id="actionMenu-rowId">
            <button type="button" class="action-menu-item" onclick="openPrintModal(rowId)">
                <i data-lucide="printer"></i>
                <span>Print Record</span>
            </button>
            <button type="button" class="action-menu-item" onclick="editRow(rowId)">
                <i data-lucide="edit-3"></i>
                <span>Edit</span>
            </button>
            <button type="button" class="action-menu-item item-danger" onclick="openDeleteModal(rowId)">
                <i data-lucide="trash-2"></i>
                <span>Delete</span>
            </button>
        </div>
    </div>
</td>
```

### Required Behavior:
- Clicking the 3-dots trigger toggles `.show` on `.row-action-menu`.
- Clicking outside closes all open menus.
- Dangerous actions (`Delete`) use class `.item-danger` (soft red hover `#fef2f2`, red icon and text `#dc2626`).

---

## 7. Zero-Regression CSS & Layout Preservation Rules (CRITICAL)

To prevent visual breakages, unstyled pages, or layout regressions across ERP module updates:

1. **Exact Blade Yield & Section Matching**:
   - `layouts/app.blade.php` defines `@yield('styles')` and `@stack('scripts')`.
   - Always declare stylesheets inside `@section('styles')` (**lowercase only**). Never use `@section('Styles')` — Blade section names are case-sensitive and mismatching casing causes 100% loss of styling.
   - Always put view scripts inside `@push('scripts') ... @endpush` so they are delivered into the layout's `@stack('scripts')`.
2. **Mandatory Sidenav CSS Inclusion**:
   - Whenever `@include('components.sidenav')` is rendered in a view, you MUST include both `sidenav.css` and the module stylesheet in `@section('styles')`:
     ```blade
     @section('styles')
     <link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
     <link rel="stylesheet" href="{{ asset('css/sales/SalesOrder/salesorder.css') }}">
     @endsection
     ```
3. **Visual & Structural Invariance**:
   - Never alter working container class names (`.page-container`, `.main-content`), layout wrappers, or existing CSS selectors.
   - All modal backdrops MUST have `display: none;` by default so forms never spill onto the document before activation.
   - Maintain consistent margins (`margin-left: 250px` for desktop sidebar alignment).



