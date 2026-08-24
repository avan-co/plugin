<?php
/**
 * Fallback reset-password template.
 *
 * @package PlatformCore
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( mpp_route_url( 'app' ) );
	exit;
}

$reset_context = \MPP\Auth\PasswordResetHandler::get_reset_context();
$route         = mpp_get_current_route();
$title         = ! empty( $route['definition']['title'] ) ? $route['definition']['title'] : __( 'Reset Password', 'platform-core' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $title ); ?> — <?php bloginfo( 'name' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'mpp-page mpp-reset-password mpp-auth-page' ); ?>>
	<main class="mpp-main mpp-main--centered">
		<div class="mpp-card mpp-card--login">
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( ! $reset_context['valid'] ) : ?>
				<p class="mpp-alert mpp-alert--error"><?php echo esc_html( $reset_context['message'] ); ?></p>
				<p><a href="<?php echo esc_url( mpp_route_url( 'forgot-password' ) ); ?>"><?php esc_html_e( 'Request a new reset link', 'platform-core' ); ?></a></p>
			<?php else : ?>
				<?php if ( ! empty( $GLOBALS['mpp_reset_password_error'] ) ) : ?>
					<p class="mpp-alert mpp-alert--error"><?php echo esc_html( (string) $GLOBALS['mpp_reset_password_error'] ); ?></p>
				<?php endif; ?>
				<form method="post" action="<?php echo esc_url( mpp_reset_password_url( $reset_context['key'], $reset_context['login'] ) ); ?>" class="mpp-form">
					<?php echo \MPP\Auth\PasswordResetHandler::reset_nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<input type="hidden" name="mpp_reset_password" value="1">
					<input type="hidden" name="reset_key" value="<?php echo esc_attr( $reset_context['key'] ); ?>">
					<input type="hidden" name="user_login" value="<?php echo esc_attr( $reset_context['login'] ); ?>">
					<label for="pass1"><?php esc_html_e( 'New Password', 'platform-core' ); ?></label>
					<input type="password" name="pass1" id="pass1" required minlength="8">
					<label for="pass2"><?php esc_html_e( 'Confirm New Password', 'platform-core' ); ?></label>
					<input type="password" name="pass2" id="pass2" required minlength="8">
					<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Reset Password', 'platform-core' ); ?></button>
				</form>
			<?php endif; ?>
			<p><a href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Back to login', 'platform-core' ); ?></a></p>
		</div>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
