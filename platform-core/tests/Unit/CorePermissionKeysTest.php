<?php

namespace MPP\Tests\Unit;

use MPP\ACL\PermissionRegistry;
use MPP\Modules\ModuleManager;
use PHPUnit\Framework\TestCase;

class CorePermissionKeysTest extends TestCase {

	public function test_core_module_registers_expected_route_permission_keys(): void {
		$registry = new PermissionRegistry();
		$manager  = new ModuleManager( $registry );
		$manager->register_core_module();

		$registered = $registry->get_registered();
		$keys       = array_keys( $registered );

		$expected = array(
			'core.panel.access',
			'core.panel.user.access',
			'core.panel.manager.access',
			'core.panel.admin.access',
			'core.profile.view',
			'core.profile.edit',
			'core.settings.view',
			'core.settings.edit',
			'core.acl.manage',
		);

		foreach ( $expected as $key ) {
			$this->assertContains( $key, $keys, "Missing permission key: {$key}" );
		}
	}
}
