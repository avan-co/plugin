<?php
/**
 * Platform user registration handler.
 *
 * @package PlatformCore
 */

namespace MPP\Auth;

use MPP\Database\Installer;
use MPP\Services\AuditLogService;
use MPP\Services\UserRoleService;
use MPP\Settings\PlatformSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Class RegistrationHandler
 */
class RegistrationHandler {

	const NONCE_ACTION = 'mpp_register';

	/**
	 * @var UserRoleService
	 */
	private $user_roles;

	/**
	 * @var AuditLogService
	 */
	private $audit;

	/**
	 * @var PlatformSettings
	 */
	private $settings;

	public function __construct( UserRoleService $user_roles, AuditLogService $audit, PlatformSettings $settings ) {
		$this->user_roles = $user_roles;
		$this->audit      = $audit;
		$this->settings   = $settings;
	}

	public function register() {
		add_action( 'init', array( $this, 'handle' ), 5 );
	}

	/**
	 * Whether registration is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		if ( function_exists( 'mpp' ) ) {
			return mpp()->get( PlatformSettings::class )->is_registration_enabled();
		}

		/**
		 * Filter whether platform registration is enabled.
		 *
		 * @param bool $enabled Whether registration is open.
		 */
		return (bool) apply_filters( 'mpp_registration_enabled', true );
	}

	public function handle() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['mpp_register'] ) ) {
			return;
		}

		if ( ! self::is_enabled() ) {
			$GLOBALS['mpp_register_error'] = __( 'Registration is currently disabled.', 'platform-core' );
			return;
		}

		if ( ! isset( $_POST['mpp_register_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mpp_register_nonce'] ) ), self::NONCE_ACTION ) ) {
			$GLOBALS['mpp_register_error'] = __( 'Invalid security token.', 'platform-core' );
			return;
		}

		$username = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ), true ) : '';
		$email    = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
		$password = isset( $_POST['user_pass'] ) ? (string) wp_unslash( $_POST['user_pass'] ) : '';
		$confirm  = isset( $_POST['user_pass_confirm'] ) ? (string) wp_unslash( $_POST['user_pass_confirm'] ) : '';

		if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
			$GLOBALS['mpp_register_error'] = __( 'All fields are required.', 'platform-core' );
			return;
		}

		if ( $password !== $confirm ) {
			$GLOBALS['mpp_register_error'] = __( 'Passwords do not match.', 'platform-core' );
			return;
		}

		if ( ! is_email( $email ) ) {
			$GLOBALS['mpp_register_error'] = __( 'Please enter a valid email address.', 'platform-core' );
			return;
		}

		if ( username_exists( $username ) ) {
			$GLOBALS['mpp_register_error'] = __( 'This username is already taken.', 'platform-core' );
			return;
		}

		if ( email_exists( $email ) ) {
			$GLOBALS['mpp_register_error'] = __( 'This email is already registered.', 'platform-core' );
			return;
		}

		if ( strlen( $password ) < 8 ) {
			$GLOBALS['mpp_register_error'] = __( 'Password must be at least 8 characters.', 'platform-core' );
			return;
		}

		Installer::ensure_defaults();

		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			$GLOBALS['mpp_register_error'] = $user_id->get_error_message();
			return;
		}

		$default_role = $this->settings->get( 'default_platform_role', 'platform_user' );
		$this->user_roles->assign_role_by_slug( (int) $user_id, $default_role );

		if ( empty( $this->user_roles->get_roles( (int) $user_id ) ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_delete_user( (int) $user_id );
			$GLOBALS['mpp_register_error'] = __( 'Registration could not be completed. Please contact the site administrator.', 'platform-core' );
			return;
		}

		$this->audit->log(
			'user.registered',
			'user',
			$user_id,
			array(),
			array(
				'username' => $username,
				'email'    => $email,
			),
			(int) $user_id
		);

		wp_set_current_user( (int) $user_id );
		wp_set_auth_cookie( (int) $user_id, false, is_ssl() );

		$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : mpp_route_url( 'app/user' );
		$redirect = wp_validate_redirect( $redirect, mpp_route_url( 'app/user' ) );

		wp_safe_redirect( $redirect );
		exit;
	}

	public static function nonce_field() {
		return wp_nonce_field( self::NONCE_ACTION, 'mpp_register_nonce', true, false );
	}
}
