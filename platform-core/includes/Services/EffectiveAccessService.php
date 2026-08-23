<?php
/**
 * Effective access explanation for users and permissions.
 *
 * @package PlatformCore
 */

namespace MPP\Services;

use MPP\ACL\AclEngine;
use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;
use MPP\ACL\ScopeResolver;

defined( 'ABSPATH' ) || exit;

/**
 * Class EffectiveAccessService
 */
class EffectiveAccessService {

	/**
	 * ACL engine.
	 *
	 * @var AclEngine
	 */
	private $acl;

	/**
	 * Role manager.
	 *
	 * @var RoleManager
	 */
	private $roles;

	/**
	 * Permission registry.
	 *
	 * @var PermissionRegistry
	 */
	private $registry;

	/**
	 * Scope resolver.
	 *
	 * @var ScopeResolver
	 */
	private $scopes;

	/**
	 * Constructor.
	 *
	 * @param AclEngine          $acl      ACL engine.
	 * @param RoleManager        $roles    Role manager.
	 * @param PermissionRegistry $registry Permission registry.
	 * @param ScopeResolver      $scopes   Scope resolver.
	 */
	public function __construct( AclEngine $acl, RoleManager $roles, PermissionRegistry $registry, ScopeResolver $scopes ) {
		$this->acl      = $acl;
		$this->roles    = $roles;
		$this->registry = $registry;
		$this->scopes   = $scopes;
	}

	/**
	 * Permission summary stats for admin UI.
	 *
	 * @return array<string, int>
	 */
	public function get_permission_stats() {
		global $wpdb;

		$table = \MPP\Database\Schema::table( 'permissions' );
		$rows  = $wpdb->get_results( "SELECT module FROM {$table}", ARRAY_A );
		$total = count( $rows );
		$core  = 0;

		foreach ( $rows as $row ) {
			if ( 'core' === $row['module'] ) {
				$core++;
			}
		}

		$module_slugs = array();
		foreach ( $rows as $row ) {
			if ( 'core' !== $row['module'] ) {
				$module_slugs[ $row['module'] ] = true;
			}
		}

		return array(
			'total'          => $total,
			'core'           => $core,
			'module'         => max( 0, $total - $core ),
			'active_modules' => count( $module_slugs ),
		);
	}

	/**
	 * Roles that grant a permission.
	 *
	 * @param int $permission_id Permission ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_roles_using_permission( $permission_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT r.id, r.name, r.slug, rp.scope_type
			FROM ' . \MPP\Database\Schema::table( 'role_permissions' ) . ' rp
			INNER JOIN ' . \MPP\Database\Schema::table( 'roles' ) . ' r ON r.id = rp.role_id
			WHERE rp.permission_id = %d AND r.status = %s
			ORDER BY r.name ASC',
			(int) $permission_id,
			'active'
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Count users with a specific platform role.
	 *
	 * @param int $role_id Role ID.
	 * @return int
	 */
	public function count_users_with_role( $role_id ) {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(DISTINCT user_id) FROM ' . \MPP\Database\Schema::table( 'user_roles' ) . ' WHERE role_id = %d',
				(int) $role_id
			)
		);
	}

	/**
	 * Explain effective access for a user across permissions.
	 *
	 * @param int                    $user_id User ID.
	 * @param array<int, string>|null $keys   Optional permission keys to check.
	 * @return array<int, array<string, mixed>>
	 */
	public function explain_user_access( $user_id, array $keys = null ) {
		$user_id = (int) $user_id;
		$tree    = $this->registry->get_grouped();
		$results = array();

		foreach ( $tree as $module => $resources ) {
			foreach ( $resources as $actions ) {
				foreach ( $actions as $action ) {
					$key = $action['key'];

					if ( null !== $keys && ! in_array( $key, $keys, true ) ) {
						continue;
					}

					$results[] = $this->explain_permission( $user_id, $key, $action );
				}
			}
		}

		return $results;
	}

	/**
	 * Explain access for a single permission.
	 *
	 * @param int                  $user_id User ID.
	 * @param string               $key     Permission key.
	 * @param array<string, mixed> $meta    Optional permission metadata.
	 * @return array<string, mixed>
	 */
	public function explain_permission( $user_id, $key, array $meta = array() ) {
		$user_id = (int) $user_id;
		$row     = $this->registry->find_by_key( $key );

		if ( ! $row && empty( $meta ) ) {
			return array(
				'permission_key' => $key,
				'granted'        => false,
				'status'         => 'denied',
				'reason'         => __( 'Permission does not exist.', 'platform-core' ),
				'sources'        => array(),
			);
		}

		if ( empty( $meta ) ) {
			$meta = array(
				'id'          => (int) $row['id'],
				'key'         => $row['permission_key'],
				'description' => $row['description'],
				'module'      => $row['module'],
			);
		}

		$granted  = $this->acl->can( $user_id, $key );
		$sources  = array();
		$user_roles = $this->roles->get_user_roles( $user_id );

		if ( $granted && 0 === strpos( $key, 'core.' ) && function_exists( 'user_can' ) && user_can( $user_id, 'manage_options' ) ) {
			$sources[] = array(
				'type'       => 'effective_admin',
				'role_name'  => __( 'WordPress Administrator', 'platform-core' ),
				'scope_type' => 'all',
				'scope_label'=> $this->get_scope_label( 'all' ),
			);
		}

		foreach ( $user_roles as $role ) {
			foreach ( $this->roles->get_permissions( (int) $role['id'] ) as $rp ) {
				if ( $rp['permission_key'] !== $key ) {
					continue;
				}

				$scope_type = $rp['scope_type'];
				$route_ok   = $this->scopes->allows( $scope_type, $rp['scope_value'] ?? null, $user_id, array() );

				$sources[] = array(
					'type'        => 'role',
					'role_id'     => (int) $role['id'],
					'role_name'   => $role['name'],
					'scope_type'  => $scope_type,
					'scope_label' => $this->get_scope_label( $scope_type ),
					'route_level' => $route_ok,
				);
			}
		}

		$reason = '';
		if ( ! $granted ) {
			if ( empty( $user_roles ) ) {
				$reason = __( 'User has no active platform roles.', 'platform-core' );
			} elseif ( empty( $sources ) ) {
				$reason = sprintf(
					/* translators: %s: permission key */
					__( 'Missing permission: %s', 'platform-core' ),
					$key
				);
			} else {
				$reason = __( 'Permission is assigned but scope requirements are not met for route-level access.', 'platform-core' );
			}
		}

		return array(
			'permission_id'  => (int) ( $meta['id'] ?? 0 ),
			'permission_key' => $key,
			'description'    => $meta['description'] ?? '',
			'module'         => $meta['module'] ?? ( $row['module'] ?? '' ),
			'granted'        => $granted,
			'status'         => $granted ? 'granted' : 'denied',
			'reason'         => $reason,
			'sources'        => $sources,
		);
	}

	/**
	 * Get human-readable scope label.
	 *
	 * @param string $scope_type Scope slug.
	 * @return string
	 */
	public function get_scope_label( $scope_type ) {
		$types = $this->scopes->get_scope_types();

		return $types[ $scope_type ] ?? ucfirst( $scope_type );
	}

	/**
	 * List implemented scope types for admin UI.
	 *
	 * @return array<string, bool>
	 */
	public function get_scope_availability() {
		return array(
			'all'          => true,
			'own'          => true,
			'department'   => false,
			'team'         => false,
			'project'      => false,
			'organization' => false,
			'custom'       => false,
		);
	}
}
