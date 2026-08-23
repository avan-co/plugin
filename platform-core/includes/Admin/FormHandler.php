<?php
/**
 * Handles admin form POST requests with CSRF and ACL checks.
 *
 * @package PlatformCore
 */

namespace MPP\Admin;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\Services\AuditLogService;
use MPP\Settings\PlatformSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Class FormHandler
 */
class FormHandler {

	const NONCE_ACTION = 'mpp_admin_action';

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
	 * Audit log service.
	 *
	 * @var AuditLogService
	 */
	private $audit;

	/**
	 * Platform settings.
	 *
	 * @var PlatformSettings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param AclEngine          $acl      ACL engine.
	 * @param RoleManager        $roles    Role manager.
	 * @param PermissionRegistry $registry Permission registry.
	 * @param AuditLogService    $audit    Audit log service.
	 * @param PlatformSettings   $settings Platform settings.
	 */
	public function __construct( AclEngine $acl, RoleManager $roles, PermissionRegistry $registry, AuditLogService $audit, PlatformSettings $settings ) {
		$this->acl      = $acl;
		$this->roles    = $roles;
		$this->registry = $registry;
		$this->audit    = $audit;
		$this->settings = $settings;
	}

	/**
	 * Register hooks.
	 */
	public function register() {
		add_action( 'init', array( $this, 'handle' ), 5 );
	}

