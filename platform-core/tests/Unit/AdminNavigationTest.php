<?php
/**
 * Admin navigation structure tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class AdminNavigationTest
 */
class AdminNavigationTest extends TestCase {

	/**
	 * Roles and permissions share a navigation group.
	 */
	public function test_roles_permissions_navigation_group(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-core/includes/functions.php' );

		$this->assertStringContainsString( 'Roles & Permissions', $source );
		$this->assertMatchesRegularExpression(
			"/'Roles & Permissions'[\s\S]*'route'\s*=>\s*'app\/admin\/roles'[\s\S]*'route'\s*=>\s*'app\/admin\/permissions'/",
			$source
		);
	}
}
