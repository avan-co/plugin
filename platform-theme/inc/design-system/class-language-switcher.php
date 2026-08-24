<?php
/**
 * Panel language switcher.
 *
 * @package PlatformTheme
 */

namespace PlatformTheme\DesignSystem;

defined( 'ABSPATH' ) || exit;

/**
 * Handles locale preference and renders the panel language switcher.
 */
final class LanguageSwitcher {

	const META_KEY  = 'mpp_locale';
	const COOKIE    = 'mpp_locale';
	const QUERY_VAR = 'mpp_lang';

	/**
	 * Register locale hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'handle_switch' ), 1 );
		add_filter( 'locale', array( __CLASS__, 'filter_locale' ) );
	}

	/**
	 * Supported locales for the platform UI.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function get_locales() {
		$locales = array(
			'en_US' => array(
				'label'  => __( 'English', 'platform-theme' ),
				'native' => 'English',
				'dir'    => 'ltr',
			),
			'fa_IR' => array(
				'label'  => __( 'Persian', 'platform-theme' ),
				'native' => 'فارسی',
				'dir'    => 'rtl',
			),
		);

		/**
		 * Filter available platform panel locales.
		 *
		 * @param array<string, array<string, string>> $locales Locale map.
		 */
		return apply_filters( 'platform_available_locales', $locales );
	}

	/**
	 * Resolve the active locale for the current visitor.
	 *
	 * @return string|null
	 */
	public static function get_active_locale() {
		$locales = self::get_locales();

		if ( is_user_logged_in() ) {
			$stored = get_user_meta( get_current_user_id(), self::META_KEY, true );
			if ( is_string( $stored ) && isset( $locales[ $stored ] ) ) {
				return $stored;
			}
		}

		if ( isset( $_COOKIE[ self::COOKIE ] ) ) {
			$cookie = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) );
			if ( isset( $locales[ $cookie ] ) ) {
				return $cookie;
			}
		}

		return null;
	}

	/**
	 * Apply stored locale preference.
	 *
	 * @param string $locale WordPress locale.
	 * @return string
	 */
	public static function filter_locale( $locale ) {
		$preferred = self::get_active_locale();

		return $preferred ? $preferred : $locale;
	}

	/**
	 * Persist locale when the switcher is used.
	 */
	public static function handle_switch() {
		if ( empty( $_GET[ self::QUERY_VAR ] ) ) {
			return;
		}

		$requested = sanitize_text_field( wp_unslash( $_GET[ self::QUERY_VAR ] ) );
		$locales   = self::get_locales();

		if ( ! isset( $locales[ $requested ] ) ) {
			return;
		}

		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), self::META_KEY, $requested );
		}

		if ( ! headers_sent() ) {
			setcookie(
				self::COOKIE,
				$requested,
				time() + YEAR_IN_SECONDS,
				COOKIEPATH,
				COOKIE_DOMAIN,
				is_ssl(),
				true
			);
		}

		$redirect = remove_query_arg( self::QUERY_VAR );
		wp_safe_redirect( $redirect ? $redirect : home_url( '/' ) );
		exit;
	}

	/**
	 * Build a switch URL for a locale.
	 *
	 * @param string $locale Locale code.
	 * @return string
	 */
	public static function get_switch_url( $locale ) {
		$target = function_exists( 'mpp_get_current_route' ) ? mpp_get_current_route() : null;

		if ( $target && ! empty( $target['slug'] ) && function_exists( 'mpp_route_url' ) ) {
			$url = mpp_route_url( $target['slug'] );
		} else {
			$url = home_url( add_query_arg( array() ) );
		}

		return add_query_arg( self::QUERY_VAR, $locale, $url );
	}

	/**
	 * Whether the switcher should render on the current view.
	 *
	 * @return bool
	 */
	public static function should_render() {
		if ( ! function_exists( 'mpp_get_current_route' ) ) {
			return is_user_logged_in();
		}

		$route = mpp_get_current_route();

		if ( ! $route || empty( $route['slug'] ) ) {
			return is_user_logged_in();
		}

		return 0 === strpos( $route['slug'], 'app/' ) || in_array( $route['slug'], array( 'profile', 'settings', 'app' ), true );
	}

	/**
	 * Render language switcher markup.
	 */
	public static function render() {
		if ( ! self::should_render() ) {
			return;
		}

		$locales = self::get_locales();
		$active  = determine_locale();

		echo '<div class="mpp-lang-switcher">';
		echo '<label class="screen-reader-text" for="mpp-lang-select">' . esc_html__( 'Language', 'platform-theme' ) . '</label>';
		echo '<select id="mpp-lang-select" class="mpp-lang-switcher__select" onchange="if (this.value) { window.location.href = this.value; }">';

		foreach ( $locales as $code => $meta ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_url( self::get_switch_url( $code ) ),
				selected( $active, $code, false ),
				esc_html( $meta['native'] )
			);
		}

		echo '</select>';
		echo '</div>';
	}
}
