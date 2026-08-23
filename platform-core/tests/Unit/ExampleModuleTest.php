<?php

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleModuleTest extends TestCase {

	public function test_example_plugin_defers_loading_until_core_available(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-example/platform-example.php' );
		$before = strstr( $source, 'function mpp_example_bootstrap', true );

		$this->assertIsString( $before );
		$this->assertStringNotContainsString( 'ExampleModule.php', $before );
		$this->assertStringContainsString( 'mpp_example_bootstrap', $source );
		$this->assertStringContainsString( "add_action( 'plugins_loaded', 'mpp_example_bootstrap', 20 )", $source );
	}

	public function test_example_plugin_shows_notice_when_core_missing(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-example/platform-example.php' );

		$this->assertStringContainsString( 'mpp_example_missing_core_notice', $source );
		$this->assertStringContainsString( "defined( 'MPP_VERSION' )", $source );
	}

	public function test_example_module_uses_route_helper_for_navigation(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-example/includes/ExampleModule.php' );

		$this->assertStringContainsString( 'mpp_route_url', $source );
		$this->assertStringContainsString( 'example.demo.view', $source );
	}

	public function test_example_module_implements_boot_method(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-example/includes/ExampleModule.php' );

		$this->assertStringContainsString( 'public function boot()', $source );
	}

	public function test_abstract_module_provides_default_boot(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Modules/AbstractModule.php' );

		$this->assertStringContainsString( 'public function boot()', $source );
	}

	public function test_theme_design_tokens_exist(): void {
		$tokens = file_get_contents( dirname( __DIR__, 3 ) . '/platform-theme/assets/css/tokens.css' );

		$this->assertStringContainsString( '--mpp-primary', $tokens );
		$this->assertStringContainsString( '--mpp-muted-foreground', $tokens );
		$this->assertStringContainsString( '--mpp-radius-md', $tokens );
	}

	public function test_theme_ui_components_exist(): void {
		$source = file_get_contents( dirname( __DIR__, 3 ) . '/platform-theme/inc/ui-components.php' );

		$this->assertStringContainsString( 'platform_ui_page_header', $source );
		$this->assertStringContainsString( 'platform_ui_empty_state', $source );
	}
}
