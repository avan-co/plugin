<?php
/**
 * User role service.
 *
 * @package PlatformCore
 */

namespace MPP\Services;

use MPP\ACL\RoleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class UserRoleService
 */
class UserRoleService {

	/**
	 * Role manager.
	 *
	 * @var RoleManager
	 */
	private $roles;

	/**
	 * Constructor.
	 *
	 * @param RoleManager $roles Role manager.
	 */
	public function __construct( RoleManager $roles ) {
		$this->roles = $roles;
	}

	/**
	 * Assign a role to a user by slugs.
	 *
	 * @param int    $user_id   User ID.
	 * @param string $role_slug Role slug.
	 * @return bool
	 */
	public function assign_role_by_slug( $user_id, $role_slug ) {
		$role = $this->roles->find_by_slug( $role_slug );

		if ( ! $role ) {
			return false;
		}

		return $this->roles->assign_to_user( (int) $user_id, (int) $role['id'] );
	}

	/**
	 * Get user roles.
	 *
	 * @param int $user_id User ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_roles( $user_id ) {
		return $this->roles->get_user_roles( (int) $user_id );
	}

	/**
	 * Sync WordPress administrator to platform admin role.
	 *
	 * Disabled by default. Enable explicitly via the mpp_sync_wp_admin_to_platform_admin filter.
	 *
	 * @param int $user_id User ID.
	 */
	public function maybe_sync_wp_admin( $user_id ) {
		/**
		 * Whether WordPress administrators should receive the platform_admin role.
		 *
		 * @param bool $sync    Whether to sync.
		 * @param int  $user_id User ID.
		 */
		if ( ! apply_filters( 'mpp_sync_wp_admin_to_platform_admin', false, $user_id ) ) {
			return;
		}

		$user = get_userdata( $user_id );

		if ( ! $user || ! user_can( $user, 'manage_options' ) ) {
			return;
		}

		$this->assign_role_by_slug( $user_id, 'platform_admin' );
	}
}
