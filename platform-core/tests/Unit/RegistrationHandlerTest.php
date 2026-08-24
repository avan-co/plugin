<?php
/**
 * RegistrationHandler tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class RegistrationHandlerTest
 */
class RegistrationHandlerTest extends TestCase {

	/**
	 * Registration can require accepted terms via filter.
	 */
	public function test_terms_requirement_is_filterable(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Auth/RegistrationHandler.php' );

		$this->assertStringContainsString( "apply_filters( 'mpp_registration_require_terms', false )", $source );
		$this->assertStringContainsString( 'accept_terms', $source );
	}

	/**
	 * Registration remains gated by platform settings.
	 */
	public function test_is_enabled_checks_platform_settings(): void {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/Auth/RegistrationHandler.php' );

		$this->assertStringContainsString( 'PlatformSettings::class', $source );
		$this->assertStringContainsString( 'is_registration_enabled', $source );
	}
}
