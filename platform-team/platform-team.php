<?php
/**
 * Plugin Name:       Platform Team
 * Description:       Team membership module for manager oversight of assigned members.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Platform Team
 * License:           GPL-2.0-or-later
 * Text Domain:       platform-team
 *
 * @package PlatformTeam
 */

defined( 'ABSPATH' ) || exit;

define( 'MPP_TEAM_VERSION', '1.0.0' );
define( 'MPP_TEAM_FILE', __FILE__ );
define( 'MPP_TEAM_DIR', plugin_dir_path( __FILE__ ) );

function mpp_team_load_textdomain() {
	load_plugin_textdomain( 'platform-team', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'mpp_team_load_textdomain' );

function mpp_team_bootstrap() {
	if ( ! defined( 'MPP_VERSION' ) || ! function_exists( 'mpp_register_module' ) ) {
		add_action( 'admin_notices', 'mpp_team_missing_core_notice' );
		return;
	}

	if ( version_compare( MPP_VERSION, '1.3.0', '<' ) ) {
		add_action( 'admin_notices', 'mpp_team_incompatible_core_notice' );
		return;
	}

	require_once MPP_TEAM_DIR . 'includes/TeamStore.php';
	require_once MPP_TEAM_DIR . 'includes/ModuleAccess.php';
	require_once MPP_TEAM_DIR . 'includes/TeamModule.php';

	mpp_register_module( new \MPP\Team\TeamModule() );
}
add_action( 'plugins_loaded', 'mpp_team_bootstrap', 20 );

function mpp_team_missing_core_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html__( 'Platform Team requires Platform Core to be installed and activated.', 'platform-team' );
	echo '</p></div>';
}

function mpp_team_incompatible_core_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	printf(
		/* translators: %s: required platform-core version */
		esc_html__( 'Platform Team requires Platform Core %s or newer.', 'platform-team' ),
		'1.3.0'
	);
	echo '</p></div>';
}

function mpp_team_deactivate() {
	if ( function_exists( 'mpp_deactivate_module' ) ) {
		mpp_deactivate_module( 'team' );
	}
}
register_deactivation_hook( __FILE__, 'mpp_team_deactivate' );

add_filter( 'mpp_module_description', 'mpp_team_module_description', 10, 3 );

function mpp_team_module_description( $description, $slug, $module ) {
	unset( $module );

	if ( 'team' !== $slug ) {
		return $description;
	}

	return __( 'Manage team membership and member visibility for managers.', 'platform-team' );
}
