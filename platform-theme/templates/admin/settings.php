<?php
/**
 * Admin settings template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

ob_start();
if ( function_exists( 'mpp_render_admin_page' ) ) {
	mpp_render_admin_page( 'settings' );
}
$content = ob_get_clean();

platform_render_panel( 'admin', __( 'Admin Settings', 'platform-theme' ), $content );
