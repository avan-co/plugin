<?php
/**
 * User panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

use PlatformTheme\DesignSystem\UIComponents;

$user      = wp_get_current_user();
$summary   = function_exists( 'mpp_get_user_summary' ) ? mpp_get_user_summary() : array();
$widgets   = function_exists( 'mpp_get_panel_widgets' ) ? mpp_get_panel_widgets( 'user' ) : array();
$activity  = function_exists( 'mpp_get_user_recent_activity' ) ? mpp_get_user_recent_activity() : array();
$has_roles = ! empty( $summary['role_names'] );

ob_start();
?>
<p class="mpp-lead"><?php echo esc_html( sprintf( __( 'Welcome back, %s.', 'platform-theme' ), $user->display_name ) ); ?></p>

<?php
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
		array(
			'label'   => __( 'Logout', 'platform-theme' ),
			'url'     => mpp_logout_url(),
			'variant' => 'ghost',
		),
	)
);

echo UIComponents::section(
	__( 'Account Summary', 'platform-theme' ),
	UIComponents::stat_grid(
		array(
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

	echo UIComponents::section(
		__( 'Module Widgets', 'platform-theme' ),
		UIComponents::stat_grid( $widget_stats )
	);
}

echo UIComponents::section(
	__( 'Notifications', 'platform-theme' ),
	'<p class="mpp-muted">' . esc_html__( 'Notification preferences will appear here when notification modules are enabled.', 'platform-theme' ) . '</p>' .
	UIComponents::button(
		array(
			'label'   => __( 'Open Settings', 'platform-theme' ),
			'url'     => mpp_route_url( 'settings' ),
			'variant' => 'secondary',
		)
	)
);

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
		__( 'No activity yet', 'platform-theme' ),
		__( 'Your recent platform actions will appear here.', 'platform-theme' )
	);
	$activity_html = ob_get_clean();
}

echo UIComponents::section( __( 'Recent Activity', 'platform-theme' ), $activity_html );
?>

<?php
$content = ob_get_clean();

platform_render_panel_shell(
	'user',
	__( 'Dashboard', 'platform-theme' ),
	$content,
	__( 'Your personal workspace, account summary, and module widgets.', 'platform-theme' )
);
