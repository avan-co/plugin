<?php
/**
 * Manager panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

$user    = wp_get_current_user();
$stats   = function_exists( 'mpp_get_manager_stats' ) ? mpp_get_manager_stats() : array();
$widgets = function_exists( 'mpp_get_panel_widgets' ) ? mpp_get_panel_widgets( 'manager' ) : array();

ob_start();
?>
<p class="mpp-lead"><?php echo esc_html( sprintf( __( 'Welcome back, %s.', 'platform-theme' ), $user->display_name ) ); ?></p>

<div class="mpp-quick-actions">
	<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'app/manager/profile' ) ); ?>"><?php esc_html_e( 'Profile', 'platform-theme' ); ?></a>
	<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'settings' ) ); ?>"><?php esc_html_e( 'Settings', 'platform-theme' ); ?></a>
</div>

<div class="mpp-stats">
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Team Members', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( $stats['team_members'] ?? '—' ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Pending Tasks', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( $stats['pending_tasks'] ?? '—' ); ?></span>
	</div>
</div>

<?php if ( ! empty( $widgets ) ) : ?>
	<h2 class="mpp-section-title"><?php esc_html_e( 'Module Widgets', 'platform-theme' ); ?></h2>
	<div class="mpp-stats">
		<?php foreach ( $widgets as $widget ) : ?>
			<div class="mpp-stat-card">
				<span class="mpp-stat-card__label"><?php echo esc_html( $widget['title'] ?? '' ); ?></span>
				<span class="mpp-stat-card__value"><?php echo esc_html( $widget['value'] ?? '—' ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<?php platform_ui_empty_state( __( 'No module widgets yet', 'platform-theme' ), __( 'Installed modules can expose manager widgets here.', 'platform-theme' ) ); ?>
<?php endif; ?>

<?php
platform_render_placeholder_section(
	__( 'Team', 'platform-theme' ),
	__( 'Team management will be available when a team module is installed.', 'platform-theme' )
);
platform_render_placeholder_section(
	__( 'Projects', 'platform-theme' ),
	__( 'Project oversight will appear here when a project module is installed.', 'platform-theme' )
);
platform_render_placeholder_section(
	__( 'Reports', 'platform-theme' ),
	__( 'Manager reports will be provided by future modules without fake data.', 'platform-theme' )
);
?>

<h2 class="mpp-section-title"><?php esc_html_e( 'Activity', 'platform-theme' ); ?></h2>
<?php
$activity = function_exists( 'mpp_get_user_recent_activity' ) ? mpp_get_user_recent_activity() : array();
if ( ! empty( $activity ) ) : ?>
	<ul class="mpp-activity-list">
		<?php foreach ( $activity as $entry ) : ?>
			<li><code><?php echo esc_html( $entry['action'] ); ?></code> — <?php echo esc_html( $entry['created_at'] ); ?></li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<?php platform_ui_empty_state( __( 'No recent activity', 'platform-theme' ), __( 'Your manager actions will be listed here.', 'platform-theme' ) ); ?>
<?php endif; ?>
<?php
$content = ob_get_clean();

platform_render_panel_shell(
	'manager',
	__( 'Dashboard', 'platform-theme' ),
	$content,
	__( 'Manager workspace for oversight, module widgets, and future team tools.', 'platform-theme' )
);
