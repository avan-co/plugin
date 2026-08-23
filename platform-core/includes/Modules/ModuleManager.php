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
	 * Modules queued before core boot.
	 *
	 * @var array<int, ModuleInterface>
	 */
	private static $pending = array();

	/**
	 * Rejected module registration messages.
	 *
	 * @var array<int, string>
	 */
	private $rejected = array();

	/**
	 * Whether modules have been booted.
	 *
	 * @var bool
	 */
	private $booted = false;

	/**
	 * Constructor.
	 *
	 * @param PermissionRegistry $registry Permission registry.
	 */
	public function __construct( PermissionRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Queue a module before core boot.
	 *
	 * @param ModuleInterface $module Module instance.
	 * @return bool
	 */
	public static function enqueue( ModuleInterface $module ) {
		self::$pending[] = $module;

		return true;
	}

	/**
	 * Whether modules have been booted.
	 *
	 * @return bool
	 */
	public function is_booted() {
		return $this->booted;
	}

	/**
	 * Boot all modules.
	 */
	public function boot() {
		if ( $this->booted ) {
			return;
		}

		$this->register_core_module();

		/**
		 * Register additional platform modules.
		 *
		 * @param ModuleManager $manager Module manager instance.
		 */
		do_action( 'mpp_register_modules', $this );

		foreach ( self::$pending as $module ) {
			$this->register( $module );
		}

		self::$pending = array();

		$modules = $this->modules;
		ksort( $modules );

		foreach ( $modules as $module ) {
			$module->run_migrations();
		}

		foreach ( $modules as $module ) {
			$module->register_permissions();
		}

		$this->registry->sync_if_needed();

		foreach ( $modules as $module ) {
			$module->boot();
		}

		$this->booted = true;

		/**
		 * Fires after all valid modules are booted.
		 *
		 * @param ModuleManager $manager Module manager instance.
		 */
		do_action( 'mpp_modules_loaded', $this );
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
	 * Register REST routes from all modules.
	 */
	public function register_module_rest_routes() {
		foreach ( $this->modules as $module ) {
			$module->register_rest_routes();
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
	 * @return bool
	 */
	public function register( ModuleInterface $module ) {
		$error = $this->validate_module( $module );

		if ( true !== $error ) {
			$this->rejected[] = $error;
			$this->log_error( $error );
			return false;
		}

		$slug = $module->get_slug();

		if ( isset( $this->modules[ $slug ] ) ) {
			$message = sprintf( 'Module "%s" is already registered.', $slug );
			$this->rejected[] = $message;
			$this->log_error( $message );
			return false;
		}

		$this->modules[ $slug ] = $module;

		/**
		 * Fires after a module is registered.
		 *
		 * @param ModuleInterface $module Registered module.
		 */
		do_action( 'mpp_module_registered', $module );

		return true;
	}

	/**
	 * Deactivate a registered module.
	 *
	 * @param string $slug Module slug.
	 * @return bool
	 */
	public function deactivate_module( $slug ) {
		$slug = sanitize_key( $slug );

		if ( ! isset( $this->modules[ $slug ] ) ) {
			return false;
		}

		$this->modules[ $slug ]->deactivate();

		/**
		 * Fires after a module is deactivated.
		 *
		 * @param string $slug Module slug.
		 */
		do_action( 'mpp_module_deactivated', $slug );

		return true;
	}

	/**
	 * Get rejected module messages from this request.
	 *
	 * @return array<int, string>
	 */
	public function get_rejected() {
		return $this->rejected;
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

	/**
	 * Validate a module before registration.
	 *
	 * @param mixed $module Module instance.
	 * @return true|string True if valid, otherwise error message.
	 */
	private function validate_module( $module ) {
		if ( ! $module instanceof ModuleInterface ) {
			return 'Module must implement ' . ModuleInterface::class . '.';
		}

		$slug = $module->get_slug();

		if ( empty( $slug ) || ! preg_match( '/^[a-z][a-z0-9_-]*$/', $slug ) ) {
			return sprintf( 'Module slug "%s" is invalid.', $slug );
		}

		if ( 'core' === $slug ) {
			return 'Module slug "core" is reserved.';
		}

		if ( empty( $module->get_name() ) ) {
			return sprintf( 'Module "%s" must provide a name.', $slug );
		}

		if ( empty( $module->get_version() ) ) {
			return sprintf( 'Module "%s" must provide a version.', $slug );
		}

		$required = $module->get_requires_core_version();

		if ( ! empty( $required ) && ! $this->is_core_version_compatible( $required ) ) {
			return sprintf(
				'Module "%s" requires platform-core >= %s (running %s).',
				$slug,
				$required,
				defined( 'MPP_VERSION' ) ? MPP_VERSION : '0.0.0'
			);
		}

		return true;
	}

	/**
	 * Check whether the running core version satisfies a requirement.
	 *
	 * @param string $required Minimum required version.
	 * @return bool
	 */
	private function is_core_version_compatible( $required ) {
		$current = defined( 'MPP_VERSION' ) ? MPP_VERSION : '0.0.0';

		return version_compare( $current, $required, '>=' );
	}

	/**
	 * Log a module registration error.
	 *
	 * @param string $message Error message.
	 */
	private function log_error( $message ) {
		error_log( '[platform-core] ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}
