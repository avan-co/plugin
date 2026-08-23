<?php
/**
 * User panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

ob_start();
?>
<p><?php esc_html_e( 'Welcome to the User Panel. This is the base infrastructure — business modules will be added in future phases.', 'platform-theme' ); ?></p>

<div class="mpp-stats">
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Status', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php esc_html_e( 'Active', 'platform-theme' ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Role', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php esc_html_e( 'User', 'platform-theme' ); ?></span>
	</div>
</div>
<?php
$content = ob_get_clean();

platform_render_panel( 'user', __( 'User Panel', 'platform-theme' ), $content );
