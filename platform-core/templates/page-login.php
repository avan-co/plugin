<?php
/**
 * Fallback login template.
 *
 * @package PlatformCore
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( mpp_route_url( 'app' ) );
	exit;
}

$route = mpp_get_current_route();
$title = ! empty( $route['definition']['title'] ) ? $route['definition']['title'] : __( 'Login', 'platform-core' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $title ); ?> — <?php bloginfo( 'name' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'mpp-page mpp-login' ); ?>>
	<main class="mpp-main mpp-main--centered">
		<div class="mpp-card mpp-card--login">
			<h1><?php echo esc_html( $title ); ?></h1>

			<?php if ( ! empty( $GLOBALS['mpp_login_error'] ) ) : ?>
				<div class="mpp-alert mpp-alert--error" role="alert">
					<?php echo esc_html( $GLOBALS['mpp_login_error'] ); ?>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( mpp_route_url( 'login' ) ); ?>" class="mpp-form">
				<?php wp_nonce_field( 'mpp_login', 'mpp_login_nonce' ); ?>
				<input type="hidden" name="mpp_login" value="1">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( mpp_route_url( 'app' ) ); ?>">

				<label for="log"><?php esc_html_e( 'Username or Email', 'platform-core' ); ?></label>
				<input type="text" name="log" id="log" required autocomplete="username">

				<label for="pwd"><?php esc_html_e( 'Password', 'platform-core' ); ?></label>
				<input type="password" name="pwd" id="pwd" required autocomplete="current-password">

				<label class="mpp-checkbox">
					<input type="checkbox" name="rememberme" value="1">
					<?php esc_html_e( 'Remember me', 'platform-core' ); ?>
				</label>

				<button type="submit" class="mpp-btn mpp-btn--primary"><?php esc_html_e( 'Log In', 'platform-core' ); ?></button>
			</form>

			<p class="mpp-form-footer">
				<a href="<?php echo esc_url( function_exists( 'mpp_forgot_password_url' ) ? mpp_forgot_password_url() : wp_lostpassword_url( mpp_route_url( 'login' ) ) ); ?>"><?php esc_html_e( 'Forgot password?', 'platform-core' ); ?></a>
			</p>
		</div>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
