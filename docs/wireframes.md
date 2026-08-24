# Platform UI Wireframes

Low-fidelity information architecture for the multi-panel WordPress platform redesign.
Visual language: Tabler (primary), shadcn/ui (secondary). Implementation: existing PHP/theme architecture.

---

## Shared Shell (Admin / Manager / User Panels)

**Purpose:** Consistent application chrome across panels.
**Primary user:** Any authenticated panel user.
**Main action:** Navigate sections; access profile, language, logout.

### Desktop

```
┌──────────────────────────────────────────────────────────────┐
│ [Logo]                    [Panel Switcher] [Lang] [User ▼]   │
├──────────────┬───────────────────────────────────────────────┤
│ Sidebar Nav  │ Breadcrumb › Current Page                     │
│              │ Page Title                    [Primary CTA]   │
│ • Section    │ Description text                              │
│   - Item     │ ┌─────────────────────────────────────────┐ │
│   - Item     │ │ Page body (cards, tables, forms)        │ │
│              │ └─────────────────────────────────────────┘ │
└──────────────┴───────────────────────────────────────────────┘
```

### Mobile

- Header: `☰` toggle + logo + user avatar
- Sidebar → off-canvas drawer
- Breadcrumb collapses to current page only
- Primary CTA moves below page title (full width)
- Tables → stacked card rows

**Current state:** `PanelShell`, `header.php`, `PanelNavigation` exist. Gap: breadcrumbs lack page-level actions slot; Users/Roles not nested in nav.

---

## 1. Admin Dashboard

**Purpose:** Platform health overview and quick navigation.
**Primary user:** Platform admin.
**Main action:** Jump to management areas; review recent ACL activity.

**Hierarchy:** Stats row → quick links → recent audit feed → system status.
**Desktop:** 4–6 stat cards; 2-column quick actions; compact activity table.
**Mobile:** Stats as 2-column grid; links as button list; activity as timeline cards.

---

## 2. Users List

**Purpose:** Browse and search platform users.
**Primary user:** Admin with `core.acl.manage`.
**Main action:** Open user detail.

**Hierarchy:** Search bar → filter chips (role, status) → user table → pagination.
**Desktop:** Table: avatar+name, username, email, WP role, platform roles, registered, actions.
**Mobile:** Card per user: name, email, role badges, "View" link.

---

## 3. User Details

**Purpose:** Manage one user's roles, permissions, and access.
**Primary user:** Admin.
**Main action:** Assign/revoke roles; inspect effective access.

**Tabs:** Overview | Roles | Permissions | Effective Access | Security | Activity

```
Ali Rezaei · ali@example.com
[Overview][Roles][Permissions][Effective Access][Security][Activity]

Overview: profile fields, status, registration date
Roles: assigned roles + add/remove
Effective Access: permission | source | scope | granted/denied
```

**Desktop:** Header card + tab content. **Mobile:** Tabs scroll horizontally; tables → definition lists.

**Gap:** Current view is single scroll page without tabs.

---

## 4. Roles List

**Purpose:** Manage platform role definitions.
**Primary user:** Admin.
**Main action:** Create role; open role detail.

**Hierarchy:** [Create Role] → table (name, slug, permissions count, users, status, system flag).
**Mobile:** Role cards with key metrics and "View" action.

---

## 5. Role Details

**Purpose:** Configure role metadata, permissions, and members.
**Primary user:** Admin.
**Main action:** Edit permissions with grouped checkboxes; save.

**Tabs:** Overview | Permissions | Users | Effective Access

```
Manager · Manage assigned platform operations
Users: 5  Permissions: 18  Modules: 4

[Overview][Permissions][Users][Effective Access]

Permissions tab:
  Search...
  Core › Users     ☑ View  ☑ Edit  ☐ Delete
  Projects         ☑ View  ☑ Create  ☑ Edit  ☐ Delete
  ─────────────────────────
  18 permissions granted        [Cancel] [Save Changes]
```

**Desktop:** Summary stats bar + tabbed editor. **Mobile:** Module groups accordion; checkboxes full-width.

**Gap:** Permissions tab is read-only list; editing redirects to Permissions page.

---

## 6. Permissions

**Purpose:** Browse all permissions; understand usage (not primary role editor).
**Primary user:** Admin.
**Main action:** Search/filter; open permission detail.

**Do NOT use a giant matrix as primary UI.**

```
Search permissions...   [Module ▼] [Category ▼] [Status ▼]

Core
  Users
    View users          core.users.view        Used by: 2 roles  →
    Create users        core.users.create      Used by: 1 role   →
  Roles
    View roles          core.roles.view        Used by: 3 roles  →
```

Each row/card: human name, description, key, module, roles count, link to detail.

**Mobile:** Module accordion; each permission as a card (name, key, roles, action).

**Gap:** Current page mixes role-assignment matrix with tree; matrix should move to Role Details.

---

## 7. Permission Details

**Purpose:** Inspect one permission and its relationships.
**Primary user:** Admin.
**Main action:** View roles using it; link to role editor.

**Hierarchy:** Title + key + module → description → roles list → related audit entries.
**Mobile:** Stacked sections; roles as linked chips.

---

## 8. ACL

**Purpose:** High-level access control overview (not permission editing).
**Primary user:** Admin.
**Main action:** Understand role/permission distribution; link to detail pages.

**Hierarchy:** Summary stats → role-permission heat summary (compact) → scope distribution → links to Users/Roles/Permissions.
**Mobile:** Stats grid + simplified list per role (permission count, user count).

**Gap:** Currently merged with audit log; split Audit Log to separate nav item.

---

## 9. Modules List

**Purpose:** Review registered platform modules.
**Primary user:** Admin.
**Main action:** Open module detail.

