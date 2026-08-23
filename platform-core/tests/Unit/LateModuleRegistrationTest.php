<?php

namespace MPP\Tests\Unit;

use MPP\ACL\PermissionRegistry;
use MPP\Modules\AbstractModule;
use MPP\Modules\ModuleManager;
use PHPUnit\Framework\TestCase;

class LateModuleRegistrationTest extends TestCase {

	protected function tearDown(): void {
		$ref = new \ReflectionClass( ModuleManager::class );
		$prop = $ref->getProperty( 'pending' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		parent::tearDown();
	}

	public function test_post_boot_registration_runs_permission_lifecycle(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->expects( $this->once() )->method( 'sync_if_needed' );

		$manager = new ModuleManager( $registry );

		$module = new class() extends AbstractModule {
			public $permissions_registered = false;
			public $booted = false;

			public function get_slug() {
				return 'late';
			}

			public function get_name() {
				return 'Late Module';
			}

			public function register_permissions() {
				$this->permissions_registered = true;
			}

			public function boot() {
				$this->booted = true;
			}
		};

		$ref = new \ReflectionClass( ModuleManager::class );
		$booted = $ref->getProperty( 'booted' );
		$booted->setAccessible( true );
		$booted->setValue( $manager, true );

		$this->assertTrue( $manager->register( $module ) );
		$this->assertTrue( $module->permissions_registered );
		$this->assertTrue( $module->booted );
	}

	public function test_example_module_registers_view_and_manage_permissions(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-example/includes/ExampleModule.php' );

		$this->assertStringContainsString( "'view'", $source );
		$this->assertStringContainsString( "'manage'", $source );
		$this->assertStringContainsString( 'get_dashboard_widgets', $source );
	}
}
