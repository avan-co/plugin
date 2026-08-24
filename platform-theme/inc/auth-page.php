<?php
/**
 * Shared auth page layout helper.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a centered authentication page shell.
 *
 * @param string $title       Page title.
 * @param string $description Introductory text.
 * @param string $content     Inner card HTML.
 */
function platform_render_auth_page( $title, $description, $content ) {
	?>
	<main class="mpp-main mpp-main--centered mpp-main--auth" id="mpp-main-content">
		<div class="mpp-card mpp-card--login">
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( $description ) : ?>
				<p class="mpp-muted"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</main>
	<?php
}
