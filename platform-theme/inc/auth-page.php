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
	$platform_name = function_exists( 'mpp_get_platform_name' ) ? mpp_get_platform_name() : get_bloginfo( 'name' );
	$logo_mark     = function_exists( 'mpp_get_logo_mark' ) ? mpp_get_logo_mark() : 'P';
	?>
	<main class="mpp-main mpp-main--centered mpp-main--auth" id="mpp-main-content">
		<div class="mpp-card mpp-card--login mpp-card--auth">
			<div class="mpp-auth-brand">
				<span class="mpp-auth-brand__mark" aria-hidden="true"><?php echo esc_html( $logo_mark ); ?></span>
				<span class="mpp-auth-brand__name"><?php echo esc_html( $platform_name ); ?></span>
			</div>
			<h1 class="mpp-auth-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $description ) : ?>
				<p class="mpp-auth-description mpp-muted"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</main>
	<?php
}
