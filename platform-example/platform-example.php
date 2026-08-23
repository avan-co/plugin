<?php
/**
 * Plugin Name:       Platform Example
 * Description:       Minimal example module demonstrating external platform module registration.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Platform Team
 * License:           GPL-2.0-or-later
 * Text Domain:       platform-example
 *
 * @package PlatformExample
 */

defined( 'ABSPATH' ) || exit;

define( 'MPP_EXAMPLE_VERSION', '1.0.0' );
define( 'MPP_EXAMPLE_FILE', __FILE__ );
define( 'MPP_EXAMPLE_DIR', plugin_dir_path( __FILE__ ) );

require_once MPP_EXAMPLE_DIR . 'includes/ExampleModule.php';

/**
 * Register the example module with platform-core.
 */
function mpp_example_register_module() {
	if ( ! function_exists( 'mpp_register_module' ) ) {
		return;
	}

	mpp_register_module( new \MPP\Example\ExampleModule() );
}
add_action( 'plugins_loaded', 'mpp_example_register_module', 5 );

/**
 * Run module deactivation cleanup.
 */
function mpp_example_deactivate() {
	if ( function_exists( 'mpp_deactivate_module' ) ) {
		mpp_deactivate_module( 'example' );
	}
}
register_deactivation_hook( __FILE__, 'mpp_example_deactivate' );
