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

get_header();
?>

<main class="mpp-main mpp-main--centered" id="mpp-main-content">
	<div class="mpp-card mpp-card--login">
		<h1><?php esc_html_e( 'Login', 'platform-theme' ); ?></h1>
		<p class="mpp-muted"><?php esc_html_e( 'Sign in with your username or email address.', 'platform-theme' ); ?></p>

		<?php if ( ! empty( $GLOBALS['mpp_login_error'] ) ) : ?>
			<?php platform_ui_alert( (string) $GLOBALS['mpp_login_error'], 'error' ); ?>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>" class="mpp-form" novalidate>
			<?php wp_nonce_field( 'mpp_login', 'mpp_login_nonce' ); ?>
			<input type="hidden" name="mpp_login" value="1">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( mpp_route_url( 'app' ) ); ?>">

			<div class="mpp-field">
				<label class="mpp-field__label" for="log"><?php esc_html_e( 'Username or Email', 'platform-theme' ); ?></label>
				<input class="mpp-input" type="text" name="log" id="log" required autocomplete="username">
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

		<p class="mpp-form-footer">
			<a href="<?php echo esc_url( wp_lostpassword_url( mpp_route_url( 'login' ) ) ); ?>"><?php esc_html_e( 'Forgot password?', 'platform-theme' ); ?></a>
		</p>

		<?php if ( class_exists( '\MPP\Auth\RegistrationHandler' ) && \MPP\Auth\RegistrationHandler::is_enabled() ) : ?>
			<p class="mpp-form-footer">
				<?php esc_html_e( 'Need an account?', 'platform-theme' ); ?>
				<a href="<?php echo esc_url( mpp_route_url( 'register' ) ); ?>"><?php esc_html_e( 'Register', 'platform-theme' ); ?></a>
			</p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
