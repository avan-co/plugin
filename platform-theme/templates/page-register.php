<?php
/**
 * Registration page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( mpp_route_url( 'app' ) );
	exit;
}

if ( class_exists( '\MPP\Auth\RegistrationHandler' ) && ! \MPP\Auth\RegistrationHandler::is_enabled() ) {
	wp_safe_redirect( mpp_route_url( 'login' ) );
	exit;
}

get_header();
?>

<main class="mpp-main mpp-main--centered" id="mpp-main-content">
	<div class="mpp-card mpp-card--login">
		<h1><?php esc_html_e( 'Register', 'platform-theme' ); ?></h1>
		<p class="mpp-muted"><?php esc_html_e( 'Create a platform account to access your user panel.', 'platform-theme' ); ?></p>

		<?php if ( ! empty( $GLOBALS['mpp_register_error'] ) ) : ?>
			<?php platform_ui_alert( (string) $GLOBALS['mpp_register_error'], 'error' ); ?>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( mpp_route_url( 'register' ) ); ?>" class="mpp-form" novalidate>
			<?php echo class_exists( '\MPP\Auth\RegistrationHandler' ) ? \MPP\Auth\RegistrationHandler::nonce_field() : wp_nonce_field( 'mpp_register', 'mpp_register_nonce', true, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="hidden" name="mpp_register" value="1">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( mpp_route_url( 'app/user' ) ); ?>">

			<div class="mpp-field">
				<label class="mpp-field__label" for="user_login"><?php esc_html_e( 'Username', 'platform-theme' ); ?></label>
				<input class="mpp-input" type="text" name="user_login" id="user_login" required autocomplete="username">
			</div>

			<div class="mpp-field">
				<label class="mpp-field__label" for="user_email"><?php esc_html_e( 'Email', 'platform-theme' ); ?></label>
				<input class="mpp-input" type="email" name="user_email" id="user_email" required autocomplete="email">
			</div>

			<div class="mpp-field">
				<label class="mpp-field__label" for="user_pass"><?php esc_html_e( 'Password', 'platform-theme' ); ?></label>
				<div class="mpp-password-field">
					<input class="mpp-input" type="password" name="user_pass" id="user_pass" required autocomplete="new-password" minlength="8" data-password-strength>
					<button type="button" class="mpp-btn mpp-btn--ghost mpp-btn--sm mpp-password-toggle" data-target="user_pass" data-show-label="<?php esc_attr_e( 'Show', 'platform-theme' ); ?>" data-hide-label="<?php esc_attr_e( 'Hide', 'platform-theme' ); ?>" aria-label="<?php esc_attr_e( 'Show password', 'platform-theme' ); ?>">
						<?php esc_html_e( 'Show', 'platform-theme' ); ?>
					</button>
				</div>
				<p class="mpp-field__hint" data-password-strength-label hidden aria-live="polite"
					data-weak="<?php esc_attr_e( 'Weak password', 'platform-theme' ); ?>"
					data-fair="<?php esc_attr_e( 'Fair password', 'platform-theme' ); ?>"
					data-good="<?php esc_attr_e( 'Good password', 'platform-theme' ); ?>"
					data-strong="<?php esc_attr_e( 'Strong password', 'platform-theme' ); ?>"></p>
			</div>

			<div class="mpp-field">
				<label class="mpp-field__label" for="user_pass_confirm"><?php esc_html_e( 'Confirm Password', 'platform-theme' ); ?></label>
				<div class="mpp-password-field">
					<input class="mpp-input" type="password" name="user_pass_confirm" id="user_pass_confirm" required autocomplete="new-password" minlength="8">
					<button type="button" class="mpp-btn mpp-btn--ghost mpp-btn--sm mpp-password-toggle" data-target="user_pass_confirm" data-show-label="<?php esc_attr_e( 'Show', 'platform-theme' ); ?>" data-hide-label="<?php esc_attr_e( 'Hide', 'platform-theme' ); ?>" aria-label="<?php esc_attr_e( 'Show password', 'platform-theme' ); ?>">
						<?php esc_html_e( 'Show', 'platform-theme' ); ?>
					</button>
				</div>
			</div>

			<button type="submit" class="mpp-btn mpp-btn--primary mpp-btn--block"><?php esc_html_e( 'Create Account', 'platform-theme' ); ?></button>
		</form>

		<p class="mpp-form-footer">
			<?php esc_html_e( 'Already have an account?', 'platform-theme' ); ?>
			<a href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Log in', 'platform-theme' ); ?></a>
		</p>
	</div>
</main>

<?php
get_footer();
