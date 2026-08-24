# Multi-Panel WordPress Platform

A modular WordPress plugin and theme that provides the foundation for a multi-panel platform with dynamic ACL (Access Control List).

## Components

| Component | Directory | Responsibility |
|-----------|-----------|----------------|
| **Platform Core** | `platform-core/` | Business logic, ACL, routing, API, security, database |
| **Platform Theme** | `platform-theme/` | Presentation layer only — layouts, UI, navigation |

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Installation

1. Copy `platform-core/` to `wp-content/plugins/platform-core/`
2. Copy `platform-theme/` to `wp-content/themes/platform-theme/`
3. Activate the **Platform Core** plugin in WordPress admin
4. Activate the **Platform Theme** in Appearance → Themes
5. Visit **Settings → Permalinks** and click Save to flush rewrite rules

## Architecture

### Panels

Three independent panels with separate navigation:

- **User Panel** — `/app/user`
- **Manager Panel** — `/app/manager`
- **Admin Panel** — `/app/admin`

Panel access is controlled by ACL permissions, not hard-coded roles.

### Routes

| Route | Permission Required |
|-------|---------------------|
| `/login` | Public |
| `/app` | `core.panel.access` |
| `/app/user` | `core.panel.user.access` |
| `/app/manager` | `core.panel.manager.access` |
| `/app/admin` | `core.panel.admin.access` |
| `/profile` | `core.profile.view` |
| `/settings` | `core.settings.view` |
| `/403` | Public |
| `/404` | Public |

### ACL Model

```
User → Role(s) → Permission(s) → Scope
```

**Permission format:** `module.resource.action`

Examples:
- `core.panel.user.access`
- `finance.invoice.view`
- `projects.project.create`

**Scope types:** `all`, `own`, `department`, `team`, `project`, `organization`, `custom`

### Database Tables

- `{prefix}platform_roles`
- `{prefix}platform_permissions`
- `{prefix}platform_role_permissions`
- `{prefix}platform_user_roles`
- `{prefix}platform_scopes`

### Default Roles (seeded on activation)

| Role Slug | Panel Access |
|-----------|-------------|
| `platform_user` | User panel |
| `platform_manager` | User + Manager panels |
| `platform_admin` | All panels + ACL management |

Assign roles to users via REST API or programmatically:

```php
mpp()->get( \MPP\Services\UserRoleService::class )
    ->assign_role_by_slug( $user_id, 'platform_user' );
```

### Permission Checks

**In PHP (backend — always use for authorization):**

```php
if ( mpp_can( 'finance.invoice.view' ) ) {
    // allowed
}

if ( mpp()->acl()->can( $user_id, 'finance.invoice.edit', [ 'owner_id' => 42 ] ) ) {
    // allowed with scope context
}
```

**In theme (UI visibility only — not authorization):**

```php
<?php if ( mpp_can_access_panel( 'manager' ) ) : ?>
    <a href="/app/manager">Manager Panel</a>
<?php endif; ?>
```

### Registering Module Permissions

Modules should register via the module contract. External plugins use `mpp_register_module()`:

```php
mpp_register_module( new \MyVendor\Finance\FinanceModule() );
```

Or the `mpp_register_modules` hook:

```php
add_action( 'mpp_register_modules', function ( $manager ) {
    $manager->register( new \MyVendor\Finance\FinanceModule() );
});
```

Permissions are registered in the module's `register_permissions()` method:

```php
mpp()->get( \MPP\ACL\PermissionRegistry::class )->register_module( 'projects', [
    'project' => [
        'view'   => 'View projects',
        'create' => 'Create projects',
    ],
]);
```

## Creating an External Module

Business modules are independent WordPress plugins that depend on **platform-core**.

```
wp-content/plugins/
├── platform-core/
├── platform-theme/
├── platform-example/    # user-panel reference
├── platform-tasks/      # manager task board
├── platform-team/       # manager team roster
└── platform-reports/    # manager operational reports
```

### Minimum structure

