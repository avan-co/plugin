<?php
/**
 * Main plugin bootstrap.
 *
 * @package PlatformCore
 */

namespace MPP\Core;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\ACL\ScopeContextService;
use MPP\ACL\ScopeResolver;
use MPP\Account\AccountFormHandler;
use MPP\Admin\AdminRenderer;
use MPP\Admin\AdminRoutes;
use MPP\Admin\FormHandler;
use MPP\API\AclController;
use MPP\API\AuditLogController;
use MPP\API\ModulesController;
use MPP\API\PermissionsController;
use MPP\API\RolesController;
use MPP\API\ScopesController;
use MPP\API\UsersController;
use MPP\Auth\AccessGuard;
use MPP\Auth\AuthIntegration;
use MPP\Auth\RegistrationHandler;
use MPP\Database\Installer;
use MPP\Modules\ModuleManager;
use MPP\Panels\DashboardService;
use MPP\Services\AuditLogService;
use MPP\Services\ModuleService;
use MPP\Services\PermissionService;
use MPP\Services\ScopeService;
use MPP\Services\UserRoleService;
use MPP\Services\UserService;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Whether the plugin has booted.
	 *
	 * @var bool
	 */
	private $booted = false;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->container = new Container();
		$this->register_services();
	}

	/**
	 * Boot the plugin.
	 */
	public function boot() {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		load_plugin_textdomain( 'platform-core', false, dirname( MPP_PLUGIN_BASENAME ) . '/languages' );

		Installer::maybe_upgrade();

		$module_manager = $this->container->get( ModuleManager::class );
		$module_manager->boot();

		$router = $this->container->get( Router::class );
		$module_manager->register_module_routes( $router );
		AdminRoutes::register( $router );
		$router->register();
		$this->maybe_flush_rewrite_rules();

		$this->container->get( FormHandler::class )->register();
		$this->container->get( AccountFormHandler::class )->register();
		$this->container->get( AuthIntegration::class )->register();
		$this->container->get( RegistrationHandler::class )->register();
		$this->container->get( AccessGuard::class )->register();
		$this->container->get( RolesController::class )->register();
		$this->container->get( PermissionsController::class )->register();
		$this->container->get( AclController::class )->register();
		$this->container->get( UsersController::class )->register();
		$this->container->get( ModulesController::class )->register();
		$this->container->get( ScopesController::class )->register();
		$this->container->get( AuditLogController::class )->register();

		add_action( 'rest_api_init', array( $module_manager, 'register_module_rest_routes' ) );

		do_action( 'mpp_booted', $this );
	}

	/**
	 * Register services in the container.
	 */
	private function register_services() {
		$this->container->set( PermissionRegistry::class, function () {
			return new PermissionRegistry();
		} );

		$this->container->set( ScopeResolver::class, function () {
			return new ScopeResolver();
		} );

		$this->container->set( ScopeContextService::class, function () {
			return new ScopeContextService();
		} );

		$this->container->set( RoleManager::class, function () {
			return new RoleManager();
		} );

		$this->container->set( AuditLogService::class, function () {
			return new AuditLogService();
		} );

		$this->container->set( AclEngine::class, function ( Container $c ) {
			return new AclEngine(
				$c->get( PermissionRegistry::class ),
				$c->get( RoleManager::class ),
				$c->get( ScopeResolver::class ),
				$c->get( ScopeContextService::class )
			);
		} );

		$this->container->set( DashboardService::class, function ( Container $c ) {
			return new DashboardService(
				$c->get( AclEngine::class ),
				$c->get( UserRoleService::class ),
				$c->get( AuditLogService::class ),
				$c->get( ModuleService::class ),
				$c->get( RoleManager::class )
			);
		} );

		$this->container->set( PermissionService::class, function ( Container $c ) {
			return new PermissionService( $c->get( PermissionRegistry::class ) );
		} );

		$this->container->set( UserRoleService::class, function ( Container $c ) {
			return new UserRoleService( $c->get( RoleManager::class ) );
		} );

		$this->container->set( UserService::class, function ( Container $c ) {
			return new UserService( $c->get( RoleManager::class ) );
		} );

		$this->container->set( ScopeService::class, function ( Container $c ) {
			return new ScopeService( $c->get( ScopeResolver::class ) );
		} );

		$this->container->set( ModuleService::class, function ( Container $c ) {
			return new ModuleService( $c->get( ModuleManager::class ), $c->get( PermissionRegistry::class ) );
		} );

		$this->container->set( ModuleManager::class, function ( Container $c ) {
			return new ModuleManager( $c->get( PermissionRegistry::class ) );
		} );

		$this->container->set( AdminRenderer::class, function ( Container $c ) {
			return new AdminRenderer(
				$c->get( UserService::class ),
				$c->get( RoleManager::class ),
				$c->get( PermissionService::class ),
				$c->get( PermissionRegistry::class ),
				$c->get( ModuleService::class ),
				$c->get( ScopeService::class ),
				$c->get( AuditLogService::class )
			);
		} );

		$this->container->set( AccountFormHandler::class, function ( Container $c ) {
			return new AccountFormHandler(
				$c->get( AclEngine::class ),
				$c->get( AuditLogService::class )
			);
		} );

		$this->container->set( FormHandler::class, function ( Container $c ) {
			return new FormHandler(
				$c->get( AclEngine::class ),
				$c->get( RoleManager::class ),
				$c->get( PermissionRegistry::class ),
				$c->get( AuditLogService::class )
			);
		} );

		$this->container->set( Router::class, function ( Container $c ) {
			return new Router( $c->get( AclEngine::class ) );
		} );

		$this->container->set( AuthIntegration::class, function ( Container $c ) {
			return new AuthIntegration( $c->get( AclEngine::class ) );
		} );

		$this->container->set( RegistrationHandler::class, function ( Container $c ) {
			return new RegistrationHandler(
				$c->get( UserRoleService::class ),
				$c->get( AuditLogService::class )
			);
		} );

		$this->container->set( AccessGuard::class, function ( Container $c ) {
			return new AccessGuard( $c->get( AclEngine::class ) );
		} );

		$this->container->set( RolesController::class, function ( Container $c ) {
			return new RolesController(
				$c->get( RoleManager::class ),
				$c->get( AclEngine::class ),
				$c->get( AuditLogService::class )
			);
		} );

		$this->container->set( PermissionsController::class, function ( Container $c ) {
			return new PermissionsController( $c->get( PermissionRegistry::class ), $c->get( AclEngine::class ) );
		} );

		$this->container->set( AclController::class, function ( Container $c ) {
			return new AclController(
				$c->get( AclEngine::class ),
				$c->get( RoleManager::class ),
				$c->get( PermissionRegistry::class ),
				$c->get( UserRoleService::class ),
				$c->get( AuditLogService::class )
			);
		} );

		$this->container->set( UsersController::class, function ( Container $c ) {
			return new UsersController( $c->get( UserService::class ), $c->get( AclEngine::class ) );
		} );

		$this->container->set( ModulesController::class, function ( Container $c ) {
			return new ModulesController( $c->get( ModuleService::class ), $c->get( AclEngine::class ) );
		} );

		$this->container->set( ScopesController::class, function ( Container $c ) {
			return new ScopesController( $c->get( ScopeService::class ), $c->get( AclEngine::class ) );
		} );

		$this->container->set( AuditLogController::class, function ( Container $c ) {
			return new AuditLogController( $c->get( AuditLogService::class ), $c->get( AclEngine::class ) );
		} );
	}

	/**
	 * Resolve a service from the container.
	 *
	 * @param string $id Service class name.
	 * @return mixed
	 */
	public function get( $id ) {
		return $this->container->get( $id );
	}

	/**
	 * Get the ACL engine.
	 *
	 * @return AclEngine
	 */
	public function acl() {
		return $this->get( AclEngine::class );
	}

	/**
	 * Flush rewrite rules when plugin routes version changes.
	 */
	private function maybe_flush_rewrite_rules() {
		$stored = get_option( 'mpp_routes_version', '' );

		if ( $stored === MPP_VERSION ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( 'mpp_routes_version', MPP_VERSION, false );
	}
}
