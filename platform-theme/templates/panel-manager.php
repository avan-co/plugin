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

$has_real_stats = false;

foreach ( array( 'team_members', 'pending_tasks' ) as $stat_key ) {
	if ( isset( $stats[ $stat_key ] ) && ! in_array( (string) $stats[ $stat_key ], array( '—', '-', '' ), true ) ) {
		$has_real_stats = true;
		break;
	}
}

$has_content = $has_real_stats || ! empty( $widgets ) || ! empty( $shortcuts ) || ! empty( $pending_items ) || ! empty( $activity );

ob_start();

echo UIComponents::dashboard_welcome(
	$user->display_name,
	__( 'Operational overview and tools for your manager workspace.', 'platform-theme' )
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

if ( ! $has_content ) {
	UIComponents::empty_state(
		__( 'Manager tools will appear here', 'platform-theme' ),
		__( 'Install modules that provide team oversight, tasks, or reports. Until then, use Profile and Settings to manage your account.', 'platform-theme' )
	);
} else {
	if ( $has_real_stats || ! empty( $widgets ) ) {
		$team_stats = array();

		if ( $has_real_stats ) {
			$team_stats[] = array(
				'label' => __( 'Team Members', 'platform-theme' ),
				'value' => $stats['team_members'] ?? '—',
				'hint'  => __( 'Managed users in your scope', 'platform-theme' ),
			);
			$team_stats[] = array(
				'label' => __( 'Pending Tasks', 'platform-theme' ),
				'value' => $stats['pending_tasks'] ?? '—',
				'hint'  => __( 'Open items requiring attention', 'platform-theme' ),
			);
		}

		if ( ! empty( $widgets ) ) {
			foreach ( $widgets as $widget ) {
				$team_stats[] = array(
					'label' => $widget['title'] ?? '',
					'value' => $widget['value'] ?? '—',
				);
			}
		}

		echo UIComponents::section(
			__( 'Overview', 'platform-theme' ),
			UIComponents::stat_grid( $team_stats, 'mpp-stats--panel' )
		);
	}

	if ( ! empty( $pending_items ) ) {
		echo UIComponents::section(
			__( 'Pending Items', 'platform-theme' ),
			UIComponents::pending_list( $pending_items )
		);
	}

	if ( ! empty( $shortcuts ) ) {
		echo UIComponents::section(
			__( 'Modules', 'platform-theme' ),
			UIComponents::module_shortcut_grid( $shortcuts )
		);
	}

	if ( ! empty( $activity ) ) {
		echo UIComponents::section(
			__( 'Recent Activity', 'platform-theme' ),
			UIComponents::activity_list( $activity )
		);
	}
}

$content = ob_get_clean();

platform_render_panel_shell(
	'manager',
	__( 'Dashboard', 'platform-theme' ),
	$content,
	''
);
