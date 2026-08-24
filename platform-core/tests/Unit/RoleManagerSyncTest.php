<?php
/**
 * RoleManager permission sync tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use MPP\ACL\RoleManager;
use PHPUnit\Framework\TestCase;

/**
 * Class RoleManagerSyncTest
 */
class RoleManagerSyncTest extends TestCase {

	/**
	 * sync_permissions grants, preserves, and revokes as expected.
	 */
	public function test_sync_permissions_grants_and_revokes(): void {
		$roles = $this->getMockBuilder( RoleManager::class )
			->disableOriginalConstructor()
			->onlyMethods( array( 'get_permissions', 'assign_permission', 'revoke_permission' ) )
			->getMock();

		$roles->method( 'get_permissions' )->willReturn(
			array(
				array( 'permission_id' => 1 ),
				array( 'permission_id' => 2 ),
			)
		);

		$roles->expects( $this->once() )
			->method( 'assign_permission' )
			->with( 5, 3, 'all' )
			->willReturn( true );

		$roles->expects( $this->once() )
			->method( 'revoke_permission' )
			->with( 5, 2 )
			->willReturn( true );

		$result = $roles->sync_permissions( 5, array( 1, 3 ) );

		$this->assertSame( 1, $result['granted'] );
		$this->assertSame( 1, $result['revoked'] );
	}
}
