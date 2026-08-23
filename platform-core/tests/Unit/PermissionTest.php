<?php

namespace MPP\Tests\Unit;

use MPP\ACL\Permission;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase {

	public function test_build_key_preserves_dots_in_action_segment(): void {
		$this->assertSame(
			'core.panel.user.access',
			Permission::build_key( 'core', 'panel', 'user.access' )
		);
	}

	public function test_build_key_for_standard_three_part_permission(): void {
		$this->assertSame(
			'finance.invoice.view',
			Permission::build_key( 'finance', 'invoice', 'view' )
		);
	}

	public function test_from_key_round_trip_for_panel_permissions(): void {
		$key = 'core.panel.manager.access';
		$permission = Permission::from_key( $key );

		$this->assertNotNull( $permission );
		$this->assertSame( $key, $permission->key );
	}

	public function test_from_key_rejects_invalid_keys(): void {
		$this->assertNull( Permission::from_key( 'invalid' ) );
		$this->assertNull( Permission::from_key( '' ) );
		$this->assertNull( Permission::from_key( 'core.panel' ) );
	}

	public function test_is_valid_key_accepts_documented_format(): void {
		$this->assertTrue( Permission::is_valid_key( 'core.panel.access' ) );
		$this->assertTrue( Permission::is_valid_key( 'core.panel.user.access' ) );
		$this->assertTrue( Permission::is_valid_key( 'finance.invoice.approve' ) );
	}

	public function test_normalize_segment_strips_unsafe_characters_but_keeps_dots(): void {
		$this->assertSame( 'user.access', Permission::normalize_segment( 'User.Access!' ) );
	}
}
