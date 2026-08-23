<?php
/**
 * Theme navigation helpers.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if the current route matches a slug.
 *
 * @param string $slug Route slug.
 * @return bool
 */
function platform_is_route( $slug ) {
	if ( ! function_exists( 'mpp_get_current_route' ) ) {
		return false;
	}

	$route = mpp_get_current_route();

	return $route && $route['slug'] === trim( $slug, '/' );
}

/**
 * Get CSS class for a navigation item.
 *
 * @param string $slug Route slug.
 * @return string
 */
function platform_nav_item_class( $slug ) {
	return platform_is_route( $slug ) ? 'mpp-nav__item--active' : '';
}

/**
 * Render panel navigation from plugin registry.
 *
 * @param string $panel Panel slug.
 */
function platform_render_panel_navigation( $panel ) {
	if ( ! function_exists( 'mpp_get_panel_navigation' ) ) {
		return;
	}

	$items = mpp_get_panel_navigation( $panel );

	if ( empty( $items ) ) {
		return;
	}

	echo '<ul class="mpp-nav__list">';

	foreach ( $items as $item ) {
		$route = isset( $item['route'] ) ? $item['route'] : '';
		printf(
			'<li class="mpp-nav__item %s"><a href="%s">%s</a></li>',
			esc_attr( $route ? platform_nav_item_class( $route ) : '' ),
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
	}

	echo '</ul>';
}
