<?php
/**
 * Role management.
 *
 * @package PlatformCore
 */

namespace MPP\ACL;

use MPP\Database\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Class RoleManager
 */
class RoleManager {

	/**
	 * Create a role.
	 *
	 * @param string $slug        Role slug.
	 * @param string $name        Display name.
	 * @param string $description Description.
	 * @param bool   $is_system   Whether this is a system role.
	 * @return int|false Role ID or false on failure.
	 */
	public function create( $slug, $name, $description = '', $is_system = false ) {
		global $wpdb;

		$table = Schema::table( 'roles' );
		$now   = current_time( 'mysql', true );

		$result = $wpdb->insert(
			$table,
			array(
				'slug'        => sanitize_key( $slug ),
				'name'        => sanitize_text_field( $name ),
				'description' => sanitize_textarea_field( $description ),
				'status'      => 'active',
				'is_system'   => $is_system ? 1 : 0,
				'created_at'  => $now,
				'updated_at'  => $now,
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Update a role.
	 *
	 * @param int    $role_id     Role ID.
	 * @param array  $data        Data to update.
	 * @return bool
	 */
	public function update( $role_id, array $data ) {
		global $wpdb;

		$allowed = array( 'name', 'description', 'status' );
		$update  = array();
		$formats = array();

		foreach ( $allowed as $field ) {
			if ( isset( $data[ $field ] ) ) {
				if ( 'description' === $field ) {
					$update[ $field ] = sanitize_textarea_field( $data[ $field ] );
				} elseif ( 'status' === $field ) {
					$status = sanitize_key( $data[ $field ] );
					$update[ $field ] = in_array( $status, array( 'active', 'inactive' ), true ) ? $status : 'active';
				} else {
					$update[ $field ] = sanitize_text_field( $data[ $field ] );
				}
				$formats[] = '%s';
			}
		}

		if ( empty( $update ) ) {
			return false;
		}

		$update['updated_at'] = current_time( 'mysql', true );
		$formats[]            = '%s';

		$table = Schema::table( 'roles' );

		return (bool) $wpdb->update(
			$table,
			$update,
			array( 'id' => (int) $role_id ),
			$formats,
			array( '%d' )
		);
	}

	/**
	 * Delete a role.
	 *
	 * @param int $role_id Role ID.
	 * @return bool
	 */
	public function delete( $role_id ) {
		global $wpdb;

		$role = $this->find( $role_id );

		if ( ! $role || ! empty( $role['is_system'] ) ) {
			return false;
		}

		$wpdb->delete( Schema::table( 'role_permissions' ), array( 'role_id' => (int) $role_id ), array( '%d' ) );
		$wpdb->delete( Schema::table( 'user_roles' ), array( 'role_id' => (int) $role_id ), array( '%d' ) );

		return (bool) $wpdb->delete( Schema::table( 'roles' ), array( 'id' => (int) $role_id ), array( '%d' ) );
	}

	/**
	 * Find a role by ID.
	 *
	 * @param int $role_id Role ID.
	 * @return array<string, mixed>|null
	 */
	public function find( $role_id ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . Schema::table( 'roles' ) . ' WHERE id = %d', (int) $role_id ),
			ARRAY_A
		);

		return $row ? $row : null;
	}

	/**
	 * Find a role by slug.
	 *
	 * @param string $slug Role slug.
	 * @return array<string, mixed>|null
	 */
	public function find_by_slug( $slug ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . Schema::table( 'roles' ) . ' WHERE slug = %s', sanitize_key( $slug ) ),
			ARRAY_A
		);

