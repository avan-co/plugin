<?php
/**
 * 403 Forbidden template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/error-page.php';

get_header();

$actions = array(
	array(
		'label'   => __( 'Go to Dashboard', 'platform-theme' ),
		'url'     => function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app' ) : home_url( '/app' ),
		'variant' => 'primary',
	),
);

if ( ! is_user_logged_in() ) {
	$actions[] = array(
		'label'   => __( 'Sign in', 'platform-theme' ),
		'url'     => function_exists( 'mpp_route_url' ) ? mpp_route_url( 'login' ) : home_url( '/login' ),
		'variant' => 'secondary',
	);
}

platform_render_error_page(
	'403',
	__( 'Access denied', 'platform-theme' ),
	__( 'You do not have permission to view this page. If you believe this is a mistake, contact your administrator.', 'platform-theme' ),
	$actions
);

get_footer();
