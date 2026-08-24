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
 * Whether the current route uses a panel sidebar.
 *
 * @return bool
 */
function platform_route_has_sidebar() {
	if ( ! function_exists( 'mpp_get_current_route' ) ) {
		return false;
	}

	$route = mpp_get_current_route();

	if ( ! $route || empty( $route['slug'] ) ) {
		return false;
	}

	$slug = $route['slug'];

	if ( in_array( $slug, array( 'app', 'login', 'register', 'forgot-password', '403', '404' ), true ) ) {
		return false;
	}

	if ( in_array( $slug, array( 'profile', 'settings' ), true ) ) {
		return true;
	}

	if ( 0 === strpos( $slug, 'app/' ) ) {
		return true;
	}

	return false;
}
