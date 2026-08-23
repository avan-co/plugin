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
use MPP\ACL\ScopeResolver;
use MPP\API\AclController;
use MPP\API\PermissionsController;
use MPP\API\RolesController;
use MPP\Auth\AccessGuard;
use MPP\Auth\AuthIntegration;
use MPP\Database\Installer;
use MPP\Modules\ModuleManager;
use MPP\Services\PermissionService;
use MPP\Services\UserRoleService;

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

		$this->container->get( ModuleManager::class )->boot();
		$this->container->get( Router::class )->register();
		$this->container->get( AuthIntegration::class )->register();
		$this->container->get( AccessGuard::class )->register();
		$this->container->get( RolesController::class )->register();
		$this->container->get( PermissionsController::class )->register();
		$this->container->get( AclController::class )->register();

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

		$this->container->set( RoleManager::class, function () {
			return new RoleManager();
		} );

		$this->container->set( AclEngine::class, function ( Container $c ) {
			return new AclEngine(
				$c->get( PermissionRegistry::class ),
				$c->get( RoleManager::class ),
				$c->get( ScopeResolver::class )
			);
		} );

		$this->container->set( PermissionService::class, function ( Container $c ) {
			return new PermissionService( $c->get( PermissionRegistry::class ) );
		} );

		$this->container->set( UserRoleService::class, function ( Container $c ) {
			return new UserRoleService( $c->get( RoleManager::class ) );
		} );

		$this->container->set( ModuleManager::class, function ( Container $c ) {
			return new ModuleManager( $c->get( PermissionRegistry::class ) );
		} );

		$this->container->set( Router::class, function ( Container $c ) {
			return new Router( $c->get( AclEngine::class ) );
		} );

		$this->container->set( AuthIntegration::class, function ( Container $c ) {
			return new AuthIntegration( $c->get( AclEngine::class ) );
		} );

		$this->container->set( AccessGuard::class, function ( Container $c ) {
			return new AccessGuard( $c->get( AclEngine::class ) );
		} );

		$this->container->set( RolesController::class, function ( Container $c ) {
			return new RolesController( $c->get( RoleManager::class ), $c->get( AclEngine::class ) );
		} );

		$this->container->set( PermissionsController::class, function ( Container $c ) {
			return new PermissionsController( $c->get( PermissionRegistry::class ), $c->get( AclEngine::class ) );
		} );

		$this->container->set( AclController::class, function ( Container $c ) {
			return new AclController(
				$c->get( AclEngine::class ),
				$c->get( RoleManager::class ),
				$c->get( PermissionRegistry::class ),
				$c->get( UserRoleService::class )
			);
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
}
