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
		// No runtime hooks required for the example module.
	}

	/**
	 * @inheritDoc
	 */
	public function deactivate() {
		// No cleanup required for the example module.
	}
}
