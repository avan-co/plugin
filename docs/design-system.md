# Platform Design System

Component catalog mapping wireframe patterns to PHP helpers and CSS classes.

Visual reference: [Tabler](https://github.com/tabler/tabler) (primary), [shadcn/ui](https://github.com/shadcn-ui/ui) (secondary).

## Tokens

File: `platform-theme/assets/css/tokens.css`

| Token | Usage |
|-------|-------|
| `--mpp-primary` | Primary actions, active nav |
| `--mpp-muted` | Sidebar hover, section headers |
| `--mpp-border` | Cards, tables, inputs |
| `--mpp-sidebar-width` | Panel sidebar (260px) |
| `--mpp-header-height` | Top bar (56px) |

## Layout

| Component | PHP | CSS |
|-----------|-----|-----|
| App shell | `PanelShell::render()` | `.mpp-layout`, `.mpp-layout__main` |
| Top bar | `header.php` | `.mpp-header` |
| Sidebar nav | `PanelNavigation::render()` | `.mpp-nav`, `.mpp-nav__sublist` |
| Page header | `UIComponents::page_header()` | `.mpp-page-header`, `.mpp-page-header__row` |
| Settings layout | `UIComponents::settings_layout()` | `.mpp-settings-layout` |

## Navigation patterns

### Flat item

```php
array(
  'label' => 'Dashboard',
  'route' => 'app/admin',
  'url'   => mpp_route_url( 'app/admin' ),
)
```

### Nested group (Users → Users / Roles)

```php
array(
  'label'    => 'Users',
  'children' => array(
    array( 'label' => 'Users', 'route' => 'app/admin/users', ... ),
    array( 'label' => 'Roles', 'route' => 'app/admin/roles', ... ),
  ),
)
```

Rendered by `PanelNavigation` with `.mpp-nav__sublist`.

## Content components

| Component | Helper | CSS class |
|-----------|--------|-----------|
| Button | `platform_ui_button()` | `.mpp-btn`, `.mpp-btn--{variant}` |
| Card | — | `.mpp-card` |
| Alert | `platform_ui_alert()` | `.mpp-alert`, `.mpp-alert--{type}` |
| Badge / chip | `platform_ui_chip()` | `.mpp-chip`, `.mpp-badge` |
| Empty state | `platform_ui_empty_state()` | `.mpp-empty-state` |
| Stat grid | `UIComponents::stat_grid()` | `.mpp-stats`, `.mpp-stat-card` |
| Section | `UIComponents::section()` | `.mpp-panel-section` |
| Tabs | `platform_ui_tabs()` | `.mpp-tabs` |
| Detail header | `platform_ui_detail_header()` | `.mpp-detail-header` |
| Back link | `platform_ui_back_link()` | `.mpp-back-link` |
| Filter bar | `platform_ui_filter_bar()` | `.mpp-filter-bar` |
| Module card | `platform_ui_module_card()` | `.mpp-module-card` |
| Avatar | `platform_ui_avatar()` | `.mpp-avatar` |

## Data display

| Pattern | Markup | Mobile |
|---------|--------|--------|
| Standard table | `.mpp-admin-table` inside `.mpp-table-wrap` | Horizontal scroll |
| Stacked table | Add `.mpp-admin-table--stack` + `data-label` on `<td>` | Card rows |
| Permission browse | `.mpp-perm-tree` hierarchy | Full-width items |
| Permission cards | `.mpp-perm-cards` / `.mpp-perm-card` | Shown below 768px |
| Permission matrix | `.mpp-admin-table--matrix` | Hidden below 768px |

### Stacked table example

```html
<table class="mpp-admin-table mpp-admin-table--stack">
  <tr>
    <td data-label="User">Ali Rezaei</td>
    <td data-label="Email">ali@example.com</td>
  </tr>
</table>
```

### Permission tree example

```html
<div class="mpp-perm-tree">
  <section class="mpp-perm-tree__module">
    <h3 class="mpp-perm-tree__module-title">Core</h3>
    <div class="mpp-perm-tree__resource">
      <h4 class="mpp-perm-tree__resource-title">Users</h4>
      <ul class="mpp-perm-tree__list">
        <li class="mpp-perm-tree__item">...</li>
      </ul>
    </div>
  </section>
</div>
```

## Forms

| Element | CSS |
|---------|-----|
| Form wrapper | `.mpp-form` |
| Field | `.mpp-field`, `.mpp-field__label` |
| Input | `.mpp-input` |
| Select | `.mpp-select` |
| Checkbox | `.mpp-checkbox` |
| Password toggle | `.mpp-password-field` |

## Auth pages

Centered card pattern: `.mpp-main--centered` + `.mpp-card--login`

Used by: login, register, forgot-password (Phase 10).

## RTL

- Body class: `mpp-rtl` when `is_rtl()`
- Breadcrumb separator flips in `[dir="rtl"]`
- Sidebar active border uses `border-inline-start`

## File load order

1. `tokens.css`
2. `components.css`
3. `style.css` (theme)
4. `main.css`
5. `panels.css`
6. `admin.css` (admin routes only)

## Phase 3 status

- [x] Tabs component generalized (`.mpp-tabs`)
- [x] Detail header, filter bar, settings layout, chips, module card
- [x] Nested admin navigation
- [x] Mobile stacked tables (`.mpp-admin-table--stack`)
- [x] Permission tree CSS (`.mpp-perm-tree`)
- [x] Page implementations (Phase 4 admin shell)
- [x] Permissions role editor on Role Details (Phase 5)
- [ ] Modules, users, panels, auth, responsive/RTL, testing (Phase 6+)
