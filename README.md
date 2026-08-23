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

```php
add_action( 'mpp_register_modules', function ( $manager ) {
    $manager->register( new \MPP\Modules\Examples\FinanceModule(
        mpp()->get( \MPP\ACL\PermissionRegistry::class )
    ) );
});
```

Or register directly:

```php
add_action( 'mpp_booted', function () {
    $registry = mpp()->get( \MPP\ACL\PermissionRegistry::class );
    $registry->register_module( 'projects', [
        'project' => [
            'view'   => 'View projects',
            'create' => 'Create projects',
            'edit'   => 'Edit projects',
            'delete' => 'Delete projects',
        ],
    ]);
    $registry->sync_to_database();
});
```

### REST API

Namespace: `platform/v1`

| Endpoint | Method | Permission |
|----------|--------|------------|
| `/roles` | GET, POST | `core.acl.manage` |
| `/roles/{id}` | GET, PUT, DELETE | `core.acl.manage` |
| `/permissions` | GET | `core.acl.manage` |
| `/acl/me` | GET | Logged in |
| `/acl/check` | POST | Logged in |
| `/acl/roles/{id}/permissions` | GET, POST | `core.acl.manage` |
| `/acl/users/{id}/roles` | GET, POST | `core.acl.manage` |

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
│   └── Modules/       # Module manager
├── modules/examples/  # Example finance module
└── templates/         # Fallback templates
```

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
