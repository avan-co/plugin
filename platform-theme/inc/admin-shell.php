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
		'roles'       => array( __( 'Roles', 'platform-theme' ), __( 'Create roles and assign permissions.', 'platform-theme' ) ),
		'permissions' => array( __( 'Permissions', 'platform-theme' ), __( 'Browse the permission catalog and inspect role usage.', 'platform-theme' ) ),
		'modules'     => array( __( 'Modules', 'platform-theme' ), __( 'Review registered platform modules and their capabilities.', 'platform-theme' ) ),
		'acl'         => array( __( 'ACL Overview', 'platform-theme' ), __( 'Review access control distribution and scope types.', 'platform-theme' ) ),
		'settings'    => array( __( 'Admin Settings', 'platform-theme' ), __( 'Configure platform-wide settings.', 'platform-theme' ) ),
		'dashboard'   => array( __( 'Admin Dashboard', 'platform-theme' ), __( 'Overview of users, roles, modules, and recent activity.', 'platform-theme' ) ),
	);

	$title       = $meta[ $page ][0] ?? ucfirst( $page );
	$description = $meta[ $page ][1] ?? '';
	$breadcrumb  = function_exists( 'platform_admin_breadcrumb_nested' ) ? platform_admin_breadcrumb_nested( $page, in_array( $page, array( 'roles', 'permissions' ), true ) ) : ( function_exists( 'platform_admin_breadcrumb' ) ? platform_admin_breadcrumb( $page ) : array() );

	if ( 'acl' === $page && isset( $_GET['view'] ) && 'audit' === sanitize_key( wp_unslash( $_GET['view'] ) ) ) {
		$title       = __( 'Audit Log', 'platform-theme' );
		$description = __( 'Searchable history of ACL and admin actions.', 'platform-theme' );
		$breadcrumb  = function_exists( 'platform_admin_breadcrumb' ) ? platform_admin_breadcrumb( 'audit-log' ) : $breadcrumb;
	}

	if ( function_exists( 'mpp_reset_admin_page_context' ) ) {
		mpp_reset_admin_page_context();
	}

	ob_start();
	if ( function_exists( 'mpp_render_admin_page' ) ) {
		mpp_render_admin_page( $page );
	}
	$content = ob_get_clean();

	$page_meta = function_exists( 'mpp_get_admin_page_meta' ) ? mpp_get_admin_page_meta() : array();
	$actions   = function_exists( 'mpp_get_admin_page_actions' ) ? mpp_get_admin_page_actions() : '';

	if ( ! empty( $page_meta['title'] ) ) {
		$title = $page_meta['title'];
	}

	if ( ! empty( $page_meta['description'] ) ) {
		$description = $page_meta['description'];
	}

	if ( function_exists( 'mpp_reset_admin_page_context' ) ) {
		mpp_reset_admin_page_context();
	}

	platform_render_panel( 'admin', $title, $content, $description, $breadcrumb, $actions );
}
