<?php
/**
 * Grant default platform role access to module permissions.
 *
 * @package PlatformTasks
 */

namespace MPP\Tasks;

use MPP\ACL\PermissionRegistry;
use MPP\ACL\RoleManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class ModuleAccess
 */
class ModuleAccess {

	/**
	 * Assign permission keys to default manager/admin roles.
	 *
	 * @param array<int, string> $permission_keys Permission keys.
	 */
	public static function grant_default_roles( array $permission_keys ) {
		if ( ! function_exists( 'mpp' ) ) {
			return;
		}

		$roles    = mpp()->get( RoleManager::class );
		$registry = mpp()->get( PermissionRegistry::class );

		foreach ( array( 'platform_manager', 'platform_admin' ) as $slug ) {
			$role = $roles->find_by_slug( $slug );

			if ( ! $role ) {
				continue;
			}

			$role_id = (int) $role['id'];

			foreach ( $permission_keys as $key ) {
				$permission_id = $registry->get_id_by_key( $key );

				if ( $permission_id ) {
					$roles->assign_permission( $role_id, $permission_id, 'all' );
				}
			}
		}
	}
}
