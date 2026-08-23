<?php
/**
 * User panel navigation.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;
?>

<nav class="mpp-nav" aria-label="<?php esc_attr_e( 'User panel navigation', 'platform-theme' ); ?>">
	<div class="mpp-nav__title"><?php esc_html_e( 'User Panel', 'platform-theme' ); ?></div>
	<?php platform_render_panel_navigation( 'user' ); ?>
</nav>
