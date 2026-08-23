<?php
/**
 * Scope listing service.
 *
 * @package PlatformCore
 */

namespace MPP\Services;

use MPP\ACL\ScopeResolver;
use MPP\Database\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Class ScopeService
 */
class ScopeService {

	/**
	 * Scope resolver.
	 *
	 * @var ScopeResolver
	 */
	private $resolver;

	/**
	 * Constructor.
	 *
	 * @param ScopeResolver $resolver Scope resolver.
	 */
	public function __construct( ScopeResolver $resolver ) {
		$this->resolver = $resolver;
	}

	/**
	 * List all scope types from DB and resolver.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function all() {
		global $wpdb;

		$rows = $wpdb->get_results( 'SELECT * FROM ' . Schema::table( 'scopes' ) . ' ORDER BY name ASC', ARRAY_A );
		$types = $this->resolver->get_scope_types();

		foreach ( $rows as &$row ) {
			if ( isset( $types[ $row['slug'] ] ) ) {
				$row['label'] = $types[ $row['slug'] ];
			} else {
				$row['label'] = $row['name'];
			}
		}

		return $rows;
	}
}
