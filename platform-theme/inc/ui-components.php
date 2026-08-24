<?php
/**
 * Reusable UI component helpers.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

use PlatformTheme\DesignSystem\UIComponents;

/**
 * Render a button or link styled as a button.
 *
 * @param array<string, mixed> $args Arguments.
 * @return string
 */
function platform_ui_button( array $args ) {
	return UIComponents::button( $args );
}

/**
 * Render page header with optional breadcrumb.
 *
 * @param string               $title       Page title.
 * @param string               $description Optional description.
 * @param array<int, string>   $breadcrumb  Breadcrumb labels.
 * @param string               $actions_html Optional action buttons HTML.
 */
function platform_ui_page_header( $title, $description = '', array $breadcrumb = array(), $actions_html = '' ) {
	UIComponents::page_header( $title, $description, $breadcrumb, $actions_html );
}

/**
 * Render an alert.
 *
 * @param string $message Message.
 * @param string $type    Alert type.
 */
function platform_ui_alert( $message, $type = 'info' ) {
	UIComponents::alert( $message, $type );
}

/**
 * Render an empty state.
 *
 * @param string $title       Title.
 * @param string $description Description.
 */
function platform_ui_empty_state( $title, $description = '' ) {
	UIComponents::empty_state( $title, $description );
}

/**
 * Render user avatar markup.
 *
 * @param int $user_id User ID.
 * @param int $size    Avatar size.
 * @return string
 */
function platform_ui_avatar( $user_id, $size = 32 ) {
	return UIComponents::avatar( $user_id, $size );
}

/**
 * Get admin breadcrumb labels for a page slug.
 *
 * @param string $page Admin page slug.
 * @return array<int, string>
 */
function platform_admin_breadcrumb( $page ) {
	$labels = array(
		'dashboard'   => __( 'Dashboard', 'platform-theme' ),
		'users'       => __( 'Users', 'platform-theme' ),
		'roles'       => __( 'Roles', 'platform-theme' ),
		'permissions' => __( 'Permissions', 'platform-theme' ),
		'modules'     => __( 'Modules', 'platform-theme' ),
		'acl'         => __( 'ACL', 'platform-theme' ),
		'settings'    => __( 'Settings', 'platform-theme' ),
	);

	return array(
		__( 'Admin', 'platform-theme' ),
		$labels[ $page ] ?? ucfirst( $page ),
	);
}