	/**
	 * Handle admin form submissions.
	 */
	public function handle() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}

		if ( empty( $_POST['mpp_admin_action'] ) ) {
			return;
		}

		if ( ! is_user_logged_in() || ! $this->acl->can( get_current_user_id(), 'core.acl.manage' ) ) {
			wp_die( esc_html__( 'Access denied.', 'platform-core' ), '', array( 'response' => 403 ) );
		}

		if ( ! isset( $_POST['mpp_admin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mpp_admin_nonce'] ) ), self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Invalid security token.', 'platform-core' ), '', array( 'response' => 403 ) );
		}

		$action  = sanitize_key( wp_unslash( $_POST['mpp_admin_action'] ) );
		$redirect = isset( $_POST['mpp_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['mpp_redirect'] ) ) : mpp_route_url( 'app/admin' );
		$redirect = wp_validate_redirect( $redirect, mpp_route_url( 'app/admin' ) );

		$result = $this->dispatch( $action );

		$redirect = add_query_arg(
			array(
				'mpp_notice' => $result['notice'] ?? ( $result['success'] ? 'success' : 'error' ),
				'mpp_message' => $result['message'] ?? '',
			),
			$redirect
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Dispatch admin action.
	 *
	 * @param string $action Action slug.
	 * @return array<string, mixed>
	 */
	private function dispatch( $action ) {
		switch ( $action ) {
			case 'create_role':
				return $this->create_role();
			case 'update_role':
				return $this->update_role();
			case 'delete_role':
				return $this->delete_role();
			case 'assign_user_role':
				return $this->assign_user_role();
			case 'revoke_user_role':
				return $this->revoke_user_role();
			case 'grant_permission':
				return $this->grant_permission();
			case 'revoke_permission':
				return $this->revoke_permission();
			case 'update_permission_scope':
				return $this->update_permission_scope();
			case 'save_settings':
				return $this->save_settings();
			default:
				return array(
					'success' => false,
					'message' => __( 'Unknown action.', 'platform-core' ),
				);
		}
	}

	/**
	 * Create a platform role.
	 *
	 * @return array<string, mixed>
	 */
	private function create_role() {
		$data = \MPP\Security\Sanitizer::role( wp_unslash( $_POST ) );

		if ( empty( $data['slug'] ) || empty( $data['name'] ) ) {
			return array( 'success' => false, 'message' => __( 'Slug and name are required.', 'platform-core' ) );
		}

		if ( $this->roles->find_by_slug( $data['slug'] ) ) {
			return array( 'success' => false, 'message' => __( 'Role slug already exists.', 'platform-core' ) );
		}

		$id = $this->roles->create( $data['slug'], $data['name'], $data['description'] );

		if ( ! $id ) {
			return array( 'success' => false, 'message' => __( 'Could not create role.', 'platform-core' ) );
		}

		if ( 'inactive' === $data['status'] ) {
			$this->roles->update( $id, array( 'status' => 'inactive' ) );
		}

		$role = $this->roles->find( $id );
		$this->audit->log( 'role.created', 'role', $id, array(), $role );

		return array( 'success' => true, 'message' => __( 'Role created.', 'platform-core' ) );
	}

	/**
	 * Update a platform role.
	 *
	 * @return array<string, mixed>
	 */
	private function update_role() {
		$role_id = isset( $_POST['role_id'] ) ? (int) $_POST['role_id'] : 0;
		$before  = $this->roles->find( $role_id );

		if ( ! $before ) {
			return array( 'success' => false, 'message' => __( 'Role not found.', 'platform-core' ) );
		}

		$data = \MPP\Security\Sanitizer::role( wp_unslash( $_POST ) );
		unset( $data['slug'] );

		$this->roles->update( $role_id, $data );
		$after = $this->roles->find( $role_id );
		$this->audit->log( 'role.updated', 'role', $role_id, $before, $after );

		return array( 'success' => true, 'message' => __( 'Role updated.', 'platform-core' ) );
	}

	/**
	 * Delete a platform role.
	 *
	 * @return array<string, mixed>
	 */
	private function delete_role() {
		$role_id = isset( $_POST['role_id'] ) ? (int) $_POST['role_id'] : 0;
		$before  = $this->roles->find( $role_id );

		if ( ! $before ) {
			return array( 'success' => false, 'message' => __( 'Role not found.', 'platform-core' ) );
		}

		if ( ! empty( $before['is_system'] ) ) {
			return array( 'success' => false, 'message' => __( 'System roles cannot be deleted.', 'platform-core' ) );
		}

		if ( ! $this->roles->delete( $role_id ) ) {
			return array( 'success' => false, 'message' => __( 'Could not delete role.', 'platform-core' ) );
		}

		$this->audit->log( 'role.deleted', 'role', $role_id, $before, array() );

		return array( 'success' => true, 'message' => __( 'Role deleted.', 'platform-core' ) );
	}

	/**
	 * Assign role to user.
	 *
	 * @return array<string, mixed>
	 */
	private function assign_user_role() {
		$user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
		$role_id = isset( $_POST['role_id'] ) ? (int) $_POST['role_id'] : 0;

		if ( ! get_userdata( $user_id ) || ! $this->roles->find( $role_id ) ) {
			return array( 'success' => false, 'message' => __( 'Invalid user or role.', 'platform-core' ) );
		}

		$result = $this->roles->assign_to_user( $user_id, $role_id );

		if ( $result ) {
			$this->audit->log(
				'user.role.assigned',
				'user_role',
				$user_id . ':' . $role_id,
				array(),
				array(
					'user_id' => $user_id,
					'role_id' => $role_id,
				)
			);
		}

		return array(
			'success' => (bool) $result,
			'message' => $result ? __( 'Role assigned.', 'platform-core' ) : __( 'Could not assign role.', 'platform-core' ),
		);
	}

	/**
	 * Revoke role from user.
	 *
	 * @return array<string, mixed>
	 */
	private function revoke_user_role() {
		$user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
		$role_id = isset( $_POST['role_id'] ) ? (int) $_POST['role_id'] : 0;

		if ( ! get_userdata( $user_id ) || ! $this->roles->find( $role_id ) ) {
			return array( 'success' => false, 'message' => __( 'Invalid user or role.', 'platform-core' ) );
		}

		$result = $this->roles->revoke_from_user( $user_id, $role_id );

		if ( $result ) {
			$this->audit->log(
				'user.role.revoked',
				'user_role',
				$user_id . ':' . $role_id,
				array(
					'user_id' => $user_id,
					'role_id' => $role_id,
				),
				array()
			);
		}

		return array(
			'success' => (bool) $result,
			'message' => $result ? __( 'Role removed.', 'platform-core' ) : __( 'Could not remove role.', 'platform-core' ),
		);
	}

	/**
	 * Grant permission to role.
	 *
	 * @return array<string, mixed>
	 */
	private function grant_permission() {
		$role_id       = isset( $_POST['role_id'] ) ? (int) $_POST['role_id'] : 0;
		$permission_id = isset( $_POST['permission_id'] ) ? (int) $_POST['permission_id'] : 0;
		$scope_type    = isset( $_POST['scope_type'] ) ? sanitize_key( wp_unslash( $_POST['scope_type'] ) ) : 'all';

		if ( ! $this->roles->find( $role_id ) || ! $this->registry->find_by_id( $permission_id ) ) {
			return array( 'success' => false, 'message' => __( 'Invalid role or permission.', 'platform-core' ) );
		}

		$result = $this->roles->assign_permission( $role_id, $permission_id, $scope_type );

		if ( $result ) {
			$this->audit->log(
				'permission.granted',
				'role_permission',
				$role_id . ':' . $permission_id,
				array(),
				array(
					'role_id'       => $role_id,
					'permission_id' => $permission_id,
					'scope_type'    => $scope_type,
				)
			);
		}

		return array(
			'success' => (bool) $result,
			'message' => $result ? __( 'Permission granted.', 'platform-core' ) : __( 'Could not grant permission.', 'platform-core' ),
		);
	}

	/**
	 * Revoke permission from role.
	 *
	 * @return array<string, mixed>
	 */
	private function revoke_permission() {
		$role_id       = isset( $_POST['role_id'] ) ? (int) $_POST['role_id'] : 0;
		$permission_id = isset( $_POST['permission_id'] ) ? (int) $_POST['permission_id'] : 0;

		$result = $this->roles->revoke_permission( $role_id, $permission_id );

		if ( $result ) {
			$this->audit->log(
				'permission.revoked',
				'role_permission',
				$role_id . ':' . $permission_id,
				array(
					'role_id'       => $role_id,
					'permission_id' => $permission_id,
				),
				array()
			);
		}

		return array(
			'success' => (bool) $result,
			'message' => $result ? __( 'Permission revoked.', 'platform-core' ) : __( 'Could not revoke permission.', 'platform-core' ),
		);
	}

	/**
	 * Update permission scope on role.
	 *
	 * @return array<string, mixed>
	 */
	private function update_permission_scope() {
		$role_id       = isset( $_POST['role_id'] ) ? (int) $_POST['role_id'] : 0;
		$permission_id = isset( $_POST['permission_id'] ) ? (int) $_POST['permission_id'] : 0;
		$scope_type    = isset( $_POST['scope_type'] ) ? sanitize_key( wp_unslash( $_POST['scope_type'] ) ) : 'all';

		$before_perms = $this->roles->get_permissions( $role_id );
		$before       = array();

		foreach ( $before_perms as $perm ) {
			if ( (int) $perm['permission_id'] === $permission_id ) {
				$before = $perm;
				break;
			}
		}

		$result = $this->roles->assign_permission( $role_id, $permission_id, $scope_type );

		if ( $result ) {
			$this->audit->log(
				'scope.changed',
				'role_permission',
				$role_id . ':' . $permission_id,
				$before,
				array(
					'role_id'       => $role_id,
					'permission_id' => $permission_id,
					'scope_type'    => $scope_type,
				)
			);
		}

		return array(
			'success' => (bool) $result,
			'message' => $result ? __( 'Scope updated.', 'platform-core' ) : __( 'Could not update scope.', 'platform-core' ),
		);
	}

	/**
	 * Save platform settings.
	 *
	 * @return array<string, mixed>
	 */
	private function save_settings() {
		$errors = $this->settings->save( wp_unslash( $_POST ) );

		if ( ! empty( $errors ) ) {
			return array(
				'success' => false,
				'message' => implode( ' ', $errors ),
			);
		}

		$this->audit->log( 'settings.updated', 'settings', 0, array(), $this->settings->all() );

		return array(
			'success' => true,
			'message' => __( 'Settings saved.', 'platform-core' ),
		);
	}

	/**
	 * Get nonce field HTML.
	 *
	 * @return string
	 */
	public static function nonce_field() {
		return wp_nonce_field( self::NONCE_ACTION, 'mpp_admin_nonce', true, false );
	}
}
