<?php

namespace MPP\Tests\Unit;

use MPP\Modules\AbstractModule;
use MPP\Modules\ModuleInterface;
use PHPUnit\Framework\TestCase;

class AbstractModuleTest extends TestCase {

	public function test_implements_module_interface_extension_points(): void {
		$module = new class() extends AbstractModule {
			public function get_slug() {
				return 'test';
			}

			public function get_name() {
				return 'Test';
			}

			public function register_permissions() {
			}

			public function boot() {
			}
		};

		$this->assertInstanceOf( ModuleInterface::class, $module );
		$this->assertSame( '1.0.0', $module->get_version() );
		$this->assertSame( array(), $module->get_navigation_items() );
		$this->assertSame( array(), $module->get_dashboard_widgets() );
	}
}
