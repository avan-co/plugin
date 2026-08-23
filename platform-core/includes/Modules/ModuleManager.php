<?php
/**
 * Module registration and bootstrapping.
 *
 * @package PlatformCore
 */

namespace MPP\Modules;

use MPP\ACL\PermissionRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Class ModuleManager
 */
class ModuleManager {

	/**
	 * Permission registry.
	 *
	 * @var PermissionRegistry
	 */
	private $registry;

	/**
	 * Registered modules.
	 *
	 * @var array<string, ModuleInterface>
	 */
	private $modules = array();

	/**
	 * Constructor.
	 *
	 * @param PermissionRegistry $registry Permission registry.
	 */
	public function __construct( PermissionRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Boot all modules.
	 */
	public function boot() {
		$this->register_core_module();

		/**
		 * Register additional platform modules.
		 *
		 * @param ModuleManager $manager Module manager instance.
		 */
		do_action( 'mpp_register_modules', $this );

		foreach ( $this->modules as $module ) {
			$module->register_permissions();
			$module->boot();
		}

		$this->registry->sync_if_needed();
	}

	/**
	 * Register routes from all modules.
	 *
	 * @param \MPP\Core\Router $router Router instance.
	 */
	public function register_module_routes( $router ) {
		foreach ( $this->modules as $module ) {
			$module->register_routes( $router );
		}
	}

	/**
	 * Get merged navigation items from modules.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function get_navigation_items() {
		$items = array();

		foreach ( $this->modules as $module ) {
			$items = array_merge( $items, $module->get_navigation_items() );
		}

		/**
		 * Filter module navigation items.
		 *
		 * @param array<int, array<string, string>> $items Navigation items.
		 */
		return apply_filters( 'mpp_module_navigation_items', $items );
	}

	/**
	 * Get merged dashboard widgets from modules.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function get_dashboard_widgets() {
		$widgets = array();

		foreach ( $this->modules as $module ) {
			$widgets = array_merge( $widgets, $module->get_dashboard_widgets() );
		}

		/**
		 * Filter module dashboard widgets.
		 *
		 * @param array<int, array<string, string>> $widgets Dashboard widgets.
		 */
		return apply_filters( 'mpp_module_dashboard_widgets', $widgets );
	}

	/**
	 * Register a module.
	 *
	 * @param ModuleInterface $module Module instance.
	 */
	public function register( ModuleInterface $module ) {
		$this->modules[ $module->get_slug() ] = $module;
	}

	/**
	 * Register core module permissions.
	 */
	public function register_core_module() {
		$this->registry->register_module(
			'core',
			array(
				'panel' => array(
					'access'         => __( 'Access platform dashboard', 'platform-core' ),
					'user.access'    => __( 'Access user panel', 'platform-core' ),
					'manager.access' => __( 'Access manager panel', 'platform-core' ),
					'admin.access'   => __( 'Access admin panel', 'platform-core' ),
				),
				'profile' => array(
					'view' => __( 'View own profile', 'platform-core' ),
					'edit' => __( 'Edit own profile', 'platform-core' ),
				),
				'settings' => array(
					'view' => __( 'View settings', 'platform-core' ),
					'edit' => __( 'Edit settings', 'platform-core' ),
				),
				'acl' => array(
					'manage' => __( 'Manage roles and permissions', 'platform-core' ),
				),
			)
		);
	}

	/**
	 * Get all registered modules.
	 *
	 * @return array<string, ModuleInterface>
	 */
	public function all() {
		return $this->modules;
	}
}
