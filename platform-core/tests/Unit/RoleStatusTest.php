<?php

namespace MPP\Tests\Unit;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\ACL\ScopeResolver;
use PHPUnit\Framework\TestCase;

class RoleStatusTest extends TestCase {

	public function test_inactive_roles_are_excluded_from_user_role_query(): void {
		$roles = $this->createMock( RoleManager::class );
		$roles->method( 'get_user_roles' )->willReturn( array() );

		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'find_by_key' )->willReturn(
			array(
				'id'             => 1,
				'permission_key' => 'core.panel.access',
			)
		);

		$engine = new AclEngine( $registry, $roles, new ScopeResolver() );

		$this->assertFalse( $engine->can( 1, 'core.panel.access' ) );
	}
}
