<?php
/**
 * Manager panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

use PlatformTheme\DesignSystem\UIComponents;

$user          = wp_get_current_user();
$stats         = function_exists( 'mpp_get_manager_stats' ) ? mpp_get_manager_stats() : array();
$widgets       = function_exists( 'mpp_get_panel_widgets' ) ? mpp_get_panel_widgets( 'manager' ) : array();
$shortcuts     = function_exists( 'mpp_get_panel_module_shortcuts' ) ? mpp_get_panel_module_shortcuts( 'manager' ) : array();
$pending_items = function_exists( 'mpp_get_manager_pending_items' ) ? mpp_get_manager_pending_items() : array();
$activity      = function_exists( 'mpp_get_user_recent_activity' ) ? mpp_get_user_recent_activity() : array();

ob_start();

echo UIComponents::dashboard_welcome(
	$user->display_name,
	__( 'Operational overview, team metrics, and manager tools.', 'platform-theme' )
);

echo UIComponents::quick_actions(
	array(
		array(
			'label'   => __( 'Profile', 'platform-theme' ),
			'url'     => mpp_route_url( 'app/manager/profile' ),
			'variant' => 'secondary',
		),
		array(
			'label'   => __( 'Settings', 'platform-theme' ),
			'url'     => mpp_route_url( 'settings' ),
			'variant' => 'secondary',
		),
	)
);

$team_stats = array(
	array(
		'label' => __( 'Team Members', 'platform-theme' ),
		'value' => $stats['team_members'] ?? '—',
		'hint'  => __( 'Managed users in your scope', 'platform-theme' ),
	),
	array(
		'label' => __( 'Pending Tasks', 'platform-theme' ),
		'value' => $stats['pending_tasks'] ?? '—',
		'hint'  => __( 'Open items requiring attention', 'platform-theme' ),
	),
);

if ( ! empty( $widgets ) ) {
	foreach ( $widgets as $widget ) {
		$team_stats[] = array(
			'label' => $widget['title'] ?? '',
			'value' => $widget['value'] ?? '—',
		);
	}
}

echo UIComponents::section(
	__( 'Team Overview', 'platform-theme' ),
	UIComponents::stat_grid( $team_stats, 'mpp-stats--panel' )
);

echo UIComponents::section(
	__( 'Pending Items', 'platform-theme' ),
	UIComponents::pending_list( $pending_items )
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
	'manager',
	__( 'Dashboard', 'platform-theme' ),
	$content,
	''
);