```
[Search modules...]

┌─────────────────────────────────────┐
│ Example Module                      │
│ Demo module for development         │
│ v1.0.1 · ● Active                   │
│ 4 Permissions · 3 Routes            │
│ [Open Module]                       │
└─────────────────────────────────────┘
```

**Mobile:** Full-width module cards.

---

## 10. Module Details

**Purpose:** Inspect module capabilities and routes.
**Primary user:** Admin.
**Main action:** Review permissions/routes; open module settings if available.

**Tabs:** Overview | Permissions | Routes | Settings

**Mobile:** Tabs + stacked permission key list.

---

## 11. Settings (Admin)

**Purpose:** Platform-wide configuration.
**Primary user:** Admin.
**Main action:** Save section changes.

```
┌─────────────────┬──────────────────────────┐
│ General         │ General Settings         │
│ Appearance      │ Site Name [___________]  │
│ Localization    │ Timezone  [___________]  │
│ Registration    │                          │
│ Security        │         [Save Changes]   │
│ System          │                          │
└─────────────────┴──────────────────────────┘
```

**Mobile:** Section picker dropdown above form (sidebar becomes select).

**Gap:** Current admin settings is a single runtime-status page.

---

## 12. Audit Log

**Purpose:** Searchable history of ACL and admin actions.
**Primary user:** Admin.
**Main action:** Filter and inspect entries.

**Hierarchy:** Filters (date, action, object type, user) → paginated log table → entry detail drawer.
**Columns:** Time, actor, action, object, details.
**Mobile:** Log entries as cards (time, action, object).

**Gap:** No dedicated route; lives under `/app/admin/acl`. Add `app/admin/audit-log`.

---

## 13. User Dashboard

**Purpose:** End-user home after login.
**Primary user:** Platform user.
**Main action:** Access profile, settings, module shortcuts.

**Hierarchy:** Welcome → quick actions → account summary stats → accessible modules → recent activity.
**Mobile:** Stats 2-up grid; modules as icon grid.

---

## 14. Manager Dashboard

**Purpose:** Operational overview for managers.
**Primary user:** Manager.
**Main action:** Review team metrics; access manager tools.

**Hierarchy:** Welcome → team stats → pending items widget → module shortcuts → activity.
**Mobile:** Same as user dashboard with manager-specific widgets first.

---

## 15. Profile

**Purpose:** View/edit personal account info.
**Primary user:** Any authenticated user.
**Main action:** Update display name, email (if permitted), avatar.

**Hierarchy:** Avatar + name → editable fields form → read-only account metadata.
**Mobile:** Single-column form; save button sticky at bottom.

---

## 16. Login

**Purpose:** Authenticate users.
**Primary user:** Guest.
**Main action:** Submit credentials.

```
┌─────────────────────┐
│ Login               │
│ Username or Email   │
│ [________________]  │
│ Password      [👁]  │
│ [________________]  │
│ ☐ Remember me       │
│ [    Log In     ]   │
│ Forgot password?    │
│ Need account? Register│
└─────────────────────┘
```

**Mobile:** Centered card, full-width inputs. No sidebar/header tools except logo.

---

## 17. Register

**Purpose:** Create new account (when enabled).
**Primary user:** Guest.
**Main action:** Submit registration form.

**Hierarchy:** Username, email, password, confirm password, terms (optional) → submit.
**Mobile:** Same as login card pattern.

---

## 18. Forgot Password

**Purpose:** Initiate password reset.
**Primary user:** Guest.
**Main action:** Submit email/username for reset link.

**Hierarchy:** Instructions → email field → submit → success message.
**Mobile:** Centered card. Currently delegates to WP `wp_lostpassword_url`; add themed template at `/forgot-password`.

---

## Navigation IA (Admin Sidebar)

```
Dashboard
Users
  ├─ Users
  └─ Roles
Permissions
ACL
Modules
Settings
Audit Log
```

Account section (footer): Profile, Logout

---

## Implementation Mapping (for Phase 3+)

| Wireframe component | Existing asset |
|---------------------|----------------|
| Shell layout | `PanelShell`, `header.php` |
| Sidebar nav | `PanelNavigation` |
| Page header + breadcrumb | `UIComponents::page_header` |
| Stat cards | `UIComponents::stat_grid`, `.mpp-stat-card` |
| Tables | `.mpp-admin-table`, `.mpp-table-wrap` |
| Forms | `.mpp-form`, `FormHandler` |
| Tabs | `render_admin_tabs()` (roles only) |
| Badges/alerts | `.mpp-badge`, `.mpp-alert` |
| Permission tree | `PermissionService::get_permission_tree()` |
| Effective access | `EffectiveAccessService` |

### Priority gaps to close

1. Split Permissions (browse) from Role permission editor (matrix → grouped checkboxes in Role Details)
2. Add User Details tabs
3. Add dedicated Audit Log route + nav item
4. Admin Settings → categorized sidebar layout
5. Themed Forgot Password page
6. Nested Users/Roles in admin navigation
7. Mobile table → card transforms (CSS + markup patterns)

---

## Phase Status

- **Phase 1 (Architecture + UI audit):** Complete — see gaps above
- **Phase 2 (Wireframes):** Complete — this document
- **Phase 3 (Design system):** Complete — see `docs/design-system.md`
- **Phase 4 (Admin shell):** Complete
- **Phase 5 (Permissions / Roles / ACL):** Complete
- **Phase 6 (Modules):** Complete
- **Phase 7 (Users):** Complete — list filters (role, status), role chips, effective access source labels
- **Phase 8+:** Pending — settings polish, panels, auth, responsive/RTL, testing
