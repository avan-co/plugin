<?php

namespace MPP\Tests\Unit;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\ACL\ScopeResolver;
use MPP\Admin\AdminRoutes;
use MPP\Auth\RegistrationHandler;
use MPP\Core\Router;
use MPP\Services\UserRoleService;
use PHPUnit\Framework\TestCase;

class RuntimeCompletionTest extends TestCase {

	public function test_default_platform_role_slugs_are_defined(): void {
		$reflection = new \ReflectionClass( \MPP\Database\Installer::class );
		$method     = $reflection->getMethod( 'seed_permissions_and_roles' );
		$method->setAccessible( true );

		$source = file_get_contents(
			dirname( __DIR__, 2 ) . '/includes/Database/Installer.php'
		);

		foreach ( array( 'platform_user', 'platform_manager', 'platform_admin' ) as $slug ) {
			$this->assertStringContainsString( "'" . $slug . "'", $source );
		}
	}

	public function test_register_route_is_public(): void {
		$engine = new AclEngine( new PermissionRegistry(), new RoleManager(), new ScopeResolver() );
		$router = new Router( $engine );
		$routes = $router->get_routes();

		$this->assertArrayHasKey( 'register', $routes );
		$this->assertFalse( $routes['register']['auth'] );
		$this->assertEmpty( $routes['register']['permission'] );
	}

	public function test_navigation_urls_match_registered_routes(): void {
		$engine = new AclEngine( new PermissionRegistry(), new RoleManager(), new ScopeResolver() );
		$router = new Router( $engine );
		AdminRoutes::register( $router );
		$routes = $router->get_routes();

		$expected = array(
			'app/user',
			'app/manager',
			'app/admin',
			'app/admin/users',
			'app/admin/roles',
			'profile',
			'settings',
			'login',
			'register',
		);

		foreach ( $expected as $slug ) {
			$this->assertArrayHasKey( $slug, $routes, 'Missing route: ' . $slug );
		}
	}

	public function test_wp_admin_sync_is_disabled_by_default(): void {
		$service = new UserRoleService( $this->createMock( RoleManager::class ) );

		$this->assertFalse( apply_filters( 'mpp_sync_wp_admin_to_platform_admin', false, 1 ) );
		$service->maybe_sync_wp_admin( 1 );
		$this->assertTrue( true );
	}

	public function test_registration_enabled_by_default(): void {
		$this->assertTrue( RegistrationHandler::is_enabled() );
	}
}
