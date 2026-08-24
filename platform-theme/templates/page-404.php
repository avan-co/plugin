<?php
/**
 * 404 Not Found template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/error-page.php';

get_header();

platform_render_error_page(
	'404',
	__( 'Page not found', 'platform-theme' ),
	__( 'The address may be incorrect, or the page may have been moved or removed.', 'platform-theme' ),
	array(
		array(
			'label'   => __( 'Go to Dashboard', 'platform-theme' ),
			'url'     => is_user_logged_in() && function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app' ) : home_url( '/' ),
			'variant' => 'primary',
		),
		array(
			'label'   => __( 'Home', 'platform-theme' ),
			'url'     => home_url( '/' ),
			'variant' => 'secondary',
		),
	)
);

get_footer();
