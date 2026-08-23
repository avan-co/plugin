<?php

namespace MPP\Tests\Integration;

use MPP\ACL\PermissionRegistry;
use MPP\Modules\AbstractModule;
use MPP\Modules\ModuleManager;
use PHPUnit\Framework\TestCase;

/**
 * Integration-style tests for module permission registration pipeline.
 */
class PermissionPipelineIntegrationTest extends TestCase {

	private $permissions_table = 'wp_mpp_permissions';
	private $role_permissions_table = 'wp_mpp_role_permissions';
	public $permission_rows = array();
	public $role_permission_rows = array();

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\setUp();

		$this->permission_rows       = array();
		$this->role_permission_rows  = array();
		$next_id                     = 1;

		global $wpdb;
		$wpdb = new class( $this ) {
			public $users = 'wp_users';
			public $prefix = 'wp_';
			private $test;

			public function __construct( $test ) {
				$this->test = $test;
			}

			public function prepare( $query, ...$args ) {
				foreach ( $args as $index => $arg ) {
					$query = preg_replace( '/%[dfs]/', "'" . (string) $arg . "'", $query, 1 );
				}
				return $query;
			}

			public function get_var( $query ) {
				if ( false !== strpos( $query, 'permission_key' ) ) {
					foreach ( $this->test->permission_rows as $row ) {
						if ( false !== strpos( $query, $row['permission_key'] ) ) {
							return $row['id'];
						}
					}
				}
				return null;
			}

			public function get_results( $query, $mode = OBJECT ) {
				unset( $mode );
				if ( false === strpos( $query, 'mpp_permissions' ) ) {
					return array();
				}

				return $this->test->permission_rows;
			}

			public function insert( $table, $data, $format = null ) {
				unset( $table, $format );
				static $id = 1;
				$data['id'] = $id++;
				$this->test->permission_rows[] = $data;
				return 1;
			}

			public function update( $table, $data, $where, $format = null, $where_format = null ) {
				unset( $table, $format, $where_format );
				foreach ( $this->test->permission_rows as &$row ) {
					if ( (int) $row['id'] === (int) $where['id'] ) {
						$row = array_merge( $row, $data );
						return 1;
					}
				}
				return 0;
			}

			public function delete( $table, $where, $where_format = null ) {
				unset( $table, $where_format );
				if ( isset( $where['id'] ) ) {
					$this->test->permission_rows = array_values(
						array_filter(
							$this->test->permission_rows,
							function ( $row ) use ( $where ) {
								return (int) $row['id'] !== (int) $where['id'];
							}
						)
					);
				}
				if ( isset( $where['permission_id'] ) ) {
					$this->test->role_permission_rows = array_values(
						array_filter(
							$this->test->role_permission_rows,
							function ( $row ) use ( $where ) {
								return (int) $row['permission_id'] !== (int) $where['permission_id'];
							}
						)
					);
				}
				return 1;
			}
		};

		\Brain\Monkey\Functions\when( 'current_time' )->justReturn( '2026-01-01 00:00:00' );
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	public function test_module_registers_permissions_and_syncs_to_database(): void {
		$registry = new PermissionRegistry();
		$registry->register_module(
			'example',
			array(
				'demo' => array(
					'view'   => 'View Example Demo',
					'manage' => 'Manage Example Demo',
				),
			)
		);
		$registry->sync_to_database();

		$keys = array_column( $this->permission_rows, 'permission_key' );
		$this->assertContains( 'example.demo.view', $keys );
		$this->assertContains( 'example.demo.manage', $keys );
	}

	public function test_unregister_module_clears_registry_entries(): void {
		$registry = new PermissionRegistry();
		$registry->register_module(
			'example',
			array(
				'demo' => array(
					'view' => 'View Example Demo',
				),
			)
		);

		$this->assertArrayHasKey( 'example.demo.view', $registry->get_registered() );
		$registry->unregister_module( 'example' );
		$this->assertArrayNotHasKey( 'example.demo.view', $registry->get_registered() );
	}

	public function test_late_module_registration_runs_permission_lifecycle(): void {
		$registry = $this->createMock( PermissionRegistry::class );
		$registry->method( 'sync_if_needed' );

		$manager = new ModuleManager( $registry );
		$module  = new class() extends AbstractModule {
			public $registered = false;

			public function get_slug() {
				return 'late';
			}

			public function get_name() {
				return 'Late';
			}

			public function register_permissions() {
				$this->registered = true;
			}
		};

		$ref = new \ReflectionClass( ModuleManager::class );
		$booted = $ref->getProperty( 'booted' );
		$booted->setAccessible( true );
		$booted->setValue( $manager, true );

		$manager->register( $module );
		$this->assertTrue( $module->registered );
	}
}
