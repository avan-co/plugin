<?php
/**
 * Header template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

use PlatformTheme\DesignSystem\LanguageSwitcher;
use PlatformTheme\DesignSystem\UIComponents;

$current_user = wp_get_current_user();
$is_panel     = function_exists( 'mpp_get_current_route' ) && mpp_get_current_route();
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
		<div class="mpp-header__brand">
			<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'app' ) : home_url( '/app' ) ); ?>" class="mpp-logo">
				<span class="mpp-logo__mark" aria-hidden="true"><?php echo esc_html( function_exists( 'mpp_get_logo_mark' ) ? mpp_get_logo_mark() : 'P' ); ?></span>
				<span class="mpp-logo__text"><?php echo esc_html( function_exists( 'mpp_get_platform_name' ) ? mpp_get_platform_name() : get_bloginfo( 'name' ) ); ?></span>
			</a>
		</div>

		<?php if ( is_user_logged_in() ) : ?>
			<div class="mpp-header__tools">
				<?php platform_render_panel_switcher(); ?>
				<?php LanguageSwitcher::render(); ?>

				<div class="mpp-header__user">
					<?php echo UIComponents::avatar( (int) $current_user->ID, 32 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<div class="mpp-header__user-meta">
						<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'profile' ) : home_url( '/profile' ) ); ?>" class="mpp-header__user-name">
							<?php echo esc_html( $current_user->display_name ); ?>
						</a>
						<a href="<?php echo esc_url( function_exists( 'mpp_logout_url' ) ? mpp_logout_url() : wp_logout_url() ); ?>" class="mpp-header__logout">
							<?php esc_html_e( 'Logout', 'platform-theme' ); ?>
						</a>
					</div>
				</div>
			</div>
		<?php else : ?>
			<nav class="mpp-public-nav" aria-label="<?php esc_attr_e( 'Public navigation', 'platform-theme' ); ?>">
				<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'login' ) : home_url( '/login' ) ); ?>" class="mpp-header__link"><?php esc_html_e( 'Login', 'platform-theme' ); ?></a>
				<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'register' ) : home_url( '/register' ) ); ?>" class="mpp-header__link mpp-header__link--cta"><?php esc_html_e( 'Register', 'platform-theme' ); ?></a>
			</nav>
		<?php endif; ?>

		<button class="mpp-nav-toggle" aria-label="<?php esc_attr_e( 'Toggle navigation', 'platform-theme' ); ?>" aria-expanded="false" aria-controls="mpp-main-content">
			<span></span>
		</button>
	</div>
</header>
