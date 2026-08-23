<?php
/**
 * Login page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app' ) : home_url( '/app' ) );
	exit;
}

get_header();
?>

<main class="mpp-main mpp-main--centered">
	<div class="mpp-card mpp-card--login">
		<h1><?php esc_html_e( 'Login', 'platform-theme' ); ?></h1>

		<?php if ( ! empty( $GLOBALS['mpp_login_error'] ) ) : ?>
			<div class="mpp-alert mpp-alert--error" role="alert">
				<?php echo esc_html( $GLOBALS['mpp_login_error'] ); ?>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'login' ) : home_url( '/login' ) ); ?>" class="mpp-form">
			<?php wp_nonce_field( 'mpp_login', 'mpp_login_nonce' ); ?>
			<input type="hidden" name="mpp_login" value="1">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app' ) : home_url( '/app' ) ); ?>">

			<label for="log"><?php esc_html_e( 'Username or Email', 'platform-theme' ); ?></label>
			<input type="text" name="log" id="log" required autocomplete="username">

			<label for="pwd"><?php esc_html_e( 'Password', 'platform-theme' ); ?></label>
			<input type="password" name="pwd" id="pwd" required autocomplete="current-password">

			<label class="mpp-checkbox">
				<input type="checkbox" name="rememberme" value="1">
				<?php esc_html_e( 'Remember me', 'platform-theme' ); ?>
			</label>

			<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Log In', 'platform-theme' ); ?></button>
		</form>

		<?php if ( class_exists( '\MPP\Auth\RegistrationHandler' ) && \MPP\Auth\RegistrationHandler::is_enabled() ) : ?>
			<p class="mpp-form-footer">
				<?php esc_html_e( 'Need an account?', 'platform-theme' ); ?>
				<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'register' ) : home_url( '/register' ) ); ?>"><?php esc_html_e( 'Register', 'platform-theme' ); ?></a>
			</p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
