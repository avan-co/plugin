<?php
/**
 * Module interface for extensibility.
 *
 * @package PlatformCore
 */

namespace MPP\Modules;

use MPP\Core\Router;

defined( 'ABSPATH' ) || exit;

/**
 * Interface ModuleInterface
 */
interface ModuleInterface {

	/**
	 * Get module slug (unique identifier).
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get module display name.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get module version.
	 *
	 * @return string
	 */
	public function get_version();

	/**
	 * Minimum platform-core version required.
	 *
	 * @return string
	 */
	public function get_requires_core_version();

	/**
	 * Register module permissions.
	 */
	public function register_permissions();

	/**
	 * Register module frontend routes.
	 *
	 * @param Router $router Platform router.
	 */
	public function register_routes( Router $router );

	/**
	 * Register module REST routes (hooked to rest_api_init by core).
	 */
	public function register_rest_routes();

	/**
	 * Navigation items for panels.
	 *
	 * Each item: label, url, route (optional), permission (optional), panel (user|manager|admin).
	 *
	 * @return array<int, array<string, string>>
	 */
	public function get_navigation_items();

	/**
	 * Dashboard widgets for panels.
	 *
	 * Each widget: id, title, panel, permission (optional), value (optional).
	 *
	 * @return array<int, array<string, string>>
	 */
	public function get_dashboard_widgets();

	/**
	 * Run module-owned database migrations (idempotent).
	 */
	public function run_migrations();

	/**
	 * Boot module hooks after permissions are registered.
	 */
	public function boot();

	/**
	 * Cleanup when the module plugin is deactivated.
	 */
	public function deactivate();
}
