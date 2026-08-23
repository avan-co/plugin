<?php
/**
 * Admin panel navigation.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;
?>

<nav class="mpp-nav" aria-label="<?php esc_attr_e( 'Admin panel navigation', 'platform-theme' ); ?>">
	<div class="mpp-nav__title"><?php esc_html_e( 'Admin Panel', 'platform-theme' ); ?></div>
	<ul class="mpp-nav__list">
		<li class="mpp-nav__item mpp-nav__item--active">
			<a href="<?php echo esc_url( home_url( '/app/admin' ) ); ?>">
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
		<?php if ( function_exists( 'mpp_can' ) && mpp_can( 'core.acl.manage' ) ) : ?>
			<li class="mpp-nav__item mpp-nav__item--section">
				<span><?php esc_html_e( 'System', 'platform-theme' ); ?></span>
			</li>
			<li class="mpp-nav__item">
				<span class="mpp-nav__placeholder"><?php esc_html_e( 'ACL Management (coming soon)', 'platform-theme' ); ?></span>
			</li>
		<?php endif; ?>
	</ul>
</nav>
