<?php
/**
 * Theme functions.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/navigation-helpers.php';
require_once get_template_directory() . '/inc/ui-components.php';

/**
 * Theme setup.
 */
function platform_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	load_theme_textdomain( 'platform-theme', get_template_directory() . '/languages' );

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
		'platform-theme-fonts',
		'https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'platform-theme-tokens',
		get_template_directory_uri() . '/assets/css/tokens.css',
		array( 'platform-theme-fonts' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'platform-theme-components',
		get_template_directory_uri() . '/assets/css/components.css',
		array( 'platform-theme-tokens' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'platform-theme',
		get_stylesheet_uri(),
		array( 'platform-theme-components' ),
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

	if ( is_front_page() ) {
		wp_enqueue_style(
			'platform-theme-home',
			get_template_directory_uri() . '/assets/css/home.css',
			array( 'platform-theme-main' ),
			wp_get_theme()->get( 'Version' )
		);
	}

	if ( function_exists( 'mpp_get_current_route' ) ) {
		$route = mpp_get_current_route();
		if ( $route && in_array( $route['slug'], array( 'login', 'register' ), true ) ) {
			wp_enqueue_script(
				'platform-theme-forms',
				get_template_directory_uri() . '/assets/js/forms.js',
				array(),
				wp_get_theme()->get( 'Version' ),
				true
			);
			wp_localize_script(
				'platform-theme-forms',
				'mppForms',
				array(
					'show'   => __( 'Show', 'platform-theme' ),
					'hide'   => __( 'Hide', 'platform-theme' ),
					'weak'   => __( 'Weak password', 'platform-theme' ),
					'fair'   => __( 'Fair password', 'platform-theme' ),
					'good'   => __( 'Good password', 'platform-theme' ),
					'strong' => __( 'Strong password', 'platform-theme' ),
				)
			);
		}
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
 * Add body classes for platform pages.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function platform_theme_body_classes( $classes ) {
	if ( is_rtl() ) {
		$classes[] = 'mpp-rtl';
	}

	$locale = get_locale();
	if ( $locale ) {
		$classes[] = 'mpp-locale-' . sanitize_html_class( strtolower( substr( $locale, 0, 2 ) ) );
	}

	return $classes;
}
add_filter( 'body_class', 'platform_theme_body_classes' );

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
			esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/' . $panel ) : home_url( '/app/' . $panel ) ),
			esc_html( $labels[ $panel ] )
		);
	}

	echo '</ul>';
	echo '</nav>';
}
