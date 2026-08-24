<?php
/**
 * Header template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

use PlatformTheme\DesignSystem\LanguageSwitcher;

$route          = function_exists( 'mpp_get_current_route' ) ? mpp_get_current_route() : null;
$is_auth_page   = function_exists( 'mpp_is_auth_route' ) && mpp_is_auth_route();
$has_sidebar    = function_exists( 'platform_route_has_sidebar' ) && platform_route_has_sidebar();
$html_lang      = str_replace( '_', '-', determine_locale() );
$html_dir       = LanguageSwitcher::get_text_direction();
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( $html_lang ); ?>" dir="<?php echo esc_attr( $html_dir ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>
		try {
			var storedTheme = localStorage.getItem('mppTheme');
			if (storedTheme === 'dark' || storedTheme === 'light') {
				document.documentElement.setAttribute('data-theme', storedTheme);
			}
		} catch (error) {}
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'mpp-body' ); ?>>
<?php wp_body_open(); ?>

<a class="mpp-skip-link screen-reader-text" href="#mpp-main-content"><?php esc_html_e( 'Skip to content', 'platform-theme' ); ?></a>

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
				<button type="button" class="mpp-btn mpp-btn--ghost mpp-btn--sm mpp-theme-toggle" data-mpp-theme-toggle aria-label="<?php esc_attr_e( 'Toggle color theme', 'platform-theme' ); ?>"><?php esc_html_e( 'Theme', 'platform-theme' ); ?></button>
				<?php LanguageSwitcher::render(); ?>
				<?php platform_render_account_menu(); ?>
			</div>
		<?php else : ?>
			<div class="mpp-header__tools mpp-header__tools--public">
				<button type="button" class="mpp-btn mpp-btn--ghost mpp-btn--sm mpp-theme-toggle" data-mpp-theme-toggle aria-label="<?php esc_attr_e( 'Toggle color theme', 'platform-theme' ); ?>"><?php esc_html_e( 'Theme', 'platform-theme' ); ?></button>
				<?php LanguageSwitcher::render(); ?>
				<?php if ( ! $is_auth_page ) : ?>
					<nav class="mpp-public-nav" aria-label="<?php esc_attr_e( 'Public navigation', 'platform-theme' ); ?>">
						<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'login' ) : home_url( '/login' ) ); ?>" class="mpp-header__link"><?php esc_html_e( 'Login', 'platform-theme' ); ?></a>
						<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'register' ) : home_url( '/register' ) ); ?>" class="mpp-header__link mpp-header__link--cta"><?php esc_html_e( 'Register', 'platform-theme' ); ?></a>
					</nav>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $has_sidebar ) : ?>
		<button type="button" class="mpp-nav-toggle" aria-label="<?php esc_attr_e( 'Toggle navigation', 'platform-theme' ); ?>" aria-expanded="false" aria-controls="mpp-sidebar">
			<span></span>
		</button>
		<?php endif; ?>
	</div>
</header>
