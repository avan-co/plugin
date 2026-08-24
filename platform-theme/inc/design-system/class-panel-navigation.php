<?php
/**
 * Sidebar navigation renderer.
 *
 * @package PlatformTheme
 */

namespace PlatformTheme\DesignSystem;

defined( 'ABSPATH' ) || exit;

/**
 * Renders grouped panel navigation from the platform registry.
 */
final class PanelNavigation {

	/**
	 * Panel labels for sidebar headings.
	 *
	 * @var array<string, string>
	 */
	private static $panel_labels = array(
		'user'    => 'User Panel',
		'manager' => 'Manager Panel',
		'admin'   => 'Admin Panel',
	);

	/**
	 * Section labels for grouped navigation.
	 *
	 * @var array<string, string>
	 */
	private static $section_labels = array(
		'main'    => 'Main',
		'account' => 'Account',
		'modules' => 'Modules',
		'system'  => 'System',
	);

	/**
	 * Render navigation for a panel.
	 *
	 * @param string $panel Panel slug.
	 */
	public static function render( $panel ) {
		$panel = sanitize_key( $panel );

		if ( 'admin' === $panel ) {
			self::render_admin();
			return;
		}

		if ( ! function_exists( 'mpp_get_panel_navigation' ) ) {
			return;
		}

		$items = mpp_get_panel_navigation( $panel );

		if ( empty( $items ) ) {
			return;
		}

		$groups = self::group_items( $items );

		echo '<nav class="mpp-nav" aria-label="' . esc_attr( self::get_panel_label( $panel ) ) . '">';
		echo '<div class="mpp-nav__title">' . esc_html( self::get_panel_label( $panel ) ) . '</div>';

		foreach ( $groups as $section => $section_items ) {
			if ( empty( $section_items ) ) {
				continue;
			}

			echo '<div class="mpp-nav__group">';
			echo '<div class="mpp-nav__section">' . esc_html( self::get_section_label( $section ) ) . '</div>';
			echo '<ul class="mpp-nav__list">';

			foreach ( $section_items as $item ) {
				self::render_item( $item );
			}

			echo '</ul>';
			echo '</div>';
		}

		echo '</nav>';
	}

	/**
	 * Render admin navigation.
	 */
	private static function render_admin() {
		$current_route = function_exists( 'mpp_get_current_route' ) ? mpp_get_current_route() : null;
		$current_slug  = $current_route ? $current_route['slug'] : '';
		$nav_items     = function_exists( 'mpp_get_admin_navigation' ) ? mpp_get_admin_navigation() : array();

		echo '<nav class="mpp-nav" aria-label="' . esc_attr__( 'Admin panel navigation', 'platform-theme' ) . '">';
		echo '<div class="mpp-nav__title">' . esc_html__( 'Admin Panel', 'platform-theme' ) . '</div>';

		echo '<div class="mpp-nav__group">';
		echo '<div class="mpp-nav__section">' . esc_html__( 'Access Control', 'platform-theme' ) . '</div>';
		echo '<ul class="mpp-nav__list">';

		foreach ( $nav_items as $item ) {
			$route  = isset( $item['route'] ) ? $item['route'] : '';
			$active = $route && $current_slug === $route;
			printf(
				'<li class="mpp-nav__item%s"><a href="%s"><span class="mpp-nav__label">%s</span>%s</a></li>',
				$active ? ' mpp-nav__item--active' : '',
				esc_url( $item['url'] ),
				esc_html( $item['label'] ),
				! empty( $item['description'] ) ? '<span class="mpp-nav__desc">' . esc_html( $item['description'] ) . '</span>' : ''
			);
		}

		echo '</ul>';
		echo '</div>';

		echo '<div class="mpp-nav__group">';
		echo '<div class="mpp-nav__section">' . esc_html__( 'Account', 'platform-theme' ) . '</div>';
		echo '<ul class="mpp-nav__list">';
		printf(
			'<li class="mpp-nav__item%s"><a href="%s"><span class="mpp-nav__label">%s</span><span class="mpp-nav__desc">%s</span></a></li>',
			'profile' === $current_slug ? ' mpp-nav__item--active' : '',
			esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'profile' ) : home_url( '/profile' ) ),
			esc_html__( 'Profile', 'platform-theme' ),
			esc_html__( 'Your account details', 'platform-theme' )
		);
		echo '</ul>';
		echo '</div>';

		echo '</nav>';
	}

	/**
	 * Render one navigation item.
	 *
	 * @param array<string, string> $item Navigation item.
	 */
	private static function render_item( array $item ) {
		$route  = isset( $item['route'] ) ? $item['route'] : '';
		$active = $route && function_exists( 'platform_is_route' ) && platform_is_route( $route );

		printf(
			'<li class="mpp-nav__item%s"><a href="%s"><span class="mpp-nav__label">%s</span>%s</a></li>',
			$active ? ' mpp-nav__item--active' : '',
			esc_url( $item['url'] ),
			esc_html( $item['label'] ),
			! empty( $item['description'] ) ? '<span class="mpp-nav__desc">' . esc_html( $item['description'] ) . '</span>' : ''
		);
	}

	/**
	 * Group navigation items by section key.
	 *
	 * @param array<int, array<string, string>> $items Navigation items.
	 * @return array<string, array<int, array<string, string>>>
	 */
	private static function group_items( array $items ) {
		$groups = array(
			'main'    => array(),
			'account' => array(),
			'modules' => array(),
			'system'  => array(),
		);

		foreach ( $items as $item ) {
			$section = ! empty( $item['section'] ) ? sanitize_key( $item['section'] ) : 'main';

			if ( ! isset( $groups[ $section ] ) ) {
				$groups[ $section ] = array();
			}

			$groups[ $section ][] = $item;
		}

		return array_filter( $groups );
	}

	/**
	 * Get translated panel label.
	 *
	 * @param string $panel Panel slug.
	 * @return string
	 */
	private static function get_panel_label( $panel ) {
		$labels = array(
			'user'    => __( 'User Panel', 'platform-theme' ),
			'manager' => __( 'Manager Panel', 'platform-theme' ),
			'admin'   => __( 'Admin Panel', 'platform-theme' ),
		);

		return $labels[ $panel ] ?? ucfirst( $panel );
	}

	/**
	 * Get translated section label.
	 *
	 * @param string $section Section slug.
	 * @return string
	 */
	private static function get_section_label( $section ) {
		$labels = array(
			'main'    => __( 'Main', 'platform-theme' ),
			'account' => __( 'Account', 'platform-theme' ),
			'modules' => __( 'Modules', 'platform-theme' ),
			'system'  => __( 'System', 'platform-theme' ),
		);

		return $labels[ $section ] ?? ucfirst( $section );
	}
}
