<?php
/**
 * Input sanitization helpers.
 *
 * @package PlatformCore
 */

namespace MPP\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Class Sanitizer
 */
class Sanitizer {

	/**
	 * Sanitize role input data.
	 *
	 * @param array<string, mixed> $data Raw input.
	 * @return array<string, string>
	 */
	public static function role( array $data ) {
		return array(
			'slug'        => isset( $data['slug'] ) ? sanitize_key( $data['slug'] ) : '',
			'name'        => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
			'description' => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
		);
	}
}
