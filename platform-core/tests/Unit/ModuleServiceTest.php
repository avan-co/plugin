<?php
/**
 * ModuleService tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use MPP\ACL\PermissionRegistry;
use MPP\Modules\ModuleManager;
use MPP\Services\ModuleService;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleServiceTest
 */
class ModuleServiceTest extends TestCase {

	/**
	 * find_module returns a matching module record.
	 */
	public function test_find_module_returns_match(): void {
		$modules = $this->getMockBuilder( ModuleManager::class )->disableOriginalConstructor()->getMock();
		$registry = $this->getMockBuilder( PermissionRegistry::class )->disableOriginalConstructor()->getMock();

		$example = $this->getMockBuilder( \MPP\Modules\ModuleInterface::class )->getMock();
		$example->method( 'get_name' )->willReturn( 'Example' );
		$example->method( 'get_version' )->willReturn( '1.0.0' );
		$example->method( 'get_requires_core_version' )->willReturn( '1.0.0' );

		$modules->method( 'all' )->willReturn( array( 'example' => $example ) );
		$registry->method( 'get_grouped' )->willReturn(
			array(
				'example' => array(
					'demo' => array(
						array(
							'id'          => 1,
							'action'      => 'view',
							'key'         => 'example.demo.view',
							'description' => 'View demo',
						),
					),
				),
			)
		);

		$service = new ModuleService( $modules, $registry );
		$module  = $service->find_module( 'example' );

		$this->assertNotNull( $module );
		$this->assertSame( 'example', $module['slug'] );
		$this->assertSame( 1, $module['permission_count'] );
	}

	/**
	 * get_module_permissions flattens grouped permissions.
	 */
	public function test_get_module_permissions_returns_flat_list(): void {
		$modules = $this->getMockBuilder( ModuleManager::class )->disableOriginalConstructor()->getMock();
		$registry = $this->getMockBuilder( PermissionRegistry::class )->disableOriginalConstructor()->getMock();

		$modules->method( 'all' )->willReturn( array() );
		$registry->method( 'get_grouped' )->willReturn(
			array(
				'example' => array(
					'demo' => array(
						array(
							'id'     => 2,
							'action' => 'manage',
							'key'    => 'example.demo.manage',
						),
					),
				),
			)
		);

		$service = new ModuleService( $modules, $registry );
		$items   = $service->get_module_permissions( 'example' );

		$this->assertCount( 1, $items );
		$this->assertSame( 'example.demo.manage', $items[0]['key'] );
		$this->assertSame( 'demo', $items[0]['resource'] );
	}
}
