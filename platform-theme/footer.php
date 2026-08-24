<?php
/**
 * Footer template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

$route       = function_exists( 'mpp_get_current_route' ) ? mpp_get_current_route() : null;
$is_panel    = $route && ! empty( $route['slug'] ) && 0 === strpos( $route['slug'], 'app/' );
$footer_class = 'mpp-footer' . ( $is_panel ? ' mpp-footer--compact' : '' );
?>

<footer class="<?php echo esc_attr( $footer_class ); ?>">
	<div class="mpp-footer__inner">
		<?php if ( ! $is_panel ) : ?>
		<div class="mpp-footer__brand">
			<strong><?php echo esc_html( function_exists( 'mpp_get_platform_name' ) ? mpp_get_platform_name() : get_bloginfo( 'name' ) ); ?></strong>
			<p class="mpp-footer__tagline"><?php esc_html_e( 'Modular platform workspace', 'platform-theme' ); ?></p>
		</div>
		<div class="mpp-footer__links">
			<?php if ( is_user_logged_in() && function_exists( 'mpp_route_url' ) ) : ?>
				<a href="<?php echo esc_url( mpp_route_url( 'app' ) ); ?>"><?php esc_html_e( 'App Launcher', 'platform-theme' ); ?></a>
				<a href="<?php echo esc_url( mpp_route_url( 'profile' ) ); ?>"><?php esc_html_e( 'Profile', 'platform-theme' ); ?></a>
				<a href="<?php echo esc_url( mpp_route_url( 'settings' ) ); ?>"><?php esc_html_e( 'Settings', 'platform-theme' ); ?></a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<p class="mpp-footer__copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( function_exists( 'mpp_get_platform_name' ) ? mpp_get_platform_name() : get_bloginfo( 'name' ) ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
