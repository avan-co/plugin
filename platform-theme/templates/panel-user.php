<?php
/**
 * User panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

$summary = function_exists( 'mpp_get_user_summary' ) ? mpp_get_user_summary() : array();
$widgets = function_exists( 'mpp_get_panel_widgets' ) ? mpp_get_panel_widgets( 'user' ) : array();

ob_start();
?>
<p><?php esc_html_e( 'Welcome to your dashboard.', 'platform-theme' ); ?></p>

<div class="mpp-stats">
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Platform Roles', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( ! empty( $summary['role_names'] ) ? implode( ', ', $summary['role_names'] ) : '—' ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Permissions', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( isset( $summary['permission_count'] ) ? (string) $summary['permission_count'] : '0' ); ?></span>
	</div>
	<div class="mpp-stat-card">
		<span class="mpp-stat-card__label"><?php esc_html_e( 'Panels', 'platform-theme' ); ?></span>
		<span class="mpp-stat-card__value"><?php echo esc_html( ! empty( $summary['panels'] ) ? implode( ', ', $summary['panels'] ) : '—' ); ?></span>
	</div>
</div>

<?php if ( ! empty( $widgets ) ) : ?>
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
$content = ob_get_clean();

platform_render_panel( 'user', __( 'User Panel', 'platform-theme' ), $content );
