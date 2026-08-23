<?php
/**
 * Example module demo route template.
 *
 * @package PlatformExample
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-shell.php';

ob_start();
?>
<div class="mpp-card">
	<h2><?php esc_html_e( 'Example Module Demo', 'platform-example' ); ?></h2>
	<p><?php esc_html_e( 'This page is served by the platform-example plugin through the core module contract.', 'platform-example' ); ?></p>
	<dl class="mpp-profile-list">
		<dt><?php esc_html_e( 'Module', 'platform-example' ); ?></dt>
		<dd><code>example</code></dd>
		<dt><?php esc_html_e( 'View Permission', 'platform-example' ); ?></dt>
		<dd><code>example.demo.view</code></dd>
		<dt><?php esc_html_e( 'Manage Permission', 'platform-example' ); ?></dt>
		<dd><code>example.demo.manage</code></dd>
		<dt><?php esc_html_e( 'Route', 'platform-example' ); ?></dt>
		<dd><code>app/example</code></dd>
	</dl>
	<p class="mpp-muted"><?php esc_html_e( 'Grant example.demo.view to a role, assign the role to a user, then open this page from the user panel navigation.', 'platform-example' ); ?></p>
</div>
<?php
$content = ob_get_clean();

platform_render_panel_shell(
	'user',
	__( 'Example Demo', 'platform-example' ),
	$content,
	__( 'Demonstrates how an external module registers a protected route and navigation item.', 'platform-example' )
);
