<?php
/**
 * Module listing service.
 *
 * @package PlatformCore
 */

namespace MPP\Services;

use MPP\ACL\PermissionRegistry;
use MPP\Modules\ModuleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class ModuleService
 */
class ModuleService {

	/**
	 * Module manager.
	 *
	 * @var ModuleManager
	 */
	private $modules;

	/**
	 * Permission registry.
	 *
	 * @var PermissionRegistry
	 */
	private $registry;

	/**
	 * Constructor.
	 *
	 * @param ModuleManager      $modules  Module manager.
	 * @param PermissionRegistry $registry Permission registry.
	 */
	public function __construct( ModuleManager $modules, PermissionRegistry $registry ) {
		$this->modules  = $modules;
		$this->registry = $registry;
	}

	/**
	 * List all registered modules with permission counts.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function list_modules() {
		$grouped  = $this->registry->get_grouped();
		$modules  = array();
		$seen     = array();

		foreach ( $this->modules->all() as $slug => $module ) {
			$seen[ $slug ] = true;
			$modules[]     = array(
				'slug'                   => $slug,
				'name'                   => $module->get_name(),
				'version'                => $module->get_version(),
				'requires_core_version'  => $module->get_requires_core_version(),
				'status'                 => 'active',
				'permission_count'       => $this->count_module_permissions( $slug, $grouped ),
				'route_count'            => $this->count_module_routes( $slug ),
				'description'            => apply_filters( 'mpp_module_description', '', $slug, $module ),
			);
		}

		foreach ( array_keys( $grouped ) as $slug ) {
			if ( isset( $seen[ $slug ] ) ) {
				continue;
			}

			$modules[] = array(
				'slug'               => $slug,
				'name'               => ucfirst( $slug ),
				'status'             => 'active',
				'permission_count'   => $this->count_module_permissions( $slug, $grouped ),
			);
		}

		usort(
			$modules,
			function ( $a, $b ) {
				return strcmp( $a['slug'], $b['slug'] );
			}
		);

		return $modules;
	}

	/**
	 * Count permissions for a module.
	 *
	 * @param string                                          $slug    Module slug.
	 * @param array<string, array<string, array<int, mixed>>> $grouped Grouped permissions.
	 * @return int
	 */
	private function count_module_permissions( $slug, array $grouped ) {
		if ( ! isset( $grouped[ $slug ] ) ) {
			return 0;
		}

		$count = 0;

		foreach ( $grouped[ $slug ] as $actions ) {
			$count += count( $actions );
		}

		return $count;
	}

	/**
	 * Count routes registered by a module slug prefix.
	 *
	 * @param string $slug Module slug.
	 * @return int
	 */
	private function count_module_routes( $slug ) {
		if ( ! function_exists( 'mpp' ) ) {
			return 0;
		}

		$router = mpp()->get( \MPP\Core\Router::class );
		$count  = 0;

		foreach ( array_keys( $router->get_routes() ) as $route_slug ) {
			if ( 0 === strpos( $route_slug, 'app/' . $slug ) || $route_slug === 'app/' . $slug ) {
				$count++;
			}
		}

		return $count;
	}
}
