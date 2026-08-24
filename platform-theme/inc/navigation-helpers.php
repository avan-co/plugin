<?php
/**
 * Theme navigation helpers.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

use PlatformTheme\DesignSystem\PanelNavigation;

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
	PanelNavigation::render( $panel );
}
