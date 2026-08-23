<?php
/**
 * Uninstall handler — removes plugin data when deleted.
 *
 * @package PlatformCore
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

$tables = array(
	$wpdb->prefix . 'platform_roles',
	$wpdb->prefix . 'platform_permissions',
	$wpdb->prefix . 'platform_role_permissions',
	$wpdb->prefix . 'platform_user_roles',
	$wpdb->prefix . 'platform_scopes',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL
}

delete_option( 'mpp_db_version' );
