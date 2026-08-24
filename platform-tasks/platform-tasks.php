<?php
/**
 * Plugin Name:       Platform Tasks
 * Description:       Task management module for manager oversight and pending work tracking.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Platform Team
 * License:           GPL-2.0-or-later
 * Text Domain:       platform-tasks
 *
 * @package PlatformTasks
 */

defined( 'ABSPATH' ) || exit;

define( 'MPP_TASKS_VERSION', '1.0.0' );
define( 'MPP_TASKS_FILE', __FILE__ );
define( 'MPP_TASKS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Load plugin translations.
 */
function mpp_tasks_load_textdomain() {
	load_plugin_textdomain( 'platform-tasks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'mpp_tasks_load_textdomain' );

/**
 * Bootstrap the tasks module.
 */
function mpp_tasks_bootstrap() {
	if ( ! defined( 'MPP_VERSION' ) || ! function_exists( 'mpp_register_module' ) ) {
		add_action( 'admin_notices', 'mpp_tasks_missing_core_notice' );
		return;
	}

	if ( version_compare( MPP_VERSION, '1.3.0', '<' ) ) {
		add_action( 'admin_notices', 'mpp_tasks_incompatible_core_notice' );
		return;
	}

	require_once MPP_TASKS_DIR . 'includes/TaskStore.php';
	require_once MPP_TASKS_DIR . 'includes/ModuleAccess.php';
	require_once MPP_TASKS_DIR . 'includes/TasksModule.php';

	mpp_register_module( new \MPP\Tasks\TasksModule() );
}
add_action( 'plugins_loaded', 'mpp_tasks_bootstrap', 20 );

/**
 * Admin notice when Platform Core is missing.
 */
function mpp_tasks_missing_core_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html__( 'Platform Tasks requires Platform Core to be installed and activated.', 'platform-tasks' );
	echo '</p></div>';
}

/**
 * Admin notice when Platform Core version is too old.
 */
function mpp_tasks_incompatible_core_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	printf(
		/* translators: %s: required platform-core version */
		esc_html__( 'Platform Tasks requires Platform Core %s or newer.', 'platform-tasks' ),
		'1.3.0'
	);
	echo '</p></div>';
}

/**
 * Deactivate module cleanup.
 */
function mpp_tasks_deactivate() {
	if ( function_exists( 'mpp_deactivate_module' ) ) {
		mpp_deactivate_module( 'tasks' );
	}
}
register_deactivation_hook( __FILE__, 'mpp_tasks_deactivate' );

add_filter( 'mpp_module_description', 'mpp_tasks_module_description', 10, 3 );

/**
 * Module description for admin UI.
 *
 * @param string                         $description Current description.
 * @param string                         $slug        Module slug.
 * @param \MPP\Modules\ModuleInterface   $module      Module instance.
 * @return string
 */
function mpp_tasks_module_description( $description, $slug, $module ) {
	unset( $module );

	if ( 'tasks' !== $slug ) {
		return $description;
	}

	return __( 'Track team tasks, pending approvals, and manager workload.', 'platform-tasks' );
}
