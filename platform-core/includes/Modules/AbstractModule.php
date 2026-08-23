<?php
/**
 * Base module with optional extension points.
 *
 * @package PlatformCore
 */

namespace MPP\Modules;

use MPP\Core\Router;

defined( 'ABSPATH' ) || exit;

/**
 * Class AbstractModule
 */
abstract class AbstractModule implements ModuleInterface {

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return '1.0.0';
	}

	/**
	 * @inheritDoc
	 */
	public function get_requires_core_version() {
		return '1.0.0';
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes( Router $router ) {
		// Optional.
	}

	/**
	 * @inheritDoc
	 */
	public function register_rest_routes() {
		// Optional.
	}

	/**
	 * @inheritDoc
	 */
	public function get_navigation_items() {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function get_dashboard_widgets() {
		return array();
	}

	/**
	 * @inheritDoc
	 */
	public function run_migrations() {
		// Optional.
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		// Optional.
	}

	/**
	 * @inheritDoc
	 */
	public function deactivate() {
		// Optional.
	}
}
