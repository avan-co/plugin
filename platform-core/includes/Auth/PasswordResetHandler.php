<?php
/**
 * Platform password reset request handler.
 *
 * @package PlatformCore
 */

namespace MPP\Auth;

defined( 'ABSPATH' ) || exit;

/**
 * Class PasswordResetHandler
 */
class PasswordResetHandler {

	const NONCE_ACTION = 'mpp_forgot_password';

	/**
	 * Register hooks.
	 */
	public function register() {
		add_action( 'init', array( $this, 'handle' ), 5 );
	}

	/**
	 * Handle forgot-password form submission.
	 */
	public function handle() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['mpp_forgot_password'] ) ) {
			return;
		}

		if ( ! isset( $_POST['mpp_forgot_password_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mpp_forgot_password_nonce'] ) ), self::NONCE_ACTION ) ) {
			$GLOBALS['mpp_forgot_password_error'] = __( 'Invalid security token.', 'platform-core' );
			return;
		}

		$login = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';

		if ( '' === $login ) {
			$GLOBALS['mpp_forgot_password_error'] = __( 'Please enter your username or email address.', 'platform-core' );
			return;
		}

		$user = get_user_by( is_email( $login ) ? 'email' : 'login', is_email( $login ) ? sanitize_email( $login ) : $login );

		if ( $user ) {
			$result = retrieve_password( $user->user_login );

			if ( is_wp_error( $result ) ) {
				$GLOBALS['mpp_forgot_password_error'] = $result->get_error_message();
				return;
			}
		}

		$GLOBALS['mpp_forgot_password_success'] = __( 'If an account exists for that username or email, password reset instructions have been sent.', 'platform-core' );
	}

	/**
	 * Get forgot-password nonce field HTML.
	 *
	 * @return string
	 */
	public static function nonce_field() {
		return wp_nonce_field( self::NONCE_ACTION, 'mpp_forgot_password_nonce', true, false );
	}
}
