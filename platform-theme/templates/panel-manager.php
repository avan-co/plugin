<?php
/**
 * Manager panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

use PlatformTheme\DesignSystem\UIComponents;

$user    = wp_get_current_user();
$stats   = function_exists( 'mpp_get_manager_stats' ) ? mpp_get_manager_stats() : array();
$widgets = function_exists( 'mpp_get_panel_widgets' ) ? mpp_get_panel_widgets( 'manager' ) : array();
$activity = function_exists( 'mpp_get_user_recent_activity' ) ? mpp_get_user_recent_activity() : array();

ob_start();
?>
<p class="mpp-lead"><?php echo esc_html( sprintf( __( 'Welcome back, %s.', 'platform-theme' ), $user->display_name ) ); ?></p>

<?php
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

echo UIComponents::section(
	__( 'Team Overview', 'platform-theme' ),
	UIComponents::stat_grid(
		array(
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
		)
	)
);

if ( ! empty( $widgets ) ) {
	$widget_stats = array();
	foreach ( $widgets as $widget ) {
		$widget_stats[] = array(
			'label' => $widget['title'] ?? '',
			'value' => $widget['value'] ?? '—',
		);
	}
	echo UIComponents::section( __( 'Module Widgets', 'platform-theme' ), UIComponents::stat_grid( $widget_stats ) );
} else {
	ob_start();
	UIComponents::empty_state(
		__( 'No module widgets yet', 'platform-theme' ),
		__( 'Installed modules can expose manager widgets here.', 'platform-theme' )
	);
	echo UIComponents::section( __( 'Module Widgets', 'platform-theme' ), ob_get_clean() );
}

$roadmap = array(
	array(
		__( 'Team', 'platform-theme' ),
		__( 'Team management will be available when a team module is installed.', 'platform-theme' ),
	),
	array(
		__( 'Projects', 'platform-theme' ),
		__( 'Project oversight will appear here when a project module is installed.', 'platform-theme' ),
	),
	array(
		__( 'Reports', 'platform-theme' ),
		__( 'Manager reports will be provided by future modules without fake data.', 'platform-theme' ),
	),
);

$roadmap_html = '';
foreach ( $roadmap as $item ) {
	$roadmap_html .= UIComponents::section( $item[0], '<p class="mpp-muted">' . esc_html( $item[1] ) . '</p>' );
}
echo $roadmap_html;

$activity_html = '';
if ( ! empty( $activity ) ) {
	$activity_html .= '<ul class="mpp-activity-list">';
	foreach ( $activity as $entry ) {
		$activity_html .= '<li><code>' . esc_html( $entry['action'] ) . '</code> — ' . esc_html( $entry['created_at'] ) . '</li>';
	}
	$activity_html .= '</ul>';
} else {
	ob_start();
	UIComponents::empty_state(
		__( 'No recent activity', 'platform-theme' ),
		__( 'Your manager actions will be listed here.', 'platform-theme' )
	);
	$activity_html = ob_get_clean();
}

echo UIComponents::section( __( 'Activity', 'platform-theme' ), $activity_html );
?>

<?php
$content = ob_get_clean();

platform_render_panel_shell(
	'manager',
	__( 'Dashboard', 'platform-theme' ),
	$content,
	__( 'Manager workspace for oversight, module widgets, and future team tools.', 'platform-theme' )
);
