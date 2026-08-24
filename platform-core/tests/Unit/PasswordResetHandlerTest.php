<?php
/**
 * PasswordResetHandler tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use Brain\Monkey\Functions;
use MPP\Auth\PasswordResetHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class PasswordResetHandlerTest
 */
class PasswordResetHandlerTest extends TestCase {

	/**
	 * Reset globals between tests.
	 */
	protected function setUp(): void {
		parent::setUp();
		$_POST                       = array();
		$_SERVER['REQUEST_METHOD']   = 'GET';
		$GLOBALS['mpp_test_nonce_valid'] = true;
		unset( $GLOBALS['mpp_forgot_password_error'], $GLOBALS['mpp_forgot_password_success'] );
	}

	/**
	 * nonce_field renders the expected field name.
	 */
	public function test_nonce_field_renders_expected_name(): void {
		$field = PasswordResetHandler::nonce_field();

		$this->assertStringContainsString( 'mpp_forgot_password_nonce', $field );
		$this->assertSame( 'mpp_forgot_password', PasswordResetHandler::NONCE_ACTION );
	}

	/**
	 * handle rejects empty login values.
	 */
	public function test_handle_rejects_empty_login(): void {
		$_SERVER['REQUEST_METHOD']        = 'POST';
		$_POST['mpp_forgot_password']     = '1';
		$_POST['mpp_forgot_password_nonce'] = 'token';
		$_POST['user_login']              = '';

		$handler = new PasswordResetHandler();
		$handler->handle();

		$this->assertArrayHasKey( 'mpp_forgot_password_error', $GLOBALS );
		$this->assertStringContainsString( 'username or email', $GLOBALS['mpp_forgot_password_error'] );
	}

	/**
	 * handle rejects invalid security tokens.
	 */
	public function test_handle_rejects_invalid_nonce(): void {
		$GLOBALS['mpp_test_nonce_valid'] = false;
		$_SERVER['REQUEST_METHOD']       = 'POST';
		$_POST['mpp_forgot_password']    = '1';
		$_POST['mpp_forgot_password_nonce'] = 'bad';
		$_POST['user_login']             = 'demo@example.com';

		$handler = new PasswordResetHandler();
		$handler->handle();

		$this->assertArrayHasKey( 'mpp_forgot_password_error', $GLOBALS );
		$this->assertStringContainsString( 'security token', $GLOBALS['mpp_forgot_password_error'] );
	}

	/**
	 * handle returns a generic success message for unknown accounts.
	 */
	public function test_handle_sets_success_for_unknown_account(): void {
		Functions\when( 'get_user_by' )->justReturn( false );

		$_SERVER['REQUEST_METHOD']        = 'POST';
		$_POST['mpp_forgot_password']     = '1';
		$_POST['mpp_forgot_password_nonce'] = 'token';
		$_POST['user_login']              = 'missing@example.com';

		$handler = new PasswordResetHandler();
		$handler->handle();

		$this->assertArrayHasKey( 'mpp_forgot_password_success', $GLOBALS );
		$this->assertArrayNotHasKey( 'mpp_forgot_password_error', $GLOBALS );
	}
}
