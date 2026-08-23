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

		$args   = $this->normalize_args( $args );
		$table  = Schema::table( 'audit_log' );
		$built  = $this->build_where( $args );
		$values = $built['values'];
		$values[] = (int) $args['limit'];
		$values[] = (int) $args['offset'];

		$sql = "SELECT * FROM {$table} WHERE {$built['where']} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$sql = $wpdb->prepare( $sql, $values );

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Count audit log entries.
	 *
	 * @param array<string, mixed> $args Filter arguments.
	 * @return int
	 */
	public function count( array $args = array() ) {
		global $wpdb;

		$args  = $this->normalize_args( $args );
		$table = Schema::table( 'audit_log' );
		$built = $this->build_where( $args );

		$sql = "SELECT COUNT(*) FROM {$table} WHERE {$built['where']}";

		if ( ! empty( $built['values'] ) ) {
			$sql = $wpdb->prepare( $sql, $built['values'] );
		}

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Normalize query arguments.
	 *
	 * @param array<string, mixed> $args Raw args.
	 * @return array<string, mixed>
	 */
	private function normalize_args( array $args ) {
		$defaults = array(
			'limit'       => 50,
			'offset'      => 0,
			'action'      => '',
			'object_type' => '',
			'user_id'     => 0,
			'date_from'   => '',
			'date_to'     => '',
		);

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Build WHERE clause for queries.
	 *
	 * @param array<string, mixed> $args Normalized args.
	 * @return array{where: string, values: array<int, mixed>}
	 */
	private function build_where( array $args ) {
		$where  = array( '1=1' );
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

		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'created_at >= %s';
			$values[] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
		}

		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'created_at <= %s';
			$values[] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
		}

		return array(
			'where'  => implode( ' AND ', $where ),
			'values' => $values,
		);
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
