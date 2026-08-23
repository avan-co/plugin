<?php
/**
 * Admin panel navigation.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

$current_route = function_exists( 'mpp_get_current_route' ) ? mpp_get_current_route() : null;
$current_slug  = $current_route ? $current_route['slug'] : '';

$nav_items = array(
	'app/admin'           => __( 'Dashboard', 'platform-theme' ),
	'app/admin/users'     => __( 'Users', 'platform-theme' ),
	'app/admin/roles'     => __( 'Roles', 'platform-theme' ),
	'app/admin/permissions' => __( 'Permissions', 'platform-theme' ),
	'app/admin/modules'   => __( 'Modules', 'platform-theme' ),
	'app/admin/acl'       => __( 'ACL Overview', 'platform-theme' ),
	'app/admin/settings'  => __( 'Settings', 'platform-theme' ),
);
?>

<nav class="mpp-nav" aria-label="<?php esc_attr_e( 'Admin panel navigation', 'platform-theme' ); ?>">
	<div class="mpp-nav__title"><?php esc_html_e( 'Admin Panel', 'platform-theme' ); ?></div>
	<ul class="mpp-nav__list">
		<?php if ( function_exists( 'mpp_can_manage_acl' ) && mpp_can_manage_acl() ) : ?>
			<?php foreach ( $nav_items as $slug => $label ) : ?>
				<li class="mpp-nav__item <?php echo $current_slug === $slug ? 'mpp-nav__item--active' : ''; ?>">
					<a href="<?php echo esc_url( home_url( '/' . $slug ) ); ?>"><?php echo esc_html( $label ); ?></a>
				</li>
			<?php endforeach; ?>
		<?php else : ?>
			<li class="mpp-nav__item mpp-nav__item--active">
				<a href="<?php echo esc_url( home_url( '/app/admin' ) ); ?>"><?php esc_html_e( 'Dashboard', 'platform-theme' ); ?></a>
			</li>
		<?php endif; ?>
		<li class="mpp-nav__item">
			<a href="<?php echo esc_url( home_url( '/profile' ) ); ?>"><?php esc_html_e( 'Profile', 'platform-theme' ); ?></a>
		</li>
	</ul>
</nav>
