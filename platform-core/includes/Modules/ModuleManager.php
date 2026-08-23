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

		$this->registry->sync_to_database();
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
