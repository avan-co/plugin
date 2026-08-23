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
	 * Persist registered permissions only when the registry has changed.
	 */
	public function sync_if_needed() {
		$hash = $this->get_registry_hash();
		$stored = get_option( 'mpp_permissions_hash', '' );

		if ( $hash === $stored ) {
			return;
		}

		$this->sync_to_database();
		update_option( 'mpp_permissions_hash', $hash, false );
	}

	/**
	 * Compute a hash of registered permission keys.
	 *
	 * @return string
	 */
	public function get_registry_hash() {
		$keys = array_keys( $this->registered );
		sort( $keys );

		return md5( implode( '|', $keys ) );
	}

	/**
	 * Get in-memory registered permissions.
	 *
	 * @return array<string, Permission>
	 */
	public function get_registered() {
		return $this->registered;
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

		$this->prune_orphaned_permissions();
	}

	/**
	 * Remove all permissions for a module from memory and database.
	 *
	 * @param string $module Module slug.
	 * @return int Number of permissions removed.
	 */
	public function unregister_module( $module ) {
		$module = sanitize_key( $module );

		if ( 'core' === $module ) {
			return 0;
		}

		foreach ( array_keys( $this->registered ) as $key ) {
			if ( $this->registered[ $key ]->module === $module ) {
				unset( $this->registered[ $key ] );
			}
		}

		return $this->prune_orphaned_permissions();
	}

	/**
	 * Remove permissions for a module that are no longer registered.
	 *
	 * @param string $module Module slug.
	 * @return int Number of permissions removed.
	 */
	public function remove_module_permissions( $module ) {
		$module = sanitize_key( $module );

		if ( 'core' === $module ) {
			return 0;
		}

		$keys_to_keep = array();

		foreach ( $this->registered as $permission ) {
			if ( $permission->module === $module ) {
				$keys_to_keep[] = $permission->key;
			}
		}

		return $this->delete_module_permissions_not_in( $module, $keys_to_keep );
	}

	/**
	 * Remove database permissions that are not in the in-memory registry.
	 *
	 * Core permissions are never auto-removed.
	 *
	 * @return int Number of permissions removed.
	 */
	public function prune_orphaned_permissions() {
		global $wpdb;

		$table         = Schema::table( 'permissions' );
		$allowed_keys  = array_keys( $this->registered );
		$rows          = $wpdb->get_results( "SELECT id, permission_key, module FROM {$table}", ARRAY_A );
		$removed       = 0;

		if ( empty( $rows ) ) {
			return 0;
		}

		foreach ( $rows as $row ) {
			if ( 'core' === $row['module'] ) {
				continue;
			}

			if ( in_array( $row['permission_key'], $allowed_keys, true ) ) {
				continue;
			}

			$this->delete_permission_by_id( (int) $row['id'] );
			$removed++;
		}

		return $removed;
	}

	/**
	 * Delete permissions for a module except allowed keys.
	 *
	 * @param string              $module Module slug.
	 * @param array<int, string>  $keys   Keys to keep.
	 * @return int
	 */
	private function delete_module_permissions_not_in( $module, array $keys ) {
		global $wpdb;

		$table = Schema::table( 'permissions' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare( "SELECT id, permission_key FROM {$table} WHERE module = %s", $module ),
			ARRAY_A
		);
		$removed = 0;

		foreach ( $rows as $row ) {
			if ( in_array( $row['permission_key'], $keys, true ) ) {
				continue;
			}

			$this->delete_permission_by_id( (int) $row['id'] );
			$removed++;
		}

		foreach ( array_keys( $this->registered ) as $registered_key ) {
			$permission = $this->registered[ $registered_key ];
			if ( $permission->module === $module && ! in_array( $registered_key, $keys, true ) ) {
				unset( $this->registered[ $registered_key ] );
			}
		}

		return $removed;
	}

	/**
	 * Delete a permission and its role assignments.
	 *
	 * @param int $permission_id Permission ID.
	 */
	private function delete_permission_by_id( $permission_id ) {
		global $wpdb;

		$permission_id = (int) $permission_id;

		$wpdb->delete(
			Schema::table( 'role_permissions' ),
			array( 'permission_id' => $permission_id ),
			array( '%d' )
		);

		$wpdb->delete(
			Schema::table( 'permissions' ),
			array( 'id' => $permission_id ),
			array( '%d' )
		);
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

	/**
	 * Find a permission by database ID.
	 *
	 * @param int $id Permission ID.
	 * @return array<string, mixed>|null
	 */
	public function find_by_id( $id ) {
		global $wpdb;

		$table = Schema::table( 'permissions' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id ),
			ARRAY_A
		);

		return $row ? $row : null;
	}
}
