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
			self::render_admin_item( $item, $current_slug );
		}

		echo '</ul>';
		echo '</div>';

		echo '<div class="mpp-nav__group">';
		echo '<div class="mpp-nav__section">' . esc_html__( 'Account', 'platform-theme' ) . '</div>';
		echo '<ul class="mpp-nav__list">';
		printf(
			'<li class="mpp-nav__item%s"><a href="%s"%s><span class="mpp-nav__label">%s</span><span class="mpp-nav__desc">%s</span></a></li>',
			'profile' === $current_slug ? ' mpp-nav__item--active' : '',
			esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'profile' ) : home_url( '/profile' ) ),
			'profile' === $current_slug ? ' aria-current="page"' : '',
			esc_html__( 'Profile', 'platform-theme' ),
			esc_html__( 'Your account details', 'platform-theme' )
		);
		echo '</ul>';
		echo '</div>';

		echo '</nav>';
	}

	/**
	 * Render one admin navigation item, including nested children.
	 *
	 * @param array<string, mixed> $item         Navigation item.
	 * @param string               $current_slug Current route slug.
	 */
	private static function render_admin_item( array $item, $current_slug ) {
		if ( ! empty( $item['children'] ) ) {
			$group_active = false;

			foreach ( $item['children'] as $child ) {
				if ( self::is_admin_item_active( $child, $current_slug ) ) {
					$group_active = true;
					break;
				}
			}

			echo '<li class="mpp-nav__item mpp-nav__item--group' . ( $group_active ? ' mpp-nav__item--group-active' : '' ) . '">';
			echo '<span class="mpp-nav__group-label">' . esc_html( $item['label'] ) . '</span>';
			echo '<ul class="mpp-nav__sublist">';

			foreach ( $item['children'] as $child ) {
				self::render_admin_link( $child, $current_slug, true );
			}

			echo '</ul>';
			echo '</li>';
			return;
		}

		self::render_admin_link( $item, $current_slug, false );
	}

	/**
	 * Render a single admin navigation link.
	 *
	 * @param array<string, mixed> $item         Navigation item.
	 * @param string               $current_slug Current route slug.
	 * @param bool                 $nested       Whether item is nested.
	 */
	private static function render_admin_link( array $item, $current_slug, $nested ) {
		$active = self::is_admin_item_active( $item, $current_slug );
		$classes = array( 'mpp-nav__item' );

		if ( $nested ) {
			$classes[] = 'mpp-nav__item--child';
		}

		if ( $active ) {
			$classes[] = 'mpp-nav__item--active';
		}

		printf(
			'<li class="%s"><a href="%s"%s><span class="mpp-nav__label">%s</span>%s</a></li>',
			esc_attr( implode( ' ', $classes ) ),
			esc_url( $item['url'] ),
			$active ? ' aria-current="page"' : '',
			esc_html( $item['label'] ),
			! empty( $item['description'] ) ? '<span class="mpp-nav__desc">' . esc_html( $item['description'] ) . '</span>' : ''
		);
	}

	/**
	 * Determine whether an admin nav item is active.
	 *
	 * @param array<string, mixed> $item         Navigation item.
	 * @param string               $current_slug Current route slug.
	 * @return bool
	 */
	private static function is_admin_item_active( array $item, $current_slug ) {
		$route = isset( $item['route'] ) ? $item['route'] : '';

		if ( ! $route || $current_slug !== $route ) {
			return false;
		}

		$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : '';

		if ( ! empty( $item['query_args'] ) && is_array( $item['query_args'] ) ) {
			foreach ( $item['query_args'] as $key => $value ) {
				$current = isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';

				if ( (string) $current !== (string) $value ) {
					return false;
				}
			}

			return true;
		}

		if ( 'audit' === $view && 'app/admin/acl' === $route ) {
			return false;
		}

		return true;
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
			'<li class="mpp-nav__item%s"><a href="%s"%s><span class="mpp-nav__label">%s</span>%s</a></li>',
			$active ? ' mpp-nav__item--active' : '',
			esc_url( $item['url'] ),
			$active ? ' aria-current="page"' : '',
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
