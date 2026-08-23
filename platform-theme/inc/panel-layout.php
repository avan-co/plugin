<?php
/**
 * Panel layout helper.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a panel page with application shell.
 *
 * @param string $panel        Panel slug.
 * @param string $title        Page title.
 * @param string $content      HTML content.
 * @param string $description  Optional page description.
 * @param array<int, string>   $breadcrumb Optional breadcrumb.
 */
function platform_render_panel( $panel, $title, $content, $description = '', array $breadcrumb = array() ) {
	get_header();
	?>
	<div class="mpp-layout">
		<?php get_sidebar( null, array( 'panel' => $panel ) ); ?>
		<div class="mpp-layout__main">
			<main class="mpp-main mpp-main--panel" id="mpp-main-content">
				<div class="mpp-content">
					<?php
					if ( function_exists( 'platform_ui_page_header' ) ) {
						platform_ui_page_header( $title, $description, $breadcrumb );
					} else {
						echo '<header class="mpp-page-header"><h1 class="mpp-page-header__title">' . esc_html( $title ) . '</h1></header>';
					}
					?>
					<div class="mpp-page-body">
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by caller ?>
					</div>
				</div>
			</main>
		</div>
	</div>
	<?php
	get_footer();
}
