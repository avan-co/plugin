<?php
/**
 * Audit log service.
 *
 * @package PlatformCore
 */

namespace MPP\Services;

use MPP\Database\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Class AuditLogService
 */
class AuditLogService {

	/**
	 * Record an audit log entry.
	 *
	 * @param string               $action      Action slug.
	 * @param string               $object_type Object type.
	 * @param string|int|null      $object_id   Object identifier.
	 * @param array<string, mixed> $before      State before change.
	 * @param array<string, mixed> $after       State after change.
	 * @param int|null             $user_id     Acting user ID.
	 * @return int|false Log ID or false.
	 */
	public function log( $action, $object_type, $object_id = null, array $before = array(), array $after = array(), $user_id = null ) {
		global $wpdb;

		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		$result = $wpdb->insert(
			Schema::table( 'audit_log' ),
			array(
				'user_id'     => (int) $user_id,
				'action'      => sanitize_key( $action ),
				'object_type' => sanitize_key( $object_type ),
				'object_id'   => null !== $object_id ? (string) $object_id : null,
				'before_data' => ! empty( $before ) ? wp_json_encode( $before ) : null,
				'after_data'  => ! empty( $after ) ? wp_json_encode( $after ) : null,
				'ip_address'  => $this->get_client_ip(),
				'created_at'  => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Query audit log entries.
	 *
	 * @param array<string, mixed> $args Query arguments.
	 * @return array<int, array<string, mixed>>
	 */
	public function query( array $args = array() ) {
		global $wpdb;

		$defaults = array(
			'limit'       => 50,
			'offset'      => 0,
			'action'      => '',
			'object_type' => '',
			'user_id'     => 0,
		);

		$args  = wp_parse_args( $args, $defaults );
		$table = Schema::table( 'audit_log' );
		$where = array( '1=1' );
		$values = array();

		if ( ! empty( $args['action'] ) ) {
			$where[]  = 'action = %s';
			$values[] = sanitize_key( $args['action'] );
		}

		if ( ! empty( $args['object_type'] ) ) {
			$where[]  = 'object_type = %s';
			$values[] = sanitize_key( $args['object_type'] );
		}

		if ( ! empty( $args['user_id'] ) ) {
			$where[]  = 'user_id = %d';
			$values[] = (int) $args['user_id'];
		}

		$values[] = (int) $args['limit'];
		$values[] = (int) $args['offset'];

		$sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY created_at DESC LIMIT %d OFFSET %d';

		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Count audit log entries.
	 *
	 * @return int
	 */
	public function count() {
		global $wpdb;

		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Schema::table( 'audit_log' ) );
	}

	/**
	 * Get client IP address.
	 *
	 * @return string
	 */
	private function get_client_ip() {
		$ip = '';

		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip;
	}
}