```
platform-example/
├── platform-example.php
├── includes/ExampleModule.php
└── templates/demo.php
```

### Registration flow

1. Business plugin loads on `plugins_loaded` (priority `< 10`, before core boots).
2. Call `mpp_register_module( new ExampleModule() )`.
3. Core validates identity, version, and duplicates.
4. Core runs `run_migrations()` → `register_permissions()` → `boot()`.
5. Core registers module routes and REST endpoints.
6. `mpp_modules_loaded` fires when all modules are ready.

### Module contract

Extend `MPP\Modules\AbstractModule` and implement:

| Method | Purpose |
|--------|---------|
| `get_slug()` | Unique module ID (e.g. `example`) |
| `get_name()` | Display name |
| `get_version()` | Module version |
| `get_requires_core_version()` | Minimum platform-core version |
| `register_permissions()` | ACL permissions |
| `register_routes( Router $router )` | Frontend routes (ACL-protected) |
| `register_rest_routes()` | REST endpoints (use `AccessGuard`) |
| `get_navigation_items()` | Panel navigation entries |
| `get_dashboard_widgets()` | Dashboard cards |
| `run_migrations()` | Module-owned DB migrations |
| `boot()` | Hooks after permissions sync |
| `deactivate()` | Cleanup on plugin deactivation |

### Dependency check

Declare the minimum core version in `get_requires_core_version()`. If core is too old, the module is skipped and an error is logged — no fatal error.

### Deactivation

```php
register_deactivation_hook( __FILE__, function () {
    mpp_deactivate_module( 'example' );
});
```

See `platform-example/` for a minimal user-panel module. Manager modules (`platform-tasks`, `platform-team`, `platform-reports`) provide real dashboard data for the manager panel.

### REST API

Namespace: `platform/v1`

| Endpoint | Method | Permission |
|----------|--------|------------|
| `/roles` | GET, POST | `core.acl.manage` |
| `/roles/{id}` | GET, PUT, DELETE | `core.acl.manage` |
| `/permissions` | GET | `core.acl.manage` |
| `/acl/me` | GET | Logged in (permissions only with `core.acl.manage`) |
| `/acl/check` | POST | `core.acl.manage` |
| `/acl/roles/{id}/permissions` | GET, POST, DELETE | `core.acl.manage` |
| `/acl/users/{id}/roles` | GET, POST, DELETE | `core.acl.manage` |

WordPress administrators are **not** automatically granted platform admin access. Enable explicitly:

```php
add_filter( 'mpp_sync_wp_admin_to_platform_admin', '__return_true' );
```

### Running Tests

```bash
cd platform-core
composer install
composer test
```

### Extending Routes

```php
add_action( 'mpp_booted', function () {
    $router = mpp()->get( \MPP\Core\Router::class );
    $router->add_route( 'app/finance', [
        'template'   => 'templates/panel-finance.php',
        'permission' => 'finance.invoice.view',
        'title'      => 'Finance',
    ]);
    flush_rewrite_rules();
});
```

## Plugin Structure

```
platform-core/
├── platform-core.php
├── includes/
│   ├── Core/          # Bootstrap, router, container
│   ├── ACL/           # Permission engine, roles, scopes
│   ├── Auth/          # WordPress auth integration
│   ├── API/           # REST controllers
│   ├── Database/      # Schema, installer
│   ├── Security/      # Sanitization
│   ├── Services/      # Shared services
│   └── Modules/       # Module manager & contract
└── templates/         # Fallback templates
```

External business modules live in separate plugins (see `platform-example/`).

## Theme Structure

```
platform-theme/
├── templates/           # Route templates
├── template-parts/      # Panel navigation components
├── assets/css/          # Styles
└── assets/js/           # Scripts
```

## Security Notes

- All permission checks are enforced in the plugin backend (router + REST API)
- Theme visibility checks (`mpp_can()`) are for UI only
- Nonces protect login and logout forms
- All database queries use prepared statements
- Input is sanitized; output is escaped

## License

GPL-2.0-or-later
