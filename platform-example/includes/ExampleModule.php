<?php
/**
 * Example external module.
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
					'view' => __( 'View example demo page', 'platform-example' ),
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
				'template'       => 'templates/example-demo.php',
				'template_file'  => MPP_EXAMPLE_DIR . 'templates/demo.php',
				'permission'     => 'example.demo.view',
				'title'          => __( 'Example Demo', 'platform-example' ),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_navigation_items() {
		return array(
			array(
				'label'      => __( 'Example Demo', 'platform-example' ),
				'url'        => home_url( '/app/example' ),
				'route'      => 'app/example',
				'permission' => 'example.demo.view',
				'panel'      => 'user',
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
	public function deactivate() {
		// No cleanup required for the example module.
	}
}
