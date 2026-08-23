<?php
/**
 * Header template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'mpp-body' ); ?>>
<?php wp_body_open(); ?>

<header class="mpp-header">
	<div class="mpp-header__inner">
		<a href="<?php echo esc_url( home_url( '/app' ) ); ?>" class="mpp-logo">
			<?php bloginfo( 'name' ); ?>
		</a>

		<?php if ( is_user_logged_in() ) : ?>
			<div class="mpp-header__actions">
				<?php platform_render_panel_switcher(); ?>
				<a href="<?php echo esc_url( home_url( '/profile' ) ); ?>" class="mpp-header__link">
					<?php echo esc_html( wp_get_current_user()->display_name ); ?>
				</a>
				<a href="<?php echo esc_url( function_exists( 'mpp_logout_url' ) ? mpp_logout_url() : wp_logout_url() ); ?>" class="mpp-header__link mpp-header__link--logout">
					<?php esc_html_e( 'Logout', 'platform-theme' ); ?>
				</a>
			</div>
		<?php endif; ?>

		<button class="mpp-nav-toggle" aria-label="<?php esc_attr_e( 'Toggle navigation', 'platform-theme' ); ?>" aria-expanded="false">
			<span></span>
		</button>
	</div>
</header>
