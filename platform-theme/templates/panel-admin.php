<?php
/**
 * Admin panel template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

ob_start();
?>
<p><?php esc_html_e( 'Welcome to the Admin Panel. System configuration and ACL management will be available here.', 'platform-theme' ); ?></p>

<?php if ( function_exists( 'mpp_can' ) && mpp_can( 'core.acl.manage' ) ) : ?>
	<section class="mpp-section">
		<h2><?php esc_html_e( 'ACL Management', 'platform-theme' ); ?></h2>
		<p><?php esc_html_e( 'Use the REST API to manage roles and permissions until the admin UI is built.', 'platform-theme' ); ?></p>
		<ul class="mpp-api-list">
			<li><code>GET /wp-json/platform/v1/roles</code></li>
			<li><code>GET /wp-json/platform/v1/permissions</code></li>
			<li><code>GET /wp-json/platform/v1/acl/me</code></li>
		</ul>
	</section>
<?php endif; ?>
<?php
$content = ob_get_clean();

platform_render_panel( 'admin', __( 'Admin Panel', 'platform-theme' ), $content );
