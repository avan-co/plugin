<?php
/**
 * Application panel shell layout.
 *
 * @package PlatformTheme
 */

namespace PlatformTheme\DesignSystem;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the shared panel chrome: sidebar, page header, and content area.
 */
final class PanelShell {

	/**
	 * Default breadcrumb labels per panel.
	 *
	 * @var array<string, string>
	 */
	private static $panel_roots = array(
		'user'    => 'User Panel',
		'manager' => 'Manager Panel',
		'admin'   => 'Admin Panel',
	);

	/**
	 * Render a full panel page.
	 *
	 * @param string               $panel        Panel slug.
	 * @param string               $title        Page title.
	 * @param string               $content      HTML content.
	 * @param string               $description  Optional page description.
	 * @param array<int, string>   $breadcrumb   Optional breadcrumb labels.
	 * @param string               $actions_html Optional header action buttons.
	 */
	public static function render( $panel, $title, $content, $description = '', array $breadcrumb = array(), $actions_html = '' ) {
		$panel = sanitize_key( $panel );

		get_header();
		?>
		<div class="mpp-layout mpp-layout--<?php echo esc_attr( $panel ); ?>" data-panel="<?php echo esc_attr( $panel ); ?>">
			<?php self::render_sidebar( $panel ); ?>
			<div class="mpp-layout__main">
				<main class="mpp-main mpp-main--panel" id="mpp-main-content">
					<div class="mpp-content">
						<?php
						UIComponents::page_header(
							$title,
							$description,
							self::normalize_breadcrumb( $panel, $title, $breadcrumb ),
							$actions_html
						);
						?>
						<div class="mpp-page-body mpp-page-body--<?php echo esc_attr( $panel ); ?>">
							<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				</main>
			</div>
		</div>
		<?php
		get_footer();
	}

	/**
	 * Render a panel page with default breadcrumb metadata.
	 *
	 * @param string               $panel       Panel slug.
	 * @param string               $title       Page title.
	 * @param string               $content     HTML content.
	 * @param string               $description Optional description.
	 * @param array<int, string>   $breadcrumb  Optional breadcrumb override.
	 */
	public static function render_with_meta( $panel, $title, $content, $description = '', array $breadcrumb = array() ) {
		if ( empty( $breadcrumb ) ) {
			$breadcrumb = array(
				self::get_panel_root_label( $panel ),
				$title,
			);
		}

		self::render( $panel, $title, $content, $description, $breadcrumb, '' );
	}

	/**
	 * Render sidebar for a panel.
	 *
	 * @param string $panel Panel slug.
	 */
	private static function render_sidebar( $panel ) {
		?>
		<aside class="mpp-sidebar" data-panel="<?php echo esc_attr( $panel ); ?>">
			<?php PanelNavigation::render( $panel ); ?>
		</aside>
		<?php
	}

	/**
	 * Normalize breadcrumb labels.
	 *
	 * @param string               $panel      Panel slug.
	 * @param string               $title      Page title.
	 * @param array<int, string>   $breadcrumb Provided breadcrumb.
	 * @return array<int, string>
	 */
	private static function normalize_breadcrumb( $panel, $title, array $breadcrumb ) {
		if ( ! empty( $breadcrumb ) ) {
			return $breadcrumb;
		}

		return array(
			self::get_panel_root_label( $panel ),
			$title,
		);
	}

	/**
	 * Get translated root breadcrumb label.
	 *
	 * @param string $panel Panel slug.
	 * @return string
	 */
	private static function get_panel_root_label( $panel ) {
		$labels = array(
			'user'    => __( 'User Panel', 'platform-theme' ),
			'manager' => __( 'Manager Panel', 'platform-theme' ),
			'admin'   => __( 'Admin Panel', 'platform-theme' ),
		);

		return $labels[ $panel ] ?? self::$panel_roots[ $panel ] ?? ucfirst( $panel );
	}
}
