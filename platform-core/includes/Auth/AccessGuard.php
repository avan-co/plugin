<?php
/**
 * Backend access guard helpers.
 *
 * @package PlatformCore
 */

namespace MPP\Auth;

use MPP\ACL\AclEngine;

defined( 'ABSPATH' ) || exit;

/**
 * Class AccessGuard
 */
class AccessGuard {

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
		add_action( 'rest_api_init', array( $this, 'register_rest_filters' ) );
	}

	/**
	 * Register REST API permission filters.
	 */
	public function register_rest_filters() {
		// Reserved for module-specific REST guards via hooks.
	}

	/**
	 * Require a permission or terminate with 403.
	 *
	 * @param string               $permission Permission key.
	 * @param array<string, mixed> $context    Context.
	 */
	public function require_permission( $permission, array $context = array() ) {
		if ( ! $this->acl->can( get_current_user_id(), $permission, $context ) ) {
			wp_die(
				esc_html__( 'You do not have permission to access this resource.', 'platform-core' ),
				esc_html__( 'Access Denied', 'platform-core' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * REST API permission callback factory.
	 *
	 * @param string $permission Permission key.
	 * @return callable
	 */
	public function rest_permission( $permission ) {
		return function ( $request ) use ( $permission ) {
			if ( ! is_user_logged_in() ) {
				return new \WP_Error( 'rest_not_logged_in', __( 'You must be logged in.', 'platform-core' ), array( 'status' => 401 ) );
			}

			if ( ! $this->acl->can( get_current_user_id(), $permission ) ) {
				return new \WP_Error( 'rest_forbidden', __( 'You do not have permission.', 'platform-core' ), array( 'status' => 403 ) );
			}

			return true;
		};
	}
}
