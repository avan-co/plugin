<?php
/**
 * Plugin Name:       Platform Reports
 * Description:       Manager reports module aggregating tasks, team, and activity summaries.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Platform Team
 * License:           GPL-2.0-or-later
 * Text Domain:       platform-reports
 *
 * @package PlatformReports
 */

defined( 'ABSPATH' ) || exit;

define( 'MPP_REPORTS_VERSION', '1.0.0' );
define( 'MPP_REPORTS_FILE', __FILE__ );
define( 'MPP_REPORTS_DIR', plugin_dir_path( __FILE__ ) );

function mpp_reports_load_textdomain() {
	load_plugin_textdomain( 'platform-reports', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'mpp_reports_load_textdomain' );

function mpp_reports_bootstrap() {
	if ( ! defined( 'MPP_VERSION' ) || ! function_exists( 'mpp_register_module' ) ) {
		add_action( 'admin_notices', 'mpp_reports_missing_core_notice' );
		return;
	}

	if ( version_compare( MPP_VERSION, '1.3.0', '<' ) ) {
		add_action( 'admin_notices', 'mpp_reports_incompatible_core_notice' );
		return;
	}

	require_once MPP_REPORTS_DIR . 'includes/ReportService.php';
	require_once MPP_REPORTS_DIR . 'includes/ModuleAccess.php';
	require_once MPP_REPORTS_DIR . 'includes/ReportsModule.php';

	mpp_register_module( new \MPP\Reports\ReportsModule() );
}
add_action( 'plugins_loaded', 'mpp_reports_bootstrap', 20 );

function mpp_reports_missing_core_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html__( 'Platform Reports requires Platform Core to be installed and activated.', 'platform-reports' );
	echo '</p></div>';
}

function mpp_reports_incompatible_core_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	printf(
		/* translators: %s: required platform-core version */
		esc_html__( 'Platform Reports requires Platform Core %s or newer.', 'platform-reports' ),
		'1.3.0'
	);
	echo '</p></div>';
}

function mpp_reports_deactivate() {
	if ( function_exists( 'mpp_deactivate_module' ) ) {
		mpp_deactivate_module( 'reports' );
	}
}
register_deactivation_hook( __FILE__, 'mpp_reports_deactivate' );

add_filter( 'mpp_module_description', 'mpp_reports_module_description', 10, 3 );

function mpp_reports_module_description( $description, $slug, $module ) {
	unset( $module );

	if ( 'reports' !== $slug ) {
		return $description;
	}

	return __( 'Operational reports for managers across tasks and team modules.', 'platform-reports' );
}
