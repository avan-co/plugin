<?php
/**
 * User/Manager panel shell helper.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/panel-layout.php';

/**
 * Render a panel page with shared shell metadata.
 *
 * @param string             $panel       Panel slug.
 * @param string             $title       Page title.
 * @param string             $content     Page HTML.
 * @param string             $description Optional description.
 * @param array<int, string> $breadcrumb  Optional breadcrumb labels.
 */
function platform_render_panel_shell( $panel, $title, $content, $description = '', array $breadcrumb = array() ) {
	if ( empty( $breadcrumb ) ) {
		$panel_labels = array(
			'user'    => __( 'User Panel', 'platform-theme' ),
			'manager' => __( 'Manager Panel', 'platform-theme' ),
		);

		$breadcrumb = array(
			$panel_labels[ $panel ] ?? ucfirst( $panel ),
			$title,
		);
	}

	platform_render_panel( $panel, $title, $content, $description, $breadcrumb );
}

/**
 * Render a placeholder section for features without backend yet.
 *
 * @param string $title       Section title.
 * @param string $description Section description.
 * @param string $cta_label   Optional CTA label.
 * @param string $cta_url     Optional CTA URL.
 */
function platform_render_placeholder_section( $title, $description, $cta_label = '', $cta_url = '' ) {
	?>
	<section class="mpp-card mpp-placeholder-section">
		<h3 class="mpp-placeholder-section__title"><?php echo esc_html( $title ); ?></h3>
		<p class="mpp-muted"><?php echo esc_html( $description ); ?></p>
		<?php if ( $cta_label && $cta_url ) : ?>
			<p><a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a></p>
		<?php endif; ?>
	</section>
	<?php
}
