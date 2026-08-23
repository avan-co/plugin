<?php
/**
 * Panel dashboard data helpers.
 *
 * @package PlatformCore
 */

namespace MPP\Panels;

use MPP\ACL\AclEngine;
use MPP\Services\UserRoleService;

defined( 'ABSPATH' ) || exit;

/**
 * Class DashboardService
 */
class DashboardService {

	/**
	 * @var AclEngine
	 */
	private $acl;

	/**
	 * @var UserRoleService
	 */
	private $user_roles;

	public function __construct( AclEngine $acl, UserRoleService $user_roles ) {
		$this->acl        = $acl;
		$this->user_roles = $user_roles;
	}

	/**
	 * Get summary data for the current user dashboard.
	 *
	 * @param int $user_id User ID.
	 * @return array<string, mixed>
	 */
	public function get_user_summary( $user_id = 0 ) {
		$user_id = $user_id ? (int) $user_id : get_current_user_id();
		$roles   = $this->user_roles->get_roles( $user_id );

		return array(
			'roles'            => $roles,
			'role_names'       => wp_list_pluck( $roles, 'name' ),
			'panels'           => $this->acl->get_accessible_panels( $user_id ),
			'permission_count' => count( $this->acl->get_user_permissions( $user_id ) ),
		);
	}

	/**
	 * Get manager dashboard stats (generic placeholders via filter).
	 *
	 * @param int $user_id User ID.
	 * @return array<string, mixed>
	 */
	public function get_manager_stats( $user_id = 0 ) {
		$user_id = $user_id ? (int) $user_id : get_current_user_id();

		$stats = array(
			'team_members'  => '—',
			'pending_tasks' => '—',
		);

		/**
		 * Filter manager dashboard stats.
		 *
		 * @param array<string, mixed> $stats   Stat key => value.
		 * @param int                  $user_id Manager user ID.
		 */
		return apply_filters( 'mpp_manager_dashboard_stats', $stats, $user_id );
	}
}
