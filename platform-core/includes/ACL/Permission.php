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
	 * Build a permission key.
	 *
	 * @param string $module   Module name.
	 * @param string $resource Resource name.
	 * @param string $action   Action name.
	 * @return string
	 */
	public static function build_key( $module, $resource, $action ) {
		return sanitize_key( $module ) . '.' . sanitize_key( $resource ) . '.' . sanitize_key( $action );
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
		$permission->module      = sanitize_key( $module );
		$permission->resource    = sanitize_key( $resource );
		$permission->action      = sanitize_key( $action );
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
		$parts = explode( '.', $key );

		if ( count( $parts ) < 3 ) {
			return null;
		}

		$action   = array_pop( $parts );
		$resource = array_pop( $parts );
		$module   = implode( '.', $parts );

		return self::from_parts( $module, $resource, $action );
	}
}
