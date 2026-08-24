<?php
/**
 * Login page template.
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

$login_value = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
$redirect_to = function_exists( 'mpp_get_post_login_redirect_url' ) ? mpp_get_post_login_redirect_url() : mpp_route_url( 'app' );

ob_start();

if ( ! empty( $GLOBALS['mpp_login_error'] ) ) {
	platform_ui_alert( (string) $GLOBALS['mpp_login_error'], 'error' );
}
?>
<form method="post" action="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>" class="mpp-form" novalidate>
	<?php wp_nonce_field( 'mpp_login', 'mpp_login_nonce' ); ?>
	<input type="hidden" name="mpp_login" value="1">
	<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>">

	<div class="mpp-field">
		<label class="mpp-field__label" for="log"><?php esc_html_e( 'Username or Email', 'platform-theme' ); ?></label>
		<input class="mpp-input" type="text" name="log" id="log" required autocomplete="username" value="<?php echo esc_attr( $login_value ); ?>">
	</div>

	<div class="mpp-field">
		<label class="mpp-field__label" for="pwd"><?php esc_html_e( 'Password', 'platform-theme' ); ?></label>
		<div class="mpp-password-field">
			<input class="mpp-input" type="password" name="pwd" id="pwd" required autocomplete="current-password">
			<button type="button" class="mpp-btn mpp-btn--ghost mpp-btn--sm mpp-password-toggle" data-target="pwd" data-show-label="<?php esc_attr_e( 'Show', 'platform-theme' ); ?>" data-hide-label="<?php esc_attr_e( 'Hide', 'platform-theme' ); ?>" aria-label="<?php esc_attr_e( 'Show password', 'platform-theme' ); ?>">
				<?php esc_html_e( 'Show', 'platform-theme' ); ?>
			</button>
		</div>
	</div>

	<label class="mpp-checkbox">
		<input type="checkbox" name="rememberme" value="1">
		<?php esc_html_e( 'Remember me', 'platform-theme' ); ?>
	</label>

	<button type="submit" class="mpp-btn mpp-btn--primary mpp-btn--block"><?php esc_html_e( 'Log In', 'platform-theme' ); ?></button>
</form>

<div class="mpp-auth-links">
	<p class="mpp-form-footer">
		<a href="<?php echo esc_url( function_exists( 'mpp_forgot_password_url' ) ? mpp_forgot_password_url() : wp_lostpassword_url( mpp_route_url( 'login' ) ) ); ?>"><?php esc_html_e( 'Forgot password?', 'platform-theme' ); ?></a>
	</p>

	<?php if ( class_exists( '\MPP\Auth\RegistrationHandler' ) && \MPP\Auth\RegistrationHandler::is_enabled() ) : ?>
		<p class="mpp-form-footer">
			<?php esc_html_e( 'Need an account?', 'platform-theme' ); ?>
			<a href="<?php echo esc_url( mpp_route_url( 'register' ) ); ?>"><?php esc_html_e( 'Register', 'platform-theme' ); ?></a>
		</p>
	<?php endif; ?>
</div>
<?php
$content = ob_get_clean();

platform_render_auth_page(
	__( 'Login', 'platform-theme' ),
	__( 'Sign in with your username or email address.', 'platform-theme' ),
	$content
);

get_footer();
