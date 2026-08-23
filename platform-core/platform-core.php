<?php
/**
 * Plugin Name:       Platform Core
 * Plugin URI:        https://example.com/platform-core
 * Description:       Core infrastructure for a multi-panel WordPress platform with dynamic ACL.
 * Version:           1.4.3
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Platform Team
 * License:           GPL-2.0-or-later
 * Text Domain:       platform-core
 *
 * @package PlatformCore
 */

defined( 'ABSPATH' ) || exit;

define( 'MPP_VERSION', '1.4.3' );
define( 'MPP_PLUGIN_FILE', __FILE__ );
define( 'MPP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MPP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once MPP_PLUGIN_DIR . 'includes/Core/Autoloader.php';

MPP\Core\Autoloader::register( MPP_PLUGIN_DIR . 'includes/' );
require_once MPP_PLUGIN_DIR . 'includes/functions.php';

/**
 * Returns the main plugin instance.
 *
 * @return \MPP\Core\Plugin
 */
function mpp() {
	return \MPP\Core\Plugin::instance();
}

register_activation_hook( __FILE__, array( 'MPP\Database\Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MPP\Database\Installer', 'deactivate' ) );

add_action( 'plugins_loaded', array( mpp(), 'boot' ) );
