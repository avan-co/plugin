<?php
/**
 * Manager panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

ob_start();
?>
<p><?php esc_html_e( 'Welcome to the Manager Panel. Team and department management modules will be integrated here.', 'platform-theme' ); ?></p>

<div class="mpp-stats">
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Team Members', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value">—</span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Pending Tasks', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value">—</span>
	</div>
</div>
<?php
$content = ob_get_clean();

platform_render_panel( 'manager', __( 'Manager Panel', 'platform-theme' ), $content );
