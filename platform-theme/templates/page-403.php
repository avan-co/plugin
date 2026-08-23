<?php
/**
 * 403 Forbidden template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="mpp-main mpp-main--centered">
	<div class="mpp-card mpp-card--error">
		<h1>403</h1>
		<p><?php esc_html_e( 'You do not have permission to access this page.', 'platform-theme' ); ?></p>
		<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app' ) : home_url( '/app' ) ); ?>" class="mpp-btn mpp-btn--primary">
			<?php esc_html_e( 'Go to Dashboard', 'platform-theme' ); ?>
		</a>
	</div>
</main>

<?php
get_footer();
