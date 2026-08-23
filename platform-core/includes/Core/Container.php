<?php
/**
 * Simple service container.
 *
 * @package PlatformCore
 */

namespace MPP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Container
 */
class Container {

	/**
	 * Registered factories.
	 *
	 * @var array<string, callable>
	 */
	private $factories = array();

	/**
	 * Resolved instances.
	 *
	 * @var array<string, mixed>
	 */
	private $instances = array();

	/**
	 * Register a service factory.
	 *
	 * @param string   $id      Service identifier.
	 * @param callable $factory Factory callback.
	 */
	public function set( $id, callable $factory ) {
		$this->factories[ $id ] = $factory;
		unset( $this->instances[ $id ] );
	}

	/**
	 * Resolve a service.
	 *
	 * @param string $id Service identifier.
	 * @return mixed
	 */
	public function get( $id ) {
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		if ( ! isset( $this->factories[ $id ] ) ) {
			throw new \InvalidArgumentException( sprintf( 'Service "%s" is not registered.', $id ) );
		}

		$this->instances[ $id ] = call_user_func( $this->factories[ $id ], $this );

		return $this->instances[ $id ];
	}
}
