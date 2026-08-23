<?php
/**
 * Registration page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app' ) : home_url( '/app' ) );
	exit;
}

if ( function_exists( 'MPP\Auth\RegistrationHandler::is_enabled' ) && ! \MPP\Auth\RegistrationHandler::is_enabled() ) {
	wp_safe_redirect( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'login' ) : home_url( '/login' ) );
	exit;
}

get_header();
?>

<main class="mpp-main mpp-main--centered">
	<div class="mpp-card mpp-card--login">
		<h1><?php esc_html_e( 'Register', 'platform-theme' ); ?></h1>

		<?php if ( ! empty( $GLOBALS['mpp_register_error'] ) ) : ?>
			<div class="mpp-alert mpp-alert--error" role="alert">
				<?php echo esc_html( $GLOBALS['mpp_register_error'] ); ?>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'register' ) : home_url( '/register' ) ); ?>" class="mpp-form">
			<?php echo class_exists( '\MPP\Auth\RegistrationHandler' ) ? \MPP\Auth\RegistrationHandler::nonce_field() : wp_nonce_field( 'mpp_register', 'mpp_register_nonce', true, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_register" value="1">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app/user' ) : home_url( '/app/user' ) ); ?>">

			<label for="user_login"><?php esc_html_e( 'Username', 'platform-theme' ); ?></label>
			<input type="text" name="user_login" id="user_login" required autocomplete="username">

			<label for="user_email"><?php esc_html_e( 'Email', 'platform-theme' ); ?></label>
			<input type="email" name="user_email" id="user_email" required autocomplete="email">

			<label for="user_pass"><?php esc_html_e( 'Password', 'platform-theme' ); ?></label>
			<input type="password" name="user_pass" id="user_pass" required autocomplete="new-password">

			<label for="user_pass_confirm"><?php esc_html_e( 'Confirm Password', 'platform-theme' ); ?></label>
			<input type="password" name="user_pass_confirm" id="user_pass_confirm" required autocomplete="new-password">

			<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Create Account', 'platform-theme' ); ?></button>
		</form>

		<p class="mpp-form-footer">
			<?php esc_html_e( 'Already have an account?', 'platform-theme' ); ?>
			<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'login' ) : home_url( '/login' ) ); ?>"><?php esc_html_e( 'Log in', 'platform-theme' ); ?></a>
		</p>
	</div>
</main>

<?php
get_footer();
