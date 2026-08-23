<?php

namespace MPP\Tests\Unit;

use MPP\ACL\PermissionRegistry;
use PHPUnit\Framework\TestCase;

class PermissionRegistryTest extends TestCase {

	public function test_register_module_builds_dotted_permission_keys(): void {
		$registry = new PermissionRegistry();
		$registry->register_module(
			'example',
			array(
				'demo' => array(
					'view'   => 'View',
					'manage' => 'Manage',
				),
			)
		);

		$registered = $registry->get_registered();
		$this->assertArrayHasKey( 'example.demo.view', $registered );
		$this->assertArrayHasKey( 'example.demo.manage', $registered );
	}

	public function test_registry_hash_changes_when_keys_change(): void {
		$registry = new PermissionRegistry();
		$registry->register( 'core', 'panel', 'access', 'Access' );
		$hash_one = $registry->get_registry_hash();

		$registry->register( 'example', 'demo', 'view', 'View' );
		$hash_two = $registry->get_registry_hash();

		$this->assertNotSame( $hash_one, $hash_two );
	}

	public function test_unregister_module_removes_memory_entries(): void {
		global $wpdb;
		$wpdb = new class() {
			public $prefix = 'wp_';

			public function get_results( $query, $mode = null ) {
				unset( $query, $mode );
				return array();
			}

			public function delete() {
				return 1;
			}
		};

		$registry = new PermissionRegistry();
		$registry->register_module(
			'example',
			array(
				'demo' => array(
					'view' => 'View',
				),
			)
		);

		$registry->unregister_module( 'example' );
		$this->assertEmpty( $registry->get_registered() );
	}
}
