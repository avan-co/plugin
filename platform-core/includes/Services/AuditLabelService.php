<?php
/**
 * Human-readable labels for audit log entries.
 *
 * @package PlatformCore
 */

namespace MPP\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Class AuditLabelService
 */
class AuditLabelService {

	/**
	 * Map internal audit action keys to readable labels.
	 *
	 * @return array<string, string>
	 */
	private function get_action_labels() {
		$labels = array(
			'role.created'           => __( 'Role created', 'platform-core' ),
			'role.updated'           => __( 'Role updated', 'platform-core' ),
			'role.deleted'           => __( 'Role deleted', 'platform-core' ),
			'permission.granted'     => __( 'Permission granted', 'platform-core' ),
			'permission.revoked'     => __( 'Permission revoked', 'platform-core' ),
			'permission.scope.updated' => __( 'Permission scope updated', 'platform-core' ),
			'scope.changed'            => __( 'Permission scope updated', 'platform-core' ),
			'role.permissions.updated' => __( 'Role permissions saved', 'platform-core' ),
			'user.role.assigned'     => __( 'Role assigned to user', 'platform-core' ),
			'user.role.revoked'      => __( 'Role removed from user', 'platform-core' ),
			'user.registered'        => __( 'User registered', 'platform-core' ),
			'profile.updated'        => __( 'Profile updated', 'platform-core' ),
			'settings.updated'       => __( 'Settings updated', 'platform-core' ),
			'role.permissions.saved' => __( 'Role permissions saved', 'platform-core' ),
		);

		/**
		 * Filter human-readable audit action labels.
		 *
		 * @param array<string, string> $labels Action key => label.
		 */
		return apply_filters( 'mpp_audit_action_labels', $labels );
	}

	/**
	 * Get a human-readable label for an audit action key.
	 *
	 * @param string $action Action slug.
	 * @return string
	 */
	public function get_action_label( $action ) {
		$action = sanitize_key( $action );
		$labels = $this->get_action_labels();

		if ( isset( $labels[ $action ] ) ) {
			return $labels[ $action ];
		}

		$parts = explode( '.', str_replace( '_', ' ', $action ) );
		$parts = array_map( 'ucfirst', $parts );

		return implode( ' ', $parts );
	}

	/**
	 * Format an audit timestamp for display.
	 *
	 * @param string $datetime MySQL datetime string.
	 * @return string
	 */
	public function format_datetime( $datetime ) {
		if ( empty( $datetime ) ) {
			return '';
		}

		$timestamp = mysql2date( 'U', $datetime, false );

		if ( ! $timestamp ) {
			return $datetime;
		}

		return wp_date(
			get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			(int) $timestamp
		);
	}

	/**
	 * Build a readable object summary for an audit row.
	 *
	 * @param array<string, mixed> $entry Audit entry row.
	 * @return string
	 */
	public function format_object_summary( array $entry ) {
		$type = $entry['object_type'] ?? '';
		$id   = $entry['object_id'] ?? '';

		if ( '' === $type ) {
			return '';
		}

		$label = ucwords( str_replace( '_', ' ', $type ) );

		if ( $id ) {
			return sprintf( '%s: %s', $label, $id );
		}

		return $label;
	}

	/**
	 * Format user column for audit tables.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public function format_user_label( $user_id ) {
		$user_id = (int) $user_id;

		if ( ! $user_id ) {
			return __( 'System', 'platform-core' );
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return sprintf(
				/* translators: %d: user ID */
				__( 'User #%d', 'platform-core' ),
				$user_id
			);
		}

		return $user->display_name;
	}
}
