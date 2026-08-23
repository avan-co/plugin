<?php

namespace MPP\Tests\Unit;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\ACL\ScopeResolver;
use MPP\Admin\AdminRoutes;
use MPP\Core\Router;
use PHPUnit\Framework\TestCase;

class AdminRoutesTest extends TestCase {

	public function test_admin_sub_routes_require_acl_manage_permission(): void {
		$engine = new AclEngine( new PermissionRegistry(), new RoleManager(), new ScopeResolver() );
		$router = new Router( $engine );
		AdminRoutes::register( $router );
		$routes = $router->get_routes();

		$admin_routes = array(
			'app/admin/users',
			'app/admin/roles',
			'app/admin/permissions',
			'app/admin/modules',
			'app/admin/acl',
			'app/admin/settings',
		);

		foreach ( $admin_routes as $slug ) {
			$this->assertArrayHasKey( $slug, $routes );
			$this->assertSame( 'core.acl.manage', $routes[ $slug ]['permission'] );
			$this->assertTrue( $routes[ $slug ]['auth'] );
			$this->assertStringStartsWith( 'templates/admin/', $routes[ $slug ]['template'] );
		}
	}
}
