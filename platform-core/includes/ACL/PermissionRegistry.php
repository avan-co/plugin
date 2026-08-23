<?php
/**
 * Dynamic permission registry.
 *
 * @package PlatformCore
 */

namespace MPP\ACL;

use MPP\Database\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Class PermissionRegistry
 */
class PermissionRegistry {

	/**
	 * In-memory registered permissions (not yet persisted).
	 *
	 * @var array<string, Permission>
	 */
	private $registered = array();

	/**
	 * Register a single permission.
	 *
	 * @param string $module      Module name.
	 * @param string $resource    Resource name.
	 * @param string $action      Action name.
	 * @param string $description Description.
	 * @return Permission
	 */
	public function register( $module, $resource, $action, $description = '' ) {
		$permission = Permission::from_parts( $module, $resource, $action, $description );
		$this->registered[ $permission->key ] = $permission;

		return $permission;
	}

	/**
	 * Register multiple actions for a resource.
	 *
	 * @param string              $module   Module name.
	 * @param string              $resource Resource name.
	 * @param array<string,string> $actions  Action => description map.
	 */
	public function register_resource( $module, $resource, array $actions ) {
		foreach ( $actions as $action => $description ) {
			$this->register( $module, $resource, $action, $description );
		}
	}

	/**
	 * Register a full module definition.
	 *
	 * @param string $module    Module name.
	 * @param array  $resources Resource => actions map.
	 */
	public function register_module( $module, array $resources ) {
		foreach ( $resources as $resource => $actions ) {
			if ( is_array( $actions ) ) {
				$this->register_resource( $module, $resource, $actions );
			}
		}
	}

	/**
	 * Persist all registered permissions to the database.
	 */
	public function sync_to_database() {
		global $wpdb;

		$table = Schema::table( 'permissions' );

		foreach ( $this->registered as $permission ) {
			$existing = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE permission_key = %s",
					$permission->key
				)
			);

			if ( $existing ) {
				$wpdb->update(
					$table,
					array(
						'module'      => $permission->module,
						'resource'    => $permission->resource,
						'action'      => $permission->action,
						'description' => $permission->description,
					),
					array( 'id' => (int) $existing ),
					array( '%s', '%s', '%s', '%s' ),
					array( '%d' )
				);
				continue;
			}

			$wpdb->insert(
				$table,
				array(
					'module'         => $permission->module,
					'resource'       => $permission->resource,
					'action'         => $permission->action,
					'permission_key' => $permission->key,
					'description'    => $permission->description,
					'created_at'     => current_time( 'mysql', true ),
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s' )
			);
		}
	}

	/**
	 * Get all permissions grouped by module and resource.
	 *
	 * @return array<string, array<string, array<int, array<string, mixed>>>>
	 */
	public function get_grouped() {
		global $wpdb;

		$table = Schema::table( 'permissions' );
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY module, resource, action", ARRAY_A );

		$grouped = array();

		foreach ( $rows as $row ) {
			$module   = $row['module'];
			$resource = $row['resource'];

			if ( ! isset( $grouped[ $module ] ) ) {
				$grouped[ $module ] = array();
			}

			if ( ! isset( $grouped[ $module ][ $resource ] ) ) {
				$grouped[ $module ][ $resource ] = array();
			}

			$grouped[ $module ][ $resource ][] = array(
				'id'          => (int) $row['id'],
				'action'      => $row['action'],
				'key'         => $row['permission_key'],
				'description' => $row['description'],
			);
		}

		return $grouped;
	}

	/**
	 * Find a permission by key.
	 *
	 * @param string $key Permission key.
	 * @return array<string, mixed>|null
	 */
	public function find_by_key( $key ) {
		global $wpdb;

		$table = Schema::table( 'permissions' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE permission_key = %s", $key ),
			ARRAY_A
		);

		return $row ? $row : null;
	}

	/**
	 * Get permission ID by key.
	 *
	 * @param string $key Permission key.
	 * @return int|null
	 */
	public function get_id_by_key( $key ) {
		$row = $this->find_by_key( $key );
		return $row ? (int) $row['id'] : null;
	}
}
