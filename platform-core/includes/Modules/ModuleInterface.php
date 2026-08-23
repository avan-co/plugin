<?php
/**
 * Module interface for extensibility.
 *
 * @package PlatformCore
 */

namespace MPP\Modules;

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
	 * Register module permissions.
	 */
	public function register_permissions();

	/**
	 * Boot module hooks.
	 */
	public function boot();
}
