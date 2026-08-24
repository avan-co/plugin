<?php
/**
 * Team membership persistence.
 *
 * @package PlatformTeam
 */

namespace MPP\Team;

defined( 'ABSPATH' ) || exit;

/**
 * Class TeamStore
 */
class TeamStore {

	const TABLE = 'platform_team_members';

	const VERSION_OPTION = 'mpp_team_db_version';

	const DB_VERSION = '1.0.0';

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE;
	}

	/**
	 * Install team table and seed demo rows.
	 */
	public static function install() {
		global $wpdb;

		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			manager_user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			member_user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			team_name varchar(120) NOT NULL DEFAULT '',
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY manager_member (manager_user_id, member_user_id),
			KEY manager_user_id (manager_user_id)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::VERSION_OPTION, self::DB_VERSION, false );

		if ( 0 === self::count_all() ) {
			self::seed_demo_members();
		}
	}

	/**
	 * Seed demo team links from existing users.
	 */
	private static function seed_demo_members() {
		$manager_id = get_current_user_id() ?: 1;
		$users      = get_users(
			array(
				'number'  => 5,
				'exclude' => array( $manager_id ),
				'fields'  => array( 'ID' ),
			)
		);

		foreach ( $users as $user ) {
			self::add_member( $manager_id, (int) $user->ID, __( 'Operations', 'platform-team' ) );
		}
	}

	/**
	 * Add a team member link.
	 *
	 * @param int    $manager_user_id Manager user ID.
	 * @param int    $member_user_id  Member user ID.
	 * @param string $team_name       Team label.
	 * @return bool
	 */
	public static function add_member( $manager_user_id, $member_user_id, $team_name = '' ) {
		global $wpdb;

		if ( ! $manager_user_id || ! $member_user_id || $manager_user_id === $member_user_id ) {
			return false;
		}

		$result = $wpdb->insert(
			self::table_name(),
			array(
				'manager_user_id' => (int) $manager_user_id,
				'member_user_id'  => (int) $member_user_id,
				'team_name'       => sanitize_text_field( $team_name ),
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s' )
		);

		return (bool) $result;
	}

	/**
	 * List members for a manager.
	 *
	 * @param int $manager_user_id Manager user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function list_members( $manager_user_id ) {
		global $wpdb;

		$table = self::table_name();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT tm.*, u.display_name, u.user_email
				FROM {$table} tm
				INNER JOIN {$wpdb->users} u ON u.ID = tm.member_user_id
				WHERE tm.manager_user_id = %d
				ORDER BY tm.created_at DESC",
				(int) $manager_user_id
			),
			ARRAY_A
		);
	}

	/**
	 * Count members for a manager.
	 *
	 * @param int $manager_user_id Manager user ID.
	 * @return int
	 */
	public static function count_members( $manager_user_id ) {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . self::table_name() . ' WHERE manager_user_id = %d',
				(int) $manager_user_id
			)
		);
	}

	/**
	 * @return int
	 */
	public static function count_all() {
		global $wpdb;
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table_name() );
	}
}
