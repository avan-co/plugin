<?php
/**
 * Example external module — canonical reference implementation.
 *
 * @package PlatformExample
 */

namespace MPP\Example;

use MPP\Core\Router;
use MPP\Modules\AbstractModule;

defined( 'ABSPATH' ) || exit;

/**
 * Class ExampleModule
 */
class ExampleModule extends AbstractModule {

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'example';
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Example', 'platform-example' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return MPP_EXAMPLE_VERSION;
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
			'example',
			array(
				'demo' => array(
					'view'   => __( 'View Example Demo', 'platform-example' ),
					'manage' => __( 'Manage Example Demo', 'platform-example' ),
				),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes( Router $router ) {
		$router->add_route(
			'app/example',
			array(
				'template'      => 'templates/demo.php',
				'template_file' => MPP_EXAMPLE_DIR . 'templates/demo.php',
				'permission'    => 'example.demo.view',
				'title'         => __( 'Example Demo', 'platform-example' ),
				'description'   => __( 'Demonstration page for the Example module.', 'platform-example' ),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_navigation_items() {
		$url = function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/example' ) : home_url( '/app/example' );

		return array(
			array(
				'label'      => __( 'Example Demo', 'platform-example' ),
				'url'        => $url,
				'route'      => 'app/example',
				'permission' => 'example.demo.view',
				'panel'      => 'user',
			),
			array(
				'label'       => __( 'Example Oversight', 'platform-example' ),
				'url'         => $url,
				'route'       => 'app/example',
				'permission'  => 'example.demo.manage',
				'panel'       => 'manager',
				'section'     => 'modules',
				'description' => __( 'Review example module activity for your team.', 'platform-example' ),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_dashboard_widgets() {
		return array(
			array(
				'id'         => 'example_demo_status',
				'title'      => __( 'Example Module', 'platform-example' ),
				'panel'      => 'user',
				'permission' => 'example.demo.view',
				'value'      => __( 'Active', 'platform-example' ),
			),
			array(
				'id'         => 'example_manager_open_items',
				'title'      => __( 'Example Open Items', 'platform-example' ),
				'panel'      => 'manager',
				'permission' => 'example.demo.manage',
				'value'      => '3',
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function run_migrations() {
		// No tables for the example module.
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_filter( 'mpp_manager_dashboard_stats', array( $this, 'filter_manager_stats' ), 10, 2 );
		add_filter( 'mpp_manager_pending_items', array( $this, 'filter_manager_pending_items' ), 10, 2 );
	}

	/**
	 * Provide sample manager dashboard stats.
	 *
	 * @param array<string, mixed> $stats   Stats map.
	 * @param int                  $user_id Manager user ID.
	 * @return array<string, mixed>
	 */
	public function filter_manager_stats( array $stats, $user_id ) {
		$stats['team_members']  = '12';
		$stats['pending_tasks'] = '3';

		return $stats;
	}

	/**
	 * Provide sample pending items for managers.
	 *
	 * @param array<int, array<string, string>> $items   Pending items.
	 * @param int                             $user_id Manager user ID.
	 * @return array<int, array<string, string>>
	 */
	public function filter_manager_pending_items( array $items, $user_id ) {
		$url = function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/example' ) : home_url( '/app/example' );

		$items[] = array(
			'title'        => __( '3 example approvals waiting', 'platform-example' ),
			'description'  => __( 'Review demo module requests assigned to your scope.', 'platform-example' ),
			'url'          => $url,
			'action_label' => __( 'Review', 'platform-example' ),
		);

		return $items;
	}

	/**
	 * @inheritDoc
	 */
	public function deactivate() {
		// No cleanup required for the example module.
	}
}
