<?php
/**
 * Fallback forgot-password template.
 *
 * @package PlatformCore
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( mpp_route_url( 'app' ) );
	exit;
}

$route = mpp_get_current_route();
$title = ! empty( $route['definition']['title'] ) ? $route['definition']['title'] : __( 'Forgot Password', 'platform-core' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $title ); ?> — <?php bloginfo( 'name' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'mpp-page mpp-forgot-password mpp-auth-page' ); ?>>
	<main class="mpp-main mpp-main--centered">
		<div class="mpp-card mpp-card--login">
			<h1><?php echo esc_html( $title ); ?></h1>
			<p class="mpp-muted"><?php esc_html_e( 'Enter your username or email address and we will send you instructions to reset your password.', 'platform-core' ); ?></p>

			<?php if ( ! empty( $GLOBALS['mpp_forgot_password_success'] ) ) : ?>
				<div class="mpp-alert mpp-alert--success" role="status">
					<?php echo esc_html( $GLOBALS['mpp_forgot_password_success'] ); ?>
				</div>
				<p><a href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Back to login', 'platform-core' ); ?></a></p>
			<?php else : ?>
				<?php if ( ! empty( $GLOBALS['mpp_forgot_password_error'] ) ) : ?>
					<div class="mpp-alert mpp-alert--error" role="alert">
						<?php echo esc_html( $GLOBALS['mpp_forgot_password_error'] ); ?>
					</div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( mpp_route_url( 'forgot-password' ) ); ?>" class="mpp-form">
					<?php echo class_exists( '\MPP\Auth\PasswordResetHandler' ) ? \MPP\Auth\PasswordResetHandler::nonce_field() : wp_nonce_field( 'mpp_forgot_password', 'mpp_forgot_password_nonce', true, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<input type="hidden" name="mpp_forgot_password" value="1">
					<label for="user_login"><?php esc_html_e( 'Username or Email', 'platform-core' ); ?></label>
					<input type="text" name="user_login" id="user_login" required autocomplete="username">
					<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Send Reset Link', 'platform-core' ); ?></button>
				</form>
				<p><a href="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>"><?php esc_html_e( 'Back to login', 'platform-core' ); ?></a></p>
			<?php endif; ?>
		</div>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
