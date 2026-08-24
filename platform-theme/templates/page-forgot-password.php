<?php
/**
 * Forgot password page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( mpp_route_url( 'app' ) );
	exit;
}

require_once get_template_directory() . '/inc/auth-page.php';

get_header();

ob_start();

if ( ! empty( $GLOBALS['mpp_forgot_password_success'] ) ) {
	platform_ui_alert( (string) $GLOBALS['mpp_forgot_password_success'], 'success' );
	?>
	<p class="mpp-form-footer">
		<a href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Back to login', 'platform-theme' ); ?></a>
	</p>
	<?php
} else {
	if ( ! empty( $GLOBALS['mpp_forgot_password_error'] ) ) {
		platform_ui_alert( (string) $GLOBALS['mpp_forgot_password_error'], 'error' );
	}
	?>
	<form method="post" action="<?php echo esc_url( mpp_route_url( 'forgot-password' ) ); ?>" class="mpp-form" novalidate>
		<?php echo class_exists( '\MPP\Auth\PasswordResetHandler' ) ? \MPP\Auth\PasswordResetHandler::nonce_field() : wp_nonce_field( 'mpp_forgot_password', 'mpp_forgot_password_nonce', true, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<input type="hidden" name="mpp_forgot_password" value="1">

		<div class="mpp-field">
			<label class="mpp-field__label" for="user_login"><?php esc_html_e( 'Username or Email', 'platform-theme' ); ?></label>
			<input class="mpp-input" type="text" name="user_login" id="user_login" required autocomplete="username">
		</div>

		<button type="submit" class="mpp-btn mpp-btn--primary mpp-btn--block"><?php esc_html_e( 'Send Reset Link', 'platform-theme' ); ?></button>
	</form>

	<p class="mpp-form-footer">
		<a href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Back to login', 'platform-theme' ); ?></a>
	</p>
	<?php
}

$content = ob_get_clean();

platform_render_auth_page(
	__( 'Forgot Password', 'platform-theme' ),
	__( 'Enter your username or email address and we will send you instructions to reset your password.', 'platform-theme' ),
	$content
);

get_footer();
