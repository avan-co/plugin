<?php
/**
 * 404 Not Found template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="mpp-main mpp-main--centered">
	<div class="mpp-card mpp-card--error">
		<h1>404</h1>
		<p><?php esc_html_e( 'The page you are looking for does not exist.', 'platform-theme' ); ?></p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mpp-btn mpp-btn--primary">
			<?php esc_html_e( 'Go Home', 'platform-theme' ); ?>
		</a>
	</div>
</main>

<?php
get_footer();
