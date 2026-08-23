<?php
/**
 * WordPress authentication integration.
 *
 * @package PlatformCore
 */

namespace MPP\Auth;

use MPP\ACL\AclEngine;

defined( 'ABSPATH' ) || exit;

/**
 * Class AuthIntegration
 */
class AuthIntegration {

	/**
	 * ACL engine.
	 *
	 * @var AclEngine
	 */
	private $acl;

	/**
	 * Constructor.
	 *
	 * @param AclEngine $acl ACL engine.
	 */
	public function __construct( AclEngine $acl ) {
		$this->acl = $acl;
	}

	/**
	 * Register hooks.
	 */
	public function register() {
		add_action( 'wp_login', array( $this, 'on_login' ), 10, 2 );
		add_action( 'wp_logout', array( $this, 'on_logout' ) );
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 10, 3 );
		add_action( 'init', array( $this, 'handle_login_form' ) );
		add_action( 'init', array( $this, 'handle_logout_request' ) );
	}

	/**
	 * Handle custom login form submission.
	 */
	public function handle_login_form() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( empty( $_POST['mpp_login'] ) ) {
			return;
		}

		if ( ! isset( $_POST['mpp_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mpp_login_nonce'] ) ), 'mpp_login' ) ) {
			return;
		}

		$username = isset( $_POST['log'] ) ? wp_unslash( $_POST['log'] ) : '';
		$username = is_string( $username ) ? trim( $username ) : '';

		if ( is_email( $username ) ) {
			$user = get_user_by( 'email', sanitize_email( $username ) );
			if ( $user ) {
				$username = $user->user_login;
			}
		} else {
			$username = sanitize_user( $username );
		}
		$password = isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : '';
		$remember = ! empty( $_POST['rememberme'] );

		$credentials = array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		);

		$user = wp_signon( $credentials, is_ssl() );

		if ( is_wp_error( $user ) ) {
			$GLOBALS['mpp_login_error'] = $user->get_error_message();
			return;
		}

		$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : mpp_route_url( 'app' );
		$redirect = wp_validate_redirect( $redirect, mpp_route_url( 'app' ) );
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle logout from platform routes.
	 */
	public function handle_logout_request() {
		if ( empty( $_GET['mpp_logout'] ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( mpp_route_url( 'login' ) );
			exit;
		}

		check_admin_referer( 'mpp_logout' );

		wp_logout();
		wp_safe_redirect( mpp_route_url( 'login' ) );
		exit;
	}

	/**
	 * Redirect after WordPress login.
	 *
	 * @param string           $redirect_to Redirect URL.
	 * @param string           $request     Requested redirect.
	 * @param \WP_User|\WP_Error $user      User object.
	 * @return string
	 */
	public function login_redirect( $redirect_to, $request, $user ) {
		if ( is_wp_error( $user ) ) {
			return $redirect_to;
		}

		if ( ! empty( $request ) ) {
			$validated = wp_validate_redirect( $request, mpp_route_url( 'app' ) );
			if ( $validated ) {
				return $validated;
			}
		}

		return mpp_route_url( 'app' );
	}

	/**
	 * Fires on successful login.
	 *
	 * @param string  $user_login Username.
	 * @param \WP_User $user      User object.
	 */
	public function on_login( $user_login, $user ) {
		$user_roles = mpp()->get( \MPP\Services\UserRoleService::class );
		$user_roles->maybe_sync_wp_admin( $user->ID );

		do_action( 'mpp_user_login', $user );
	}

	/**
	 * Fires on logout.
	 */
	public function on_logout() {
		do_action( 'mpp_user_logout' );
	}

	/**
	 * Get logout URL.
	 *
	 * @return string
	 */
	public static function logout_url() {
		return wp_nonce_url( add_query_arg( 'mpp_logout', '1', home_url( '/' ) ), 'mpp_logout' );
	}
}
