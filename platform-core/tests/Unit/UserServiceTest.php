<?php
/**
 * UserService tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use MPP\ACL\RoleManager;
use MPP\Services\UserService;
use PHPUnit\Framework\TestCase;

/**
 * Class UserServiceTest
 */
class UserServiceTest extends TestCase {

	/**
	 * format_user maps WP_User fields and platform roles.
	 */
	public function test_format_user_returns_expected_shape(): void {
		$roles = $this->getMockBuilder( RoleManager::class )->disableOriginalConstructor()->getMock();
		$roles->method( 'get_user_roles' )->willReturn(
			array(
				array(
					'id'   => 3,
					'name' => 'Manager',
					'slug' => 'manager',
				),
			)
		);

		$user                    = new \stdClass();
		$user->ID                = 12;
		$user->user_login        = 'ali';
		$user->display_name      = 'Ali Rezaei';
		$user->user_email        = 'ali@example.com';
		$user->user_status       = 0;
		$user->user_registered   = '2024-01-01 00:00:00';

		$service   = new UserService( $roles );
		$formatted = $service->format_user( $user );

		$this->assertSame( 12, $formatted['id'] );
		$this->assertSame( 'ali', $formatted['username'] );
		$this->assertSame( 'active', $formatted['status'] );
		$this->assertCount( 1, $formatted['platform_roles'] );
		$this->assertSame( 'Manager', $formatted['platform_roles'][0]['name'] );
	}

	/**
	 * format_user marks non-zero user_status as inactive.
	 */
	public function test_format_user_marks_inactive_status(): void {
		$roles = $this->getMockBuilder( RoleManager::class )->disableOriginalConstructor()->getMock();
		$roles->method( 'get_user_roles' )->willReturn( array() );

		$user                  = new \stdClass();
		$user->ID              = 4;
		$user->user_login      = 'inactive-user';
		$user->display_name    = 'Inactive User';
		$user->user_email      = 'inactive@example.com';
		$user->user_status     = 1;
		$user->user_registered = '2024-01-01 00:00:00';

		$service   = new UserService( $roles );
		$formatted = $service->format_user( $user );

		$this->assertSame( 'inactive', $formatted['status'] );
	}

	/**
	 * build_status_filter returns null for invalid status values.
	 */
	public function test_build_status_filter_ignores_invalid_status(): void {
		$roles   = $this->getMockBuilder( RoleManager::class )->disableOriginalConstructor()->getMock();
		$service = new UserService( $roles );
		$method  = new \ReflectionMethod( UserService::class, 'build_status_filter' );
		$method->setAccessible( true );

		$this->assertNull( $method->invoke( $service, array( 'status' => 'pending' ) ) );
		$this->assertIsCallable( $method->invoke( $service, array( 'status' => 'active' ) ) );
	}
}
