<?php
/**
 * DashboardService tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use MPP\ACL\AclEngine;
use MPP\ACL\RoleManager;
use MPP\Panels\DashboardService;
use MPP\Services\AuditLogService;
use MPP\Services\ModuleService;
use MPP\Services\UserRoleService;
use PHPUnit\Framework\TestCase;

/**
 * Class DashboardServiceTest
 */
class DashboardServiceTest extends TestCase {

	/**
	 * get_module_shortcuts skips core panel routes.
	 */
	public function test_get_module_shortcuts_excludes_core_routes(): void {
		if ( ! function_exists( 'mpp_get_panel_navigation' ) ) {
			/**
			 * Test stub for panel navigation.
			 *
			 * @param string $panel Panel slug.
			 * @return array<int, array<string, string>>
			 */
			function mpp_get_panel_navigation( $panel ) {
				if ( 'manager' === $panel ) {
					return array(
						array(
							'label' => 'Dashboard',
							'url'   => '/app/manager',
							'route' => 'app/manager',
						),
						array(
							'label' => 'Team Reports',
							'url'   => '/app/reports',
							'route' => 'app/reports',
						),
					);
				}

				return array(
					array(
						'label' => 'Dashboard',
						'url'   => '/app/user',
						'route' => 'app/user',
					),
					array(
						'label'       => 'Example Demo',
						'url'         => '/app/example',
						'route'       => 'app/example',
						'description' => 'Example module page',
					),
				);
			}
		}

		$service   = $this->make_service();
		$shortcuts = $service->get_module_shortcuts( 'user' );

		$this->assertCount( 1, $shortcuts );
		$this->assertSame( 'Example Demo', $shortcuts[0]['label'] );
		$this->assertSame( '/app/example', $shortcuts[0]['url'] );
		$this->assertSame( 'E', $shortcuts[0]['icon'] );
	}

	/**
	 * get_module_shortcuts excludes manager core routes.
	 */
	public function test_get_module_shortcuts_excludes_manager_core_routes(): void {
		$service   = $this->make_service();
		$shortcuts = $service->get_module_shortcuts( 'manager' );

		$this->assertCount( 1, $shortcuts );
		$this->assertSame( 'Team Reports', $shortcuts[0]['label'] );
	}

	/**
	 * get_pending_items returns an empty list by default.
	 */
	public function test_get_pending_items_returns_empty_by_default(): void {
		$service = $this->make_service();

		$this->assertSame( array(), $service->get_pending_items( 1 ) );
	}

	/**
	 * Build a dashboard service with mocked dependencies.
	 *
	 * @return DashboardService
	 */
	private function make_service() {
		$acl        = $this->getMockBuilder( AclEngine::class )->disableOriginalConstructor()->getMock();
		$user_roles = $this->getMockBuilder( UserRoleService::class )->disableOriginalConstructor()->getMock();
		$audit      = $this->getMockBuilder( AuditLogService::class )->disableOriginalConstructor()->getMock();
		$modules    = $this->getMockBuilder( ModuleService::class )->disableOriginalConstructor()->getMock();
		$roles      = $this->getMockBuilder( RoleManager::class )->disableOriginalConstructor()->getMock();

		return new DashboardService( $acl, $user_roles, $audit, $modules, $roles );
	}
}
