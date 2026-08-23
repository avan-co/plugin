<?php
/**
 * ACL management REST API controller.
 *
 * @package PlatformCore
 */

namespace MPP\API;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\Auth\AccessGuard;
use MPP\Services\UserRoleService;

defined( 'ABSPATH' ) || exit;

/**
 * Class AclController
 */
class AclController extends RestController {

	/**
	 * ACL engine.
	 *
	 * @var AclEngine
	 */
	private $acl;

	/**
	 * Role manager.
	 *
	 * @var RoleManager
	 */
	private $roles;

	/**
	 * Permission registry.
	 *
	 * @var PermissionRegistry
	 */
	private $registry;

	/**
	 * User role service.
	 *
	 * @var UserRoleService
	 */
	private $user_roles;

	/**
	 * Access guard.
	 *
	 * @var AccessGuard
	 */
	private $guard;

	/**
	 * Constructor.
	 *
	 * @param AclEngine          $acl        ACL engine.
	 * @param RoleManager        $roles      Role manager.
	 * @param PermissionRegistry $registry   Permission registry.
	 * @param UserRoleService    $user_roles User role service.
	 */
	public function __construct( AclEngine $acl, RoleManager $roles, PermissionRegistry $registry, UserRoleService $user_roles ) {
		$this->acl        = $acl;
		$this->roles      = $roles;
		$this->registry   = $registry;
		$this->user_roles = $user_roles;
		$this->guard      = new AccessGuard( $acl );
	}

	/**
	 * Register routes.
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/acl/check',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'check_permission' ),
				'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/acl/roles/(?P<role_id>[\d]+)/permissions',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_role_permissions' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'assign_role_permission' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'revoke_role_permission' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/acl/users/(?P<user_id>[\d]+)/roles',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_user_roles' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'assign_user_role' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'revoke_user_role' ),
					'permission_callback' => $this->guard->rest_permission( 'core.acl.manage' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/acl/me',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_current_user_acl' ),
				'permission_callback' => function () {
					return is_user_logged_in();
				},
			)
		);
	}

	/**
	 * Check a permission for the current user.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function check_permission( $request ) {
		$params     = $request->get_json_params();
		$permission = isset( $params['permission'] ) ? sanitize_text_field( $params['permission'] ) : '';
		$context    = isset( $params['context'] ) && is_array( $params['context'] ) ? $this->sanitize_context( $params['context'] ) : array();

		if ( empty( $permission ) || ! \MPP\ACL\Permission::is_valid_key( $permission ) ) {
			return new \WP_Error( 'invalid_permission', __( 'A valid permission key is required.', 'platform-core' ), array( 'status' => 400 ) );
		}

		$target_user_id = isset( $params['user_id'] ) ? (int) $params['user_id'] : get_current_user_id();

		if ( $target_user_id !== get_current_user_id() && ! $this->acl->can( get_current_user_id(), 'core.acl.manage' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot check permissions for other users.', 'platform-core' ), array( 'status' => 403 ) );
		}

		return rest_ensure_response(
			array(
				'allowed' => $this->acl->can( $target_user_id, $permission, $context ),
			)
		);
	}

	/**
	 * Get role permissions.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function get_role_permissions( $request ) {
		return rest_ensure_response( $this->roles->get_permissions( (int) $request['role_id'] ) );
	}

	/**
	 * Assign permission to role.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function assign_role_permission( $request ) {
		$params = $request->get_json_params();

		$permission_id = isset( $params['permission_id'] ) ? (int) $params['permission_id'] : 0;
		$scope_type    = isset( $params['scope_type'] ) ? sanitize_key( $params['scope_type'] ) : 'all';
		$scope_value   = isset( $params['scope_value'] ) ? $params['scope_value'] : null;

		if ( ! $permission_id || ! $this->registry->find_by_id( $permission_id ) ) {
			return new \WP_Error( 'invalid_data', __( 'A valid permission_id is required.', 'platform-core' ), array( 'status' => 400 ) );
		}

		$result = $this->roles->assign_permission( (int) $request['role_id'], $permission_id, $scope_type, $scope_value );

		if ( ! $result ) {
			return new \WP_Error( 'assign_failed', __( 'Could not assign permission. Check scope_type.', 'platform-core' ), array( 'status' => 400 ) );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Revoke permission from role.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function revoke_role_permission( $request ) {
		$params        = $request->get_json_params();
		$permission_id = isset( $params['permission_id'] ) ? (int) $params['permission_id'] : 0;

		if ( ! $permission_id ) {
			return new \WP_Error( 'invalid_data', __( 'permission_id is required.', 'platform-core' ), array( 'status' => 400 ) );
		}

		$result = $this->roles->revoke_permission( (int) $request['role_id'], $permission_id );

		return rest_ensure_response( array( 'success' => (bool) $result ) );
	}

	/**
	 * Get user roles.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function get_user_roles( $request ) {
		return rest_ensure_response( $this->user_roles->get_roles( (int) $request['user_id'] ) );
	}

	/**
	 * Assign role to user.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function assign_user_role( $request ) {
		$params  = $request->get_json_params();
		$role_id = isset( $params['role_id'] ) ? (int) $params['role_id'] : 0;

		if ( ! $role_id || ! $this->roles->find( $role_id ) ) {
			return new \WP_Error( 'invalid_data', __( 'A valid role_id is required.', 'platform-core' ), array( 'status' => 400 ) );
		}

		$result = $this->roles->assign_to_user( (int) $request['user_id'], $role_id );

		return rest_ensure_response( array( 'success' => (bool) $result ) );
	}

	/**
	 * Revoke role from user.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function revoke_user_role( $request ) {
		$params  = $request->get_json_params();
		$role_id = isset( $params['role_id'] ) ? (int) $params['role_id'] : 0;

		if ( ! $role_id ) {
			return new \WP_Error( 'invalid_data', __( 'role_id is required.', 'platform-core' ), array( 'status' => 400 ) );
		}

		$result = $this->roles->revoke_from_user( (int) $request['user_id'], $role_id );

		return rest_ensure_response( array( 'success' => (bool) $result ) );
	}

	/**
	 * Get current user ACL info.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_current_user_acl() {
		$user_id = get_current_user_id();

		$response = array(
			'roles'  => $this->user_roles->get_roles( $user_id ),
			'panels' => $this->acl->get_accessible_panels( $user_id ),
		);

		if ( $this->acl->can( $user_id, 'core.acl.manage' ) ) {
			$response['permissions'] = $this->acl->get_user_permissions( $user_id );
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Sanitize ACL context values.
	 *
	 * @param array<string, mixed> $context Raw context.
	 * @return array<string, mixed>
	 */
	private function sanitize_context( array $context ) {
		$sanitized = array();

		foreach ( $context as $key => $value ) {
			$key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$sanitized[ $key ] = array_map( 'absint', $value );
			} elseif ( is_numeric( $value ) ) {
				$sanitized[ $key ] = absint( $value );
			} else {
				$sanitized[ $key ] = sanitize_text_field( (string) $value );
			}
		}

		return $sanitized;
	}
}
