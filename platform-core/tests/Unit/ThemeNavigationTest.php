<?php
/**
 * Theme navigation and accessibility wiring tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class ThemeNavigationTest
 */
class ThemeNavigationTest extends TestCase {

	/**
	 * Mobile nav toggle targets the sidebar landmark.
	 */
	public function test_header_nav_toggle_controls_sidebar(): void {
		$root   = dirname( __DIR__, 3 );
		$header = file_get_contents( $root . '/platform-theme/header.php' );

		$this->assertStringContainsString( 'aria-controls="mpp-sidebar"', $header );
		$this->assertStringContainsString( 'platform_route_has_sidebar', $header );
	}

	/**
	 * Panel shell exposes sidebar id for navigation script.
	 */
	public function test_panel_shell_sidebar_has_id(): void {
		$root   = dirname( __DIR__, 3 );
		$shell  = file_get_contents( $root . '/platform-theme/inc/design-system/class-panel-shell.php' );

		$this->assertStringContainsString( 'id="mpp-sidebar"', $shell );
	}

	/**
	 * Language switcher renders on platform routes and exposes text direction.
	 */
	public function test_language_switcher_platform_coverage(): void {
		$root = dirname( __DIR__, 3 );
		$src  = file_get_contents( $root . '/platform-theme/inc/design-system/class-language-switcher.php' );

		$this->assertStringContainsString( 'function get_text_direction', $src );
		$this->assertStringContainsString( 'is_front_page()', $src );
	}
}
