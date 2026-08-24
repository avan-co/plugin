<?php
/**
 * PlatformSettings tests.
 *
 * @package PlatformCore
 */

namespace MPP\Tests\Unit;

use MPP\Settings\PlatformSettings;
use PHPUnit\Framework\TestCase;

/**
 * Class PlatformSettingsTest
 */
class PlatformSettingsTest extends TestCase {

	/**
	 * save rejects invalid accent colors.
	 */
	public function test_save_rejects_invalid_accent_color(): void {
		$settings = new PlatformSettings();
		$errors   = $settings->save(
			array(
				'accent_color' => 'not-a-color',
			)
		);

		$this->assertArrayHasKey( 'accent_color', $errors );
	}

	/**
	 * save accepts valid branding payload without validation errors.
	 */
	public function test_save_accepts_branding_fields(): void {
		$settings = new PlatformSettings();
		$errors   = $settings->save(
			array(
				'platform_name' => 'Avan Platform',
				'logo_mark'     => 'AB',
				'accent_color'  => '#112233',
				'timezone'      => 'UTC',
			)
		);

		$this->assertSame( array(), $errors );
	}

	/**
	 * save rejects invalid timezone identifiers.
	 */
	public function test_save_rejects_invalid_timezone(): void {
		$settings = new PlatformSettings();
		$errors   = $settings->save(
			array(
				'timezone' => 'Not/A/Timezone',
			)
		);

		$this->assertArrayHasKey( 'timezone', $errors );
	}
}
