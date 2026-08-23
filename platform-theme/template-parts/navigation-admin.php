<?php
/**
 * Admin panel navigation.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

$current_route = function_exists( 'mpp_get_current_route' ) ? mpp_get_current_route() : null;
$current_slug  = $current_route ? $current_route['slug'] : '';
$nav_items     = function_exists( 'mpp_get_admin_navigation' ) ? mpp_get_admin_navigation() : array();
?>

<nav class="mpp-nav" aria-label="<?php esc_attr_e( 'Admin panel navigation', 'platform-theme' ); ?>">
	<div class="mpp-nav__title"><?php esc_html_e( 'Admin Panel', 'platform-theme' ); ?></div>
	<ul class="mpp-nav__list">
		<?php foreach ( $nav_items as $item ) : ?>
			<li class="mpp-nav__item <?php echo ( ! empty( $item['route'] ) && $current_slug === $item['route'] ) ? 'mpp-nav__item--active' : ''; ?>">
				<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
			</li>
		<?php endforeach; ?>
		<li class="mpp-nav__item <?php echo 'profile' === $current_slug ? 'mpp-nav__item--active' : ''; ?>">
			<a href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'profile' ) : home_url( '/profile' ) ); ?>"><?php esc_html_e( 'Profile', 'platform-theme' ); ?></a>
		</li>
	</ul>
</nav>
