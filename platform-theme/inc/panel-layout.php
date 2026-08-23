<?php
/**
 * Panel layout helper.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a panel page.
 *
 * @param string $panel   Panel slug.
 * @param string $title   Page title.
 * @param string $content HTML content.
 */
function platform_render_panel( $panel, $title, $content ) {
	get_header();
	?>
	<div class="mpp-layout">
		<?php get_sidebar( null, array( 'panel' => $panel ) ); ?>
		<main class="mpp-main mpp-main--panel">
			<div class="mpp-content">
				<header class="mpp-page-header">
					<h1><?php echo esc_html( $title ); ?></h1>
				</header>
				<div class="mpp-page-body">
					<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by caller ?>
				</div>
			</div>
		</main>
	</div>
	<?php
	get_footer();
}
