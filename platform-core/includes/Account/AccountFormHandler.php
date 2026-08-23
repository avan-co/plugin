<?php
/**
 * Account form handler (profile and settings).
 *
 * @package PlatformCore
 */

namespace MPP\Account;

use MPP\ACL\AclEngine;
use MPP\Services\AuditLogService;

defined( 'ABSPATH' ) || exit;

/**
 * Class AccountFormHandler
 */
class AccountFormHandler {

	const NONCE_ACTION = 'mpp_account_action';

	/**
	 * @var AclEngine
	 */
	private $acl;

	/**
	 * @var AuditLogService
	 */
	private $audit;

	public function __construct( AclEngine $acl, AuditLogService $audit ) {
		$this->acl   = $acl;
		$this->audit = $audit;
	}

	public function register() {
		add_action( 'init', array( $this, 'handle' ), 5 );
	}

	public function handle() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['mpp_account_action'] ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'Access denied.', 'platform-core' ), '', array( 'response' => 403 ) );
		}

		if ( ! isset( $_POST['mpp_account_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mpp_account_nonce'] ) ), self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Invalid security token.', 'platform-core' ), '', array( 'response' => 403 ) );
		}

		$action   = sanitize_key( wp_unslash( $_POST['mpp_account_action'] ) );
		$redirect = isset( $_POST['mpp_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['mpp_redirect'] ) ) : mpp_route_url( 'profile' );
		$redirect = wp_validate_redirect( $redirect, mpp_route_url( 'profile' ) );
		$result   = $this->dispatch( $action );

		wp_safe_redirect(
			add_query_arg(
				array(
					'mpp_notice'  => $result['success'] ? 'success' : 'error',
					'mpp_message' => $result['message'] ?? '',
				),
				$redirect
			)
		);
		exit;
	}

	/**
	 * @param string $action Action slug.
	 * @return array<string, mixed>
	 */
	private function dispatch( $action ) {
		switch ( $action ) {
			case 'update_profile':
				return $this->update_profile();
			case 'update_settings':
				return $this->update_settings();
			default:
				return array( 'success' => false, 'message' => __( 'Unknown action.', 'platform-core' ) );
		}
	}

	private function update_profile() {
		if ( ! $this->acl->can( get_current_user_id(), 'core.profile.edit' ) ) {
			return array( 'success' => false, 'message' => __( 'Permission denied.', 'platform-core' ) );
		}

		$user_id = get_current_user_id();
		$before  = array(
			'display_name' => get_userdata( $user_id )->display_name,
			'email'        => get_userdata( $user_id )->user_email,
		);

		$data = array( 'ID' => $user_id );

		if ( isset( $_POST['display_name'] ) ) {
			$data['display_name'] = sanitize_text_field( wp_unslash( $_POST['display_name'] ) );
		}

		if ( isset( $_POST['email'] ) ) {
			$data['user_email'] = sanitize_email( wp_unslash( $_POST['email'] ) );
		}

		$result = wp_update_user( $data );

		if ( is_wp_error( $result ) ) {
			return array( 'success' => false, 'message' => $result->get_error_message() );
		}

		$after = array(
			'display_name' => get_userdata( $user_id )->display_name,
			'email'        => get_userdata( $user_id )->user_email,
		);

		$this->audit->log( 'profile.updated', 'user', $user_id, $before, $after );

		return array( 'success' => true, 'message' => __( 'Profile updated.', 'platform-core' ) );
	}

	private function update_settings() {
		if ( ! $this->acl->can( get_current_user_id(), 'core.settings.edit' ) ) {
			return array( 'success' => false, 'message' => __( 'Permission denied.', 'platform-core' ) );
		}

		$user_id = get_current_user_id();
		$before  = array(
			'notifications' => (bool) get_user_meta( $user_id, 'mpp_notifications', true ),
		);

		$notifications = ! empty( $_POST['mpp_notifications'] ) ? 1 : 0;
		update_user_meta( $user_id, 'mpp_notifications', $notifications );

		$after = array( 'notifications' => (bool) $notifications );
		$this->audit->log( 'settings.updated', 'user', $user_id, $before, $after );

		return array( 'success' => true, 'message' => __( 'Settings saved.', 'platform-core' ) );
	}

	public static function nonce_field() {
		return wp_nonce_field( self::NONCE_ACTION, 'mpp_account_nonce', true, false );
	}
}
