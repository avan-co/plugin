<?php
/**
 * Phase 1 UX wiring tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class Phase1UxTest
 */
class Phase1UxTest extends TestCase {

	/**
	 * Post-login redirect helper respects configured dashboard.
	 */
	public function test_post_login_redirect_helper_exists(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/functions.php' );

		$this->assertStringContainsString( 'function mpp_get_post_login_redirect_url', $source );
		$this->assertStringContainsString( 'default_dashboard', $source );
	}

	/**
	 * Platform settings wire remember-me and date format hooks.
	 */
	public function test_platform_settings_consumption_hooks(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Settings/PlatformSettings.php' );

		$this->assertStringContainsString( 'filter_auth_cookie_expiration', $source );
		$this->assertStringContainsString( 'filter_date_format', $source );
	}

	/**
	 * Theme header uses account menu instead of inline logout.
	 */
	public function test_theme_account_menu_wiring(): void {
		$root = dirname( __DIR__, 3 );

		$this->assertStringContainsString( 'platform_render_account_menu', file_get_contents( $root . '/platform-theme/header.php' ) );
		$this->assertStringContainsString( 'function platform_render_account_menu', file_get_contents( $root . '/platform-theme/functions.php' ) );
		$this->assertStringContainsString( 'function platform_get_current_panel', file_get_contents( $root . '/platform-theme/functions.php' ) );
	}
}
