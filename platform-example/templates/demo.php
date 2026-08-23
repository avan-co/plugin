<?php
/**
 * Example module demo route template.
 *
 * @package PlatformExample
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

ob_start();
?>
<p><?php esc_html_e( 'This page is served by the platform-example plugin via the core module contract.', 'platform-example' ); ?></p>
<?php
$content = ob_get_clean();

platform_render_panel( 'user', __( 'Example Demo', 'platform-example' ), $content );
