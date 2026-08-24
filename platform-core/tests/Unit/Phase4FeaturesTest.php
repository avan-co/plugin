<?php
/**
 * Phase 4 feature wiring tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class Phase4FeaturesTest
 */
class Phase4FeaturesTest extends TestCase {

	/**
	 * Reset password route and handler exist.
	 */
	public function test_reset_password_flow(): void {
		$router = file_get_contents( dirname( __DIR__, 3 ) . '/platform-core/includes/Core/Router.php' );
		$handler = file_get_contents( dirname( __DIR__, 3 ) . '/platform-core/includes/Auth/PasswordResetHandler.php' );
		$functions = file_get_contents( dirname( __DIR__, 3 ) . '/platform-core/includes/functions.php' );

		$this->assertStringContainsString( 'reset-password', $router );
		$this->assertStringContainsString( 'get_reset_context', $handler );
		$this->assertStringContainsString( 'mpp_reset_password_url', $functions );
		$this->assertFileExists( dirname( __DIR__, 3 ) . '/platform-theme/templates/page-reset-password.php' );
	}

	/**
	 * Impact preview service methods are wired.
	 */
	public function test_impact_preview_service(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-core/includes/Services/EffectiveAccessService.php' );

		$this->assertStringContainsString( 'preview_permission_sync_impact', $source );
		$this->assertStringContainsString( 'preview_role_delete_impact', $source );
		$this->assertStringContainsString( 'get_routes_for_permission_key', $source );
	}

	/**
	 * Dark mode preference script is enqueued.
	 */
	public function test_dark_mode_assets(): void {
		$functions = file_get_contents( dirname( __DIR__, 3 ) . '/platform-theme/functions.php' );
		$tokens    = file_get_contents( dirname( __DIR__, 3 ) . '/platform-theme/assets/css/tokens.css' );

		$this->assertStringContainsString( 'theme-preference.js', $functions );
		$this->assertStringContainsString( '[data-theme="dark"]', $tokens );
	}

	/**
	 * Example module contributes manager dashboard data.
	 */
	public function test_example_manager_module(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-example/includes/ExampleModule.php' );

		$this->assertStringContainsString( "'panel'      => 'manager'", $source );
		$this->assertStringContainsString( 'mpp_manager_dashboard_stats', $source );
		$this->assertStringContainsString( 'mpp_manager_pending_items', $source );
	}
}
