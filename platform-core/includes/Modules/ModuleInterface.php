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
	 * Get module slug.
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
	 * Register module permissions.
	 */
	public function register_permissions();

	/**
	 * Register module routes.
	 *
	 * @param Router $router Platform router.
	 */
	public function register_routes( Router $router );

	/**
	 * Navigation items for panels.
	 *
	 * Each item: label, url, permission (optional), panel (user|manager|admin).
	 *
	 * @return array<int, array<string, string>>
	 */
	public function get_navigation_items();

	/**
	 * Dashboard widgets for panels.
	 *
	 * Each widget: id, title, panel, permission (optional).
	 *
	 * @return array<int, array<string, string>>
	 */
	public function get_dashboard_widgets();

	/**
	 * Boot module hooks.
	 */
	public function boot();
}
