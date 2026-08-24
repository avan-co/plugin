<?php
/**
 * Error page layout helper.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a centered error page.
 *
 * @param string                              $code    HTTP-style code label.
 * @param string                              $title   Human-readable title.
 * @param string                              $message Description for the visitor.
 * @param array<int, array<string, string>> $actions Optional action buttons (label, url, variant).
 */
function platform_render_error_page( $code, $title, $message, array $actions = array() ) {
	?>
	<main class="mpp-main mpp-main--centered mpp-main--error" id="mpp-main-content">
		<div class="mpp-card mpp-card--error">
			<div class="mpp-error-page__icon" aria-hidden="true"><?php echo esc_html( $code ); ?></div>
			<h1 class="mpp-error-page__title"><?php echo esc_html( $title ); ?></h1>
			<p class="mpp-error-page__message"><?php echo esc_html( $message ); ?></p>
			<?php if ( ! empty( $actions ) ) : ?>
				<div class="mpp-error-page__actions">
					<?php foreach ( $actions as $action ) : ?>
						<?php
						if ( empty( $action['label'] ) || empty( $action['url'] ) ) {
							continue;
						}
						$variant = ! empty( $action['variant'] ) ? $action['variant'] : 'primary';
						?>
						<a class="mpp-btn mpp-btn--<?php echo esc_attr( $variant ); ?>" href="<?php echo esc_url( $action['url'] ); ?>">
							<?php echo esc_html( $action['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</main>
	<?php
}
