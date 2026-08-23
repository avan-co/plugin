<?php
/**
 * Theme functions.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/navigation-helpers.php';

/**
 * Theme setup.
 */
function platform_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus(
		array(
			'platform_primary' => __( 'Platform Primary', 'platform-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'platform_theme_setup' );

/**
 * Enqueue theme assets.
 */
function platform_theme_enqueue_assets() {
	wp_enqueue_style(
		'platform-theme',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'platform-theme-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array( 'platform-theme' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'platform-theme-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	if ( function_exists( 'mpp_get_current_route' ) ) {
		$route = mpp_get_current_route();
		if ( $route && 0 === strpos( $route['slug'], 'app/admin' ) ) {
			wp_enqueue_style(
				'platform-theme-admin',
				get_template_directory_uri() . '/assets/css/admin.css',
				array( 'platform-theme-main' ),
				wp_get_theme()->get( 'Version' )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'platform_theme_enqueue_assets' );

/**
 * Get platform page title from current route.
 *
 * @return string
 */
function platform_get_page_title() {
	if ( function_exists( 'mpp_get_current_route' ) ) {
		$route = mpp_get_current_route();

		if ( $route && ! empty( $route['definition']['title'] ) ) {
			return $route['definition']['title'];
		}
	}

	return get_bloginfo( 'name' );
}

/**
 * Render panel switcher links.
 */
function platform_render_panel_switcher() {
	if ( ! function_exists( 'mpp_get_accessible_panels' ) ) {
		return;
	}

	$panels = mpp_get_accessible_panels();
	$labels = array(
		'user'    => __( 'User Panel', 'platform-theme' ),
		'manager' => __( 'Manager Panel', 'platform-theme' ),
		'admin'   => __( 'Admin Panel', 'platform-theme' ),
	);

	if ( empty( $panels ) ) {
		return;
	}

	echo '<nav class="mpp-panel-switcher" aria-label="' . esc_attr__( 'Panel navigation', 'platform-theme' ) . '">';
	echo '<ul>';

	foreach ( $panels as $panel ) {
		if ( ! isset( $labels[ $panel ] ) ) {
			continue;
		}

		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( home_url( '/app/' . $panel ) ),
			esc_html( $labels[ $panel ] )
		);
	}

	echo '</ul>';
	echo '</nav>';
}
