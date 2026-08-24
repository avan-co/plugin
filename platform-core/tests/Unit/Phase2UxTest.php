<?php
/**
 * Phase 2 visual and landing page tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class Phase2UxTest
 */
class Phase2UxTest extends TestCase {

	/**
	 * Landing page uses condensed marketing structure.
	 */
	public function test_landing_page_structure(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-theme/front-page.php' );

		$this->assertStringContainsString( 'mpp-home-outcomes', $source );
		$this->assertStringContainsString( 'RegistrationHandler::is_enabled', $source );
		$this->assertStringNotContainsString( 'mpp-home-arch', $source );
	}

	/**
	 * Self-hosted fonts replace Google Fonts enqueue.
	 */
	public function test_self_hosted_fonts(): void {
		$functions = file_get_contents( dirname( __DIR__, 3 ) . '/platform-theme/functions.php' );

		$this->assertStringContainsString( 'assets/css/fonts.css', $functions );
		$this->assertStringNotContainsString( 'fonts.googleapis.com', $functions );
		$this->assertFileExists( dirname( __DIR__, 3 ) . '/platform-theme/assets/css/fonts.css' );
	}

	/**
	 * Error page helper provides structured layout.
	 */
	public function test_error_page_helper(): void {
		$helper = dirname( __DIR__, 3 ) . '/platform-theme/inc/error-page.php';

		$this->assertFileExists( $helper );
		$this->assertStringContainsString( 'function platform_render_error_page', file_get_contents( $helper ) );
	}
}
