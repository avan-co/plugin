<?php
/**
 * Manager panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

$user    = wp_get_current_user();
$stats   = function_exists( 'mpp_get_manager_stats' ) ? mpp_get_manager_stats() : array();
$widgets = function_exists( 'mpp_get_panel_widgets' ) ? mpp_get_panel_widgets( 'manager' ) : array();

ob_start();
?>
<div class="mpp-dashboard-welcome">
	<h2><?php echo esc_html( sprintf( __( 'Welcome, %s', 'platform-theme' ), $user->display_name ) ); ?></h2>
	<p><?php esc_html_e( 'Manager workspace for team oversight and module widgets.', 'platform-theme' ); ?></p>
</div>

<div class="mpp-quick-actions">
	<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/manager/profile' ) : home_url( '/app/manager/profile' ) ); ?>"><?php esc_html_e( 'Profile', 'platform-theme' ); ?></a>
	<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'settings' ) : home_url( '/settings' ) ); ?>"><?php esc_html_e( 'Settings', 'platform-theme' ); ?></a>
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

<h3><?php esc_html_e( 'Module Area', 'platform-theme' ); ?></h3>
<?php if ( ! empty( $widgets ) ) : ?>
	<div class="mpp-stats">
		<?php foreach ( $widgets as $widget ) : ?>
			<div class="mpp-stat-card">
				<span class="mpp-stat-card__label"><?php echo esc_html( $widget['title'] ?? '' ); ?></span>
				<span class="mpp-stat-card__value"><?php echo esc_html( $widget['value'] ?? '—' ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<p class="mpp-muted"><?php esc_html_e( 'Business module widgets will appear here when modules are installed.', 'platform-theme' ); ?></p>
<?php endif; ?>
<?php
$content = ob_get_clean();

platform_render_panel( 'manager', __( 'Manager Panel', 'platform-theme' ), $content );
