<?php
/**
 * Admin users template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

ob_start();
if ( function_exists( 'mpp_render_admin_page' ) ) {
	mpp_render_admin_page( 'users' );
}
$content = ob_get_clean();

platform_render_panel( 'admin', __( 'Users', 'platform-theme' ), $content );
