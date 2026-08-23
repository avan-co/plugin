<?php
/**
 * Manager panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

$stats = function_exists( 'mpp_get_manager_stats' ) ? mpp_get_manager_stats() : array();

ob_start();
?>
<p><?php esc_html_e( 'Welcome to the Manager Panel.', 'platform-theme' ); ?></p>

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
<?php
$content = ob_get_clean();

platform_render_panel( 'manager', __( 'Manager Panel', 'platform-theme' ), $content );
