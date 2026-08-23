<?php
/**
 * Manager panel navigation.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;
?>

<nav class="mpp-nav" aria-label="<?php esc_attr_e( 'Manager panel navigation', 'platform-theme' ); ?>">
	<div class="mpp-nav__title"><?php esc_html_e( 'Manager Panel', 'platform-theme' ); ?></div>
	<ul class="mpp-nav__list">
		<li class="mpp-nav__item mpp-nav__item--active">
			<a href="<?php echo esc_url( home_url( '/app/manager' ) ); ?>">
				<?php esc_html_e( 'Dashboard', 'platform-theme' ); ?>
			</a>
		</li>
		<li class="mpp-nav__item">
			<a href="<?php echo esc_url( home_url( '/profile' ) ); ?>">
				<?php esc_html_e( 'Profile', 'platform-theme' ); ?>
			</a>
		</li>
		<li class="mpp-nav__item">
			<a href="<?php echo esc_url( home_url( '/settings' ) ); ?>">
				<?php esc_html_e( 'Settings', 'platform-theme' ); ?>
			</a>
		</li>
	</ul>
</nav>
