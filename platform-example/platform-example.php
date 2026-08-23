<?php
/**
 * Plugin Name:       Platform Example
 * Description:       Minimal example module demonstrating external platform module registration.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Platform Team
 * License:           GPL-2.0-or-later
 * Text Domain:       platform-example
 *
 * @package PlatformExample
 */

defined( 'ABSPATH' ) || exit;

define( 'MPP_EXAMPLE_VERSION', '1.1.0' );
define( 'MPP_EXAMPLE_FILE', __FILE__ );
define( 'MPP_EXAMPLE_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Bootstrap the example module after Platform Core is available.
 */
function mpp_example_bootstrap() {
	if ( ! defined( 'MPP_VERSION' ) || ! function_exists( 'mpp_register_module' ) ) {
		add_action( 'admin_notices', 'mpp_example_missing_core_notice' );
		return;
	}

	if ( ! class_exists( '\MPP\Modules\AbstractModule' ) ) {
		add_action( 'admin_notices', 'mpp_example_missing_core_notice' );
		return;
	}

	if ( version_compare( MPP_VERSION, '1.3.0', '<' ) ) {
		add_action( 'admin_notices', 'mpp_example_incompatible_core_notice' );
		return;
	}

	require_once MPP_EXAMPLE_DIR . 'includes/ExampleModule.php';

	mpp_register_module( new \MPP\Example\ExampleModule() );
}
add_action( 'plugins_loaded', 'mpp_example_bootstrap', 20 );

/**
 * Admin notice when Platform Core is missing.
 */
function mpp_example_missing_core_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html__( 'Platform Example requires Platform Core to be installed and activated.', 'platform-example' );
	echo '</p></div>';
}

/**
 * Admin notice when Platform Core version is too old.
 */
function mpp_example_incompatible_core_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	printf(
		/* translators: %s: required platform-core version */
		esc_html__( 'Platform Example requires Platform Core %s or newer.', 'platform-example' ),
		'1.3.0'
	);
	echo '</p></div>';
}

/**
 * Run module deactivation cleanup.
 */
function mpp_example_deactivate() {
	if ( function_exists( 'mpp_deactivate_module' ) ) {
		mpp_deactivate_module( 'example' );
	}
}
add_filter( 'mpp_module_description', 'mpp_example_module_description', 10, 3 );

/**
 * Example module description for admin UI.
 *
 * @param string                         $description Current description.
 * @param string                         $slug        Module slug.
 * @param \MPP\Modules\ModuleInterface   $module      Module instance.
 * @return string
 */
function mpp_example_module_description( $description, $slug, $module ) {
	unset( $module );

	if ( 'example' !== $slug ) {
		return $description;
	}

	return __( 'Reference module demonstrating permissions, routes, navigation, and dashboard widgets.', 'platform-example' );
}
register_deactivation_hook( __FILE__, 'mpp_example_deactivate' );
