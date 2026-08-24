<?php
/**
 * PasswordResetHandler tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use MPP\Auth\PasswordResetHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class PasswordResetHandlerTest
 */
class PasswordResetHandlerTest extends TestCase {

	/**
	 * nonce_field renders the expected field name.
	 */
	public function test_nonce_field_renders_expected_name(): void {
		if ( ! function_exists( 'wp_nonce_field' ) ) {
			/**
			 * Minimal nonce field stub for PHPUnit.
			 *
			 * @param string $action Action name.
			 * @param string $name   Field name.
			 * @param bool   $referer Include referer.
			 * @param bool   $echo    Echo output.
			 * @return string
			 */
			function wp_nonce_field( $action, $name, $referer = true, $echo = true ) {
				unset( $referer, $echo );
				return '<input type="hidden" name="' . $name . '" value="token" data-action="' . $action . '">';
			}
		}

		$field = PasswordResetHandler::nonce_field();

		$this->assertStringContainsString( 'mpp_forgot_password_nonce', $field );
		$this->assertSame( 'mpp_forgot_password', PasswordResetHandler::NONCE_ACTION );
	}
}
