<?php
/**
 * Public template functions for themes.
 *
 * @package PlatformCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if current user has a permission.
 *
 * @param string               $permission Permission key.
 * @param array<string, mixed> $context    Access context.
 * @return bool
 */
function mpp_can( $permission, array $context = array() ) {
	return mpp()->acl()->can( get_current_user_id(), $permission, $context );
}

/**
 * Check if current user can access a panel.
 *
 * @param string $panel Panel slug (user, manager, admin).
 * @return bool
 */
function mpp_can_access_panel( $panel ) {
	return mpp_can( 'core.panel.' . sanitize_key( $panel ) . '.access' );
}

/**
 * Get accessible panels for current user.
 *
 * @return array<int, string>
 */
function mpp_get_accessible_panels() {
	return mpp()->acl()->get_accessible_panels( get_current_user_id() );
}

/**
 * Get current route data.
 *
 * @return array<string, mixed>|null
 */
function mpp_get_current_route() {
	return isset( $GLOBALS['mpp_current_route'] ) ? $GLOBALS['mpp_current_route'] : null;
}

/**
 * Get logout URL.
 *
 * @return string
 */
function mpp_logout_url() {
	return \MPP\Auth\AuthIntegration::logout_url();
}

/**
 * Render navigation for a panel type.
 *
 * @param string $panel Panel slug.
 */
function mpp_render_panel_nav( $panel ) {
	get_template_part( 'template-parts/navigation', sanitize_key( $panel ) );
}
