<?php
/**
 * PSR-4 style autoloader for the plugin.
 *
 * @package PlatformCore
 */

namespace MPP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Autoloader
 */
class Autoloader {

	/**
	 * Base directory for class files.
	 *
	 * @var string
	 */
	private static $base_dir = '';

	/**
	 * Register the autoloader.
	 *
	 * @param string $base_dir Plugin includes directory.
	 */
	public static function register( $base_dir ) {
		self::$base_dir = rtrim( $base_dir, '/\\' ) . DIRECTORY_SEPARATOR;
		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	/**
	 * Load a class file.
	 *
	 * @param string $class Fully qualified class name.
	 */
	public static function load( $class ) {
		$prefix = 'MPP\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$file     = self::$base_dir . str_replace( '\\', DIRECTORY_SEPARATOR, $relative ) . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
