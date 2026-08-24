<?php
/**
 * Platform settings storage and validation.
 *
 * @package PlatformCore
 */

namespace MPP\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Class PlatformSettings
 */
class PlatformSettings {

	const OPTION_PREFIX = 'mpp_setting_';

	/**
	 * Register WordPress hooks for stored settings.
	 */
	public function register_hooks() {
		add_filter( 'pre_option_timezone_string', array( $this, 'filter_timezone_string' ) );
	}

	/**
	 * Override WordPress timezone when a platform timezone is saved.
	 *
	 * @param mixed $value Current pre-option value.
	 * @return mixed
	 */
	public function filter_timezone_string( $value ) {
		$timezone = get_option( self::OPTION_PREFIX . 'timezone', '' );

		if ( is_string( $timezone ) && '' !== $timezone ) {
			return $timezone;
		}

		return $value;
	}

	/**
	 * Get a setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		$value = get_option( self::OPTION_PREFIX . sanitize_key( $key ), null );

		if ( null === $value ) {
			return $this->get_default( $key, $default );
		}

		return $value;
	}

	/**
	 * Set a setting value.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool
	 */
	public function set( $key, $value ) {
		return update_option( self::OPTION_PREFIX . sanitize_key( $key ), $value, false );
	}

	/**
	 * Get all settings grouped for admin UI.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function all() {
		return array(
			'general' => array(
				'default_dashboard' => $this->get( 'default_dashboard', 'app/user' ),
			),
			'appearance' => array(
				'platform_name' => $this->get( 'platform_name', get_bloginfo( 'name' ) ),
				'logo_mark'     => $this->get( 'logo_mark', 'P' ),
				'accent_color'  => $this->get( 'accent_color', '' ),
			),
			'registration' => array(
				'enabled'               => $this->is_registration_enabled(),
				'default_platform_role' => $this->get( 'default_platform_role', 'platform_user' ),
			),
			'security' => array(
				'session_remember_days' => (int) $this->get( 'session_remember_days', 14 ),
			),
			'localization' => array(
				'date_format' => $this->get( 'date_format', get_option( 'date_format' ) ),
				'timezone'    => $this->get_timezone_override(),
			),
		);
	}

	/**
	 * Whether public registration is enabled.
	 *
	 * @return bool
	 */
	public function is_registration_enabled() {
		$stored = get_option( self::OPTION_PREFIX . 'registration_enabled', null );

		if ( null !== $stored ) {
			return (bool) $stored;
		}

		return (bool) apply_filters( 'mpp_registration_enabled', true );
	}

	/**
	 * Get the saved timezone override, if any.
	 *
	 * @return string
	 */
	public function get_timezone_override() {
		$stored = get_option( self::OPTION_PREFIX . 'timezone', null );

		return null === $stored ? '' : (string) $stored;
	}

	/**
	 * Save settings from admin form payload.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, string> Field errors.
	 */
	public function save( array $input ) {
		$errors = array();

		if ( isset( $input['platform_name'] ) ) {
			$name = sanitize_text_field( $input['platform_name'] );
			if ( '' === $name ) {
				$errors['platform_name'] = __( 'Platform name cannot be empty.', 'platform-core' );
			} else {
				$this->set( 'platform_name', $name );
			}
		}

		if ( isset( $input['logo_mark'] ) ) {
			$mark = sanitize_text_field( $input['logo_mark'] );
			$mark = '' !== $mark ? mb_substr( $mark, 0, 1 ) : 'P';
			$this->set( 'logo_mark', $mark );
		}

		if ( isset( $input['accent_color'] ) ) {
			$color = sanitize_text_field( $input['accent_color'] );
			if ( '' !== $color && ! preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color ) ) {
				$errors['accent_color'] = __( 'Accent color must be a valid hex value.', 'platform-core' );
			} else {
				$this->set( 'accent_color', $color );
			}
		}

		if ( isset( $input['default_dashboard'] ) ) {
			$route   = sanitize_text_field( $input['default_dashboard'] );
			$allowed = array( 'app/user', 'app/manager', 'app/admin', 'app' );
			if ( ! in_array( $route, $allowed, true ) ) {
				$errors['default_dashboard'] = __( 'Invalid default dashboard route.', 'platform-core' );
			} else {
				$this->set( 'default_dashboard', $route );
			}
		}

		if ( isset( $input['registration_enabled'] ) ) {
			$this->set( 'registration_enabled', '1' === (string) $input['registration_enabled'] );
		} else {
			$this->set( 'registration_enabled', false );
		}

		if ( isset( $input['default_platform_role'] ) ) {
			$role = sanitize_key( $input['default_platform_role'] );
			$this->set( 'default_platform_role', $role );
		}

		if ( isset( $input['session_remember_days'] ) ) {
			$days = max( 1, min( 365, (int) $input['session_remember_days'] ) );
			$this->set( 'session_remember_days', $days );
		}

		if ( isset( $input['date_format'] ) ) {
			$this->set( 'date_format', sanitize_text_field( $input['date_format'] ) );
		}

		if ( isset( $input['timezone'] ) ) {
			$timezone = sanitize_text_field( $input['timezone'] );
			if ( '' === $timezone ) {
				delete_option( self::OPTION_PREFIX . 'timezone' );
			} elseif ( ! in_array( $timezone, timezone_identifiers_list(), true ) ) {
				$errors['timezone'] = __( 'Invalid timezone selected.', 'platform-core' );
			} else {
				$this->set( 'timezone', $timezone );
			}
		}

		return $errors;
	}

	/**
	 * Default value for a setting key.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback default.
	 * @return mixed
	 */
	private function get_default( $key, $default ) {
		$defaults = array(
			'platform_name'         => get_bloginfo( 'name' ),
			'logo_mark'             => 'P',
			'accent_color'          => '',
			'default_dashboard'     => 'app/user',
			'default_platform_role' => 'platform_user',
			'session_remember_days' => 14,
			'date_format'           => get_option( 'date_format' ),
			'timezone'              => wp_timezone_string(),
		);

		return $defaults[ $key ] ?? $default;
	}
}
