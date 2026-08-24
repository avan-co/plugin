<?php
/**
 * User panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

use PlatformTheme\DesignSystem\UIComponents;

$user       = wp_get_current_user();
$summary    = function_exists( 'mpp_get_user_summary' ) ? mpp_get_user_summary() : array();
$widgets    = function_exists( 'mpp_get_panel_widgets' ) ? mpp_get_panel_widgets( 'user' ) : array();
$shortcuts  = function_exists( 'mpp_get_panel_module_shortcuts' ) ? mpp_get_panel_module_shortcuts( 'user' ) : array();
$activity   = function_exists( 'mpp_get_user_recent_activity' ) ? mpp_get_user_recent_activity() : array();
$has_roles  = ! empty( $summary['role_names'] );

ob_start();

echo UIComponents::dashboard_welcome(
	$user->display_name,
	__( 'Your personal workspace, account summary, and module shortcuts.', 'platform-theme' )
);

echo UIComponents::quick_actions(
	array(
		array(
			'label'   => __( 'Profile', 'platform-theme' ),
			'url'     => mpp_route_url( 'profile' ),
			'variant' => 'secondary',
		),
		array(
			'label'   => __( 'Settings', 'platform-theme' ),
			'url'     => mpp_route_url( 'settings' ),
			'variant' => 'secondary',
		),
	)
);

$summary_stats = array(
	array(
		'label' => __( 'Account Status', 'platform-theme' ),
		'value' => $has_roles ? __( 'Active', 'platform-theme' ) : __( 'No Platform Role', 'platform-theme' ),
		'hint'  => __( 'Based on assigned platform roles', 'platform-theme' ),
	),
	array(
		'label' => __( 'Platform Roles', 'platform-theme' ),
		'value' => ! empty( $summary['role_names'] ) ? implode( ', ', $summary['role_names'] ) : '—',
	),
	array(
		'label' => __( 'Permissions', 'platform-theme' ),
		'value' => isset( $summary['permission_count'] ) ? (string) $summary['permission_count'] : '0',
		'hint'  => __( 'Effective permissions from your roles', 'platform-theme' ),
	),
);

if ( ! empty( $widgets ) ) {
	foreach ( $widgets as $widget ) {
		$summary_stats[] = array(
			'label' => $widget['title'] ?? '',
			'value' => $widget['value'] ?? '—',
		);
	}
}

echo UIComponents::section(
	__( 'Account Summary', 'platform-theme' ),
	UIComponents::stat_grid( $summary_stats, 'mpp-stats--panel' )
);

echo UIComponents::section(
	__( 'Modules', 'platform-theme' ),
	UIComponents::module_shortcut_grid( $shortcuts )
);

echo UIComponents::section(
	__( 'Recent Activity', 'platform-theme' ),
	UIComponents::activity_list( $activity )
);

$content = ob_get_clean();

platform_render_panel_shell(
	'user',
	__( 'Dashboard', 'platform-theme' ),
	$content,
	''
);
