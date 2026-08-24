<?php
/**
 * Task persistence for the Tasks module.
 *
 * @package PlatformTasks
 */

namespace MPP\Tasks;

defined( 'ABSPATH' ) || exit;

/**
 * Class TaskStore
 */
class TaskStore {

	const TABLE = 'platform_tasks';

	const VERSION_OPTION = 'mpp_tasks_db_version';

	const DB_VERSION = '1.0.0';

	/**
	 * Full table name with prefix.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE;
	}

	/**
	 * Create or upgrade the tasks table.
	 */
	public static function install() {
		global $wpdb;

		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			description text DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			manager_user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			assignee_user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY status (status),
			KEY manager_user_id (manager_user_id)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::VERSION_OPTION, self::DB_VERSION, false );

		if ( 0 === self::count_all() ) {
			self::seed_demo_tasks();
		}
	}

	/**
	 * Seed demo tasks for fresh installs.
	 */
	private static function seed_demo_tasks() {
		$manager_id = get_current_user_id() ?: 1;
		$now        = current_time( 'mysql' );

		self::insert(
			array(
				'title'            => __( 'Review onboarding checklist', 'platform-tasks' ),
				'description'      => __( 'Verify new member access and profile completion.', 'platform-tasks' ),
				'status'           => 'pending',
				'manager_user_id'  => $manager_id,
				'assignee_user_id' => 0,
			)
		);

		self::insert(
			array(
				'title'            => __( 'Approve quarterly report draft', 'platform-tasks' ),
				'description'      => __( 'Confirm metrics before publishing to stakeholders.', 'platform-tasks' ),
				'status'           => 'pending',
				'manager_user_id'  => $manager_id,
				'assignee_user_id' => 0,
			)
		);

		self::insert(
			array(
				'title'            => __( 'Schedule team sync', 'platform-tasks' ),
				'description'      => __( 'Coordinate weekly manager review session.', 'platform-tasks' ),
				'status'           => 'in_progress',
				'manager_user_id'  => $manager_id,
				'assignee_user_id' => 0,
			)
		);
	}

	/**
	 * Insert a task row.
	 *
	 * @param array<string, mixed> $data Task data.
	 * @return int|false
	 */
	public static function insert( array $data ) {
		global $wpdb;

		$now = current_time( 'mysql' );

		$result = $wpdb->insert(
			self::table_name(),
			array(
				'title'            => sanitize_text_field( $data['title'] ?? '' ),
				'description'      => wp_kses_post( $data['description'] ?? '' ),
				'status'           => sanitize_key( $data['status'] ?? 'pending' ),
				'manager_user_id'  => (int) ( $data['manager_user_id'] ?? 0 ),
				'assignee_user_id' => (int) ( $data['assignee_user_id'] ?? 0 ),
				'created_at'       => $now,
				'updated_at'       => $now,
			),
			array( '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * List tasks for a manager.
	 *
	 * @param int $manager_user_id Manager user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function list_for_manager( $manager_user_id ) {
		global $wpdb;

		$table = self::table_name();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE manager_user_id = %d ORDER BY updated_at DESC",
				(int) $manager_user_id
			),
			ARRAY_A
		);
	}

	/**
	 * Count pending tasks for a manager.
	 *
	 * @param int $manager_user_id Manager user ID.
	 * @return int
	 */
	public static function count_pending_for_manager( $manager_user_id ) {
		global $wpdb;

		$table = self::table_name();

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE manager_user_id = %d AND status = %s",
				(int) $manager_user_id,
				'pending'
			)
		);
	}

	/**
	 * Count all tasks.
	 *
	 * @return int
	 */
	public static function count_all() {
		global $wpdb;

		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table_name() );
	}

	/**
	 * Summary counts for reports.
	 *
	 * @param int $manager_user_id Manager user ID.
	 * @return array<string, int>
	 */
	public static function summary_for_manager( $manager_user_id ) {
		global $wpdb;

		$table = self::table_name();
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT status, COUNT(*) AS total FROM {$table} WHERE manager_user_id = %d GROUP BY status",
				(int) $manager_user_id
			),
			ARRAY_A
		);

		$summary = array(
			'pending'     => 0,
			'in_progress' => 0,
			'done'        => 0,
			'total'       => 0,
		);

		foreach ( $rows as $row ) {
			$status = sanitize_key( $row['status'] );
			$count  = (int) $row['total'];

			if ( isset( $summary[ $status ] ) ) {
				$summary[ $status ] = $count;
			}

			$summary['total'] += $count;
		}

		return $summary;
	}
}
