<?php

namespace MPP\Tests\Unit;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\ACL\ScopeResolver;
use PHPUnit\Framework\TestCase;

class AclEngineTest extends TestCase {

	private function make_engine( PermissionRegistry $registry, RoleManager $roles ): AclEngine {
		return new AclEngine( $registry, $roles, new ScopeResolver() );
	}

	public function test_denies_unknown_permission(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn( null );

		$roles = $this->createMock( RoleManager::class );
		$engine = $this->make_engine( $registry, $roles );

		$this->assertFalse( $engine->can( 1, 'missing.permission.key' ) );
	}

	public function test_denies_user_without_roles(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn(
			array(
				'id'             => 10,
				'permission_key' => 'core.panel.access',
			)
		);

		$roles = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn( array() );

		$engine = $this->make_engine( $registry, $roles );

		$this->assertFalse( $engine->can( 1, 'core.panel.access' ) );
	}

	public function test_allows_permission_from_single_role_with_all_scope(): void {
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

		$engine = $this->make_engine( $registry, $roles );

		$this->assertTrue( $engine->can( 5, 'core.panel.user.access' ) );
	}

	public function test_combines_permissions_from_multiple_roles(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn(
			array(
				'id'             => 20,
				'permission_key' => 'core.panel.manager.access',
			)
		);

		$roles = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn(
			array(
				array( 'id' => 1, 'slug' => 'platform_user' ),
				array( 'id' => 2, 'slug' => 'platform_manager' ),
			)
		);
		$roles->method( 'get_permissions' )->willReturnMap(
			array(
				array(
					1,
					array(
						array(
							'permission_id'  => 10,
							'scope_type'     => 'all',
							'scope_value'    => null,
							'permission_key' => 'core.panel.user.access',
						),
					),
				),
				array(
					2,
					array(
						array(
							'permission_id'  => 20,
							'scope_type'     => 'all',
							'scope_value'    => null,
							'permission_key' => 'core.panel.manager.access',
						),
					),
				),
			)
		);

		$engine = $this->make_engine( $registry, $roles );

		$this->assertTrue( $engine->can( 5, 'core.panel.manager.access' ) );
	}

	public function test_permission_removal_denies_access(): void {
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
		$roles->method( 'get_permissions' )->willReturn( array() );

		$engine = $this->make_engine( $registry, $roles );

		$this->assertFalse( $engine->can( 5, 'core.panel.user.access' ) );
	}

	public function test_own_scope_requires_matching_owner_context(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn(
			array(
				'id'             => 30,
				'permission_key' => 'finance.invoice.view',
			)
		);

		$roles = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn(
			array(
				array( 'id' => 1, 'slug' => 'accountant' ),
			)
		);
		$roles->method( 'get_permissions' )->willReturn(
			array(
				array(
					'permission_id'  => 30,
					'scope_type'     => 'own',
					'scope_value'    => null,
					'permission_key' => 'finance.invoice.view',
				),
			)
		);

		$engine = $this->make_engine( $registry, $roles );

		$this->assertFalse( $engine->can( 5, 'finance.invoice.view' ) );
		$this->assertTrue( $engine->can( 5, 'finance.invoice.view', array( 'owner_id' => 5 ) ) );
		$this->assertFalse( $engine->can( 5, 'finance.invoice.view', array( 'owner_id' => 99 ) ) );
	}

	public function test_any_scope_match_across_roles_grants_access(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn(
			array(
				'id'             => 30,
				'permission_key' => 'finance.invoice.view',
			)
		);

		$roles = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn(
			array(
				array( 'id' => 1, 'slug' => 'role_a' ),
				array( 'id' => 2, 'slug' => 'role_b' ),
			)
		);
		$roles->method( 'get_permissions' )->willReturnMap(
			array(
				array(
					1,
					array(
						array(
							'permission_id'  => 30,
							'scope_type'     => 'own',
							'scope_value'    => null,
							'permission_key' => 'finance.invoice.view',
						),
					),
				),
				array(
					2,
					array(
						array(
							'permission_id'  => 30,
							'scope_type'     => 'all',
							'scope_value'    => null,
							'permission_key' => 'finance.invoice.view',
						),
					),
				),
			)
		);

		$engine = $this->make_engine( $registry, $roles );

		$this->assertTrue( $engine->can( 5, 'finance.invoice.view' ) );
	}

	public function test_denies_guest_user(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$roles = $this->createMock( RoleManager::class );
		$engine = $this->make_engine( $registry, $roles );

		$this->assertFalse( $engine->can( 0, 'core.panel.access' ) );
	}
}
