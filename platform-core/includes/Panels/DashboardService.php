<?php
/**
 * Panel dashboard data helpers.
 *
 * @package PlatformCore
 */

namespace MPP\Panels;

use MPP\ACL\AclEngine;
use MPP\ACL\RoleManager;
use MPP\Modules\ModuleManager;
use MPP\Services\AuditLogService;
use MPP\Services\ModuleService;
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

	/**
	 * @var AuditLogService
	 */
	private $audit;

	/**
	 * @var ModuleService
	 */
	private $modules;

	/**
	 * @var RoleManager
	 */
	private $role_manager;

	public function __construct( AclEngine $acl, UserRoleService $user_roles, AuditLogService $audit, ModuleService $modules, RoleManager $role_manager ) {
		$this->acl          = $acl;
		$this->user_roles   = $user_roles;
		$this->audit        = $audit;
		$this->modules      = $modules;
		$this->role_manager = $role_manager;
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

	/**
	 * Get module shortcut links for a panel dashboard.
	 *
	 * @param string $panel   Panel slug.
	 * @param int    $user_id User ID.
	 * @return array<int, array<string, string>>
	 */
	public function get_module_shortcuts( $panel, $user_id = 0 ) {
		$panel   = sanitize_key( $panel );
		$user_id = $user_id ? (int) $user_id : get_current_user_id();

		if ( ! function_exists( 'mpp_get_panel_navigation' ) ) {
			return array();
		}

		$skip_routes = array(
			'user'    => array( 'app/user', 'profile', 'settings' ),
			'manager' => array( 'app/manager', 'app/manager/profile', 'settings' ),
		);
		$skip        = $skip_routes[ $panel ] ?? array();
		$shortcuts   = array();

		foreach ( mpp_get_panel_navigation( $panel ) as $item ) {
			$route = $item['route'] ?? '';

			if ( '' === $route || in_array( $route, $skip, true ) ) {
				continue;
			}

			$label = $item['label'] ?? '';

			if ( '' === $label || empty( $item['url'] ) ) {
				continue;
			}

			$shortcuts[] = array(
				'label'       => $label,
				'url'         => $item['url'],
				'description' => $item['description'] ?? '',
				'icon'        => mb_strtoupper( mb_substr( $label, 0, 1 ) ),
			);
		}

		/**
		 * Filter module shortcuts shown on panel dashboards.
		 *
		 * @param array<int, array<string, string>> $shortcuts Shortcuts.
		 * @param string                            $panel     Panel slug.
		 * @param int                               $user_id   User ID.
		 */
		return apply_filters( 'mpp_panel_module_shortcuts', $shortcuts, $panel, $user_id );
	}

	/**
	 * Get pending items for the manager dashboard.
	 *
	 * @param int $user_id Manager user ID.
	 * @return array<int, array<string, string>>
	 */
	public function get_pending_items( $user_id = 0 ) {
		$user_id = $user_id ? (int) $user_id : get_current_user_id();
		$items   = array();

		/**
		 * Filter manager pending items.
		 *
		 * @param array<int, array<string, string>> $items   Pending items.
		 * @param int                               $user_id Manager user ID.
		 */
		return apply_filters( 'mpp_manager_pending_items', $items, $user_id );
	}

	/**
	 * Get recent activity for a user.
	 *
	 * @param int $user_id User ID.
	 * @param int $limit   Entry limit.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_user_recent_activity( $user_id = 0, $limit = 5 ) {
		$user_id = $user_id ? (int) $user_id : get_current_user_id();

		if ( ! $user_id ) {
			return array();
		}

		return $this->audit->query(
			array(
				'user_id' => $user_id,
				'limit'   => $limit,
				'offset'  => 0,
			)
		);
	}

	/**
	 * Get admin dashboard summary.
	 *
	 * @return array<string, mixed>
	 */
	public function get_admin_summary() {
		$roles   = $this->role_manager->all();
		$modules = $this->modules->list_modules();

		return array(
			'platform_version'       => defined( 'MPP_VERSION' ) ? MPP_VERSION : '',
			'wordpress_version'    => get_bloginfo( 'version' ),
			'role_count'           => count( $roles ),
			'module_count'         => count( $modules ),
			'recent_audit'         => $this->audit->query( array( 'limit' => 5 ) ),
			'registration_enabled' => \MPP\Auth\RegistrationHandler::is_enabled(),
			'permalink_mode'       => function_exists( 'mpp_uses_pretty_permalinks' ) && mpp_uses_pretty_permalinks()
				? __( 'Pretty permalinks', 'platform-core' )
				: __( 'Plain permalinks (index.php routes)', 'platform-core' ),
			'current_user'         => is_user_logged_in() ? wp_get_current_user()->user_login : '',
			'database_version'     => get_option( \MPP\Database\Schema::VERSION_OPTION, '' ),
		);
	}
}
