<?php

namespace MPP\Tests\Unit;

use MPP\ACL\PermissionRegistry;
use MPP\Core\Router;
use MPP\Modules\AbstractModule;
use MPP\Modules\ModuleManager;
use PHPUnit\Framework\TestCase;

class ModuleManagerTest extends TestCase {

	protected function tearDown(): void {
		$ref = new \ReflectionClass( ModuleManager::class );
		$prop = $ref->getProperty( 'pending' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		parent::tearDown();
	}

	private function make_manager( $mock_sync = false ): ModuleManager {
		if ( $mock_sync ) {
			$registry = $this->createMock( PermissionRegistry::class );
			$registry->method( 'sync_if_needed' );

			return new ModuleManager( $registry );
		}

		return new ModuleManager( new PermissionRegistry() );
	}

	private function make_module( $slug, $overrides = array() ) {
		return new class( $slug, $overrides ) extends AbstractModule {
			private $slug;
			private $overrides;

			public function __construct( $slug, $overrides ) {
				$this->slug      = $slug;
				$this->overrides = $overrides;
			}

			public function get_slug() {
				return $this->slug;
			}

			public function get_name() {
				return $this->overrides['name'] ?? 'Test Module';
			}

			public function get_version() {
				return $this->overrides['version'] ?? '1.0.0';
			}

			public function get_requires_core_version() {
				return $this->overrides['requires'] ?? '1.0.0';
			}

			public function register_permissions() {
			}

			public function boot() {
			}
		};
	}

	public function test_registers_valid_module(): void {
		$manager = $this->make_manager();

		$this->assertTrue( $manager->register( $this->make_module( 'example' ) ) );
		$this->assertArrayHasKey( 'example', $manager->all() );
	}

	public function test_rejects_duplicate_module(): void {
		$manager = $this->make_manager();
		$module  = $this->make_module( 'example' );

		$this->assertTrue( $manager->register( $module ) );
		$this->assertFalse( $manager->register( $module ) );
		$this->assertCount( 1, $manager->get_rejected() );
	}

	public function test_rejects_invalid_slug(): void {
		$manager = $this->make_manager();

		$this->assertFalse( $manager->register( $this->make_module( 'Invalid_Slug' ) ) );
		$this->assertFalse( $manager->register( $this->make_module( 'core' ) ) );
	}

	public function test_rejects_incompatible_core_version(): void {
		if ( ! defined( 'MPP_VERSION' ) ) {
			define( 'MPP_VERSION', '1.3.0' );
		}

		$manager = $this->make_manager();

		$this->assertFalse(
			$manager->register(
				$this->make_module(
					'future',
					array( 'requires' => '99.0.0' )
				)
			)
		);
	}

	public function test_enqueue_registers_on_boot(): void {
		ModuleManager::enqueue( $this->make_module( 'queued' ) );

		$manager = $this->make_manager( true );
		$manager->boot();

		$this->assertArrayHasKey( 'queued', $manager->all() );
		$this->assertTrue( $manager->is_booted() );
	}

	public function test_lifecycle_runs_in_order(): void {
		$calls = array();

		$module = new class( $calls ) extends AbstractModule {
			private $calls;

			public function __construct( &$calls ) {
				$this->calls = &$calls;
			}

			public function get_slug() {
				return 'lifecycle';
			}

			public function get_name() {
				return 'Lifecycle';
			}

			public function run_migrations() {
				$this->calls[] = 'migrate';
			}

			public function register_permissions() {
				$this->calls[] = 'permissions';
			}

			public function boot() {
				$this->calls[] = 'boot';
			}
		};

		$manager = $this->make_manager( true );
		$manager->register( $module );
		$manager->boot();

		$this->assertSame( array( 'migrate', 'permissions', 'boot' ), $calls );
	}

	public function test_registers_module_routes(): void {
		$module = new class() extends AbstractModule {
			public $registered = false;

			public function get_slug() {
				return 'routes';
			}

			public function get_name() {
				return 'Routes';
			}

			public function register_permissions() {
			}

			public function register_routes( Router $router ) {
				$this->registered = true;
				$router->add_route(
					'app/routes-test',
					array(
						'template'   => 'templates/test.php',
						'permission' => 'core.panel.user.access',
					)
				);
			}

			public function boot() {
			}
		};

		$manager = $this->make_manager( true );
		$manager->register( $module );
		$manager->boot();

		$router = new Router( new \MPP\ACL\AclEngine( new PermissionRegistry(), new \MPP\ACL\RoleManager(), new \MPP\ACL\ScopeResolver() ) );
		$manager->register_module_routes( $router );

		$this->assertTrue( $module->registered );
		$this->assertArrayHasKey( 'app/routes-test', $router->get_routes() );
	}

	public function test_exposes_navigation_items(): void {
		$module = new class() extends AbstractModule {
			public function get_slug() {
				return 'nav';
			}

			public function get_name() {
				return 'Nav';
			}

			public function register_permissions() {
			}

			public function get_navigation_items() {
				return array(
					array(
						'label' => 'Nav Item',
						'url'   => '/app/nav',
						'panel' => 'user',
					),
				);
			}

			public function boot() {
			}
		};

		$manager = $this->make_manager( true );
		$manager->register( $module );
		$manager->boot();

		$this->assertCount( 1, $manager->get_navigation_items() );
		$this->assertSame( 'Nav Item', $manager->get_navigation_items()[0]['label'] );
	}

	public function test_deactivate_module_calls_hook(): void {
		$deactivated = false;

		$module = new class( $deactivated ) extends AbstractModule {
			private $flag;

			public function __construct( &$flag ) {
				$this->flag = &$flag;
			}

			public function get_slug() {
				return 'deactivate-me';
			}

			public function get_name() {
				return 'Deactivate';
			}

			public function register_permissions() {
			}

			public function boot() {
			}

			public function deactivate() {
				$this->flag = true;
			}
		};

		$manager = $this->make_manager( true );
		$manager->register( $module );
		$manager->boot();

		$this->assertTrue( $manager->deactivate_module( 'deactivate-me' ) );
		$this->assertTrue( $deactivated );
	}
}