		return $row ? $row : null;
	}

	/**
	 * Get all roles.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function all() {
		global $wpdb;

		return $wpdb->get_results( 'SELECT * FROM ' . Schema::table( 'roles' ) . ' ORDER BY name ASC', ARRAY_A );
	}

	/**
	 * Assign a permission to a role with scope.
	 *
	 * @param int    $role_id       Role ID.
	 * @param int    $permission_id Permission ID.
	 * @param string $scope_type    Scope type.
	 * @param mixed  $scope_value   Scope value (stored as JSON).
	 * @return bool
	 */
	public function assign_permission( $role_id, $permission_id, $scope_type = 'all', $scope_value = null ) {
		global $wpdb;

		$scope_type = sanitize_key( $scope_type );

		if ( ! $this->is_valid_scope_type( $scope_type ) ) {
			return false;
		}

		$table = Schema::table( 'role_permissions' );
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE role_id = %d AND permission_id = %d",
				(int) $role_id,
				(int) $permission_id
			)
		);

		$data = array(
			'scope_type'  => sanitize_key( $scope_type ),
			'scope_value' => null !== $scope_value ? wp_json_encode( $scope_value ) : null,
		);

		if ( $existing ) {
			return (bool) $wpdb->update(
				$table,
				$data,
				array( 'id' => (int) $existing ),
				array( '%s', '%s' ),
				array( '%d' )
			);
		}

		$data['role_id']       = (int) $role_id;
		$data['permission_id'] = (int) $permission_id;

		return (bool) $wpdb->insert(
			$table,
			$data,
			array( '%d', '%d', '%s', '%s' )
		);
	}

	/**
	 * Remove a permission from a role.
	 *
	 * @param int $role_id       Role ID.
	 * @param int $permission_id Permission ID.
	 * @return bool
	 */
	public function revoke_permission( $role_id, $permission_id ) {
		global $wpdb;

		return (bool) $wpdb->delete(
			Schema::table( 'role_permissions' ),
			array(
				'role_id'       => (int) $role_id,
				'permission_id' => (int) $permission_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * Sync role permissions to a target set of permission IDs.
	 *
	 * Grants missing permissions, revokes removed ones, and preserves scope
	 * for permissions that remain assigned.
	 *
	 * @param int           $role_id        Role ID.
	 * @param array<int>    $permission_ids Permission IDs to keep granted.
	 * @param string        $default_scope  Scope for newly granted permissions.
	 * @return array{granted: int, revoked: int}
	 */
	public function sync_permissions( $role_id, array $permission_ids, $default_scope = 'all' ) {
		$permission_ids = array_values( array_unique( array_filter( array_map( 'intval', $permission_ids ) ) ) );
		$current        = $this->get_permissions( $role_id );
		$current_ids    = array_map(
			static function ( $perm ) {
				return (int) $perm['permission_id'];
			},
			$current
		);

		$to_grant = array_diff( $permission_ids, $current_ids );
		$to_revoke = array_diff( $current_ids, $permission_ids );
		$granted   = 0;
		$revoked   = 0;

		foreach ( $to_grant as $permission_id ) {
			if ( $this->assign_permission( $role_id, $permission_id, $default_scope ) ) {
				$granted++;
			}
		}

		foreach ( $to_revoke as $permission_id ) {
			if ( $this->revoke_permission( $role_id, $permission_id ) ) {
				$revoked++;
			}
		}

		return array(
			'granted' => $granted,
			'revoked' => $revoked,
		);
	}

	/**
	 * Get permissions assigned to a role.
	 *
	 * @param int $role_id Role ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_permissions( $role_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT rp.*, p.permission_key, p.module, p.resource, p.action, p.description
			FROM ' . Schema::table( 'role_permissions' ) . ' rp
			INNER JOIN ' . Schema::table( 'permissions' ) . ' p ON p.id = rp.permission_id
			WHERE rp.role_id = %d',
			(int) $role_id
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Assign a role to a user.
	 *
	 * @param int $user_id User ID.
	 * @param int $role_id Role ID.
	 * @return bool
	 */
	public function assign_to_user( $user_id, $role_id ) {
		global $wpdb;

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT id FROM ' . Schema::table( 'user_roles' ) . ' WHERE user_id = %d AND role_id = %d',
				(int) $user_id,
				(int) $role_id
			)
		);

		if ( $existing ) {
			return true;
		}

		return (bool) $wpdb->insert(
			Schema::table( 'user_roles' ),
			array(
				'user_id'     => (int) $user_id,
				'role_id'     => (int) $role_id,
				'assigned_at' => current_time( 'mysql', true ),
			),
			array( '%d', '%d', '%s' )
		);
	}

	/**
	 * Remove a role from a user.
	 *
	 * @param int $user_id User ID.
	 * @param int $role_id Role ID.
	 * @return bool
	 */
	public function revoke_from_user( $user_id, $role_id ) {
		global $wpdb;

		return (bool) $wpdb->delete(
			Schema::table( 'user_roles' ),
			array(
				'user_id' => (int) $user_id,
				'role_id' => (int) $role_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * Get roles assigned to a user.
	 *
	 * @param int $user_id User ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_user_roles( $user_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT r.*, ur.assigned_at
			FROM ' . Schema::table( 'user_roles' ) . ' ur
			INNER JOIN ' . Schema::table( 'roles' ) . ' r ON r.id = ur.role_id
			WHERE ur.user_id = %d AND r.status = %s
			ORDER BY r.name ASC',
			(int) $user_id,
			'active'
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get users assigned to a role.
	 *
	 * @param int $role_id Role ID.
	 * @param int $limit   Result limit.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_users_with_role( $role_id, $limit = 50 ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT u.ID as id, u.user_login as username, u.user_email as email, u.display_name
			FROM ' . Schema::table( 'user_roles' ) . ' ur
			INNER JOIN ' . $wpdb->users . ' u ON u.ID = ur.user_id
			WHERE ur.role_id = %d
			ORDER BY u.display_name ASC
			LIMIT %d',
			(int) $role_id,
			(int) $limit
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Validate a scope type slug.
	 *
	 * @param string $scope_type Scope type.
	 * @return bool
	 */
	private function is_valid_scope_type( $scope_type ) {
		$valid = array( 'all', 'own', 'department', 'team', 'project', 'organization', 'custom' );

		/**
		 * Filter valid scope types for permission assignment.
		 *
		 * @param array<int, string> $valid Valid scope type slugs.
		 */
		$valid = apply_filters( 'mpp_valid_scope_types', $valid );

		return in_array( $scope_type, $valid, true );
	}
}
