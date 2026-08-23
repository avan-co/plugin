<?php
/**
 * Builds ACL scope context for users.
 *
 * @package PlatformCore
 */

namespace MPP\ACL;

defined( 'ABSPATH' ) || exit;

/**
 * Class ScopeContextService
 */
class ScopeContextService {

	/**
	 * Build default scope context for a user.
	 *
	 * Modules may extend via the mpp_acl_context filter.
	 *
	 * @param int $user_id User ID.
	 * @return array<string, mixed>
	 */
	public function for_user( $user_id ) {
		$user_id = (int) $user_id;

		$context = array(
			'owner_id'              => $user_id,
			'user_department_id'    => (int) get_user_meta( $user_id, 'mpp_department_id', true ),
			'user_organization_id'  => (int) get_user_meta( $user_id, 'mpp_organization_id', true ),
			'user_team_ids'         => $this->parse_id_list( get_user_meta( $user_id, 'mpp_team_ids', true ) ),
			'user_project_ids'      => $this->parse_id_list( get_user_meta( $user_id, 'mpp_project_ids', true ) ),
		);

		/**
		 * Filter scope context before ACL evaluation.
		 *
		 * @param array<string, mixed> $context Context values.
		 * @param int                  $user_id User ID.
		 */
		return apply_filters( 'mpp_acl_context', $context, $user_id );
	}

	/**
	 * Parse a comma-separated or array user meta value into int IDs.
	 *
	 * @param mixed $value Raw meta value.
	 * @return array<int, int>
	 */
	private function parse_id_list( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'intval', $value );
		}

		if ( empty( $value ) ) {
			return array();
		}

		$parts = array_map( 'trim', explode( ',', (string) $value ) );

		return array_values( array_filter( array_map( 'intval', $parts ) ) );
	}
}
