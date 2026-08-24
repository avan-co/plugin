<?php
/**
 * Tasks module implementation.
 *
 * @package PlatformTasks
 */

namespace MPP\Tasks;

use MPP\Core\Router;
use MPP\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Class TasksModule
 */
class TasksModule extends AbstractModule {

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'tasks';
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Tasks', 'platform-tasks' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return MPP_TASKS_VERSION;
	}

	/**
	 * @inheritDoc
	 */
	public function get_requires_core_version() {
		return '1.3.0';
	}

	/**
	 * @inheritDoc
	 */
	public function register_permissions() {
		if ( ! function_exists( 'mpp' ) ) {
			return;
		}

		mpp()->get( \MPP\ACL\PermissionRegistry::class )->register_module(
			'tasks',
			array(
				'task' => array(
					'view'   => __( 'View tasks', 'platform-tasks' ),
					'manage' => __( 'Manage tasks', 'platform-tasks' ),
				),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes( Router $router ) {
		$router->add_route(
			'app/tasks',
			array(
				'template'      => 'templates/tasks.php',
				'template_file' => MPP_TASKS_DIR . 'templates/tasks.php',
				'permission'    => 'tasks.task.view',
				'title'         => __( 'Tasks', 'platform-tasks' ),
				'description'   => __( 'Review and manage team tasks and approvals.', 'platform-tasks' ),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_navigation_items() {
		$url = function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/tasks' ) : home_url( '/app/tasks' );

		return array(
			array(
				'label'       => __( 'Tasks', 'platform-tasks' ),
				'url'         => $url,
				'route'       => 'app/tasks',
				'permission'  => 'tasks.task.view',
				'panel'       => 'manager',
				'section'     => 'modules',
				'description' => __( 'Pending work and task approvals for your team.', 'platform-tasks' ),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_dashboard_widgets() {
		return array(
			array(
				'id'         => 'tasks_open_count',
				'title'      => __( 'Open Tasks', 'platform-tasks' ),
				'panel'      => 'manager',
				'permission' => 'tasks.task.view',
				'value'      => (string) TaskStore::count_pending_for_manager( get_current_user_id() ),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function run_migrations() {
		TaskStore::install();
		ModuleAccess::grant_default_roles(
			array(
				'tasks.task.view',
				'tasks.task.manage',
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_filter( 'mpp_manager_dashboard_stats', array( $this, 'filter_manager_stats' ), 10, 2 );
		add_filter( 'mpp_manager_pending_items', array( $this, 'filter_pending_items' ), 10, 2 );
	}

	/**
	 * @inheritDoc
	 */
	public function deactivate() {
		// Tables retained for data safety.
	}

	/**
	 * Provide pending task stat for manager dashboard.
	 *
	 * @param array<string, mixed> $stats   Stats map.
	 * @param int                  $user_id Manager user ID.
	 * @return array<string, mixed>
	 */
	public function filter_manager_stats( array $stats, $user_id ) {
		$stats['pending_tasks'] = (string) TaskStore::count_pending_for_manager( $user_id );
		return $stats;
	}

	/**
	 * Add pending task items for manager dashboard.
	 *
	 * @param array<int, array<string, string>> $items   Pending items.
	 * @param int                               $user_id Manager user ID.
	 * @return array<int, array<string, string>>
	 */
	public function filter_pending_items( array $items, $user_id ) {
		$pending = TaskStore::count_pending_for_manager( $user_id );

		if ( $pending <= 0 ) {
			return $items;
		}

		$url = function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/tasks' ) : home_url( '/app/tasks' );

		$items[] = array(
			'title'        => sprintf(
				/* translators: %d: pending task count */
				_n( '%d task awaiting review', '%d tasks awaiting review', $pending, 'platform-tasks' ),
				$pending
			),
			'description'  => __( 'Open the task board to approve or assign work.', 'platform-tasks' ),
			'url'          => $url,
			'action_label' => __( 'Review tasks', 'platform-tasks' ),
		);

		return $items;
	}
}
