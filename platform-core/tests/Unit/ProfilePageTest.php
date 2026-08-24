<?php
/**
 * Profile page wiring tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class ProfilePageTest
 */
class ProfilePageTest extends TestCase {

	/**
	 * Shared profile helper exists and matches wireframe structure.
	 */
	public function test_profile_page_helper_exists(): void {
		$root   = dirname( __DIR__, 3 );
		$helper = $root . '/platform-theme/inc/profile-page.php';

		$this->assertFileExists( $helper );

		$source = file_get_contents( $helper );
		$this->assertStringContainsString( 'function platform_render_profile_content', $source );
		$this->assertStringContainsString( 'mpp-profile-header', $source );
		$this->assertStringContainsString( 'mpp-profile-section', $source );
		$this->assertStringContainsString( 'mpp-profile-form__actions', $source );
		$this->assertStringContainsString( 'core.profile.edit', $source );
	}

	/**
	 * User and manager profile templates use the shared helper.
	 */
	public function test_profile_templates_use_shared_helper(): void {
		$root = dirname( __DIR__, 3 );

		foreach ( array( 'page-profile.php', 'page-manager-profile.php' ) as $template ) {
			$source = file_get_contents( $root . '/platform-theme/templates/' . $template );
			$this->assertStringContainsString( 'profile-page.php', $source );
			$this->assertStringContainsString( 'platform_render_profile_content', $source );
			$this->assertStringContainsString( 'mpp-card--profile', $source );
		}
	}

	/**
	 * Profile form handler validates required fields.
	 */
	public function test_profile_update_validates_required_fields(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Account/AccountFormHandler.php' );

		$this->assertStringContainsString( 'Display name is required.', $source );
		$this->assertStringContainsString( 'A valid email address is required.', $source );
	}

	/**
	 * Account notices use the design-system alert when available.
	 */
	public function test_account_notice_uses_ui_alert_helper(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/functions.php' );

		$this->assertStringContainsString( 'function mpp_render_account_notice', $source );
		$this->assertStringContainsString( 'platform_ui_alert', $source );
	}
}
