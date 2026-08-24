<?php
/**
 * Installer migration tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class InstallerTest
 */
class InstallerTest extends TestCase {

	/**
	 * DB version bump includes scope_type repair migration.
	 */
	public function test_installer_repairs_invalid_scope_types(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Database/Installer.php' );

		$this->assertStringContainsString( 'repair_invalid_scope_types', $source );
		$this->assertStringContainsString( "scope_type = '0'", $source );
		$this->assertStringContainsString( 'is_system = 1', $source );
	}

	/**
	 * Router error routes expose HTTP status metadata.
	 */
	public function test_router_error_routes_define_status_codes(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Core/Router.php' );

		$this->assertStringContainsString( "'status'   => 403", $source );
		$this->assertStringContainsString( "'status'   => 404", $source );
		$this->assertStringContainsString( '$definition[\'status\']', $source );
	}
}
