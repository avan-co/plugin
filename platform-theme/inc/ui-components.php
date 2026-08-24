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
		'audit-log'   => __( 'Audit Log', 'platform-theme' ),
		'settings'    => __( 'Settings', 'platform-theme' ),
	);

	return array(
		__( 'Admin', 'platform-theme' ),
		$labels[ $page ] ?? ucfirst( $page ),
	);
}

/**
 * Render tab navigation.
 *
 * @param array<string, string> $tabs      Tab slug => label.
 * @param string                $current   Active tab.
 * @param string                $base_url  Base URL.
 * @param string                $query_arg Query arg name.
 * @return string
 */
function platform_ui_tabs( array $tabs, $current, $base_url, $query_arg = 'tab' ) {
	return UIComponents::tabs( $tabs, $current, $base_url, $query_arg );
}

/**
 * Render a detail page header.
 *
 * @param string                              $title    Title.
 * @param string                              $subtitle Subtitle.
 * @param array<int, array<string, string>>   $meta     Meta stats.
 * @param string                              $leading  Leading HTML.
 * @return string
 */
function platform_ui_detail_header( $title, $subtitle = '', array $meta = array(), $leading = '' ) {
	return UIComponents::detail_header( $title, $subtitle, $meta, $leading );
}

/**
 * Render a back link.
 *
 * @param string $url   URL.
 * @param string $label Label.
 * @return string
 */
function platform_ui_back_link( $url, $label ) {
	return UIComponents::back_link( $url, $label );
}

/**
 * Render a filter bar.
 *
 * @param string                           $action Form action.
 * @param array<int, array<string, mixed>> $fields Fields.
 * @return string
 */
function platform_ui_filter_bar( $action, array $fields ) {
	return UIComponents::filter_bar( $action, $fields );
}

/**
 * Render a settings layout.
 *
 * @param array<string, string> $sections  Sections.
 * @param string                $current   Current section.
 * @param string                $base_url  Base URL.
 * @param string                $content   Content HTML.
 * @param string                $query_arg Query arg.
 * @return string
 */
function platform_ui_settings_layout( array $sections, $current, $base_url, $content, $query_arg = 'section' ) {
	return UIComponents::settings_layout( $sections, $current, $base_url, $content, $query_arg );
}

/**
 * Render a chip.
 *
 * @param string $label   Label.
 * @param string $url     URL.
 * @param string $variant Variant.
 * @return string
 */
function platform_ui_chip( $label, $url = '', $variant = '' ) {
	return UIComponents::chip( $label, $url, $variant );
}

/**
 * Render a module card.
 *
 * @param array<string, mixed> $module Module data.
 * @return string
 */
function platform_ui_module_card( array $module ) {
	return UIComponents::module_card( $module );
}

/**
 * Render a form field.
 *
 * @param array<string, mixed> $args Field arguments.
 * @return string
 */
function platform_ui_form_field( array $args ) {
	return UIComponents::form_field( $args );
}

/**
 * Render a dashboard welcome header.
 *
 * @param string $name        Display name.
 * @param string $description Description.
 * @return string
 */
function platform_ui_dashboard_welcome( $name, $description = '' ) {
	return UIComponents::dashboard_welcome( $name, $description );
}

/**
 * Render a module shortcut grid.
 *
 * @param array<int, array<string, string>> $shortcuts Shortcuts.
 * @return string
 */
function platform_ui_module_shortcut_grid( array $shortcuts ) {
	return UIComponents::module_shortcut_grid( $shortcuts );
}

/**
 * Render a pending items list.
 *
 * @param array<int, array<string, string>> $items Items.
 * @return string
 */
function platform_ui_pending_list( array $items ) {
	return UIComponents::pending_list( $items );
}

/**
 * Render a recent activity list.
 *
 * @param array<int, array<string, mixed>> $entries Entries.
 * @return string
 */
function platform_ui_activity_list( array $entries ) {
	return UIComponents::activity_list( $entries );
}
