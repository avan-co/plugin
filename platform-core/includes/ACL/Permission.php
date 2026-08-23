<?php
/**
 * Permission value object.
 *
 * @package PlatformCore
 */

namespace MPP\ACL;

defined( 'ABSPATH' ) || exit;

/**
 * Class Permission
 */
class Permission {

	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $module;

	/**
	 * Resource name.
	 *
	 * @var string
	 */
	public $resource;

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action;

	/**
	 * Permission key (module.resource.action).
	 *
	 * @var string
	 */
	public $key;

	/**
	 * Human-readable description.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Database ID.
	 *
	 * @var int|null
	 */
	public $id;

	/**
	 * Normalize a permission key segment while preserving dots.
	 *
	 * @param string $segment Segment value.
	 * @return string
	 */
	public static function normalize_segment( $segment ) {
		$segment = strtolower( (string) $segment );
		$segment = preg_replace( '/[^a-z0-9._-]+/', '', $segment );

		return trim( $segment, '.-' );
	}

	/**
	 * Validate a full permission key.
	 *
	 * @param string $key Permission key.
	 * @return bool
	 */
	public static function is_valid_key( $key ) {
		return (bool) preg_match( '/^[a-z0-9]+(?:[._-][a-z0-9]+)*\.[a-z0-9]+(?:[._-][a-z0-9]+)*\.[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $key );
	}

	/**
	 * Build a permission key.
	 *
	 * @param string $module   Module name.
	 * @param string $resource Resource name.
	 * @param string $action   Action name.
	 * @return string
	 */
	public static function build_key( $module, $resource, $action ) {
		return self::normalize_segment( $module ) . '.' . self::normalize_segment( $resource ) . '.' . self::normalize_segment( $action );
	}

	/**
	 * Create from parts.
	 *
	 * @param string      $module      Module name.
	 * @param string      $resource    Resource name.
	 * @param string      $action      Action name.
	 * @param string      $description Description.
	 * @param int|null    $id          Database ID.
	 * @return Permission
	 */
	public static function from_parts( $module, $resource, $action, $description = '', $id = null ) {
		$permission              = new self();
		$permission->module      = self::normalize_segment( $module );
		$permission->resource    = self::normalize_segment( $resource );
		$permission->action      = self::normalize_segment( $action );
		$permission->key         = self::build_key( $module, $resource, $action );
		$permission->description = $description;
		$permission->id          = $id;

		return $permission;
	}

	/**
	 * Create from a permission key string.
	 *
	 * @param string $key Permission key.
	 * @return Permission|null
	 */
	public static function from_key( $key ) {
		if ( ! self::is_valid_key( $key ) ) {
			return null;
		}

		$parts = explode( '.', $key );

		$action   = array_pop( $parts );
		$resource = array_pop( $parts );
		$module   = implode( '.', $parts );

		return self::from_parts( $module, $resource, $action );
	}
}
