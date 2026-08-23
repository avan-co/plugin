<?php
/**
 * Database table names and schema definitions.
 *
 * @package PlatformCore
 */

namespace MPP\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Class Schema
 */
class Schema {

	/**
	 * Database version for migrations.
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Option key for stored DB version.
	 */
	const VERSION_OPTION = 'mpp_db_version';

	/**
	 * Table suffixes.
	 *
	 * @var array<string, string>
	 */
	private static $tables = array(
		'roles'             => 'platform_roles',
		'permissions'       => 'platform_permissions',
		'role_permissions'  => 'platform_role_permissions',
		'user_roles'        => 'platform_user_roles',
		'scopes'            => 'platform_scopes',
	);

	/**
	 * Get full table name with WordPress prefix.
	 *
	 * @param string $name Table suffix key.
	 * @return string
	 */
	public static function table( $name ) {
		global $wpdb;
		$suffix = isset( self::$tables[ $name ] ) ? self::$tables[ $name ] : $name;
		return $wpdb->prefix . $suffix;
	}

	/**
	 * Get CREATE TABLE SQL statements.
	 *
	 * @return array<string, string>
	 */
	public static function get_tables_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$roles = self::table( 'roles' );
		$permissions = self::table( 'permissions' );
		$role_permissions = self::table( 'role_permissions' );
		$user_roles = self::table( 'user_roles' );
		$scopes = self::table( 'scopes' );

		return array(
			'roles' => "CREATE TABLE {$roles} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				slug varchar(100) NOT NULL,
				name varchar(255) NOT NULL,
				description text DEFAULT NULL,
				is_system tinyint(1) NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY slug (slug)
			) {$charset_collate};",

			'permissions' => "CREATE TABLE {$permissions} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				module varchar(100) NOT NULL,
				resource varchar(100) NOT NULL,
				action varchar(100) NOT NULL,
				permission_key varchar(255) NOT NULL,
				description text DEFAULT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY permission_key (permission_key),
				KEY module (module)
			) {$charset_collate};",

			'role_permissions' => "CREATE TABLE {$role_permissions} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				role_id bigint(20) unsigned NOT NULL,
				permission_id bigint(20) unsigned NOT NULL,
				scope_type varchar(50) NOT NULL DEFAULT 'all',
				scope_value text DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY role_permission (role_id, permission_id),
				KEY role_id (role_id),
				KEY permission_id (permission_id)
			) {$charset_collate};",

			'user_roles' => "CREATE TABLE {$user_roles} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				role_id bigint(20) unsigned NOT NULL,
				assigned_at datetime NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY user_role (user_id, role_id),
				KEY user_id (user_id),
				KEY role_id (role_id)
			) {$charset_collate};",

			'scopes' => "CREATE TABLE {$scopes} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				slug varchar(100) NOT NULL,
				name varchar(255) NOT NULL,
				description text DEFAULT NULL,
				handler varchar(255) DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY slug (slug)
			) {$charset_collate};",
		);
	}
}
