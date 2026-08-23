<?php
/**
 * User panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

$user     = wp_get_current_user();
$summary  = function_exists( 'mpp_get_user_summary' ) ? mpp_get_user_summary() : array();
$widgets  = function_exists( 'mpp_get_panel_widgets' ) ? mpp_get_panel_widgets( 'user' ) : array();
$activity = function_exists( 'mpp_get_user_recent_activity' ) ? mpp_get_user_recent_activity() : array();
$has_roles = ! empty( $summary['role_names'] );

ob_start();
?>
<p class="mpp-lead"><?php echo esc_html( sprintf( __( 'Welcome back, %s.', 'platform-theme' ), $user->display_name ) ); ?></p>

<div class="mpp-stats">
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Account Status', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo $has_roles ? esc_html__( 'Active', 'platform-theme' ) : esc_html__( 'No Platform Role', 'platform-theme' ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Platform Roles', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( ! empty( $summary['role_names'] ) ? implode( ', ', $summary['role_names'] ) : '—' ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Permissions', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( isset( $summary['permission_count'] ) ? (string) $summary['permission_count'] : '0' ); ?></span>
	</div>
</div>

<div class="mpp-quick-actions">
	<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'profile' ) ); ?>"><?php esc_html_e( 'Profile', 'platform-theme' ); ?></a>
	<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_route_url( 'settings' ) ); ?>"><?php esc_html_e( 'Settings', 'platform-theme' ); ?></a>
	<?php if ( function_exists( 'mpp_logout_url' ) ) : ?>
		<a class="mpp-btn mpp-btn--ghost" href="<?php echo esc_url( mpp_logout_url() ); ?>"><?php esc_html_e( 'Logout', 'platform-theme' ); ?></a>
	<?php endif; ?>
</div>

<?php if ( ! empty( $widgets ) ) : ?>
	<h2 class="mpp-section-title"><?php esc_html_e( 'Modules', 'platform-theme' ); ?></h2>
	<div class="mpp-stats">
		<?php foreach ( $widgets as $widget ) : ?>
			<div class="mpp-stat-card">
				<span class="mpp-stat-card__label"><?php echo esc_html( $widget['title'] ?? '' ); ?></span>
				<span class="mpp-stat-card__value"><?php echo esc_html( $widget['value'] ?? '—' ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php
platform_render_placeholder_section(
	__( 'Notifications', 'platform-theme' ),
	__( 'Notification preferences will appear here when notification modules are enabled.', 'platform-theme' ),
	__( 'Open Settings', 'platform-theme' ),
	mpp_route_url( 'settings' )
);
?>

<h2 class="mpp-section-title"><?php esc_html_e( 'Recent Activity', 'platform-theme' ); ?></h2>
<?php if ( ! empty( $activity ) ) : ?>
	<ul class="mpp-activity-list">
		<?php foreach ( $activity as $entry ) : ?>
			<li><code><?php echo esc_html( $entry['action'] ); ?></code> — <?php echo esc_html( $entry['created_at'] ); ?></li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<?php platform_ui_empty_state( __( 'No activity yet', 'platform-theme' ), __( 'Your recent platform actions will appear here.', 'platform-theme' ) ); ?>
<?php endif; ?>
<?php
$content = ob_get_clean();

platform_render_panel_shell(
	'user',
	__( 'Dashboard', 'platform-theme' ),
	$content,
	__( 'Your personal workspace, account summary, and module widgets.', 'platform-theme' )
);
