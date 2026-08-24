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
		return count( $this->get_module_routes( $slug ) );
	}

	/**
	 * Find a module by slug.
	 *
	 * @param string $slug Module slug.
	 * @return array<string, mixed>|null
	 */
	public function find_module( $slug ) {
		$slug = sanitize_key( $slug );

		foreach ( $this->list_modules() as $module ) {
			if ( $module['slug'] === $slug ) {
				return $module;
			}
		}

		return null;
	}

	/**
	 * Get permissions registered for a module.
	 *
	 * @param string $slug Module slug.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_module_permissions( $slug ) {
		$slug    = sanitize_key( $slug );
		$grouped = $this->registry->get_grouped();
		$items   = array();

		if ( empty( $grouped[ $slug ] ) ) {
			return $items;
		}

		foreach ( $grouped[ $slug ] as $resource => $actions ) {
			foreach ( $actions as $action ) {
				$items[] = array_merge(
					$action,
					array(
						'resource' => $resource,
						'module'   => $slug,
					)
				);
			}
		}

		return $items;
	}

	/**
	 * Get platform routes owned by a module.
	 *
	 * @param string $slug Module slug.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_module_routes( $slug ) {
		$slug = sanitize_key( $slug );

		if ( ! function_exists( 'mpp' ) ) {
			return array();
		}

		$router = mpp()->get( \MPP\Core\Router::class );
		$routes = array();

		foreach ( $router->get_routes() as $route_slug => $definition ) {
			if ( ! $this->route_belongs_to_module( $route_slug, $slug ) ) {
				continue;
			}

			$routes[] = array(
				'slug'        => $route_slug,
				'title'       => $definition['title'] ?? '',
				'description' => $definition['description'] ?? '',
				'permission'  => $definition['permission'] ?? '',
				'url'         => mpp_route_url( $route_slug ),
			);
		}

		usort(
			$routes,
			function ( $a, $b ) {
				return strcmp( $a['slug'], $b['slug'] );
			}
		);

		return $routes;
	}

	/**
	 * Determine whether a route belongs to a module slug.
	 *
	 * @param string $route_slug Route slug.
	 * @param string $module     Module slug.
	 * @return bool
	 */
	private function route_belongs_to_module( $route_slug, $module ) {
		if ( 'core' === $module ) {
			return 0 === strpos( $route_slug, 'app/admin' )
				|| in_array( $route_slug, array( 'app', 'app/user', 'app/manager', 'profile', 'settings', 'login', 'register', 'forgot-password' ), true );
		}

		return $route_slug === 'app/' . $module || 0 === strpos( $route_slug, 'app/' . $module . '/' );
	}
}
