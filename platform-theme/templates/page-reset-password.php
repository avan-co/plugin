<?php
/**
 * Reset password page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( mpp_route_url( 'app' ) );
	exit;
}

require_once get_template_directory() . '/inc/auth-page.php';

$reset_context = class_exists( '\MPP\Auth\PasswordResetHandler' )
	? \MPP\Auth\PasswordResetHandler::get_reset_context()
	: array( 'valid' => false, 'message' => __( 'Password reset is unavailable.', 'platform-theme' ) );

get_header();

ob_start();

if ( ! $reset_context['valid'] ) {
	platform_ui_alert( (string) $reset_context['message'], 'error' );
	?>
	<p class="mpp-form-footer">
		<a href="<?php echo esc_url( mpp_route_url( 'forgot-password' ) ); ?>"><?php esc_html_e( 'Request a new reset link', 'platform-theme' ); ?></a>
		&nbsp;·&nbsp;
		<a href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Back to login', 'platform-theme' ); ?></a>
	</p>
	<?php
} else {
	if ( ! empty( $GLOBALS['mpp_reset_password_error'] ) ) {
		platform_ui_alert( (string) $GLOBALS['mpp_reset_password_error'], 'error' );
	}
	?>
	<form method="post" action="<?php echo esc_url( mpp_reset_password_url( $reset_context['key'], $reset_context['login'] ) ); ?>" class="mpp-form" novalidate>
		<?php echo class_exists( '\MPP\Auth\PasswordResetHandler' ) ? \MPP\Auth\PasswordResetHandler::reset_nonce_field() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<input type="hidden" name="mpp_reset_password" value="1">
		<input type="hidden" name="reset_key" value="<?php echo esc_attr( $reset_context['key'] ); ?>">
		<input type="hidden" name="user_login" value="<?php echo esc_attr( $reset_context['login'] ); ?>">

		<div class="mpp-field">
			<label class="mpp-field__label" for="pass1"><?php esc_html_e( 'New Password', 'platform-theme' ); ?></label>
			<div class="mpp-password-field">
				<input class="mpp-input" type="password" name="pass1" id="pass1" required autocomplete="new-password" minlength="8" data-password-strength>
				<button type="button" class="mpp-btn mpp-btn--ghost mpp-btn--sm mpp-password-toggle" data-target="pass1" data-show-label="<?php esc_attr_e( 'Show', 'platform-theme' ); ?>" data-hide-label="<?php esc_attr_e( 'Hide', 'platform-theme' ); ?>" aria-label="<?php esc_attr_e( 'Show password', 'platform-theme' ); ?>">
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
			<label class="mpp-field__label" for="pass2"><?php esc_html_e( 'Confirm New Password', 'platform-theme' ); ?></label>
			<div class="mpp-password-field">
				<input class="mpp-input" type="password" name="pass2" id="pass2" required autocomplete="new-password" minlength="8">
				<button type="button" class="mpp-btn mpp-btn--ghost mpp-btn--sm mpp-password-toggle" data-target="pass2" data-show-label="<?php esc_attr_e( 'Show', 'platform-theme' ); ?>" data-hide-label="<?php esc_attr_e( 'Hide', 'platform-theme' ); ?>" aria-label="<?php esc_attr_e( 'Show password', 'platform-theme' ); ?>">
					<?php esc_html_e( 'Show', 'platform-theme' ); ?>
				</button>
			</div>
		</div>

		<button type="submit" class="mpp-btn mpp-btn--primary mpp-btn--block"><?php esc_html_e( 'Reset Password', 'platform-theme' ); ?></button>
	</form>

	<p class="mpp-form-footer">
		<a href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Back to login', 'platform-theme' ); ?></a>
	</p>
	<?php
}

$content = ob_get_clean();

platform_render_auth_page(
	__( 'Reset Password', 'platform-theme' ),
	__( 'Choose a new password for your account.', 'platform-theme' ),
	$content
);

get_footer();
