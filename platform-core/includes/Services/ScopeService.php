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

	/**
	 * Scope types available for assignment in admin UI.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function assignable() {
		$availability = array(
			'all'          => true,
			'own'          => true,
			'department'   => true,
			'team'         => true,
			'organization' => true,
		);

		/**
		 * Filter which scope types are available for assignment.
		 *
		 * @param array<string, bool> $availability Scope slug => implemented.
		 */
		$availability = apply_filters( 'mpp_assignable_scope_types', $availability );

		$rows = array();

		foreach ( $this->all() as $row ) {
			$slug = $row['slug'];

			if ( isset( $availability[ $slug ] ) && ! $availability[ $slug ] ) {
				continue;
			}

			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Whether a scope type requires resource context at enforcement time.
	 *
	 * @param string $scope_type Scope slug.
	 * @return bool
	 */
	public function requires_resource_context( $scope_type ) {
		return in_array( sanitize_key( $scope_type ), array( 'own', 'department', 'team', 'project', 'organization' ), true );
	}
}
