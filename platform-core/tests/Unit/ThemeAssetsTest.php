<?php
/**
 * Theme asset wiring tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class ThemeAssetsTest
 */
class ThemeAssetsTest extends TestCase {

	/**
	 * Responsive stylesheet is enqueued after panel styles.
	 */
	public function test_responsive_stylesheet_is_registered(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-theme/functions.php' );

		$this->assertStringContainsString( 'platform-theme-responsive', $source );
		$this->assertStringContainsString( '/assets/css/responsive.css', $source );
		$this->assertStringContainsString( 'platform-theme-panels', $source );
	}

	/**
	 * Auth pages share a common render helper.
	 */
	public function test_auth_page_helper_exists(): void {
		$this->assertFileExists( dirname( __DIR__, 3 ) . '/platform-theme/inc/auth-page.php' );

		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-theme/inc/auth-page.php' );
		$this->assertStringContainsString( 'function platform_render_auth_page', $source );
		$this->assertStringContainsString( 'mpp-card--login', $source );
	}

	/**
	 * Auth routes use minimal header behavior.
	 */
	public function test_auth_route_detection_is_available(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/functions.php' );

		$this->assertStringContainsString( 'function mpp_is_auth_route', $source );
		$this->assertStringContainsString( "'forgot-password'", $source );
	}
}
