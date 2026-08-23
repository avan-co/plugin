<?php

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

class RouterDispatchTest extends TestCase {

	public function test_flush_is_scheduled_on_init_after_rewrite_rules(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Core/Plugin.php' );

		$this->assertStringContainsString( "add_action( 'init', array( \$this, 'maybe_flush_rewrite_rules' ), 99 )", $source );
		$this->assertStringNotContainsString( '$this->maybe_flush_rewrite_rules();', $source );
	}

	public function test_activation_defers_rewrite_flush(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Database/Installer.php' );

		$this->assertStringContainsString( "delete_option( 'mpp_routes_version' )", $source );
		$this->assertStringNotContainsString( "flush_rewrite_rules();", $source );
	}

	public function test_router_resolves_request_uri_fallback(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Core/Router.php' );

		$this->assertStringContainsString( 'resolve_route_slug', $source );
		$this->assertStringContainsString( 'REQUEST_URI', $source );
	}
}
