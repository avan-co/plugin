<?php

namespace MPP\Tests\Unit;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\ACL\ScopeResolver;
use MPP\Core\Router;
use PHPUnit\Framework\TestCase;

class RouteDefinitionsTest extends TestCase {

	public function test_protected_routes_define_server_side_permissions(): void {
		$engine = new AclEngine( new PermissionRegistry(), new RoleManager(), new ScopeResolver() );
		$router = new Router( $engine );
		$routes = $router->get_routes();

		$protected = array(
			'app'          => 'core.panel.access',
			'app/user'     => 'core.panel.user.access',
			'app/manager'  => 'core.panel.manager.access',
			'app/admin'    => 'core.panel.admin.access',
			'profile'      => 'core.profile.view',
			'settings'     => 'core.settings.view',
		);

		foreach ( $protected as $slug => $permission ) {
			$this->assertArrayHasKey( $slug, $routes );
			$this->assertSame( $permission, $routes[ $slug ]['permission'] );
			$this->assertTrue( $routes[ $slug ]['auth'] );
		}
	}

	public function test_public_routes_do_not_require_auth(): void {
		$engine = new AclEngine( new PermissionRegistry(), new RoleManager(), new ScopeResolver() );
		$router = new Router( $engine );
		$routes = $router->get_routes();

		foreach ( array( 'login', '403', '404' ) as $slug ) {
			$this->assertArrayHasKey( $slug, $routes );
			$this->assertFalse( $routes[ $slug ]['auth'] );
			$this->assertEmpty( $routes[ $slug ]['permission'] );
		}
	}
}
