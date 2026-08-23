<?php

namespace MPP\Tests\Unit;

use Brain\Monkey\Functions;
use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\ACL\ScopeResolver;
use PHPUnit\Framework\TestCase;

class AccessBootstrapTest extends TestCase {

	public function test_wp_administrator_receives_effective_core_access(): void {
		Functions\when( 'user_can' )->alias(
			function ( $user_id, $capability ) {
				return (int) $user_id === 99 && 'manage_options' === $capability;
			}
		);

		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn(
			array(
				'id'             => 1,
				'permission_key' => 'core.acl.manage',
			)
		);

		$roles  = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn( array() );

		$engine = new AclEngine( $registry, $roles, new ScopeResolver() );

		$this->assertTrue( $engine->can( 99, 'core.acl.manage' ) );
		$this->assertFalse( $engine->can( 5, 'core.acl.manage' ) );
	}

	public function test_wp_administrator_does_not_receive_non_core_module_access(): void {
		Functions\when( 'user_can' )->alias(
			function ( $user_id, $capability ) {
				return (int) $user_id === 99 && 'manage_options' === $capability;
			}
		);

		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn(
			array(
				'id'             => 2,
				'permission_key' => 'finance.invoice.view',
			)
		);

		$roles  = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn( array() );

		$engine = new AclEngine( $registry, $roles, new ScopeResolver() );

		$this->assertFalse( $engine->can( 99, 'finance.invoice.view' ) );
	}

	public function test_platform_user_permissions_are_seeded(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Database/Installer.php' );

		foreach ( array(
			'core.panel.access',
			'core.panel.user.access',
			'core.profile.view',
			'core.settings.view',
		) as $permission ) {
			$this->assertStringContainsString( "'" . $permission . "'", $source );
		}
	}

	public function test_platform_manager_and_admin_permissions_are_seeded(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Database/Installer.php' );

		$this->assertStringContainsString( 'core.panel.manager.access', $source );
		$this->assertStringContainsString( 'core.panel.admin.access', $source );
		$this->assertStringContainsString( 'core.acl.manage', $source );
	}

	public function test_ensure_defaults_is_called_on_boot(): void {
		$plugin = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Core/Plugin.php' );
		$this->assertStringContainsString( 'Installer::ensure_defaults()', $plugin );
	}

	public function test_registration_assigns_platform_user(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Auth/RegistrationHandler.php' );
		$this->assertStringContainsString( "assign_role_by_slug( (int) \$user_id, 'platform_user' )", $source );
		$this->assertStringContainsString( 'Installer::ensure_defaults()', $source );
		$this->assertStringContainsString( 'user_pass_confirm', $source );
		$this->assertStringContainsString( 'wp_set_auth_cookie', $source );
	}

	public function test_login_supports_email_lookup(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Auth/AuthIntegration.php' );
		$this->assertStringContainsString( "get_user_by( 'email'", $source );
		$this->assertStringContainsString( 'wp_signon', $source );
	}

	public function test_platform_user_can_access_user_panel(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn(
			array(
				'id'             => 10,
				'permission_key' => 'core.panel.user.access',
			)
		);

		$roles = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn(
			array(
				array( 'id' => 1, 'slug' => 'platform_user' ),
			)
		);
		$roles->method( 'get_permissions' )->willReturn(
			array(
				array(
					'permission_id'  => 10,
					'scope_type'     => 'all',
					'scope_value'    => null,
					'permission_key' => 'core.panel.user.access',
				),
			)
		);

		$engine = new AclEngine( $registry, $roles, new ScopeResolver() );

		$this->assertTrue( $engine->can( 5, 'core.panel.user.access' ) );
	}

	public function test_platform_user_cannot_access_admin_acl(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn(
			array(
				'id'             => 11,
				'permission_key' => 'core.acl.manage',
			)
		);

		$roles = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn(
			array(
				array( 'id' => 1, 'slug' => 'platform_user' ),
			)
		);
		$roles->method( 'get_permissions' )->willReturn(
			array(
				array(
					'permission_id'  => 10,
					'scope_type'     => 'all',
					'scope_value'    => null,
					'permission_key' => 'core.panel.user.access',
				),
			)
		);

		$engine = new AclEngine( $registry, $roles, new ScopeResolver() );

		$this->assertFalse( $engine->can( 5, 'core.acl.manage' ) );
	}

	public function test_platform_manager_can_access_manager_panel_but_not_admin(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturnMap(
			array(
				array(
					'core.panel.manager.access',
					array(
						'id'             => 20,
						'permission_key' => 'core.panel.manager.access',
					),
				),
				array(
					'core.panel.admin.access',
					array(
						'id'             => 21,
						'permission_key' => 'core.panel.admin.access',
					),
				),
			)
		);

		$roles = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn(
			array(
				array( 'id' => 2, 'slug' => 'platform_manager' ),
			)
		);
		$roles->method( 'get_permissions' )->willReturn(
			array(
				array(
					'permission_id'  => 20,
					'scope_type'     => 'all',
					'scope_value'    => null,
					'permission_key' => 'core.panel.manager.access',
				),
			)
		);

		$engine = new AclEngine( $registry, $roles, new ScopeResolver() );

		$this->assertTrue( $engine->can( 7, 'core.panel.manager.access' ) );
		$this->assertFalse( $engine->can( 7, 'core.panel.admin.access' ) );
	}

	public function test_plain_permalink_route_urls_remain_supported(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/functions.php' );
		$this->assertStringContainsString( 'mpp_uses_pretty_permalinks', $source );
		$this->assertStringContainsString( 'index.php?', $source );
	}
}
