<?php
/**
 * ACL engine — central permission checking.
 *
 * @package PlatformCore
 */

namespace MPP\ACL;

defined( 'ABSPATH' ) || exit;

/**
 * Class AclEngine
 */
class AclEngine {

	/**
	 * Permission registry.
	 *
	 * @var PermissionRegistry
	 */
	private $registry;

	/**
	 * Role manager.
	 *
	 * @var RoleManager
	 */
	private $roles;

	/**
	 * Scope resolver.
	 *
	 * @var ScopeResolver
	 */
	private $scopes;

	/**
	 * Per-request permission cache.
	 *
	 * @var array<string, bool>
	 */
	private $cache = array();

	/**
	 * Constructor.
	 *
	 * @param PermissionRegistry $registry Permission registry.
	 * @param RoleManager        $roles    Role manager.
	 * @param ScopeResolver      $scopes   Scope resolver.
	 */
	public function __construct( PermissionRegistry $registry, RoleManager $roles, ScopeResolver $scopes ) {
		$this->registry = $registry;
		$this->roles    = $roles;
		$this->scopes   = $scopes;
	}

	/**
	 * Check if a user has a permission.
	 *
	 * @param int                  $user_id    User ID.
	 * @param string               $permission Permission key.
	 * @param array<string, mixed> $context    Access context for scope checks.
	 * @return bool
	 */
	public function can( $user_id, $permission, array $context = array() ) {
		$user_id    = (int) $user_id;
		$permission = sanitize_text_field( $permission );

		if ( 0 === $user_id ) {
			return false;
		}

		$cache_key = $user_id . ':' . $permission . ':' . md5( wp_json_encode( $context ) );

		if ( isset( $this->cache[ $cache_key ] ) ) {
			return $this->cache[ $cache_key ];
		}

		/**
		 * Filter permission check before ACL evaluation.
		 *
		 * @param null|bool            $result     Override result, or null to continue.
		 * @param int                  $user_id    User ID.
		 * @param string               $permission Permission key.
		 * @param array<string, mixed> $context    Context.
		 */
		$override = apply_filters( 'mpp_pre_can', null, $user_id, $permission, $context );

		if ( null !== $override ) {
			$this->cache[ $cache_key ] = (bool) $override;
			return $this->cache[ $cache_key ];
		}

		$allowed = $this->evaluate( $user_id, $permission, $context );

		/**
		 * Filter final permission result.
		 *
		 * @param bool                 $allowed    Whether access is allowed.
		 * @param int                  $user_id    User ID.
		 * @param string               $permission Permission key.
		 * @param array<string, mixed> $context    Context.
		 */
		$allowed = (bool) apply_filters( 'mpp_can', $allowed, $user_id, $permission, $context );

		$this->cache[ $cache_key ] = $allowed;

		return $allowed;
	}

	/**
	 * Check if user has any of the given permissions.
	 *
	 * @param int                  $user_id     User ID.
	 * @param array<int, string>   $permissions Permission keys.
	 * @param array<string, mixed> $context     Context.
	 * @return bool
	 */
	public function can_any( $user_id, array $permissions, array $context = array() ) {
		foreach ( $permissions as $permission ) {
			if ( $this->can( $user_id, $permission, $context ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if user has all of the given permissions.
	 *
	 * @param int                  $user_id     User ID.
	 * @param array<int, string>   $permissions Permission keys.
	 * @param array<string, mixed> $context     Context.
	 * @return bool
	 */
	public function can_all( $user_id, array $permissions, array $context = array() ) {
		foreach ( $permissions as $permission ) {
			if ( ! $this->can( $user_id, $permission, $context ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get all effective permissions for a user.
	 *
	 * @param int $user_id User ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_user_permissions( $user_id ) {
		$user_roles = $this->roles->get_user_roles( (int) $user_id );
		$permissions = array();
		$seen        = array();

		foreach ( $user_roles as $role ) {
			$role_permissions = $this->roles->get_permissions( (int) $role['id'] );

			foreach ( $role_permissions as $rp ) {
				$key = $rp['permission_key'];

				if ( isset( $seen[ $key ] ) ) {
					continue;
				}

				$seen[ $key ]  = true;
				$permissions[] = $rp;
			}
		}

		return $permissions;
	}

	/**
	 * Get accessible panel routes for a user.
	 *
	 * @param int $user_id User ID.
	 * @return array<int, string>
	 */
	public function get_accessible_panels( $user_id ) {
		$panels = array(
			'user'    => 'core.panel.user.access',
			'manager' => 'core.panel.manager.access',
			'admin'   => 'core.panel.admin.access',
		);

		$accessible = array();

		foreach ( $panels as $panel => $permission ) {
			if ( $this->can( $user_id, $permission ) ) {
				$accessible[] = $panel;
			}
		}

		return $accessible;
	}

	/**
	 * Evaluate permission against user roles.
	 *
	 * @param int                  $user_id    User ID.
	 * @param string               $permission Permission key.
	 * @param array<string, mixed> $context    Context.
	 * @return bool
	 */
	private function evaluate( $user_id, $permission, array $context ) {
		$perm_row = $this->registry->find_by_key( $permission );

		if ( ! $perm_row ) {
			return false;
		}

		$user_roles = $this->roles->get_user_roles( $user_id );

		if ( empty( $user_roles ) ) {
			return false;
		}

		foreach ( $user_roles as $role ) {
			$role_permissions = $this->roles->get_permissions( (int) $role['id'] );

			foreach ( $role_permissions as $rp ) {
				if ( (int) $rp['permission_id'] !== (int) $perm_row['id'] ) {
					continue;
				}

				$scope_value = ! empty( $rp['scope_value'] ) ? json_decode( $rp['scope_value'], true ) : null;

				if ( $this->scopes->allows( $rp['scope_type'], $scope_value, $user_id, $context ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
