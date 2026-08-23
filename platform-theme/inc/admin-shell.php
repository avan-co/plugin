<?php
/**
 * Admin page shell helper.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

/**
 * Render an admin page through the shared panel shell.
 *
 * @param string $page Admin page slug.
 */
function platform_render_admin_shell( $page ) {
	$meta = array(
		'users'       => array( __( 'Users', 'platform-theme' ), __( 'Manage WordPress users and platform role assignments.', 'platform-theme' ) ),
		'roles'       => array( __( 'Roles', 'platform-theme' ), __( 'Create and maintain platform roles and permissions.', 'platform-theme' ) ),
		'permissions' => array( __( 'Permissions', 'platform-theme' ), __( 'Grant or revoke permissions for each platform role.', 'platform-theme' ) ),
		'modules'     => array( __( 'Modules', 'platform-theme' ), __( 'Review registered platform modules and their capabilities.', 'platform-theme' ) ),
		'acl'         => array( __( 'ACL Overview', 'platform-theme' ), __( 'Audit platform access control activity and assignments.', 'platform-theme' ) ),
		'settings'    => array( __( 'Admin Settings', 'platform-theme' ), __( 'Review core platform configuration and runtime status.', 'platform-theme' ) ),
		'dashboard'   => array( __( 'Admin Dashboard', 'platform-theme' ), __( 'Overview of users, roles, modules, and recent activity.', 'platform-theme' ) ),
	);

	$title = $meta[ $page ][0] ?? ucfirst( $page );
	$description = $meta[ $page ][1] ?? '';
	$breadcrumb = function_exists( 'platform_admin_breadcrumb' ) ? platform_admin_breadcrumb( $page ) : array();

	ob_start();
	if ( function_exists( 'mpp_render_admin_page' ) ) {
		mpp_render_admin_page( $page );
	}
	$content = ob_get_clean();

	platform_render_panel( 'admin', $title, $content, $description, $breadcrumb );
}
