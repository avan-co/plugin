<?php
/**
 * User panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

$user         = wp_get_current_user();
$summary      = function_exists( 'mpp_get_user_summary' ) ? mpp_get_user_summary() : array();
$widgets      = function_exists( 'mpp_get_panel_widgets' ) ? mpp_get_panel_widgets( 'user' ) : array();
$activity     = function_exists( 'mpp_get_user_recent_activity' ) ? mpp_get_user_recent_activity() : array();
$has_roles    = ! empty( $summary['role_names'] );

ob_start();
?>
<div class="mpp-dashboard-welcome">
	<h2><?php echo esc_html( sprintf( __( 'Welcome, %s', 'platform-theme' ), $user->display_name ) ); ?></h2>
	<p><?php esc_html_e( 'This is your personal platform dashboard.', 'platform-theme' ); ?></p>
</div>

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
	<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'profile' ) : home_url( '/profile' ) ); ?>"><?php esc_html_e( 'Profile', 'platform-theme' ); ?></a>
	<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'settings' ) : home_url( '/settings' ) ); ?>"><?php esc_html_e( 'Settings', 'platform-theme' ); ?></a>
	<?php if ( function_exists( 'mpp_logout_url' ) ) : ?>
		<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( mpp_logout_url() ); ?>"><?php esc_html_e( 'Logout', 'platform-theme' ); ?></a>
	<?php endif; ?>
</div>

<?php if ( ! empty( $widgets ) ) : ?>
	<h3><?php esc_html_e( 'Modules', 'platform-theme' ); ?></h3>
	<div class="mpp-stats">
		<?php foreach ( $widgets as $widget ) : ?>
			<div class="mpp-stat-card">
				<span class="mpp-stat-card__label"><?php echo esc_html( $widget['title'] ?? '' ); ?></span>
				<span class="mpp-stat-card__value"><?php echo esc_html( $widget['value'] ?? '—' ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<h3><?php esc_html_e( 'Recent Activity', 'platform-theme' ); ?></h3>
<?php if ( ! empty( $activity ) ) : ?>
	<ul class="mpp-activity-list">
		<?php foreach ( $activity as $entry ) : ?>
			<li><code><?php echo esc_html( $entry['action'] ); ?></code> — <?php echo esc_html( $entry['created_at'] ); ?></li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p class="mpp-muted"><?php esc_html_e( 'No recent activity yet.', 'platform-theme' ); ?></p>
<?php endif; ?>
<?php
$content = ob_get_clean();

platform_render_panel( 'user', __( 'User Panel', 'platform-theme' ), $content );
