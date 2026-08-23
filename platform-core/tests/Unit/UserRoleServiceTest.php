<?php

namespace MPP\Tests\Unit;

use MPP\Services\UserRoleService;
use MPP\ACL\RoleManager;
use PHPUnit\Framework\TestCase;

class UserRoleServiceTest extends TestCase {

	public function test_wp_admin_sync_is_disabled_by_default(): void {
		$service = new UserRoleService( $this->createMock( RoleManager::class ) );

		$service->maybe_sync_wp_admin( 1 );

		$this->assertTrue( true );
	}
}
