<?php
/**
 * Platform password reset request and completion handler.
 *
 * @package PlatformCore
 */

namespace MPP\Auth;

defined( 'ABSPATH' ) || exit;

/**
 * Class PasswordResetHandler
 */
class PasswordResetHandler {

	const FORGOT_NONCE_ACTION = 'mpp_forgot_password';

	const RESET_NONCE_ACTION = 'mpp_reset_password';

	/**
	 * Register hooks.
	 */
	public function register() {
		add_action( 'init', array( $this, 'handle' ), 5 );
		add_filter( 'retrieve_password_message', array( $this, 'filter_reset_email_message' ), 10, 4 );
	}

	/**
	 * Handle forgot-password and reset-password form submissions.
	 */
	public function handle() {
		$this->handle_forgot_request();
		$this->handle_reset_submission();
	}

	/**
	 * Handle forgot-password form submission.
	 */
	private function handle_forgot_request() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['mpp_forgot_password'] ) ) {
			return;
		}

		if ( ! isset( $_POST['mpp_forgot_password_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mpp_forgot_password_nonce'] ) ), self::FORGOT_NONCE_ACTION ) ) {
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
	 * Handle reset-password form submission.
	 */
	private function handle_reset_submission() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['mpp_reset_password'] ) ) {
			return;
		}

		if ( ! isset( $_POST['mpp_reset_password_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mpp_reset_password_nonce'] ) ), self::RESET_NONCE_ACTION ) ) {
			$GLOBALS['mpp_reset_password_error'] = __( 'Invalid security token.', 'platform-core' );
			return;
		}

		$key   = isset( $_POST['reset_key'] ) ? sanitize_text_field( wp_unslash( $_POST['reset_key'] ) ) : '';
		$login = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';
		$pass  = isset( $_POST['pass1'] ) ? (string) wp_unslash( $_POST['pass1'] ) : '';
		$pass2 = isset( $_POST['pass2'] ) ? (string) wp_unslash( $_POST['pass2'] ) : '';

		if ( '' === $key || '' === $login ) {
			$GLOBALS['mpp_reset_password_error'] = __( 'Invalid reset link.', 'platform-core' );
			return;
		}

		if ( '' === $pass || '' === $pass2 ) {
			$GLOBALS['mpp_reset_password_error'] = __( 'Please enter and confirm your new password.', 'platform-core' );
			return;
		}

		if ( $pass !== $pass2 ) {
			$GLOBALS['mpp_reset_password_error'] = __( 'Passwords do not match.', 'platform-core' );
			return;
		}

		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			$GLOBALS['mpp_reset_password_error'] = $user->get_error_message();
			return;
		}

		reset_password( $user, $pass );

		wp_safe_redirect(
			add_query_arg(
				'password_reset',
				'1',
				mpp_route_url( 'login' )
			)
		);
		exit;
	}

	/**
	 * Point password reset emails to the themed route.
	 *
	 * @param string          $message    Email body.
	 * @param string          $key        Reset key.
	 * @param string          $user_login User login.
	 * @param \WP_User        $user_data  User object.
	 * @return string
	 */
	public function filter_reset_email_message( $message, $key, $user_login, $user_data ) {
		if ( ! function_exists( 'mpp_reset_password_url' ) ) {
			return $message;
		}

		$themed_url = mpp_reset_password_url( $key, $user_login );

		$default_url = network_site_url(
			'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user_login ),
			'login'
		);

		$message = str_replace( $default_url, $themed_url, $message );

		if ( strpos( $message, $themed_url ) === false ) {
			$message = preg_replace(
				'#https?://[^\s<"]+wp-login\.php\?action=rp[^\s<"]*#',
				esc_url( $themed_url ),
				$message
			);
		}

		return $message;
	}

	/**
	 * Validate reset key from the current request.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_reset_context() {
		$key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
		$login = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';

		if ( '' === $key || '' === $login ) {
			return array(
				'valid'   => false,
				'key'     => $key,
				'login'   => $login,
				'message' => __( 'Invalid reset link.', 'platform-core' ),
			);
		}

		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			return array(
				'valid'   => false,
				'key'     => $key,
				'login'   => $login,
				'message' => $user->get_error_message(),
			);
		}

		return array(
			'valid'   => true,
			'key'     => $key,
			'login'   => $login,
			'user_id' => (int) $user->ID,
		);
	}

	/**
	 * Get forgot-password nonce field HTML.
	 *
	 * @return string
	 */
	public static function forgot_nonce_field() {
		return wp_nonce_field( self::FORGOT_NONCE_ACTION, 'mpp_forgot_password_nonce', true, false );
	}

	/**
	 * Get reset-password nonce field HTML.
	 *
	 * @return string
	 */
	public static function reset_nonce_field() {
		return wp_nonce_field( self::RESET_NONCE_ACTION, 'mpp_reset_password_nonce', true, false );
	}

	/**
	 * Backwards-compatible alias.
	 *
	 * @return string
	 */
	public static function nonce_field() {
		return self::forgot_nonce_field();
	}

	/**
	 * Backwards-compatible constant alias.
	 */
	const NONCE_ACTION = 'mpp_forgot_password';
}
